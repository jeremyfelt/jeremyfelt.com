// The Settings Shortcut panel against real WordPress: it creates a
// "WriteNow Shortcut" password, and the header it shows works for exactly
// the requests the Shortcut recipes make. Also: every authenticated
// response the app received told caches to keep out.

const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { BASE, inspect, launch, openApp, typeNote, settled, drain, unique } = require( './helpers' );

let browser;
before( async () => ( browser = await launch() ) );
after( () => browser.close() );

function assertPrivate( headers, what ) {
	assert.match( headers[ 'cache-control' ] || '', /no-store/, `${ what }: Cache-Control no-store` );
	assert.match( headers[ 'cache-control' ] || '', /private/, `${ what }: Cache-Control private` );
	assert.match( headers.vary || '', /Authorization/i, `${ what }: Vary: Authorization` );
}

test( 'Settings creates a Shortcut password whose header works for the Like and Note recipes', async () => {
	const app = await openApp( browser );
	const { page } = app;
	const before = inspect( 'app-passwords', 'admin' ).filter( ( name ) => name === 'WriteNow Shortcut' ).length;

	await page.click( '.tab[data-view="settings"]' );
	await page.click( '#shortcut-details summary' );
	assert.equal( await page.inputValue( '#shortcut-like-url' ), `${ BASE }/wp-json/writenow/v1/likes` );
	assert.equal( await page.inputValue( '#shortcut-note-url' ), `${ BASE }/wp-json/writenow/v1/notes` );
	assert.match( await page.textContent( '#shortcut-details' ), /have not been checked on a device/ );

	await page.click( '#shortcut-create' );
	await page.waitForSelector( '#shortcut-header-field:not([hidden])' );
	const header = await page.inputValue( '#shortcut-header' );
	assert.match( header, /^Basic [A-Za-z0-9+/=]+$/ );
	assert.equal( inspect( 'app-passwords', 'admin' ).filter( ( name ) => name === 'WriteNow Shortcut' ).length, before + 1 );
	const [ , password ] = Buffer.from( header.slice( 6 ), 'base64' ).toString().split( ':' );
	assert.notEqual( password, app.credential.password, 'a separate password from the app’s own' );

	// Shown once: leaving Settings clears it.
	await page.click( '.tab[data-view="note"]' );
	await page.click( '.tab[data-view="settings"]' );
	assert.equal( await page.inputValue( '#shortcut-header' ), '' );

	// The Like recipe: POST the URL, read `message`.
	const url = `https://titles.test/og?shortcut=${ Date.now() }`;
	const like = await fetch( `${ BASE }/wp-json/writenow/v1/likes`, {
		method: 'POST',
		headers: { Authorization: header, 'Content-Type': 'application/json' },
		body: JSON.stringify( { url } ),
	} );
	assert.equal( like.status, 201 );
	assert.equal( ( await like.json() ).message, 'Liked: An & OG title' );
	assertPrivate( Object.fromEntries( like.headers ), 'Shortcut like' );

	// The Note recipes: draft (recommended for dictation) and publish.
	for ( const status of [ 'draft', 'publish' ] ) {
		const content = unique( `Dictated ${ status }` );
		const response = await fetch( `${ BASE }/wp-json/writenow/v1/notes`, {
			method: 'POST',
			headers: { Authorization: header, 'Content-Type': 'application/json' },
			body: JSON.stringify( { content, status } ),
		} );
		assert.equal( response.status, 201 );
		assert.equal( ( await response.json() ).message, status === 'draft' ? 'Saved as a draft.' : 'Posted.' );
		const post = inspect( 'post', inspect( 'notes-with-markdown', content )[ 0 ] );
		assert.equal( post.status, status );
	}
	await app.close();
} );

test( 'every authenticated response the app gets is marked private, and refusals too', async () => {
	const app = await openApp( browser );
	const { page } = app;
	await typeNote( page, unique( 'Checking headers' ) );
	await page.click( '#note-save' );
	await settled( page, '#note-status' );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-list li' );
	await page.click( '.tab[data-view="like"]' );
	await page.fill( '#like-url', `https://titles.test/og?headers=${ Date.now() }` );
	await page.click( '#like-submit' );
	await settled( page, '#like-status' );
	await drain( app );

	const authenticated = app.traffic.filter( ( entry ) => entry.url.includes( '/wp-json/' ) && entry.headers && entry.headers.authorization );
	assert.ok( authenticated.length >= 4, `saw ${ authenticated.length } authenticated responses` );
	for ( const entry of authenticated ) {
		assertPrivate( entry.responseHeaders, `${ entry.method } ${ new URL( entry.url ).pathname }` );
	}

	for ( const route of [ 'status', 'recent', 'notes/1' ] ) {
		const response = await fetch( `${ BASE }/wp-json/writenow/v1/${ route }` );
		assert.equal( response.status, 401, route );
		assert.match( response.headers.get( 'cache-control' ) || '', /no-store/, `anonymous ${ route } refusal is not cacheable` );
	}
	await app.close();
} );
