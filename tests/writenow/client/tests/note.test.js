const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { setup, openApp, typeNote } = require( '../helpers' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

test( 'post a note: one request, composer clears, confirmation with View', async () => {
	const { page, context, wp, pageErrors } = await openApp( env );
	assert.equal( await page.evaluate( () => document.activeElement.id ), 'note-text', 'composer asks for focus on open' );
	await typeNote( page, 'A small thought.\n\nWith a second paragraph.' );
	// A double tap must still post once.
	await page.locator( '#note-post' ).dblclick();
	await page.waitForSelector( '#note-status.is-success' );

	const posts = wp.requests( 'POST', '/writenow/v1/notes' );
	assert.equal( posts.length, 1 );
	assert.equal( posts[ 0 ].body.content, 'A small thought.\n\nWith a second paragraph.' );
	assert.equal( posts[ 0 ].body.status, 'publish' );
	assert.match( posts[ 0 ].body.client_id, /^[0-9a-f-]{36}$/ );
	assert.deepEqual( posts[ 0 ].body.media, [] );

	assert.equal( await page.inputValue( '#note-text' ), '' );
	assert.match( await page.textContent( '#note-status' ), /^Posted\./ );
	assert.equal( await page.getAttribute( '#note-status', 'role' ), 'status' );
	const view = page.locator( '#note-status a' );
	assert.match( await view.getAttribute( 'href' ), /\/notes\// );

	const draft = await page.evaluate( () => JSON.parse( localStorage.getItem( 'writenow.draft' ) ) );
	assert.equal( draft.text, '' );
	assert.notEqual( draft.clientId, posts[ 0 ].body.client_id, 'the next note gets a new client_id' );
	assert.deepEqual( pageErrors, [] );
	await context.close();
} );

test( 'Post acknowledges the tap immediately', async () => {
	const { page, context, wp } = await openApp( env );
	wp.delay[ 'POST /writenow/v1/notes' ] = 600;
	await typeNote( page, 'Slow network.' );
	await page.click( '#note-post' );
	assert.equal( await page.textContent( '#note-post' ), 'Posting…' );
	assert.equal( await page.isDisabled( '#note-save' ), true );
	await page.waitForSelector( '#note-status.is-success' );
	assert.equal( await page.textContent( '#note-post' ), 'Post' );
	await context.close();
} );

test( 'an empty note explains itself and sends nothing', async () => {
	const { page, context, wp } = await openApp( env );
	await page.click( '#note-post' );
	assert.match( await page.textContent( '#note-status' ), /Write something or add an image/ );
	assert.equal( wp.requests( 'POST', '/writenow/v1/notes' ).length, 0 );
	await context.close();
} );

test( 'local draft survives a reload', async () => {
	const { page, context } = await openApp( env );
	await typeNote( page, 'Half a thought' );
	await page.click( '#reply-toggle' );
	await page.fill( '#reply-url', 'https://example.com/a-post' );
	await page.waitForTimeout( 350 );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.inputValue( '#note-text' ), 'Half a thought' );
	assert.equal( await page.isVisible( '#reply-fields' ), true );
	assert.equal( await page.inputValue( '#reply-url' ), 'https://example.com/a-post' );
	await context.close();
} );

test( 'text typed just before the page is hidden is saved at once', async () => {
	const { page, context } = await openApp( env );
	await typeNote( page, 'Quick' );
	// No debounce wait: pagehide must flush.
	await page.evaluate( () => window.dispatchEvent( new Event( 'pagehide' ) ) );
	const saved = await page.evaluate( () => JSON.parse( localStorage.getItem( 'writenow.draft' ) ).text );
	assert.equal( saved, 'Quick' );
	await context.close();
} );

test( 'a failed post keeps everything and offers Retry', async () => {
	const { page, context, wp } = await openApp( env );
	await typeNote( page, 'Keep me' );
	wp.fail[ 'POST /writenow/v1/notes' ] = { status: 400, code: 'writenow_empty_note', message: 'The site rejected this for a test.', times: 1 };
	await page.click( '#note-post' );
	await page.waitForSelector( '#note-status.is-error' );
	assert.match( await page.textContent( '#note-status' ), /Couldn’t post: The site rejected this for a test\. Your note is still here\./ );
	assert.equal( await page.inputValue( '#note-text' ), 'Keep me' );
	await page.click( '#note-status button:has-text("Retry")' );
	await page.waitForSelector( '#note-status.is-success' );
	const posts = wp.requests( 'POST', '/writenow/v1/notes' );
	assert.equal( posts.length, 2 );
	assert.equal( posts[ 0 ].body.client_id, posts[ 1 ].body.client_id, 'a retry reuses the client_id' );
	await context.close();
} );

test( 'reply fields are sent only while open', async () => {
	const { page, context, wp } = await openApp( env );
	await page.click( '#reply-toggle' );
	assert.equal( await page.getAttribute( '#reply-toggle', 'aria-pressed' ), 'true' );
	assert.equal( await page.evaluate( () => document.activeElement.id ), 'reply-url' );
	await page.fill( '#reply-url', 'not a url' );
	await typeNote( page, 'Replying' );
	await page.click( '#note-post' );
	assert.match( await page.textContent( '#note-status' ), /reply needs a web address/ );

	await page.fill( '#reply-url', 'https://example.com/their-post' );
	await page.fill( '#reply-name', 'Example Person' );
	await page.click( '#note-post' );
	await page.waitForSelector( '#note-status.is-success' );
	let body = wp.requests( 'POST', '/writenow/v1/notes' ).at( -1 ).body;
	assert.equal( body.reply_to_url, 'https://example.com/their-post' );
	assert.equal( body.reply_to_name, 'Example Person' );
	assert.equal( await page.isVisible( '#reply-fields' ), false, 'reply fields close with the posted note' );

	await page.click( '#reply-toggle' );
	await page.fill( '#reply-url', 'https://example.com/ignored' );
	await page.click( '#reply-toggle' );
	await typeNote( page, 'Not a reply' );
	await page.click( '#note-post' );
	await page.waitForSelector( '#note-status.is-success' );
	body = wp.requests( 'POST', '/writenow/v1/notes' ).at( -1 ).body;
	assert.equal( body.reply_to_url, '' );
	await context.close();
} );

test( 'character count appears past 400 and changes past the soft limit', async () => {
	const { page, context } = await openApp( env );
	await page.fill( '#note-text', 'a'.repeat( 400 ) );
	assert.equal( await page.isVisible( '#note-count' ), false );
	await page.fill( '#note-text', 'a'.repeat( 401 ) );
	assert.equal( await page.isVisible( '#note-count' ), true );
	assert.match( await page.textContent( '#note-count' ), /401\/500/ );
	assert.equal( await page.locator( '#note-count.is-over' ).count(), 0 );
	await page.fill( '#note-text', '😀'.repeat( 501 ) );
	assert.match( await page.textContent( '#note-count' ), /501\/500/, 'emoji count as one character' );
	assert.equal( await page.locator( '#note-count.is-over' ).count(), 1 );
	assert.equal( await page.isDisabled( '#note-post' ), false, 'the count never blocks posting' );
	await context.close();
} );

test( 'save a draft, reopen it from Recent, edit, then publish', async () => {
	const { page, context, wp } = await openApp( env );
	await typeNote( page, 'Draft words' );
	await page.click( '#note-save' );
	await page.waitForSelector( '#note-status.is-success' );
	assert.match( await page.textContent( '#note-status' ), /Saved as a draft\. It’s in Recent\./ );
	const created = wp.requests( 'POST', '/writenow/v1/notes' ).at( -1 );
	assert.equal( created.body.status, 'draft' );
	const id = [ ...wp.notes.keys() ].at( -1 );

	await page.click( '#note-status button:has-text("Open Recent")' );
	const row = page.locator( `#recent-list li[data-id="${ id }"] .row-main` );
	await row.waitFor();
	assert.match( await row.textContent(), /Draft/ );
	await row.click();
	await page.waitForSelector( '#view-note:not([hidden])' );
	assert.equal( await page.inputValue( '#note-text' ), 'Draft words' );
	assert.match( await page.textContent( '#note-banner' ), /Editing a draft.*Draft words/ );

	await page.locator( '#note-text' ).press( 'End' );
	await page.keyboard.type( ', edited.' );
	await page.click( '#note-save' );
	await page.waitForSelector( '#note-status.is-success' );
	let update = wp.requests( 'POST', `/writenow/v1/notes/${ id }` ).at( -1 );
	assert.equal( update.body.content, 'Draft words, edited.' );
	assert.equal( update.body.status, 'draft' );

	// Open it again and publish.
	await page.click( '.tab[data-view="recent"]' );
	await page.locator( `#recent-list li[data-id="${ id }"] .row-main` ).click();
	await page.waitForSelector( '#note-banner:not([hidden])' );
	await page.click( '#note-post' );
	await page.waitForSelector( '#note-status.is-success' );
	update = wp.requests( 'POST', `/writenow/v1/notes/${ id }` ).at( -1 );
	assert.equal( update.body.status, 'publish' );
	assert.equal( wp.notes.get( id ).status, 'publish' );
	assert.equal( wp.requests( 'POST', '/writenow/v1/notes' ).filter( ( r ) => r.key === 'POST /writenow/v1/notes' ).length, 1, 'no second note was created' );
	assert.equal( await page.isVisible( '#note-banner' ), false );
	await context.close();
} );

test( 'opening a draft never replaces unsaved text', async () => {
	const { page, context, wp } = await openApp( env );
	const draft = wp.addDraft( { content: 'Server draft' } );
	await typeNote( page, 'Unsaved words' );
	await page.click( '.tab[data-view="recent"]' );
	await page.locator( `#recent-list li[data-id="${ draft.id }"] .row-main` ).click();
	await page.waitForSelector( `#recent-list li[data-id="${ draft.id }"] .row-status.is-error` );
	assert.match( await page.textContent( `#recent-list li[data-id="${ draft.id }"] .row-status` ), /isn’t empty/ );
	assert.equal( await page.inputValue( '#note-text' ), 'Unsaved words' );
	await context.close();
} );

test( 'Discard changes needs a second tap and leaves the site draft alone', async () => {
	const { page, context, wp } = await openApp( env );
	const draft = wp.addDraft( { content: 'Server draft', reply_to_url: 'https://example.com/x', reply_to_name: 'X' } );
	await page.click( '.tab[data-view="recent"]' );
	await page.locator( `#recent-list li[data-id="${ draft.id }"] .row-main` ).click();
	await page.waitForSelector( '#note-banner:not([hidden])' );
	assert.equal( await page.inputValue( '#reply-url' ), 'https://example.com/x' );
	await page.fill( '#note-text', 'Changed locally' );
	await page.click( '#note-discard' );
	assert.equal( await page.inputValue( '#note-text' ), 'Changed locally' );
	await page.click( '#note-discard' );
	assert.equal( await page.inputValue( '#note-text' ), '' );
	assert.equal( await page.isVisible( '#note-banner' ), false );
	assert.equal( wp.notes.get( draft.id ).content, 'Server draft' );
	await context.close();
} );

test( 'text typed while a note is sending stays in the composer', async () => {
	const { page, context, wp } = await openApp( env );
	wp.delay[ 'POST /writenow/v1/notes' ] = 600;
	await typeNote( page, 'First thought.' );
	await page.click( '#note-post' );
	await page.locator( '#note-text' ).click();
	await page.keyboard.press( 'End' );
	await page.keyboard.type( ' Second thought.' );
	await page.waitForSelector( '#note-status.is-success' );
	assert.equal( wp.requests( 'POST', '/writenow/v1/notes' )[ 0 ].body.content, 'First thought.' );
	assert.equal( await page.inputValue( '#note-text' ), 'Second thought.' );
	assert.match( await page.textContent( '#note-status' ), /What you typed since is still here/ );
	await context.close();
} );
