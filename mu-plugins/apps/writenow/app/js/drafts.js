// The local draft: whatever is in the composer (and the Like form), saved
// to the device as it changes so it survives a reload, a crash or iOS
// discarding the app. Image data lives in IndexedDB (db.js); the draft only
// holds references to it.
//
// localStorage is synchronous, so a save that has been called has happened.

import { uuid } from './db.js';

const NOTE_KEY = 'writenow.draft';
const LIKE_KEY = 'writenow.likeDraft';

export function emptyNote() {
	return {
		clientId: uuid(),
		text: '',
		replyOpen: false,
		replyUrl: '',
		replyName: '',
		// [{ key, name, alt, mediaId, thumbnail }]
		images: [],
		// Set when a draft from the site is open in the composer.
		noteId: null,
		noteLabel: '',
		noteLink: '',
		editLink: '',
		baseline: null,
		updatedAt: Date.now(),
	};
}

function read( key ) {
	try {
		const raw = localStorage.getItem( key );
		return raw ? JSON.parse( raw ) : null;
	} catch {
		return null;
	}
}

function write( key, value ) {
	try {
		localStorage.setItem( key, JSON.stringify( value ) );
		return true;
	} catch {
		return false;
	}
}

export function loadNote() {
	const saved = read( NOTE_KEY );
	if ( ! saved || typeof saved !== 'object' ) {
		return emptyNote();
	}
	const draft = { ...emptyNote(), ...saved };
	draft.images = Array.isArray( saved.images ) ? saved.images.filter( ( image ) => image && ( image.key || image.mediaId ) ) : [];
	return draft;
}

export function saveNote( draft ) {
	draft.updatedAt = Date.now();
	return write( NOTE_KEY, draft );
}

export function hasContent( draft ) {
	return Boolean( draft.text.trim() || draft.images.length || draft.replyUrl.trim() );
}

// What the composer holds compared with the draft as it was opened.
export function isChanged( draft ) {
	if ( ! draft.noteId ) {
		return hasContent( draft );
	}
	return JSON.stringify( snapshot( draft ) ) !== JSON.stringify( draft.baseline );
}

export function snapshot( draft ) {
	return {
		text: draft.text,
		replyUrl: draft.replyUrl,
		replyName: draft.replyName,
		images: draft.images.map( ( image ) => [ image.mediaId || image.key, image.alt || '' ] ),
	};
}

// Debounced writer. `schedule()` on every change, `flush()` when the page
// is hidden so nothing typed in the last moments is lost.
export function createSaver( getDraft, delay = 250 ) {
	let timer = null;
	const flush = () => {
		clearTimeout( timer );
		timer = null;
		saveNote( getDraft() );
	};
	return {
		schedule() {
			clearTimeout( timer );
			timer = setTimeout( flush, delay );
		},
		flush,
		pending: () => timer !== null,
	};
}

export function loadLike() {
	const saved = read( LIKE_KEY );
	return { url: '', title: '', ...( saved || {} ) };
}

export function saveLike( value ) {
	write( LIKE_KEY, { url: value.url || '', title: value.title || '' } );
}
