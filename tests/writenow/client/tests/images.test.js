const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { setup, openApp, typeNote, makeImage, jpegSize, jpegHasApp1 } = require( '../helpers' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

async function attach( page, files ) {
	await page.setInputFiles( '#image-input', files );
}

test( 'attach, describe and post images: re-encoded JPEG, long edge 2400, alt text sent', async () => {
	const { page, context, wp } = await openApp( env );
	const big = await makeImage( page, { width: 3200, height: 1800 } );
	const small = await makeImage( page, { width: 800, height: 1200, type: 'image/jpeg' } );
	await attach( page, [
		{ name: 'IMG_0001.png', mimeType: 'image/png', buffer: big },
		{ name: 'IMG_0002.jpg', mimeType: 'image/jpeg', buffer: small },
	] );
	await page.waitForSelector( '#attachments li:nth-child(2)[data-state="uploaded"]' );
	await page.waitForSelector( '#attachments li:nth-child(1)[data-state="uploaded"]' );

	const uploads = wp.requests( 'POST', '/wp/v2/media' );
	assert.equal( uploads.length, 2 );
	for ( const upload of uploads ) {
		assert.equal( upload.headers[ 'content-type' ], 'image/jpeg' );
		assert.match( upload.headers[ 'content-disposition' ], /^attachment; filename="IMG_000\d\.jpg"$/ );
		assert.match( upload.headers.authorization, /^Basic / );
		assert.equal( jpegHasApp1( upload.raw ), false, 'no EXIF/XMP segment' );
	}
	const sizes = uploads.map( ( u ) => jpegSize( u.raw ) );
	assert.deepEqual( sizes.find( ( s ) => s.width > s.height ), { width: 2400, height: 1350 } );
	assert.deepEqual( sizes.find( ( s ) => s.height > s.width ), { width: 800, height: 1200 }, 'small images are not enlarged' );

	assert.equal( await page.getAttribute( '#attachments li:nth-child(1) .attachment-remove', 'aria-label' ), 'Remove image 1' );
	await page.getByLabel( 'Alt text for image 1' ).fill( 'A blue gradient with a W' );
	await typeNote( page, 'Two pictures' );
	await page.click( '#note-post' );
	await page.waitForSelector( '#note-status.is-success' );

	const body = wp.requests( 'POST', '/writenow/v1/notes' ).at( -1 ).body;
	assert.equal( body.media.length, 2 );
	assert.equal( body.media[ 0 ].alt, 'A blue gradient with a W' );
	assert.equal( body.media[ 1 ].alt, '' );
	assert.equal( wp.requests( 'POST', '/wp/v2/media' ).length, 2, 'posting does not upload again' );
	assert.equal( await page.isVisible( '#attachments' ), false );
	await context.close();
} );

test( 'removing an image takes it out of the post', async () => {
	const { page, context, wp } = await openApp( env );
	const img = await makeImage( page, { width: 600, height: 400 } );
	await attach( page, [
		{ name: 'a.png', mimeType: 'image/png', buffer: img },
		{ name: 'b.png', mimeType: 'image/png', buffer: img },
	] );
	await page.waitForSelector( '#attachments li:nth-child(2)[data-state="uploaded"]' );
	await page.waitForSelector( '#attachments li:nth-child(1)[data-state="uploaded"]' );
	const firstId = await page.evaluate( () => JSON.parse( localStorage.getItem( 'writenow.draft' ) ).images[ 0 ].mediaId );
	await page.click( '#attachments li:nth-child(1) .attachment-remove' );
	assert.equal( await page.locator( '#attachments li' ).count(), 1 );
	assert.match( await page.textContent( '#note-status' ), /Image removed/ );
	await page.click( '#note-post' );
	await page.waitForSelector( '#note-status.is-success' );
	const body = wp.requests( 'POST', '/writenow/v1/notes' ).at( -1 ).body;
	assert.equal( body.media.length, 1 );
	assert.notEqual( body.media[ 0 ].id, firstId );
	assert.equal( body.content, '', 'an image alone is a valid note' );
	await context.close();
} );

test( 'posting waits for uploads that are still running', async () => {
	const { page, context, wp } = await openApp( env );
	wp.delay[ 'POST /wp/v2/media' ] = 900;
	const img = await makeImage( page, { width: 600, height: 400 } );
	await attach( page, [ { name: 'slow.png', mimeType: 'image/png', buffer: img } ] );
	await page.waitForSelector( '#attachments li[data-state="uploading"]' );
	assert.match( await page.textContent( '#attachments .attachment-state' ), /Uploading/ );
	await page.click( '#note-post' );
	assert.match( await page.textContent( '#note-status' ), /Finishing the images/ );
	await page.waitForSelector( '#note-status.is-success' );
	const body = wp.requests( 'POST', '/writenow/v1/notes' ).at( -1 ).body;
	assert.equal( body.media.length, 1 );
	assert.equal( wp.requests( 'POST', '/wp/v2/media' ).length, 1 );
	await context.close();
} );

test( 'attached images survive a reload', async () => {
	const { page, context } = await openApp( env );
	const img = await makeImage( page, { width: 600, height: 400 } );
	await attach( page, [ { name: 'keep.png', mimeType: 'image/png', buffer: img } ] );
	await page.waitForSelector( '#attachments li[data-state="uploaded"]' );
	await page.getByLabel( 'Alt text for image 1' ).fill( 'Kept' );
	await page.waitForTimeout( 350 );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.locator( '#attachments li' ).count(), 1 );
	assert.equal( await page.inputValue( '#attachments .attachment-alt' ), 'Kept' );
	await page.waitForFunction( () => document.querySelector( '#attachments img' ).src.startsWith( 'blob:' ) );
	await context.close();
} );

test( 'a file that is not an image is refused politely', async () => {
	const { page, context } = await openApp( env );
	await attach( page, [ { name: 'notes.txt', mimeType: 'text/plain', buffer: Buffer.from( 'hello' ) } ] );
	assert.match( await page.textContent( '#note-status' ), /isn’t an image/ );
	await context.close();
} );
