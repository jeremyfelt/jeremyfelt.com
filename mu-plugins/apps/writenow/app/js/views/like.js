// The Like view: a URL, an optional title, and a Like button.

import { uuid } from '../db.js';
import * as drafts from '../drafts.js';
import * as outbox from '../outbox.js';
import { busy, normalizeUrl, setStatus } from '../ui.js';

const $ = ( id ) => document.getElementById( id );

let nav = { show: () => {} };
let sending = false;

export function init( options ) {
	nav = options;
	const saved = drafts.loadLike();
	$( 'like-url' ).value = saved.url;
	$( 'like-title' ).value = saved.title;

	const remember = () => drafts.saveLike( { url: $( 'like-url' ).value, title: $( 'like-title' ).value } );
	$( 'like-url' ).addEventListener( 'input', () => {
		remember();
		if ( $( 'like-status' ).classList.contains( 'is-success' ) ) {
			setStatus( $( 'like-status' ), null );
		}
	} );
	$( 'like-title' ).addEventListener( 'input', remember );

	$( 'like-paste' ).addEventListener( 'click', paste );
	$( 'like-form' ).addEventListener( 'submit', ( event ) => {
		event.preventDefault();
		submit();
	} );
}

export function prefill( url, title ) {
	$( 'like-url' ).value = url || '';
	$( 'like-title' ).value = title || '';
	drafts.saveLike( { url: url || '', title: title || '' } );
	setStatus( $( 'like-status' ), null );
}

async function paste() {
	const status = $( 'like-status' );
	// Read only now, because Jeremy tapped Paste.
	if ( ! navigator.clipboard || ! navigator.clipboard.readText ) {
		setStatus( status, { tone: 'error', text: 'This browser won’t let WriteNow read the clipboard. Press and hold the URL field and choose Paste.' } );
		return;
	}
	try {
		const text = await navigator.clipboard.readText();
		const url = normalizeUrl( text );
		if ( ! url ) {
			setStatus( status, { tone: 'error', text: 'The clipboard doesn’t hold a web address.' } );
			return;
		}
		$( 'like-url' ).value = url;
		drafts.saveLike( { url, title: $( 'like-title' ).value } );
		setStatus( status, 'Pasted.' );
	} catch {
		setStatus( status, { tone: 'error', text: 'WriteNow couldn’t read the clipboard. Press and hold the URL field and choose Paste.' } );
	}
}

async function submit() {
	if ( sending ) {
		return;
	}
	const status = $( 'like-status' );
	const url = normalizeUrl( $( 'like-url' ).value );
	if ( ! url ) {
		setStatus( status, {
			tone: 'error',
			text: $( 'like-url' ).value.trim() ? 'That doesn’t look like a web address. It should start with https://.' : 'Enter or paste the address you want to like.',
		} );
		$( 'like-url' ).focus();
		return;
	}
	const title = $( 'like-title' ).value.trim();
	const typed = { url: $( 'like-url' ).value, title: $( 'like-title' ).value };

	sending = true;
	const restore = busy( $( 'like-submit' ), 'Liking…' );
	setStatus( status, 'Liking…' );
	try {
		const id = uuid();
		await outbox.enqueue( 'like', id, { url, title } );
		const result = await outbox.send( id );
		if ( result.outcome === 'sent' || result.outcome === 'gone' ) {
			const data = result.data || {};
			clearForm( typed );
			setStatus( status, {
				tone: 'success',
				text: data.message || ( data.duplicate ? `Already liked: ${ data.title || url }` : `Liked: ${ data.title || url }` ),
				actions: data.link ? [ { label: 'View', href: data.link } ] : [],
			} );
		} else if ( result.outcome === 'queued' || result.outcome === 'busy' ) {
			clearForm( typed );
			setStatus( status, {
				tone: 'info',
				text: `${ result.error ? result.error.message + ' ' : '' }The like is saved on this phone and will send when you’re back online or next open WriteNow.`,
				actions: [ { label: 'See Recent', onClick: () => nav.show( 'recent' ) } ],
			} );
		} else {
			await outbox.discard( id );
			setStatus( status, {
				tone: 'error',
				text: result.outcome === 'unauthorized' ? 'WriteNow needs to connect again. The address is still here.' : `Couldn’t like it: ${ result.error.message }`,
				actions: result.outcome === 'unauthorized' ? [] : [ { label: 'Retry', onClick: submit } ],
			} );
		}
	} catch {
		setStatus( status, { tone: 'error', text: 'Something went wrong on this phone. The address is still here.', actions: [ { label: 'Retry', onClick: submit } ] } );
	} finally {
		sending = false;
		restore();
	}
}

// Clear the form once the like is sent or queued, unless something new was
// typed into it while it was sending.
function clearForm( typed ) {
	if ( $( 'like-url' ).value !== typed.url || $( 'like-title' ).value !== typed.title ) {
		return;
	}
	$( 'like-url' ).value = '';
	$( 'like-title' ).value = '';
	drafts.saveLike( { url: '', title: '' } );
}
