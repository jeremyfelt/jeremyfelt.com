const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { setup, openApp } = require( '../helpers' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

test( 'like a URL: resolved title and a View link; the form clears', async () => {
	const { page, context, wp } = await openApp( env );
	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', 'https://example.com/post?utm_source=x' );
	await page.click( '#like-submit' );
	await page.waitForSelector( '#like-status.is-success' );
	assert.match( await page.textContent( '#like-status' ), /^Liked: A post title/ );
	assert.match( await page.getAttribute( '#like-status a', 'href' ), /\/liked\// );
	const request = wp.requests( 'POST', '/writenow/v1/likes' )[ 0 ];
	assert.deepEqual( request.body, { url: 'https://example.com/post?utm_source=x' }, 'no title sent when left blank' );
	assert.equal( request.headers.cookie, undefined );
	assert.equal( await page.inputValue( '#like-url' ), '' );
	await context.close();
} );

test( 'a duplicate like says so without alarm', async () => {
	const { page, context } = await openApp( env );
	await page.click( '.tab[data-view="like"]' );
	for ( let i = 0; i < 2; i++ ) {
		await page.fill( '#like-url', 'https://example.com/same' );
		await page.fill( '#like-title', 'Same post' );
		await page.click( '#like-submit' );
		await page.waitForSelector( '#like-status.is-success' );
	}
	assert.match( await page.textContent( '#like-status' ), /^Already liked: Same post/ );
	assert.equal( await page.locator( '#like-status.is-error' ).count(), 0 );
	await context.close();
} );

test( 'Paste reads the clipboard only when tapped and finds the URL', async () => {
	const { page, context, wp } = await openApp( env );
	await page.evaluate( () => navigator.clipboard.writeText( 'Look at this https://example.com/shared thing' ) );
	await page.click( '.tab[data-view="like"]' );
	assert.equal( await page.inputValue( '#like-url' ), '' );
	await page.click( '#like-paste' );
	await page.waitForFunction( () => document.querySelector( '#like-url' ).value !== '' );
	assert.equal( await page.inputValue( '#like-url' ), 'https://example.com/shared' );
	assert.equal( wp.requests( 'POST', '/writenow/v1/likes' ).length, 0 );
	await context.close();
} );

test( 'a bare domain gets https://, nonsense is explained', async () => {
	const { page, context, wp } = await openApp( env );
	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', 'not a link' );
	await page.click( '#like-submit' );
	assert.match( await page.textContent( '#like-status' ), /doesn’t look like a web address/ );
	assert.equal( wp.requests( 'POST', '/writenow/v1/likes' ).length, 0 );
	await page.fill( '#like-url', 'example.com/bare' );
	await page.click( '#like-submit' );
	await page.waitForSelector( '#like-status.is-success' );
	assert.equal( wp.requests( 'POST', '/writenow/v1/likes' )[ 0 ].body.url, 'https://example.com/bare' );
	await context.close();
} );

test( 'the Like form is kept on the device as it is typed', async () => {
	const { page, context } = await openApp( env );
	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', 'https://example.com/later' );
	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.inputValue( '#like-url' ), 'https://example.com/later' );
	await context.close();
} );
