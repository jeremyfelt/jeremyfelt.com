// The outbox: posts, draft saves and likes that could not reach the site
// yet, kept in IndexedDB until they can.
//
// Every note carries its client_id, so the server returns the existing note
// if a request is repeated, and a lock per item makes sure only one request
// for an item is ever in flight, even with two windows open.
//
// iOS has no Background Sync. The outbox flushes when WriteNow launches,
// returns to the foreground, or comes back online.

import * as api from './api.js';
import * as db from './db.js';

const listeners = new Set();
const inFlight = new Set();
let flushing = null;

function emit( event ) {
	for ( const listener of listeners ) {
		try {
			listener( event );
		} catch {}
	}
}

export function subscribe( listener ) {
	listeners.add( listener );
	return () => listeners.delete( listener );
}

export async function list() {
	const items = await db.all( 'outbox' );
	return items.sort( ( a, b ) => a.createdAt - b.createdAt );
}

export function summarize( kind, payload ) {
	if ( kind === 'like' ) {
		return payload.title || payload.url;
	}
	const text = ( payload.content || '' ).replace( /\s+/g, ' ' ).trim();
	if ( text ) {
		return text.length > 140 ? text.slice( 0, 139 ) + '…' : text;
	}
	const count = ( payload.images || [] ).length;
	return count === 1 ? 'A note with 1 image' : `A note with ${ count } images`;
}

// Store an item before the first attempt, so it survives whatever happens
// during the request.
export async function enqueue( kind, id, payload ) {
	const item = {
		id,
		kind,
		payload,
		summary: summarize( kind, payload ),
		createdAt: Date.now(),
		state: 'waiting',
		error: '',
		attempts: 0,
	};
	const existing = await db.get( 'outbox', id );
	if ( existing ) {
		item.createdAt = existing.createdAt;
		item.attempts = existing.attempts;
	}
	await db.put( 'outbox', item );
	emit( { type: 'change' } );
	return item;
}

// Remove an item. Image data is deleted too unless the composer still
// needs it (`keepBlobs`).
export async function discard( id, { keepBlobs = false } = {} ) {
	const item = await db.get( 'outbox', id );
	await db.remove( 'outbox', id );
	if ( item && ! keepBlobs && item.kind === 'note' ) {
		for ( const image of item.payload.images || [] ) {
			await db.removeBlob( image.key );
		}
	}
	emit( { type: 'change' } );
}

async function withItemLock( id, fn ) {
	if ( inFlight.has( id ) ) {
		return null;
	}
	inFlight.add( id );
	try {
		if ( navigator.locks && navigator.locks.request ) {
			return await navigator.locks.request( `writenow-outbox-${ id }`, { ifAvailable: true }, ( lock ) => ( lock ? fn() : null ) );
		}
		return await fn();
	} finally {
		inFlight.delete( id );
	}
}

async function uploadImages( item ) {
	const images = item.payload.images || [];
	for ( const image of images ) {
		if ( image.mediaId ) {
			continue;
		}
		const blob = await db.getBlob( image.key );
		if ( ! blob ) {
			throw new api.ApiError( 'An image for this note is no longer on this phone. Remove it and attach it again.', { status: 410, code: 'writenow_missing_image' } );
		}
		const uploaded = await api.uploadMedia( blob, image.name || 'writenow.jpg' );
		image.mediaId = uploaded.id;
		image.thumbnail = uploaded.thumbnail;
		// Remember each upload at once so a retry never uploads it twice,
		// unless the item was removed meanwhile.
		if ( ! ( await putIfQueued( item ) ) ) {
			throw new Removed();
		}
	}
}

// Thrown when an item is removed from the outbox while it is being sent.
class Removed extends Error {}

// Write an item back only if it is still in the outbox. Remove (in Recent)
// can run while a send is in flight; writing the item back afterwards
// would bring it back, and the next flush would send what Jeremy removed.
function putIfQueued( item ) {
	return db.putIfPresent( 'outbox', item );
}

function noteBody( item ) {
	const p = item.payload;
	return {
		content: p.content || '',
		status: p.status === 'publish' ? 'publish' : 'draft',
		reply_to_url: p.reply_to_url || '',
		reply_to_name: p.reply_to_name || '',
		media: ( p.images || [] ).map( ( image ) => ( { id: image.mediaId, alt: image.alt || '' } ) ),
		client_id: item.id,
	};
}

// Try one item now. Resolves to { outcome, data?, error?, item }, where
// outcome is 'sent', 'queued' (try again later), 'failed' (the site said no
// and it will not be retried automatically), 'unauthorized', or 'busy'
// (another attempt is already running).
export async function send( id, { background = false } = {} ) {
	const result = await withItemLock( id, async () => {
		const item = await db.get( 'outbox', id );
		if ( ! item ) {
			return { outcome: 'gone' };
		}
		item.state = 'sending';
		item.attempts += 1;
		if ( ! ( await putIfQueued( item ) ) ) {
			return { outcome: 'gone' };
		}
		emit( { type: 'change' } );

		try {
			let data;
			if ( item.kind === 'like' ) {
				data = await api.like( { url: item.payload.url, ...( item.payload.title ? { title: item.payload.title } : {} ) } );
			} else {
				await uploadImages( item );
				data = await api.saveNote( noteBody( item ), item.payload.noteId );
			}
			await db.remove( 'outbox', id );
			for ( const image of item.payload.images || [] ) {
				await db.removeBlob( image.key );
			}
			emit( { type: 'sent', item, data, background } );
			emit( { type: 'change' } );
			return { outcome: 'sent', data, item };
		} catch ( error ) {
			if ( error instanceof Removed ) {
				emit( { type: 'change' } );
				return { outcome: 'gone', item };
			}
			const err = error instanceof api.ApiError ? error : new api.ApiError( 'Something went wrong while sending.', {} );
			let outcome = 'failed';
			if ( err.retryable ) {
				outcome = 'queued';
			} else if ( err.status === 401 ) {
				outcome = 'unauthorized';
			}
			item.state = outcome === 'failed' ? 'failed' : 'waiting';
			item.error = err.message;
			item.offline = err.offline;
			if ( ! ( await putIfQueued( item ) ) ) {
				emit( { type: 'change' } );
				return { outcome: 'gone', item };
			}
			emit( { type: 'change' } );
			return { outcome, error: err, item };
		}
	} );
	return result || { outcome: 'busy' };
}

// Send everything that is waiting, oldest first. Failed items wait for an
// explicit Retry. Stops at the first item that cannot get through.
export function flush() {
	if ( flushing ) {
		return flushing;
	}
	flushing = ( async () => {
		const results = [];
		try {
			for ( const item of await list() ) {
				if ( item.state === 'failed' ) {
					continue;
				}
				const result = await send( item.id, { background: true } );
				results.push( result );
				if ( result.outcome === 'queued' || result.outcome === 'unauthorized' ) {
					break;
				}
			}
		} finally {
			flushing = null;
		}
		return results;
	} )();
	return flushing;
}

// A manual Retry from Recent: clears the failed mark and sends now.
export async function retry( id ) {
	const item = await db.get( 'outbox', id );
	if ( item ) {
		item.state = 'waiting';
		await putIfQueued( item );
	}
	return send( id );
}

export async function count() {
	return ( await db.all( 'outbox' ) ).length;
}
