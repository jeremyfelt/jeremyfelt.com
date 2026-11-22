// Going offline and coming back, against real WordPress: queued notes and
// likes send exactly once, and the installed app opens offline from its
// service worker with the composer intact.

const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { APP, inspect, launch, openApp, typeNote, settled, setOffline, drain, makeJpegWithExif, unique } = require( './helpers' );

let browser;
before( async () => ( browser = await launch() ) );
after( () => browser.close() );

async function waitForEmptyOutbox( page ) {
	await page.waitForFunction( () => document.querySelector( '#tab-badge' ).hidden, null, { timeout: 30000 } );
}

test( 'a note, a draft and a like made offline each send once when back online', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const note = unique( 'Written on a plane' );
	const draft = unique( 'A draft on a plane' );
	const url = `https://titles.test/title?plane=${ Date.now() }`;

	await setOffline( app, true );
	await typeNote( page, note );
	await page.click( '#note-post' );
	assert.match( await settled( page, '#note-status' ), /saved on this phone/ );
	assert.equal( await page.inputValue( '#note-text' ), '', 'the outbox holds it now' );

	await typeNote( page, draft );
	await page.click( '#note-save' );
	assert.match( await settled( page, '#note-status' ), /draft is saved on this phone/ );

	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', url );
	await page.click( '#like-submit' );
	assert.match( await settled( page, '#like-status' ), /saved on this phone/ );

	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#queue:not([hidden])' );
	assert.equal( await page.locator( '#queue-list li' ).count(), 3 );
	assert.equal( await page.textContent( '#tab-badge' ), '3' );
	assert.deepEqual( inspect( 'notes-with-markdown', note ), [] );

	await setOffline( app, false );
	await waitForEmptyOutbox( page );

	const [ noteId ] = inspect( 'notes-with-markdown', note );
	assert.equal( inspect( 'notes-with-markdown', note ).length, 1 );
	assert.equal( inspect( 'post', noteId ).status, 'publish' );
	const drafts = inspect( 'notes-with-markdown', draft );
	assert.equal( drafts.length, 1 );
	assert.equal( inspect( 'post', drafts[ 0 ] ).status, 'draft' );
	assert.equal( inspect( 'likes-of', url ).length, 1 );
	assert.equal( inspect( 'post', inspect( 'likes-of', url )[ 0 ] ).title, 'Just a title — here' );

	// Coming back online again, and back to the foreground, sends nothing more.
	await page.evaluate( () => window.dispatchEvent( new Event( 'online' ) ) );
	await page.waitForTimeout( 1000 );
	await drain( app );
	const notePosts = app.api( 'writenow/v1/notes', 'POST' ).filter( ( entry ) => entry.status );
	assert.equal( notePosts.length, 2, 'one request per queued note' );
	assert.equal( app.api( 'writenow/v1/likes', 'POST' ).filter( ( entry ) => entry.status ).length, 1 );
	assert.equal( inspect( 'notes-with-markdown', note ).length, 1 );
	await app.close();
} );

test( 'a lost response is retried with the same client_id and does not publish twice', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const text = unique( 'The response never arrived' );
	// The site gets the note and publishes it, but the phone never hears back.
	let dropped = 0;
	await page.route( '**/wp-json/writenow/v1/notes', async ( route ) => {
		if ( dropped === 0 ) {
			dropped++;
			await route.fetch();
			await route.abort( 'connectionreset' );
			return;
		}
		await route.continue();
	} );
	await typeNote( page, text );
	await page.click( '#note-post' );
	assert.match( await settled( page, '#note-status' ), /saved on this phone/ );
	assert.equal( inspect( 'notes-with-markdown', text ).length, 1, 'the site published it' );

	// Next launch: the outbox sends it again and the site recognises it.
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	await waitForEmptyOutbox( page );
	const ids = inspect( 'notes-with-markdown', text );
	assert.equal( ids.length, 1, 'still exactly one note' );
	const post = inspect( 'post', ids[ 0 ] );
	assert.equal( post.transitions.filter( ( t ) => t === 'publish_shortnote' ).length, 1 );
	await app.close();
} );

test( 'an image added offline uploads when the outbox flushes', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const photo = await makeJpegWithExif( page, { width: 1200, height: 900 } );
	await setOffline( app, true );
	await page.setInputFiles( '#image-input', [ { name: 'offline.jpg', mimeType: 'image/jpeg', buffer: photo } ] );
	await page.waitForSelector( '#attachments li[data-state="waiting"]' );
	await page.getByLabel( 'Alt text for image 1' ).fill( 'Taken offline' );
	const text = unique( 'Photo from the trail' );
	await typeNote( page, text );
	await page.click( '#note-post' );
	assert.match( await settled( page, '#note-status' ), /saved on this phone/ );

	await setOffline( app, false );
	await waitForEmptyOutbox( page );
	const [ id ] = inspect( 'notes-with-markdown', text );
	const post = inspect( 'post', id );
	assert.equal( post.attachments.length, 1 );
	assert.equal( post.attachments[ 0 ].alt, 'Taken offline' );
	assert.equal( post.attachments[ 0 ].has_exif, false );
	await drain( app );
	assert.equal( app.api( 'wp/v2/media', 'POST' ).filter( ( entry ) => entry.status === 201 ).length, 1 );
	await app.close();
} );

test( 'the installed app opens offline with the note in progress, and never caches the API', async () => {
	const app = await openApp( browser, { serviceWorkers: 'allow' } );
	const { page } = app;
	await page.waitForFunction( async () => {
		const registration = await navigator.serviceWorker.getRegistration();
		return Boolean( registration && registration.active );
	}, null, { timeout: 20000 } );
	// A second load is controlled by the worker.
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	await page.waitForFunction( () => Boolean( navigator.serviceWorker.controller ) );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-list li' );

	const text = unique( 'Half a thought' );
	await page.click( '.tab[data-view="note"]' );
	await typeNote( page, text );

	await app.context.setOffline( true );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.inputValue( '#note-text' ), text );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-status.is-info' );
	assert.match( await page.textContent( '#recent-status' ), /offline/ );
	assert.ok( ( await page.locator( '#recent-list li' ).count() ) > 0, 'the last list is shown' );

	const cached = await page.evaluate( async () => {
		const urls = [];
		for ( const name of await caches.keys() ) {
			const cache = await caches.open( name );
			for ( const request of await cache.keys() ) {
				urls.push( request.url );
			}
		}
		return urls;
	} );
	assert.ok( cached.includes( APP ) );
	assert.equal( cached.filter( ( url ) => /wp-json|wp-admin|wp-login/.test( url ) ).length, 0, cached.join( '\n' ) );
	const stored = await page.evaluate( async () => {
		const bodies = [];
		for ( const name of await caches.keys() ) {
			const cache = await caches.open( name );
			for ( const request of await cache.keys() ) {
				bodies.push( await ( await cache.match( request ) ).text() );
			}
		}
		return bodies.join( '\n' );
	} );
	assert.ok( ! stored.includes( app.credential.password ), 'the password is in no cached response' );
	await app.context.setOffline( false );
	await app.close();
} );
