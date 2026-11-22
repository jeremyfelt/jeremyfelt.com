// The Recent view: the outbox first, then the site's recent notes and
// likes. The last list loaded is kept on the device and shown at once.

import * as api from '../api.js';
import * as outbox from '../outbox.js';
import { confirmTap, el, hostOf, icon, relativeTime, setStatus } from '../ui.js';

const CACHE_KEY = 'writenow.recent';
const PULL_DISTANCE = 64;

const $ = ( id ) => document.getElementById( id );

let nav = { show: () => {}, openDraft: async () => ( { ok: false } ) };
let loading = null;

function readCache() {
	try {
		const cached = JSON.parse( localStorage.getItem( CACHE_KEY ) || 'null' );
		return cached && Array.isArray( cached.items ) ? cached : null;
	} catch {
		return null;
	}
}

function writeCache( items ) {
	try {
		localStorage.setItem( CACHE_KEY, JSON.stringify( { items, loadedAt: Date.now() } ) );
	} catch {}
}

export function init( options ) {
	nav = options;
	$( 'recent-refresh' ).addEventListener( 'click', () => refresh() );
	setUpPull();
	outbox.subscribe( ( event ) => {
		if ( event.type === 'change' ) {
			renderQueue();
		}
		if ( event.type === 'sent' && ! $( 'view-recent' ).hidden ) {
			refresh( { quiet: true } );
		}
	} );
	renderQueue();
	renderList( readCache() );
}

export function shown() {
	renderQueue();
	renderList( readCache() );
	refresh();
}

export function clearCache() {
	try {
		localStorage.removeItem( CACHE_KEY );
	} catch {}
	$( 'recent-list' ).replaceChildren();
}

// ---- The outbox ----

// Renders can overlap (every outbox change asks for one), so only the
// latest one applies. A row is rebuilt only when what it shows has changed,
// so a Remove button waiting for its second tap is not swapped out by an
// unrelated change.
let queueRender = 0;
const queueRows = new Map();

function rowSignature( item ) {
	return JSON.stringify( [ item.state, item.error, item.offline, item.summary, item.payload.status ] );
}

async function renderQueue( { force = false } = {} ) {
	const ticket = ++queueRender;
	const items = await outbox.list();
	if ( ticket !== queueRender ) {
		return;
	}
	$( 'queue' ).hidden = items.length === 0;
	const rows = items.map( ( item ) => {
		const signature = rowSignature( item );
		const existing = queueRows.get( item.id );
		if ( existing && existing.signature === signature && ! force ) {
			return existing.node;
		}
		const node = queueRow( item );
		queueRows.set( item.id, { signature, node } );
		return node;
	} );
	const ids = new Set( items.map( ( item ) => item.id ) );
	for ( const id of queueRows.keys() ) {
		if ( ! ids.has( id ) ) {
			queueRows.delete( id );
		}
	}
	const list = $( 'queue-list' );
	if ( rows.length !== list.children.length || rows.some( ( row, index ) => list.children[ index ] !== row ) ) {
		list.replaceChildren( ...rows );
	}
}

function queueRow( item ) {
	const kind = item.kind === 'like' ? 'Like' : item.payload.status === 'publish' ? 'Note' : 'Draft';
	let state;
	if ( item.state === 'sending' ) {
		state = 'Sending…';
	} else if ( item.state === 'failed' ) {
		state = `Not sent: ${ item.error }`;
	} else if ( item.error && ! item.offline ) {
		state = `Waiting: ${ item.error }`;
	} else {
		state = 'Waiting. Sends when you’re online or next open WriteNow.';
	}

	const status = el( 'p', { class: 'row-status', role: 'status' } );
	const retry = el( 'button', {
		type: 'button',
		class: 'button secondary small',
		text: 'Retry',
		onclick: async () => {
			retry.disabled = true;
			retry.textContent = 'Sending…';
			const result = await outbox.retry( item.id );
			if ( result.outcome !== 'sent' ) {
				renderQueue( { force: true } );
			}
		},
	} );
	const remove = el( 'button', { type: 'button', class: 'button text small danger', text: 'Remove' } );
	confirmTap( remove, {
		confirmLabel: 'Tap again to remove',
		onConfirm: () => outbox.discard( item.id ),
	} );

	return el(
		'li',
		{ class: `row queued is-${ item.state }`, dataset: { id: item.id } },
		el(
			'div',
			{ class: 'row-main' },
			el( 'span', { class: 'row-meta' }, el( 'span', { class: 'kind', text: kind } ), el( 'span', { class: 'badge waiting', text: item.state === 'failed' ? 'Not sent' : 'Waiting' } ) ),
			el( 'span', { class: 'row-text', text: item.summary } ),
			el( 'span', { class: 'row-note', text: state } )
		),
		el( 'div', { class: 'row-actions' }, retry, remove ),
		status
	);
}

// ---- The site's list ----

function statusLabel( entry ) {
	if ( entry.type === 'note' && entry.status === 'draft' ) {
		return 'Draft';
	}
	if ( entry.status === 'future' ) {
		return 'Scheduled';
	}
	return '';
}

function recentRow( entry ) {
	const kind = entry.type === 'like' ? 'Like' : 'Note';
	const label = statusLabel( entry );
	const text = entry.text || ( entry.type === 'like' ? entry.url : '(No text)' );
	const host = entry.type === 'like' ? hostOf( entry.url ) : '';
	const time = entry.date ? el( 'time', { datetime: entry.date, text: relativeTime( entry.date ) } ) : null;

	const meta = el(
		'span',
		{ class: 'row-meta' },
		el( 'span', { class: 'kind', text: kind } ),
		label ? el( 'span', { class: `badge ${ label.toLowerCase() }`, text: label } ) : null,
		time
	);
	const body = [ meta, el( 'span', { class: 'row-text', text } ), host ? el( 'span', { class: 'row-note', text: host } ) : null ];

	const status = el( 'p', { class: 'row-status', role: 'status' } );
	let main;
	if ( entry.type === 'note' && entry.editable ) {
		main = el( 'button', { type: 'button', class: 'row-main', 'aria-label': `Draft: ${ text }. Open in Note.` }, ...body );
		main.addEventListener( 'click', async () => {
			main.setAttribute( 'aria-busy', 'true' );
			main.classList.add( 'is-busy' );
			setStatus( status, 'Opening…' );
			const result = await nav.openDraft( entry.id );
			main.classList.remove( 'is-busy' );
			main.removeAttribute( 'aria-busy' );
			if ( result.ok ) {
				setStatus( status, null );
			} else {
				setStatus( status, {
					tone: 'error',
					text: result.message,
					actions: result.href ? [ { label: result.hrefLabel || 'Open', href: result.href } ] : [],
				} );
			}
		} );
	} else {
		main = el( 'a', { class: 'row-main', href: entry.link || '#', target: '_blank', rel: 'noopener' }, ...body );
	}

	const edit = entry.edit_link
		? el( 'a', { class: 'icon-button row-edit', href: entry.edit_link, target: '_blank', rel: 'noopener', 'aria-label': `Edit in WordPress: ${ text.slice( 0, 60 ) }` }, icon( 'edit' ) )
		: null;

	return el( 'li', { class: `row is-${ entry.type }`, dataset: { id: String( entry.id ) } }, main, edit, status );
}

function renderList( cached ) {
	const list = $( 'recent-list' );
	if ( ! cached ) {
		return;
	}
	if ( ! cached.items.length ) {
		list.replaceChildren( el( 'li', { class: 'empty', text: 'Nothing here yet. Notes and likes you post show up here.' } ) );
		return;
	}
	list.replaceChildren( ...cached.items.map( recentRow ) );
}

export function refresh( { quiet = false } = {} ) {
	if ( loading ) {
		return loading;
	}
	const status = $( 'recent-status' );
	const button = $( 'recent-refresh' );
	const cached = readCache();
	button.classList.add( 'is-spinning' );
	button.setAttribute( 'aria-busy', 'true' );
	if ( ! quiet ) {
		setStatus( status, cached ? 'Refreshing…' : 'Loading…' );
	}

	loading = ( async () => {
		try {
			const items = await api.getRecent( 20 );
			writeCache( items );
			renderList( { items } );
			setStatus( status, null );
		} catch ( error ) {
			const when = cached ? relativeTime( cached.loadedAt ) : '';
			let text;
			if ( error.offline ) {
				text = cached ? `You’re offline. This is the list from ${ when }.` : 'You’re offline, and there’s no saved list yet.';
			} else {
				text = cached ? `Couldn’t refresh: ${ error.message } This is the list from ${ when }.` : `Couldn’t load Recent: ${ error.message }`;
			}
			setStatus( status, { tone: error.offline ? 'info' : 'error', text, actions: [ { label: 'Try again', onClick: () => refresh() } ] } );
		} finally {
			button.classList.remove( 'is-spinning' );
			button.removeAttribute( 'aria-busy' );
			loading = null;
			updatePull( 0, 'idle' );
		}
	} )();
	return loading;
}

// ---- Pull to refresh ----

function updatePull( distance, state ) {
	const pull = $( 'recent-pull' );
	pull.dataset.state = state;
	pull.style.setProperty( '--pull', `${ Math.min( distance, PULL_DISTANCE * 1.5 ) }px` );
	pull.textContent = state === 'ready' ? 'Release to refresh' : state === 'pulling' ? 'Pull to refresh' : '';
}

function setUpPull() {
	const scroller = $( 'recent-scroll' );
	let startY = null;
	let distance = 0;

	scroller.addEventListener(
		'touchstart',
		( event ) => {
			startY = scroller.scrollTop <= 0 && event.touches.length === 1 ? event.touches[ 0 ].clientY : null;
			distance = 0;
		},
		{ passive: true }
	);
	scroller.addEventListener(
		'touchmove',
		( event ) => {
			if ( startY === null ) {
				return;
			}
			distance = event.touches[ 0 ].clientY - startY;
			if ( distance <= 0 || scroller.scrollTop > 0 ) {
				updatePull( 0, 'idle' );
				return;
			}
			updatePull( distance * 0.5, distance * 0.5 >= PULL_DISTANCE ? 'ready' : 'pulling' );
		},
		{ passive: true }
	);
	const end = () => {
		if ( startY !== null && distance * 0.5 >= PULL_DISTANCE ) {
			updatePull( PULL_DISTANCE, 'refreshing' );
			refresh();
		} else {
			updatePull( 0, 'idle' );
		}
		startY = null;
		distance = 0;
	};
	scroller.addEventListener( 'touchend', end, { passive: true } );
	scroller.addEventListener( 'touchcancel', () => {
		startY = null;
		updatePull( 0, 'idle' );
	} );
}
