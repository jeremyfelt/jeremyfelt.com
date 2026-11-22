// Likes from the app to real WordPress, with the Likes plugin rendering
// them. Outbound title lookups are answered by the test site's helper
// plugin (titles.test), so nothing leaves the machine.

const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { inspect, launch, openApp, settled, drain, unique } = require( './helpers' );

let browser;
before( async () => ( browser = await launch() ) );
after( () => browser.close() );

test( 'like a URL, then like it again', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const id = unique( 'x' ).replace( /\W/g, '' );
	const clean = `https://titles.test/og?id=${ id }#section`;

	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', `https://titles.test/og?utm_source=share&id=${ id }&fbclid=abc#section` );
	await page.click( '#like-submit' );
	assert.equal( await settled( page, '#like-status' ), 'Liked: An & OG titleView' );
	assert.equal( await page.inputValue( '#like-url' ), '' );

	const ids = inspect( 'likes-of', clean );
	assert.equal( ids.length, 1, 'one like, stored with tracking parameters removed and the fragment kept' );
	const like = inspect( 'post', ids[ 0 ] );
	assert.equal( like.type, 'like' );
	assert.equal( like.status, 'publish' );
	assert.equal( like.title, 'An & OG title', 'stored as plain text, as the Likes meta box stores it' );
	assert.match( like.slug, /^an-og-title(-\d+)?$/, 'slug from the title, as wp-admin gives it' );
	assert.equal( like.meta.like_of_url, clean );
	assert.deepEqual( like.transitions, [ 'new>publish' ], 'published directly, as the Likes meta box does' );
	assert.ok( like.rendered.includes( `<a class="u-like-of" href="${ clean }">An &#038; OG title</a>` ), like.rendered );
	assert.ok( like.rendered.includes( '<span class="like-of-domain">titles.test</span>' ) );
	assert.equal( await page.locator( '#like-status a' ).getAttribute( 'href' ), like.permalink );

	const rendered = await fetch( like.permalink );
	assert.equal( rendered.status, 200 );
	assert.ok( ( await rendered.text() ).includes( 'class="u-like-of"' ) );

	// Again, with a title this time: nothing new, and it says so calmly.
	await page.fill( '#like-url', clean );
	await page.fill( '#like-title', 'Some other title' );
	await page.click( '#like-submit' );
	assert.equal( await settled( page, '#like-status' ), 'Already liked: An & OG titleView' );
	assert.equal( await page.locator( '#like-status.is-success' ).count(), 1 );
	assert.equal( inspect( 'likes-of', clean ).length, 1 );

	await drain( app );
	const posts = app.api( 'writenow/v1/likes', 'POST' );
	assert.deepEqual( posts.map( ( entry ) => entry.status ), [ 201, 200 ] );
	await app.close();
} );

test( 'a like whose title cannot be looked up uses the URL', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const url = `https://unreachable.example/${ Date.now() }`;
	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', url );
	await page.click( '#like-submit' );
	assert.equal( await settled( page, '#like-status' ), `Liked: ${ url }View` );
	const like = inspect( 'post', inspect( 'likes-of', url )[ 0 ] );
	assert.equal( like.title, url );
	await app.close();
} );

test( 'an address that is not a web URL is refused in the app and by the site', async () => {
	const app = await openApp( browser );
	const { page } = app;
	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', 'javascript:alert(1)' );
	await page.click( '#like-submit' );
	await page.waitForSelector( '#like-status.is-error' );
	await drain( app );
	assert.equal( app.api( 'writenow/v1/likes', 'POST' ).length, 0, 'never sent' );

	const response = await fetch( app.page.url().replace( /writenow\/.*$/, 'wp-json/writenow/v1/likes' ), {
		method: 'POST',
		headers: { Authorization: 'Basic ' + Buffer.from( `${ app.credential.username }:${ app.credential.password }` ).toString( 'base64' ), 'Content-Type': 'application/json' },
		body: JSON.stringify( { url: 'javascript:alert(1)' } ),
	} );
	assert.equal( response.status, 400 );
	assert.equal( ( await response.json() ).code, 'writenow_invalid_url' );
	await app.close();
} );
