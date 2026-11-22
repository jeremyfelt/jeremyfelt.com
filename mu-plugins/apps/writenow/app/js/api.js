// The only module that knows about HTTP.
//
// Every WriteNow API request carries the Basic Authorization header and is
// made with credentials: 'omit'. WordPress rejects a request that carries
// both a login cookie and a Basic header, so the cookie must never ride
// along. The two cookie requests the Connect flow needs are separate
// functions below and never send the Basic header.
//
// Nothing here logs, and no error message ever contains the password.

import { config } from './config.js';

const NOTE_TIMEOUT = 20000;
const UPLOAD_TIMEOUT = 90000;

export class ApiError extends Error {
	constructor( message, { status = 0, code = '', offline = false } = {} ) {
		super( message );
		this.name = 'ApiError';
		this.status = status;
		this.code = code;
		this.offline = offline;
	}

	// Worth trying again later without asking: no connection, a timeout,
	// rate limiting or a server error.
	get retryable() {
		return this.offline || this.status === 429 || this.status >= 500;
	}
}

let getCredential = () => null;
let onUnauthorized = () => {};

export function setAuth( { credential, unauthorized } ) {
	getCredential = credential;
	onUnauthorized = unauthorized;
}

export function restUrl( path, params = {} ) {
	const url = new URL( config.restUrl, location.href );
	if ( url.searchParams.has( 'rest_route' ) ) {
		const route = url.searchParams.get( 'rest_route' ).replace( /\/?$/, '/' );
		url.searchParams.set( 'rest_route', route + path );
	} else {
		url.pathname = url.pathname.replace( /\/?$/, '/' ) + path;
	}
	for ( const [ key, value ] of Object.entries( params ) ) {
		url.searchParams.set( key, String( value ) );
	}
	return url.href;
}

export function authorizationHeader( credential ) {
	const bytes = new TextEncoder().encode( `${ credential.username }:${ credential.password }` );
	let binary = '';
	for ( const byte of bytes ) {
		binary += String.fromCharCode( byte );
	}
	return 'Basic ' + btoa( binary );
}

function offlineError() {
	return new ApiError(
		navigator.onLine === false ? 'You’re offline.' : 'Couldn’t reach the site.',
		{ offline: true }
	);
}

function messageFor( status, data ) {
	if ( data && typeof data.message === 'string' && data.message.trim() ) {
		// Server messages are written to be shown unchanged. Strip any markup
		// a core error might carry; the UI only ever renders text anyway.
		return data.message.replace( /<[^>]*>/g, '' ).trim();
	}
	if ( status === 401 || status === 403 ) {
		return 'The site didn’t accept WriteNow’s password.';
	}
	if ( status === 404 ) {
		return 'The site doesn’t have the WriteNow API yet.';
	}
	if ( status === 429 ) {
		return 'The site asked WriteNow to slow down. Try again in a minute.';
	}
	if ( status >= 500 ) {
		return `The site had a problem (${ status }). Try again in a moment.`;
	}
	return `The site said no (${ status }).`;
}

async function send( url, init, timeout ) {
	const controller = new AbortController();
	const timer = setTimeout( () => controller.abort(), timeout );
	try {
		return await fetch( url, { ...init, signal: controller.signal } );
	} catch {
		throw offlineError();
	} finally {
		clearTimeout( timer );
	}
}

async function readJson( response ) {
	try {
		return await response.json();
	} catch {
		return null;
	}
}

// One authenticated WriteNow request. `credential` overrides the stored one
// (used to check a password before saving it); with an override, a 401 is
// reported to the caller but does not disconnect the app.
async function call( path, { method = 'GET', body, params, credential, timeout = NOTE_TIMEOUT, headers = {} } = {} ) {
	const cred = credential || getCredential();
	if ( ! cred ) {
		throw new ApiError( 'WriteNow isn’t connected to your site.', { status: 401, code: 'writenow_not_connected' } );
	}

	const init = {
		method,
		headers: { Accept: 'application/json', Authorization: authorizationHeader( cred ), ...headers },
		credentials: 'omit',
		cache: 'no-store',
		referrerPolicy: 'no-referrer',
	};
	if ( body instanceof Blob ) {
		init.body = body;
	} else if ( body !== undefined ) {
		init.headers[ 'Content-Type' ] = 'application/json';
		init.body = JSON.stringify( body );
	}

	const response = await send( restUrl( path, params ), init, timeout );
	const data = await readJson( response );

	if ( ! response.ok ) {
		const error = new ApiError( messageFor( response.status, data ), {
			status: response.status,
			code: data && data.code ? String( data.code ) : '',
		} );
		if ( response.status === 401 && ! credential ) {
			onUnauthorized( error );
		}
		throw error;
	}
	return { status: response.status, data };
}

export async function getStatus( credential ) {
	return ( await call( 'writenow/v1/status', { credential } ) ).data;
}

export async function getRecent( perPage = 20 ) {
	const { data } = await call( 'writenow/v1/recent', { params: { per_page: perPage } } );
	return Array.isArray( data ) ? data : [];
}

export async function getNote( id ) {
	return ( await call( `writenow/v1/notes/${ encodeURIComponent( id ) }` ) ).data;
}

export async function saveNote( body, noteId = null ) {
	const path = noteId ? `writenow/v1/notes/${ encodeURIComponent( noteId ) }` : 'writenow/v1/notes';
	return ( await call( path, { method: 'POST', body } ) ).data;
}

export async function like( body ) {
	return ( await call( 'writenow/v1/likes', { method: 'POST', body } ) ).data;
}

// Core media upload. fetch() with credentials: 'omit' cannot report upload
// progress (XMLHttpRequest would send the login cookie on a same-origin
// request), so callers show a per-image state instead of a percentage.
export async function uploadMedia( blob, filename ) {
	const safeName = filename.replace( /[^A-Za-z0-9._-]+/g, '-' );
	const { data } = await call( 'wp/v2/media', {
		method: 'POST',
		body: blob,
		timeout: UPLOAD_TIMEOUT,
		headers: {
			'Content-Type': blob.type || 'image/jpeg',
			'Content-Disposition': `attachment; filename="${ safeName }"`,
		},
	} );
	const sizes = ( data && data.media_details && data.media_details.sizes ) || {};
	const thumb = sizes.medium || sizes.thumbnail;
	return {
		id: data.id,
		thumbnail: ( thumb && thumb.source_url ) || data.source_url || '',
	};
}

// A Shortcut password, created with the app's own credential.
export async function createShortcutPassword( name ) {
	const cred = getCredential();
	const { data } = await call( 'wp/v2/users/me/application-passwords', { method: 'POST', body: { name } } );
	return { username: cred.username, password: data.password };
}

// ---- Cookie requests, used only while connecting. ----

// Returns a wp_rest nonce when the browser is logged in to WordPress, or null.
export async function fetchRestNonce() {
	const url = new URL( 'admin-ajax.php', config.adminUrl );
	url.searchParams.set( 'action', 'rest-nonce' );
	const response = await send( url.href, { credentials: 'same-origin', cache: 'no-store' }, NOTE_TIMEOUT );
	const text = ( await response.text().catch( () => '' ) ).trim();
	if ( response.ok && /^[a-f0-9]{6,}$/i.test( text ) ) {
		return text;
	}
	return null;
}

// Creates the app's Application Password using the login cookie and nonce.
export async function createPasswordWithCookie( nonce, name ) {
	const init = ( method, body ) => ( {
		method,
		credentials: 'same-origin',
		cache: 'no-store',
		headers: {
			Accept: 'application/json',
			'X-WP-Nonce': nonce,
			...( body ? { 'Content-Type': 'application/json' } : {} ),
		},
		body: body ? JSON.stringify( body ) : undefined,
	} );

	const meResponse = await send( restUrl( 'wp/v2/users/me', { context: 'edit' } ), init( 'GET' ), NOTE_TIMEOUT );
	const me = await readJson( meResponse );
	if ( ! meResponse.ok || ! me || ! me.username ) {
		throw new ApiError( messageFor( meResponse.status, me ), { status: meResponse.status, code: me && me.code } );
	}

	const pwResponse = await send( restUrl( 'wp/v2/users/me/application-passwords' ), init( 'POST', { name } ), NOTE_TIMEOUT );
	const created = await readJson( pwResponse );
	if ( ! pwResponse.ok || ! created || ! created.password ) {
		throw new ApiError( messageFor( pwResponse.status, created ), { status: pwResponse.status, code: created && created.code } );
	}
	return { username: me.username, password: created.password };
}
