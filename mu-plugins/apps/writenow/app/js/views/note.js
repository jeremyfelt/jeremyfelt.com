// The Note view: the composer.
//
// Everything in it is saved to the device on every change and restored on
// the next launch. Text leaves the composer only once the site has
// confirmed it, or once it is safely stored in the outbox.

import * as api from '../api.js';
import * as credentials from '../credentials.js';
import * as db from '../db.js';
import * as drafts from '../drafts.js';
import { jpegName, reencode } from '../images.js';
import * as outbox from '../outbox.js';
import { busy, confirmTap, el, icon, normalizeUrl, setStatus } from '../ui.js';

const COUNT_FROM = 400;

const $ = ( id ) => document.getElementById( id );

let draft = drafts.loadNote();
const saver = drafts.createSaver( () => draft );

// Per-image progress that is not worth persisting: key -> { state, message }
const imageStates = new Map();
// Work in progress per image: key -> Promise
const imageWork = new Map();
// Object URLs for thumbnails: key -> url
const objectUrls = new Map();

let posting = false;
let nav = { show: () => {} };
let resetDiscard = () => {};

const nodes = {};

export function init( options ) {
	nav = options;
	for ( const id of [ 'composer', 'note-text', 'reply-fields', 'reply-url', 'reply-name', 'reply-toggle', 'image-button', 'image-input', 'attachments', 'note-status', 'note-count', 'note-save', 'note-post', 'note-banner', 'note-banner-text', 'note-discard' ] ) {
		nodes[ id ] = $( id );
	}

	nodes[ 'note-text' ].addEventListener( 'input', () => {
		draft.text = nodes[ 'note-text' ].value;
		saver.schedule();
		updateCount();
		clearStaleStatus();
	} );

	// iOS may refuse programmatic focus, so the whole empty composer area
	// is a tap target for the text.
	nodes.composer.addEventListener( 'click', ( event ) => {
		if ( event.target === nodes.composer ) {
			focusText( true );
		}
	} );

	nodes[ 'reply-toggle' ].addEventListener( 'click', () => {
		draft.replyOpen = ! draft.replyOpen;
		saver.schedule();
		renderReply();
		if ( draft.replyOpen ) {
			nodes[ 'reply-url' ].focus();
		} else {
			focusText( true );
		}
	} );
	nodes[ 'reply-url' ].addEventListener( 'input', () => {
		draft.replyUrl = nodes[ 'reply-url' ].value;
		saver.schedule();
	} );
	nodes[ 'reply-name' ].addEventListener( 'input', () => {
		draft.replyName = nodes[ 'reply-name' ].value;
		saver.schedule();
	} );

	nodes[ 'image-button' ].addEventListener( 'click', () => nodes[ 'image-input' ].click() );
	nodes[ 'image-input' ].addEventListener( 'change', () => {
		const files = [ ...nodes[ 'image-input' ].files ];
		nodes[ 'image-input' ].value = '';
		addImages( files );
	} );

	nodes[ 'note-post' ].addEventListener( 'click', () => submit( 'publish' ) );
	nodes[ 'note-save' ].addEventListener( 'click', () => submit( 'draft' ) );

	resetDiscard = confirmTap( nodes[ 'note-discard' ], {
		confirmLabel: 'Tap again to discard',
		onConfirm: discardChanges,
	} );

	// Save immediately whenever the app might be suspended or closed.
	const flushNow = () => saver.flush();
	document.addEventListener( 'visibilitychange', () => {
		if ( document.visibilityState === 'hidden' ) {
			flushNow();
		}
	} );
	window.addEventListener( 'pagehide', flushNow );

	renderAll();
	resumeImageWork();
}

export function focusText( toEnd = false ) {
	const text = nodes[ 'note-text' ];
	text.focus( { preventScroll: true } );
	if ( toEnd ) {
		const end = text.value.length;
		text.setSelectionRange( end, end );
	}
}

export function prefill( text ) {
	const incoming = String( text || '' ).trim();
	if ( ! incoming ) {
		return;
	}
	draft.text = draft.text.trim() ? `${ draft.text.replace( /\s+$/, '' ) }\n\n${ incoming }` : incoming;
	drafts.saveNote( draft );
	renderAll();
}

export function hasUnsavedWork() {
	return drafts.isChanged( draft );
}

export function currentNoteId() {
	return draft.noteId;
}

function softLimit() {
	const status = credentials.getStatus();
	return ( status && status.note_soft_limit ) || 500;
}

function renderAll() {
	nodes[ 'note-text' ].value = draft.text;
	renderReply();
	renderBanner();
	renderAttachments();
	updateCount();
}

function renderReply() {
	nodes[ 'reply-fields' ].hidden = ! draft.replyOpen;
	nodes[ 'reply-toggle' ].setAttribute( 'aria-pressed', String( draft.replyOpen ) );
	nodes[ 'reply-url' ].value = draft.replyUrl;
	nodes[ 'reply-name' ].value = draft.replyName;
}

function renderBanner() {
	const editing = Boolean( draft.noteId );
	nodes[ 'note-banner' ].hidden = ! editing;
	if ( editing ) {
		nodes[ 'note-banner-text' ].replaceChildren(
			el( 'span', { class: 'banner-label', text: 'Editing a draft' } ),
			el( 'span', { class: 'banner-name', text: draft.noteLabel ? `“${ draft.noteLabel }”` : '' } )
		);
	}
	resetDiscard();
}

function updateCount() {
	const count = [ ...draft.text ].length;
	const limit = softLimit();
	const node = nodes[ 'note-count' ];
	node.hidden = count <= COUNT_FROM;
	node.classList.toggle( 'is-over', count > limit );
	node.replaceChildren(
		el( 'span', { 'aria-hidden': 'true', text: `${ count }/${ limit }` } ),
		el( 'span', {
			class: 'visually-hidden',
			text: count > limit ? `${ count } characters, over the usual ${ limit } for Mastodon` : `${ count } of ${ limit } characters`,
		} )
	);
}

function clearStaleStatus() {
	// A confirmation from the last post goes away once the next note starts.
	const status = nodes[ 'note-status' ];
	if ( status.classList.contains( 'is-success' ) || status.dataset.transient === '1' ) {
		setStatus( status, null );
		delete status.dataset.transient;
	}
}

// ---- Images ----

function imageState( image ) {
	if ( imageStates.has( image.key ) ) {
		return imageStates.get( image.key );
	}
	return image.mediaId ? { state: 'uploaded' } : { state: 'waiting' };
}

function setImageState( key, state, message = '' ) {
	imageStates.set( key, { state, message } );
	const item = nodes.attachments.querySelector( `[data-key="${ CSS.escape( key ) }"]` );
	if ( item ) {
		updateImageItem( item, draft.images.find( ( image ) => image.key === key ) );
	}
}

const STATE_TEXT = {
	preparing: 'Preparing…',
	uploading: 'Uploading…',
	uploaded: 'Uploaded',
	waiting: 'Uploads when you post',
	failed: 'Upload failed',
	error: 'Couldn’t read this image',
};

function updateImageItem( item, image ) {
	if ( ! image ) {
		return;
	}
	const { state, message } = imageState( image );
	item.dataset.state = state;
	const label = item.querySelector( '.attachment-state' );
	label.textContent = message || STATE_TEXT[ state ] || '';
}

async function thumbnailFor( image ) {
	if ( image.key ) {
		if ( objectUrls.has( image.key ) ) {
			return objectUrls.get( image.key );
		}
		const blob = await db.getBlob( image.key );
		if ( blob ) {
			const url = URL.createObjectURL( blob );
			objectUrls.set( image.key, url );
			return url;
		}
	}
	return image.thumbnail || '';
}

function renderAttachments() {
	const list = nodes.attachments;
	list.replaceChildren();
	list.hidden = draft.images.length === 0;
	draft.images.forEach( ( image, index ) => {
		if ( ! image.key ) {
			image.key = `media-${ image.mediaId }`;
		}
		const n = index + 1;
		const altId = `alt-${ image.key }`;
		const thumb = el( 'img', { class: 'attachment-thumb', alt: '', width: 64, height: 64 } );
		thumbnailFor( image ).then( ( url ) => {
			if ( url ) {
				thumb.src = url;
			}
		} );
		const alt = el( 'input', {
			id: altId,
			class: 'attachment-alt',
			value: image.alt || '',
			placeholder: 'Describe this image',
			autocomplete: 'off',
		} );
		alt.addEventListener( 'input', () => {
			image.alt = alt.value;
			saver.schedule();
		} );
		const remove = el(
			'button',
			{ type: 'button', class: 'icon-button attachment-remove', 'aria-label': `Remove image ${ n }`, onclick: () => removeImage( image.key ) },
			icon( 'close' )
		);
		const item = el(
			'li',
			{ class: 'attachment', dataset: { key: image.key } },
			el( 'div', { class: 'attachment-media' }, thumb, el( 'span', { class: 'attachment-progress', 'aria-hidden': 'true' } ) ),
			el(
				'div',
				{ class: 'attachment-body' },
				el( 'label', { for: altId, class: 'attachment-label', text: `Alt text for image ${ n }` } ),
				alt,
				el( 'span', { class: 'attachment-state', 'aria-live': 'polite' } )
			),
			remove
		);
		updateImageItem( item, image );
		list.append( item );
	} );
}

async function addImages( files ) {
	const images = files.filter( ( file ) => file.type.startsWith( 'image/' ) || /\.(heic|heif|jpe?g|png|gif|webp)$/i.test( file.name ) );
	if ( ! images.length ) {
		if ( files.length ) {
			setStatus( nodes[ 'note-status' ], { tone: 'error', text: 'That file isn’t an image WriteNow can attach.' } );
		}
		return;
	}
	const added = images.map( ( file ) => {
		const image = { key: db.uuid(), name: jpegName( file.name ), alt: '', mediaId: null, thumbnail: '' };
		imageStates.set( image.key, { state: 'preparing' } );
		draft.images.push( image );
		return { image, file };
	} );
	drafts.saveNote( draft );
	renderAttachments();
	setStatus( nodes[ 'note-status' ], null );

	for ( const { image, file } of added ) {
		const work = processImage( image, file );
		imageWork.set( image.key, work );
		work.finally( () => imageWork.delete( image.key ) );
	}
}

async function processImage( image, file ) {
	try {
		const { blob } = await reencode( file );
		if ( ! draft.images.includes( image ) ) {
			return;
		}
		await db.putBlob( image.key, blob, image.name );
		const url = URL.createObjectURL( blob );
		objectUrls.set( image.key, url );
		const thumb = nodes.attachments.querySelector( `[data-key="${ CSS.escape( image.key ) }"] img` );
		if ( thumb ) {
			thumb.src = url;
		}
	} catch {
		setImageState( image.key, 'error' );
		return;
	}
	await uploadImage( image );
}

async function uploadImage( image ) {
	if ( ! credentials.isConnected() || navigator.onLine === false ) {
		setImageState( image.key, 'waiting' );
		return;
	}
	setImageState( image.key, 'uploading' );
	try {
		const blob = await db.getBlob( image.key );
		if ( ! blob ) {
			setImageState( image.key, 'error' );
			return;
		}
		const uploaded = await api.uploadMedia( blob, image.name );
		if ( ! draft.images.includes( image ) ) {
			return;
		}
		image.mediaId = uploaded.id;
		image.thumbnail = uploaded.thumbnail;
		drafts.saveNote( draft );
		setImageState( image.key, 'uploaded' );
	} catch ( error ) {
		if ( error.retryable || error.status === 401 ) {
			setImageState( image.key, 'waiting' );
		} else {
			setImageState( image.key, 'failed', `Upload failed: ${ error.message }` );
		}
	}
}

// After a reload, images that were stored but not uploaded try again.
function resumeImageWork() {
	for ( const image of draft.images ) {
		if ( image.key && ! image.mediaId ) {
			const work = uploadImage( image );
			imageWork.set( image.key, work );
			work.finally( () => imageWork.delete( image.key ) );
		}
	}
}

export function retryWaitingUploads() {
	for ( const image of draft.images ) {
		if ( image.key && ! image.mediaId && ! imageWork.has( image.key ) && imageState( image ).state === 'waiting' ) {
			const work = uploadImage( image );
			imageWork.set( image.key, work );
			work.finally( () => imageWork.delete( image.key ) );
		}
	}
}

async function removeImage( key ) {
	const index = draft.images.findIndex( ( image ) => image.key === key );
	if ( index === -1 ) {
		return;
	}
	const [ image ] = draft.images.splice( index, 1 );
	drafts.saveNote( draft );
	imageStates.delete( key );
	if ( objectUrls.has( key ) ) {
		URL.revokeObjectURL( objectUrls.get( key ) );
		objectUrls.delete( key );
	}
	renderAttachments();
	const next = nodes.attachments.querySelectorAll( '.attachment-remove' )[ Math.min( index, draft.images.length - 1 ) ];
	( next || nodes[ 'image-button' ] ).focus();
	setStatus( nodes[ 'note-status' ], 'Image removed.' );
	nodes[ 'note-status' ].dataset.transient = '1';
	await db.removeBlob( image.key );
}

// ---- Posting ----

// The tapped button shows its working label at once; the other one waits.
function setPostingUi( status ) {
	const post = nodes[ 'note-post' ];
	const save = nodes[ 'note-save' ];
	const active = status === 'publish' ? post : save;
	const other = status === 'publish' ? save : post;
	const restoreActive = busy( active, status === 'publish' ? 'Posting…' : 'Saving…' );
	other.disabled = true;
	return () => {
		restoreActive();
		other.disabled = false;
	};
}

async function submit( status ) {
	if ( posting ) {
		return;
	}
	const statusNode = nodes[ 'note-status' ];
	delete statusNode.dataset.transient;
	saver.flush();

	if ( ! draft.text.trim() && ! draft.images.length ) {
		setStatus( statusNode, { tone: 'error', text: 'Write something or add an image first.' } );
		focusText();
		return;
	}
	let replyUrl = '';
	if ( draft.replyOpen && draft.replyUrl.trim() ) {
		replyUrl = normalizeUrl( draft.replyUrl );
		if ( ! replyUrl ) {
			setStatus( statusNode, { tone: 'error', text: 'The reply needs a web address that starts with http:// or https://.' } );
			nodes[ 'reply-url' ].focus();
			return;
		}
	}

	posting = true;
	const restore = setPostingUi( status );
	const publishing = status === 'publish';
	setStatus( statusNode, publishing ? 'Posting…' : 'Saving the draft…' );

	try {
		// Wait only for images still attached; a removed one may still be
		// finishing its upload in the background.
		const pending = draft.images.filter( ( image ) => imageWork.has( image.key ) ).map( ( image ) => imageWork.get( image.key ) );
		if ( pending.length ) {
			setStatus( statusNode, 'Finishing the images first…' );
			await Promise.allSettled( pending );
			setStatus( statusNode, publishing ? 'Posting…' : 'Saving the draft…' );
		}
		const unreadable = draft.images.findIndex( ( image ) => imageState( image ).state === 'error' );
		if ( unreadable !== -1 ) {
			setStatus( statusNode, { tone: 'error', text: `Image ${ unreadable + 1 } couldn’t be read. Remove it, then try again.` } );
			return;
		}

		const clientId = draft.clientId;
		const payload = {
			content: draft.text,
			status,
			reply_to_url: replyUrl,
			reply_to_name: replyUrl ? draft.replyName.trim() : '',
			noteId: draft.noteId,
			images: draft.images.map( ( image ) => ( {
				key: image.key && ! image.key.startsWith( 'media-' ) ? image.key : null,
				name: image.name,
				alt: image.alt || '',
				mediaId: image.mediaId,
				thumbnail: image.thumbnail,
			} ) ),
		};

		// What leaves the composer if this works. Anything typed or attached
		// while the request is in flight stays behind.
		const sent = { text: draft.text, keys: new Set( draft.images.map( ( image ) => image.key ) ) };

		await outbox.enqueue( 'note', clientId, payload );
		const result = await outbox.send( clientId );
		handleResult( result, status, sent );
	} catch {
		setStatus( statusNode, {
			tone: 'error',
			text: 'Something went wrong on this phone. Your note is still here.',
			actions: [ { label: 'Retry', onClick: () => submit( status ) } ],
		} );
	} finally {
		posting = false;
		restore();
	}
}

function handleResult( result, status, sent ) {
	const statusNode = nodes[ 'note-status' ];
	const publishing = status === 'publish';
	const since = 'What you typed since is still here.';

	switch ( result.outcome ) {
		case 'sent':
		case 'gone': {
			const data = result.data || {};
			const message = data.message || ( publishing ? 'Posted.' : 'Saved as a draft.' );
			const kept = clearSent( sent );
			if ( data.status === 'publish' || ( publishing && data.status !== 'draft' && data.status !== 'future' ) ) {
				setStatus( statusNode, {
					tone: 'success',
					text: kept ? `${ message } ${ since }` : message,
					actions: data.link ? [ { label: 'View', href: data.link } ] : [],
				} );
			} else {
				setStatus( statusNode, {
					tone: 'success',
					text: `${ message } It’s in Recent.${ kept ? ` ${ since }` : '' }`,
					actions: [ { label: 'Open Recent', onClick: () => nav.show( 'recent' ) } ],
				} );
			}
			focusText();
			break;
		}
		case 'queued':
		case 'busy': {
			const kept = clearSent( sent );
			setStatus( statusNode, {
				tone: 'info',
				text: `${ result.error ? result.error.message + ' ' : '' }Your ${ publishing ? 'note' : 'draft' } is saved on this phone and will send when you’re back online or next open WriteNow.${ kept ? ` ${ since }` : '' }`,
				actions: [ { label: 'See Recent', onClick: () => nav.show( 'recent' ) } ],
			} );
			break;
		}
		case 'unauthorized':
			keepAfterFailure( result );
			setStatus( statusNode, { tone: 'error', text: 'WriteNow needs to connect again. Your note is still here.' } );
			break;
		case 'failed':
		default: {
			keepAfterFailure( result );
			const error = result.error;
			const actions = [ { label: 'Retry', onClick: () => submit( status ) } ];
			if ( error && error.code === 'writenow_not_editable' && draft.editLink ) {
				actions.unshift( { label: 'Open in WordPress', href: draft.editLink } );
			}
			setStatus( statusNode, {
				tone: 'error',
				text: `Couldn’t ${ publishing ? 'post' : 'save' }: ${ error ? error.message : 'the site said no.' } Your note is still here.`,
				actions,
			} );
		}
	}
}

// The site refused: take the item back out of the outbox (keeping its
// images), and keep anything it did upload so a retry does not repeat it.
function keepAfterFailure( result ) {
	const item = result.item;
	if ( item ) {
		for ( const sent of item.payload.images || [] ) {
			const image = draft.images.find( ( candidate ) => sent.key && candidate.key === sent.key );
			if ( image && sent.mediaId ) {
				image.mediaId = sent.mediaId;
				image.thumbnail = sent.thumbnail;
				imageStates.set( image.key, { state: 'uploaded' } );
			}
		}
		outbox.discard( item.id, { keepBlobs: true } );
	}
	drafts.saveNote( draft );
	renderAttachments();
}

// The site (or the outbox) now holds what was sent. Clear it from the
// composer, but keep anything typed or attached after Post was tapped, as
// the start of a new note. Returns true when something was kept.
function clearSent( sent ) {
	const current = draft;
	const added = current.images.filter( ( image ) => ! sent.keys.has( image.key ) );
	if ( current.text === sent.text && ! added.length ) {
		clearComposer( { keepBlobs: true } );
		return false;
	}
	for ( const key of sent.keys ) {
		if ( objectUrls.has( key ) ) {
			URL.revokeObjectURL( objectUrls.get( key ) );
			objectUrls.delete( key );
		}
		imageStates.delete( key );
	}
	const next = drafts.emptyNote();
	next.text = current.text.startsWith( sent.text ) ? current.text.slice( sent.text.length ).replace( /^\s+/, '' ) : current.text;
	next.images = added;
	draft = next;
	drafts.saveNote( draft );
	renderAll();
	return Boolean( next.text.trim() || next.images.length );
}

function clearComposer( { keepBlobs } ) {
	const old = draft;
	for ( const url of objectUrls.values() ) {
		URL.revokeObjectURL( url );
	}
	objectUrls.clear();
	imageStates.clear();
	draft = drafts.emptyNote();
	drafts.saveNote( draft );
	if ( ! keepBlobs ) {
		for ( const image of old.images ) {
			db.removeBlob( image.key );
		}
	}
	renderAll();
}

// ---- Drafts from the site ----

function excerpt( text ) {
	const line = String( text || '' ).replace( /\s+/g, ' ' ).trim();
	return line.length > 48 ? line.slice( 0, 47 ) + '…' : line;
}

// Open a draft from Recent. Resolves to { ok, message?, href? }.
export async function openDraft( id ) {
	if ( draft.noteId === id ) {
		nav.show( 'note' );
		return { ok: true };
	}
	if ( drafts.isChanged( draft ) ) {
		return { ok: false, message: 'Your note in progress isn’t empty. Post it or save it as a draft first, then open this one.' };
	}
	let note;
	try {
		note = await api.getNote( id );
	} catch ( error ) {
		return { ok: false, message: `Couldn’t open this draft: ${ error.message }` };
	}
	if ( ! note || ! note.editable ) {
		return { ok: false, message: 'This note can’t be edited in WriteNow.', href: note && note.edit_link, hrefLabel: 'Open in WordPress' };
	}
	clearComposer( { keepBlobs: false } );
	draft = {
		...drafts.emptyNote(),
		noteId: note.id,
		noteLabel: excerpt( note.content ) || 'Untitled',
		noteLink: note.link || '',
		editLink: note.edit_link || '',
		text: note.content || '',
		replyOpen: Boolean( note.reply_to_url ),
		replyUrl: note.reply_to_url || '',
		replyName: note.reply_to_name || '',
		images: ( note.media || [] ).map( ( media ) => ( {
			key: `media-${ media.id }`,
			name: '',
			alt: media.alt || '',
			mediaId: media.id,
			thumbnail: media.thumbnail || '',
		} ) ),
	};
	draft.baseline = drafts.snapshot( draft );
	drafts.saveNote( draft );
	renderAll();
	setStatus( nodes[ 'note-status' ], null );
	nav.show( 'note' );
	return { ok: true };
}

function discardChanges() {
	const label = draft.noteLabel;
	clearComposer( { keepBlobs: false } );
	setStatus( nodes[ 'note-status' ], {
		tone: 'info',
		text: `Changes discarded. The draft${ label ? ` “${ label }”` : '' } on your site is unchanged.`,
	} );
	nodes[ 'note-status' ].dataset.transient = '1';
	focusText();
}

// A note that waited in the outbox has gone out.
export function announceSent( item, data ) {
	const statusNode = nodes[ 'note-status' ];
	if ( statusNode.classList.contains( 'is-error' ) || posting ) {
		return;
	}
	const published = data && data.status === 'publish';
	const what = item.kind === 'like' ? 'A waiting like was sent.' : published ? 'A waiting note was posted.' : 'A waiting draft was saved.';
	setStatus( statusNode, {
		tone: 'success',
		text: what,
		actions: data && data.link && published ? [ { label: 'View', href: data.link } ] : [],
	} );
}
