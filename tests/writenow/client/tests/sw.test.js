// The service worker, with service workers allowed (other tests block them).

const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { setup, openApp, setOffline, typeNote, PASSWORD } = require( '../helpers' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

async function waitForController( page ) {
	await page.evaluate( () => navigator.serviceWorker.ready );
	await page.waitForFunction( () => navigator.serviceWorker.controller !== null );
}

async function cachedUrls( page ) {
	return page.evaluate( async () => {
		const out = [];
		for ( const name of await caches.keys() ) {
			const cache = await caches.open( name );
			for ( const request of await cache.keys() ) {
				const response = await cache.match( request );
				out.push( { name, url: request.url, body: await response.clone().text() } );
			}
		}
		return out;
	} );
}

test( 'precaches the shell and assets, never the API, and never the password', async () => {
	const app = await openApp( env, { serviceWorkers: 'allow' } );
	const { page } = app;
	await waitForController( page );
	await typeNote( page, 'Cache check' );
	await page.click( '#note-post' );
	await page.waitForSelector( '#note-status.is-success' );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-list li' );

	const entries = await cachedUrls( page );
	const urls = entries.map( ( entry ) => entry.url );
	assert.ok( urls.includes( `${ env.server.origin }/writenow/` ), 'shell cached' );
	assert.ok( urls.includes( `${ env.server.origin }/app/js/main.js` ), 'entry module cached without its query' );
	assert.ok( urls.includes( `${ env.server.origin }/app/js/views/note.js` ) );
	assert.ok( urls.some( ( url ) => url.includes( '/theme/fonts/' ) ), 'theme font cached' );
	assert.ok( ! urls.some( ( url ) => /wp-json|wp-admin|wp-login/.test( url ) ), 'no API, admin or login responses cached' );
	assert.ok( ! entries.some( ( entry ) => entry.body.includes( PASSWORD ) || entry.url.includes( encodeURIComponent( PASSWORD ) ) ), 'password not in any cache' );
	await app.context.close();
} );

test( 'opens offline from the cache with the local draft intact', async () => {
	const app = await openApp( env, { serviceWorkers: 'allow' } );
	const { page } = app;
	await waitForController( page );
	await typeNote( page, 'Still here offline' );
	await page.waitForTimeout( 350 );
	await setOffline( app, true );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.inputValue( '#note-text' ), 'Still here offline' );
	assert.equal( await page.isVisible( '#view-note' ), true );
	await app.context.close();
} );

test( 'a new deploy waits for the next launch instead of switching mid-session', async () => {
	const app = await openApp( env, { serviceWorkers: 'allow' } );
	const { page, context } = app;
	await waitForController( page );
	const oldVersion = await page.getAttribute( 'html', 'data-asset-version' );

	env.server.state.version = String( Number( oldVersion ) + 100 );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.getAttribute( 'html', 'data-asset-version' ), oldVersion, 'the open session keeps its version' );
	await page.waitForFunction( async () => {
		const reg = await navigator.serviceWorker.getRegistration();
		return Boolean( reg && reg.waiting );
	} );

	// Close every window, then launch again.
	await page.close();
	const next = await context.newPage();
	await next.goto( env.server.origin + '/writenow/' );
	await next.waitForSelector( 'html.is-ready' );
	await next.waitForFunction( async () => ( await navigator.serviceWorker.getRegistration() ).active && ! ( await navigator.serviceWorker.getRegistration() ).waiting );
	await next.reload();
	await next.waitForSelector( 'html.is-ready' );
	assert.equal( await next.getAttribute( 'html', 'data-asset-version' ), env.server.state.version );
	env.server.state.version = oldVersion;
	await context.close();
} );
