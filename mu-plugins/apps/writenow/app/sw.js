/*
 * WriteNow service worker.
 *
 * PHP serves this file at /writenow/sw.js (so its scope is /writenow/) after
 * replacing the tokens below. Each token sits inside a double-quoted
 * JavaScript string, so fill it with the value escaped for a JSON string,
 * without the surrounding quotes, for example
 * substr( wp_json_encode( $value, JSON_UNESCAPED_SLASHES ), 1, -1 ).
 *
 *   ASSET_URL      URL of the app/ directory, with a trailing slash.
 *   ASSET_VERSION  The same version string the shell carries.
 *   FONT_SANS_URL  The theme's Inter woff2 URL, or an empty string.
 *   FONT_MONO_URL  The theme's Monaspace woff URL, or an empty string.
 *
 * What it does:
 * - Precaches the shell and every static asset for this version.
 * - The shell is network-first with a 2 second timeout, falling back to the
 *   cache. A shell from the network that belongs to a different version is
 *   not used: this version keeps serving its own cached shell, and the new
 *   worker takes over on the next launch, never mid-session.
 * - Static assets and theme fonts are cache-first.
 * - Nothing else is touched. REST, admin, login and upload requests go
 *   straight to the network and are never cached.
 */

const VERSION = "{{ASSET_VERSION}}";
const ASSET_URL = clean( "{{ASSET_URL}}" );
const FONTS = [ "{{FONT_SANS_URL}}", "{{FONT_MONO_URL}}" ].map( clean ).filter( Boolean );

const CACHE_PREFIX = 'writenow-';
const CACHE = CACHE_PREFIX + VERSION;
const SHELL_URL = new URL( './', self.location.href ).href;
const SHELL_TIMEOUT = 2000;

const ASSETS = [
	'app.css',
	'js/main.js',
	'js/config.js',
	'js/api.js',
	'js/credentials.js',
	'js/db.js',
	'js/drafts.js',
	'js/outbox.js',
	'js/images.js',
	'js/ui.js',
	'js/connect.js',
	'js/views/note.js',
	'js/views/like.js',
	'js/views/recent.js',
	'js/views/settings.js',
	'icons/icon.svg',
	'icons/icon-192.png',
	'icons/icon-512.png',
	'icons/icon-maskable-512.png',
	'icons/apple-touch-icon.png',
];

function clean( value ) {
	return value && value.indexOf( '{{' ) === -1 ? value : '';
}

// Cache keys never carry a query string, so `main.js?ver=1` and the bare
// `api.js` that main.js imports both find their entry.
function keyFor( url ) {
	const u = new URL( url, self.location.href );
	u.search = '';
	u.hash = '';
	return u.href;
}

function versionOf( html ) {
	const match = /data-asset-version="([^"]*)"/.exec( html );
	return match ? match[ 1 ] : null;
}

async function precache() {
	const cache = await caches.open( CACHE );
	const urls = [ SHELL_URL ];
	if ( ASSET_URL ) {
		for ( const path of ASSETS ) {
			urls.push( ASSET_URL + path + '?ver=' + encodeURIComponent( VERSION ) );
		}
	}
	urls.push( ...FONTS );

	await Promise.all(
		urls.map( async ( url ) => {
			const response = await fetch( url, { cache: 'reload', credentials: 'same-origin' } );
			if ( ! response.ok ) {
				throw new Error( 'Precache failed for ' + keyFor( url ) + ': ' + response.status );
			}
			await cache.put( keyFor( url ), response );
		} )
	);
}

self.addEventListener( 'install', ( event ) => {
	// No skipWaiting(): a new version waits until every WriteNow window has
	// closed, so it activates on the next launch.
	event.waitUntil( precache() );
} );

self.addEventListener( 'activate', ( event ) => {
	event.waitUntil(
		( async () => {
			const names = await caches.keys();
			await Promise.all(
				names
					.filter( ( name ) => name.startsWith( CACHE_PREFIX ) && name !== CACHE )
					.map( ( name ) => caches.delete( name ) )
			);
			await self.clients.claim();
		} )()
	);
} );

async function cachedShell() {
	const cache = await caches.open( CACHE );
	return cache.match( SHELL_URL );
}

async function networkShell( request ) {
	const response = await fetch( request, { cache: 'no-store' } );
	if ( ! response.ok ) {
		return null;
	}
	const html = await response.text();
	if ( versionOf( html ) !== VERSION ) {
		// A newer (or older) deploy. Keep this session consistent with the
		// assets this worker holds; the browser installs the new worker now.
		self.registration.update().catch( () => {} );
		return null;
	}
	const headers = new Headers( response.headers );
	const fresh = new Response( html, { status: response.status, statusText: response.statusText, headers } );
	const cache = await caches.open( CACHE );
	await cache.put( SHELL_URL, fresh.clone() );
	return fresh;
}

function shellResponse( event ) {
	const network = networkShell( event.request ).catch( () => null );
	event.waitUntil( network );

	const timeout = new Promise( ( resolve ) => setTimeout( () => resolve( null ), SHELL_TIMEOUT ) );

	return ( async () => {
		const first = await Promise.race( [ network, timeout ] );
		if ( first ) {
			return first;
		}
		const cached = await cachedShell();
		if ( cached ) {
			return cached;
		}
		const late = await network;
		return late || Response.error();
	} )();
}

async function cacheFirst( request ) {
	const cache = await caches.open( CACHE );
	const hit = await cache.match( keyFor( request.url ) );
	if ( hit ) {
		return hit;
	}
	const response = await fetch( request );
	if ( response.ok ) {
		await cache.put( keyFor( request.url ), response.clone() );
	}
	return response;
}

self.addEventListener( 'fetch', ( event ) => {
	const request = event.request;
	if ( request.method !== 'GET' ) {
		return;
	}

	const url = new URL( request.url );
	const shell = new URL( SHELL_URL );

	if ( request.mode === 'navigate' && url.origin === shell.origin && url.pathname === shell.pathname ) {
		event.respondWith( shellResponse( event ) );
		return;
	}

	const key = keyFor( request.url );
	if ( ( ASSET_URL && key.startsWith( ASSET_URL ) ) || FONTS.some( ( font ) => keyFor( font ) === key ) ) {
		event.respondWith( cacheFirst( request ) );
	}
	// Everything else is left to the network.
} );
