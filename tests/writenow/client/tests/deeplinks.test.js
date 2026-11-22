const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { setup, openApp, typeNote } = require( '../helpers' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

test( '?like= and &title= open Like prefilled and leave a clean address', async () => {
	const { page, context } = await openApp( env, { query: '?like=' + encodeURIComponent( 'https://example.com/a?b=c' ) + '&title=' + encodeURIComponent( 'A & B' ) } );
	assert.equal( await page.isVisible( '#view-like' ), true );
	assert.equal( await page.inputValue( '#like-url' ), 'https://example.com/a?b=c' );
	assert.equal( await page.inputValue( '#like-title' ), 'A & B' );
	assert.equal( new URL( page.url() ).search, '' );
	assert.equal( await page.getAttribute( '.tab[data-view="like"]', 'aria-current' ), 'page' );
	await context.close();
} );

test( '?text= opens Note prefilled', async () => {
	const { page, context } = await openApp( env, { query: '?text=' + encodeURIComponent( 'From a bookmarklet' ) } );
	assert.equal( await page.isVisible( '#view-note' ), true );
	assert.equal( await page.inputValue( '#note-text' ), 'From a bookmarklet' );
	assert.equal( new URL( page.url() ).search, '' );
	await context.close();
} );

test( '?text= adds to a note in progress instead of replacing it', async () => {
	const { page, context } = await openApp( env );
	await typeNote( page, 'Already here' );
	await page.waitForTimeout( 350 );
	await page.goto( env.server.origin + '/writenow/?text=' + encodeURIComponent( 'And this' ) );
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.inputValue( '#note-text' ), 'Already here\n\nAnd this' );
	await context.close();
} );

test( 'a deep link survives the Connect screen', async () => {
	const { page, context } = await openApp( env, { connected: false, query: '?like=' + encodeURIComponent( 'https://example.com/later' ) } );
	assert.equal( await page.isVisible( '#view-connect' ), true );
	await page.click( '#connect-manual summary' );
	await page.fill( '#manual-username', 'jeremy' );
	await page.fill( '#manual-password', require( '../fake-wp' ).PASSWORD );
	await page.click( '#manual-submit' );
	await page.waitForSelector( '#view-like:not([hidden])' );
	assert.equal( await page.inputValue( '#like-url' ), 'https://example.com/later' );
	await context.close();
} );
