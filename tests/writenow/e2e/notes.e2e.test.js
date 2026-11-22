// Notes from the app to real WordPress, checked inside WordPress: slug,
// title, block markup through parse_blocks(), meta, hooks, images, replies,
// and the page the theme renders.

const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { BASE, inspect, launch, openApp, typeNote, settled, drain, makeJpegWithExif, unique } = require( './helpers' );

let browser;
before( async () => ( browser = await launch() ) );
after( () => browser.close() );

function onlyNote( markdown ) {
	const ids = inspect( 'notes-with-markdown', markdown );
	assert.equal( ids.length, 1, `exactly one note holds this markdown, found ${ ids.length }` );
	return inspect( 'post', ids[ 0 ] );
}

function assertPublishedOnce( post ) {
	assert.deepEqual( post.transitions.filter( ( t ) => t.endsWith( '>publish' ) ), [ 'draft>publish' ], 'published once, from draft' );
	assert.equal( post.transitions.filter( ( t ) => t === 'publish_shortnote' ).length, 1, 'publish_shortnote (webmentions) ran once' );
}

async function publicPage( link ) {
	const response = await fetch( link, { redirect: 'manual' } );
	return { status: response.status, html: await response.text() };
}

test( 'post a note: slug, title, blocks, meta and the rendered page', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const tag = unique( 'e2e' );
	const markdown = `Hello from WriteNow, **bold** and _quiet_ words. ${ tag }\nA second line with https://example.com/a?b=1\n\n- one\n- two\n\n> A quote\n\n#hashtag at the start`;

	await page.locator( '#note-text' ).fill( markdown );
	await page.click( '#note-post' );
	assert.match( await settled( page, '#note-status' ), /^Posted\./ );
	assert.equal( await page.inputValue( '#note-text' ), '', 'the composer clears once the site confirms' );
	const view = await page.locator( '#note-status a' ).getAttribute( 'href' );

	const post = onlyNote( markdown );
	assert.equal( post.status, 'publish' );
	assert.match( post.slug, /^[0-9a-f]{4}\d{10}$/, 'Shortnotes slug format' );
	assert.equal( post.permalink, `${ BASE }/notes/${ post.slug }/` );
	assert.equal( view, post.permalink, 'the View link is the permalink' );
	assert.match( post.title, /^Note: Hello from WriteNow, bold and quiet words\./ );
	assert.equal( post.guid, `${ BASE }/?post_type=shortnote&#038;p=${ post.id }` );
	assert.equal( post.meta._markdown_content, markdown, 'markdown stored exactly as typed' );
	assert.match( post.meta._writenow_client_id, /^[0-9a-f-]{36}$/ );
	assert.equal( post.reserialize, true, 'parse_blocks and serialize_blocks round-trip' );
	assert.deepEqual(
		post.blocks.map( ( block ) => block.name ),
		[ 'core/paragraph', 'core/list', 'core/quote', 'core/paragraph' ]
	);
	assert.equal(
		post.blocks[ 0 ].html,
		`<p>Hello from WriteNow, <strong>bold</strong> and <em>quiet</em> words. ${ tag }<br>A second line with <a href="https://example.com/a?b=1">https://example.com/a?b=1</a></p>`
	);
	assert.deepEqual( post.blocks[ 1 ].blocks.map( ( block ) => block.name ), [ 'core/list-item', 'core/list-item' ] );
	assert.deepEqual( post.blocks[ 2 ].blocks.map( ( block ) => block.name ), [ 'core/paragraph' ] );
	assert.equal( post.blocks[ 3 ].html, '<p>#hashtag at the start</p>' );
	assert.equal( post.meta.shortnotes_note_type, undefined, 'not a reply' );
	assert.match( post.mastodon, /^Hello from WriteNow, bold and quiet words\./ );
	assertPublishedOnce( post );

	const rendered = await publicPage( post.permalink );
	assert.equal( rendered.status, 200 );
	assert.ok( rendered.html.includes( `<strong>bold</strong> and <em>quiet</em> words. ${ tag }` ) );
	await app.close();
} );

test( 'save a draft, reopen it from Recent, edit it and publish it', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const first = unique( 'A draft to finish later' );
	await typeNote( page, first );
	await page.click( '#note-save' );
	assert.match( await settled( page, '#note-status' ), /Saved as a draft\. It’s in Recent\./ );

	const draft = onlyNote( first );
	assert.equal( draft.status, 'draft' );
	assert.equal( draft.title, 'Note', 'placeholder title until published' );
	assert.match( draft.slug, /^[0-9a-f]{4}\d{10}$/ );
	assert.deepEqual( draft.transitions, [ 'new>draft' ] );

	await page.click( '.tab[data-view="recent"]' );
	const row = page.locator( `#recent-list li[data-id="${ draft.id }"]` );
	await row.waitFor();
	assert.match( await row.textContent(), /Draft/ );
	await row.locator( 'button.row-main' ).click();
	await page.waitForSelector( '#view-note:not([hidden])' );
	assert.equal( await page.inputValue( '#note-text' ), first );
	assert.equal( await page.locator( '#note-banner' ).isVisible(), true );

	const edited = `${ first }\n\nAnd a second paragraph, added later.`;
	await page.locator( '#note-text' ).fill( edited );
	await page.click( '#note-post' );
	assert.match( await settled( page, '#note-status' ), /^Posted\./ );

	const published = inspect( 'post', draft.id );
	assert.equal( published.status, 'publish' );
	assert.equal( published.slug, draft.slug, 'the slug stays' );
	assert.equal( published.meta._markdown_content, edited );
	assert.deepEqual( published.blocks.map( ( block ) => block.name ), [ 'core/paragraph', 'core/paragraph' ] );
	assert.match( published.title, /^Note: A draft to finish later/ );
	assert.deepEqual( inspect( 'notes-with-markdown', first ), [], 'no second note' );
	assertPublishedOnce( published );

	// Published, so no longer editable here.
	await page.click( '.tab[data-view="recent"]' );
	await page.locator( `#recent-list li[data-id="${ draft.id }"] a.row-main` ).waitFor();
	await app.close();
} );

test( 'a reply carries Shortnotes reply meta and renders as one', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const text = unique( 'Agreed, and well put.' );
	await page.click( '#reply-toggle' );
	await page.fill( '#reply-url', 'https://example.com/someone/post?utm_source=x' );
	await page.fill( '#reply-name', 'Someone' );
	await typeNote( page, text );
	await page.click( '#note-post' );
	assert.match( await settled( page, '#note-status' ), /^Posted\./ );

	const post = onlyNote( text );
	assert.equal( post.meta.shortnotes_note_type, 'reply' );
	assert.equal( post.meta.shortnotes_reply_to_url, 'https://example.com/someone/post?utm_source=x', 'reply URLs are kept as given' );
	assert.equal( post.meta.shortnotes_reply_to_name, 'Someone' );
	assert.ok( post.rendered.includes( 'In reply to: <a class="u-in-reply-to" href="https://example.com/someone/post?utm_source=x">Someone</a>' ) );
	const rendered = await publicPage( post.permalink );
	assert.ok( rendered.html.includes( 'class="u-in-reply-to"' ) );
	assert.equal( await page.locator( '#reply-fields' ).isVisible(), false, 'reply fields reset after posting' );
	await app.close();
} );

test( 'attach an image: re-encoded, uploaded, parented, alt text, no location data', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const photo = await makeJpegWithExif( page );
	assert.ok( photo.includes( Buffer.from( 'WRITENOW-SECRET-LOCATION' ) ), 'the original carries Exif' );

	await page.setInputFiles( '#image-input', [ { name: 'IMG_0001.JPG', mimeType: 'image/jpeg', buffer: photo } ] );
	await page.waitForSelector( '#attachments li[data-state="uploaded"]', { timeout: 30000 } );
	await page.getByLabel( 'Alt text for image 1' ).fill( 'A blue square, white inside' );
	const text = unique( 'A photo note' );
	await typeNote( page, text );
	await page.click( '#note-post' );
	assert.match( await settled( page, '#note-status' ), /^Posted\./ );

	const post = onlyNote( text );
	assert.equal( post.attachments.length, 1, 'one image, attached to the note' );
	const [ image ] = post.attachments;
	assert.equal( image.mime, 'image/jpeg' );
	assert.equal( image.alt, 'A blue square, white inside' );
	assert.equal( image.has_exif, false, 'the uploaded file has no Exif' );
	assert.deepEqual( image.dimension, [ 2400, 1600 ], 'long edge capped at 2400px' );
	const blocks = post.blocks.map( ( block ) => block.name );
	assert.deepEqual( blocks, [ 'core/paragraph', 'core/image' ] );
	assert.equal( post.blocks[ 1 ].attrs.id, image.id );
	assert.match( post.blocks[ 1 ].html, /alt="A blue square, white inside"/ );
	assert.ok( post.rendered.includes( 'alt="A blue square, white inside"' ) );

	await drain( app );
	const uploads = app.api( 'wp/v2/media', 'POST' );
	assert.equal( uploads.length, 1 );
	assert.equal( uploads[ 0 ].status, 201 );
	assert.equal( uploads[ 0 ].headers.cookie, undefined );
	await app.close();
} );

test( 'an image-only note gets Shortnotes’ image title', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const photo = await makeJpegWithExif( page, { width: 800, height: 600 } );
	await page.setInputFiles( '#image-input', [ { name: 'only.jpg', mimeType: 'image/jpeg', buffer: photo } ] );
	await page.waitForSelector( '#attachments li[data-state="uploaded"]', { timeout: 30000 } );
	await page.click( '#note-post' );
	const status = await settled( page, '#note-status' );
	assert.match( status, /^Posted\./ );
	await drain( app );
	const created = app.api( 'writenow/v1/notes', 'POST' ).find( ( entry ) => entry.status === 201 );
	assert.ok( created );
	const href = await page.locator( '#note-status a' ).getAttribute( 'href' );
	const slug = href.split( '/' ).filter( Boolean ).pop();
	assert.match( slug, /^[0-9a-f]{4}\d{10}$/ );
	const post = inspect( 'post', inspect( 'note-by-slug', slug ) );
	assert.match( post.title, /^Note: Image posted on / );
	assert.equal( post.meta._markdown_content, '' );
	assert.deepEqual( post.blocks.map( ( block ) => block.name ), [ 'core/image' ] );
	assert.equal( post.attachments.length, 1 );
	await app.close();
} );

test( 'a double tap on Post publishes once', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const text = unique( 'Tapped twice by accident' );
	await typeNote( page, text );
	await page.locator( '#note-post' ).dblclick();
	assert.match( await settled( page, '#note-status' ), /^Posted\./ );
	await page.waitForTimeout( 500 );
	onlyNote( text );
	await app.close();
} );

test( 'text typed while a post is sending is kept', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const text = unique( 'The first thought' );
	await typeNote( page, text );
	// Hold the request so there is time to keep typing.
	let release;
	const held = new Promise( ( resolve ) => ( release = resolve ) );
	await page.route( '**/wp-json/writenow/v1/notes', async ( route ) => {
		await held;
		await route.continue();
	} );
	await page.click( '#note-post' );
	await page.locator( '#note-text' ).click();
	await page.keyboard.press( 'End' );
	await page.keyboard.type( ' and a second one' );
	release();
	assert.match( await settled( page, '#note-status' ), /^Posted\./ );
	onlyNote( text );
	assert.equal( await page.inputValue( '#note-text' ), 'and a second one', 'only what was sent leaves the composer' );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.inputValue( '#note-text' ), 'and a second one', 'and it is saved on the phone' );
	await app.close();
} );
