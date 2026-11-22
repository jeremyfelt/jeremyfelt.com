const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { setup, openApp, setOffline, typeNote, makeImage } = require( '../helpers' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

const sentNotes = ( wp ) => wp.requests( 'POST', '/writenow/v1/notes' ).filter( ( r ) => r.key === 'POST /writenow/v1/notes' );

test( 'offline: a post is queued, shown in Recent, and sent once on reconnect', async () => {
	const app = await openApp( env );
	const { page, wp } = app;
	await setOffline( app, true );
	await typeNote( page, 'Written on a plane.' );
	await page.click( '#note-post' );
	await page.waitForFunction( () => document.querySelector( '#note-status' ).textContent.includes( 'saved on this phone' ) );
	assert.match( await page.textContent( '#note-status' ), /will send when you’re back online or next open WriteNow/ );
	assert.equal( await page.inputValue( '#note-text' ), '', 'the composer clears once the note is safely queued' );
	assert.equal( await page.textContent( '#tab-badge' ), '1' );
	assert.equal( await page.getAttribute( '.tab[data-view="recent"]', 'aria-label' ), 'Recent, 1 waiting to send' );

	await page.click( '#note-status button:has-text("See Recent")' );
	await page.waitForSelector( '#queue:not([hidden]) li' );
	assert.match( await page.textContent( '#queue li' ), /Note.*Waiting.*Written on a plane\./ );
	assert.equal( await page.locator( '#queue li button:has-text("Retry")' ).count(), 1 );
	assert.equal( await page.locator( '#queue li button:has-text("Remove")' ).count(), 1 );

	await setOffline( app, false );
	await page.waitForFunction( () => document.querySelector( '#queue' ).hidden );
	await page.waitForTimeout( 500 );
	const posts = sentNotes( wp );
	assert.equal( posts.length, 1, 'exactly one request reached the server' );
	assert.equal( posts[ 0 ].body.content, 'Written on a plane.' );
	assert.equal( wp.notes.size, 1 );
	assert.equal( await page.isVisible( '#tab-badge' ), false );
	await app.context.close();
} );

test( 'the outbox flushes on launch, oldest first', async () => {
	const app = await openApp( env );
	const { page, wp } = app;
	await setOffline( app, true );
	await typeNote( page, 'First' );
	await page.click( '#note-post' );
	await page.waitForFunction( () => document.querySelector( '#note-status' ).textContent.includes( 'saved on this phone' ) );
	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', 'https://example.com/second' );
	await page.click( '#like-submit' );
	await page.waitForFunction( () => document.querySelector( '#like-status' ).textContent.includes( 'saved on this phone' ) );
	assert.equal( await page.textContent( '#tab-badge' ), '2' );

	// The app is closed and opened again with a connection.
	wp.offline = false;
	await app.context.setOffline( false );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	await page.waitForFunction( () => document.querySelector( '#tab-badge' ).hidden );
	const order = wp.log.filter( ( r ) => ! r.offline && r.method === 'POST' ).map( ( r ) => r.key );
	assert.deepEqual( order, [ 'POST /writenow/v1/notes', 'POST /writenow/v1/likes' ] );
	await app.context.close();
} );

test( 'a lost response is retried with the same client_id and creates one note', async () => {
	const app = await openApp( env );
	const { page, wp } = app;
	// The server creates the note, then the connection drops.
	wp.dropAfter[ 'POST /writenow/v1/notes' ] = 1;
	await typeNote( page, 'Did it send?' );
	await page.click( '#note-post' );
	await page.waitForFunction( () => document.querySelector( '#note-status' ).textContent.includes( 'saved on this phone' ) );
	assert.equal( wp.notes.size, 1 );

	await page.evaluate( () => window.dispatchEvent( new Event( 'online' ) ) );
	await page.waitForFunction( () => document.querySelector( '#tab-badge' ).hidden );
	const posts = sentNotes( wp );
	assert.equal( posts.length, 2 );
	assert.equal( posts[ 0 ].body.client_id, posts[ 1 ].body.client_id );
	assert.equal( wp.notes.size, 1, 'the server returned the existing note instead of a second one' );
	await app.context.close();
} );

test( 'a 4xx marks a queued item failed with the server’s message and does not retry it', async () => {
	const app = await openApp( env );
	const { page, wp } = app;
	await setOffline( app, true );
	await typeNote( page, 'Will be refused' );
	await page.click( '#note-post' );
	await page.waitForFunction( () => document.querySelector( '#note-status' ).textContent.includes( 'saved on this phone' ) );

	wp.fail[ 'POST /writenow/v1/notes' ] = { status: 400, code: 'writenow_test', message: 'Refused for the test.' };
	await setOffline( app, false );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#queue li.is-failed' );
	assert.match( await page.textContent( '#queue li' ), /Not sent: Refused for the test\./ );

	await page.evaluate( () => window.dispatchEvent( new Event( 'online' ) ) );
	await page.waitForTimeout( 600 );
	assert.equal( sentNotes( wp ).length, 1, 'no automatic retry after a 4xx' );

	delete wp.fail[ 'POST /writenow/v1/notes' ];
	await page.click( '#queue li button:has-text("Retry")' );
	await page.waitForFunction( () => document.querySelector( '#queue' ).hidden );
	assert.equal( wp.notes.size, 1 );
	await app.context.close();
} );

test( 'Remove takes a second tap and deletes the queued item', async () => {
	const app = await openApp( env );
	const { page } = app;
	await setOffline( app, true );
	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', 'https://example.com/never' );
	await page.click( '#like-submit' );
	await page.waitForFunction( () => document.querySelector( '#like-status' ).textContent.includes( 'saved on this phone' ) );
	await page.click( '.tab[data-view="recent"]' );
	const remove = page.locator( '#queue li button:has-text("Remove")' );
	await remove.click();
	assert.equal( await page.locator( '#queue li' ).count(), 1 );
	await page.click( '#queue li button:has-text("Tap again to remove")' );
	await page.waitForFunction( () => document.querySelector( '#queue' ).hidden );
	await app.context.close();
} );

test( 'an image added offline is kept and uploaded when the outbox flushes', async () => {
	const app = await openApp( env );
	const { page, wp } = app;
	const img = await makeImage( page, { width: 900, height: 600 } );
	await setOffline( app, true );
	await page.setInputFiles( '#image-input', [ { name: 'offline.png', mimeType: 'image/png', buffer: img } ] );
	await page.waitForSelector( '#attachments li[data-state="waiting"]' );
	assert.match( await page.textContent( '#attachments .attachment-state' ), /Uploads when you post/ );
	await page.getByLabel( 'Alt text for image 1' ).fill( 'Taken offline' );
	await typeNote( page, 'Photo from the trail' );
	await page.click( '#note-post' );
	await page.waitForFunction( () => document.querySelector( '#note-status' ).textContent.includes( 'saved on this phone' ) );

	// Reopen the app with a connection: the outbox flushes on launch.
	await setOffline( app, false );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	await page.waitForFunction( () => document.querySelector( '#tab-badge' ).hidden );
	assert.equal( wp.requests( 'POST', '/wp/v2/media' ).length, 1 );
	const body = sentNotes( wp )[ 0 ].body;
	assert.equal( body.media.length, 1 );
	assert.equal( body.media[ 0 ].alt, 'Taken offline' );
	const blobs = await page.evaluate( () => new Promise( ( resolve ) => {
		const req = indexedDB.open( 'writenow' );
		req.onsuccess = () => {
			const r = req.result.transaction( 'blobs' ).objectStore( 'blobs' ).count();
			r.onsuccess = () => resolve( r.result );
		};
	} ) );
	assert.equal( blobs, 0, 'image data is cleaned up once sent' );
	await app.context.close();
} );
