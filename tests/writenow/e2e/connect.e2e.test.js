// Connecting the app to real WordPress: the automatic path through
// wp-login.php, the automatic path when already logged in, the manual
// path, a revoked password, and Disconnect.

const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { APP, inspect, credentials, launch, openApp, logIn, typeNote, settled, drain } = require( './helpers' );

let browser;
before( async () => ( browser = await launch() ) );
after( () => browser.close() );

function assertNeverLeaked( app, password ) {
	const compact = password.replace( / /g, '' );
	for ( const entry of app.traffic ) {
		assert.ok( ! entry.url.includes( compact ) && ! entry.url.includes( encodeURIComponent( password ) ), `password in a URL: ${ entry.url }` );
	}
}

test( 'automatic Connect goes through wp-login.php and comes back connected', async () => {
	const app = await openApp( browser, { connectAs: null } );
	const { page } = app;
	const before = inspect( 'app-passwords', 'admin' ).length;

	assert.equal( await page.locator( '#view-connect' ).isVisible(), true );
	await page.click( '#connect-button' );
	await page.waitForURL( /wp-login\.php/ );
	const login = new URL( page.url() );
	assert.equal( login.searchParams.get( 'redirect_to' ), APP + '?connect=1' );

	await logIn( page );
	await page.waitForSelector( '#views:not([hidden])', { timeout: 20000 } );
	assert.equal( page.url(), APP, 'the ?connect=1 query is stripped' );

	const stored = await page.evaluate( () => JSON.parse( localStorage.getItem( 'writenow.credential' ) ) );
	assert.equal( stored.username, 'admin' );
	const names = inspect( 'app-passwords', 'admin' );
	assert.equal( names.length, before + 1, 'exactly one Application Password was created' );
	assert.match( names[ names.length - 1 ], /^WriteNow \(/ );

	// From now on the app uses Basic auth without the login cookie.
	await typeNote( page, 'Posted right after connecting.' );
	await page.click( '#note-post' );
	assert.match( await settled( page, '#note-status' ), /^Posted\./ );
	await drain( app );
	const posts = app.api( 'writenow/v1/notes', 'POST' );
	assert.equal( posts.length, 1 );
	assert.match( posts[ 0 ].headers.authorization, /^Basic / );
	assert.equal( posts[ 0 ].headers.cookie, undefined, 'credentials: omit keeps the login cookie off API requests' );
	assert.equal( posts[ 0 ].status, 201 );
	assertNeverLeaked( app, stored.password );
	assert.deepEqual( app.errors, [] );
	await app.close();
} );

test( 'automatic Connect when already logged in skips the login page', async () => {
	const app = await openApp( browser, { connectAs: null } );
	const { page } = app;
	await page.goto( APP.replace( /writenow\/$/, 'wp-login.php' ) );
	await logIn( page );
	await page.goto( APP );
	await page.waitForSelector( 'html.is-ready' );
	await page.click( '#connect-button' );
	await page.waitForSelector( '#views:not([hidden])', { timeout: 20000 } );
	await drain( app );
	assert.equal( app.traffic.filter( ( entry ) => entry.url.includes( 'wp-login.php' ) && entry.method === 'GET' ).length, 1, 'only the manual login visit, no redirect' );
	assert.equal( app.api( 'wp/v2/users/me/application-passwords', 'POST' ).length, 1 );
	assert.ok( app.api( 'wp/v2/users/me/application-passwords', 'POST' )[ 0 ].headers[ 'x-wp-nonce' ], 'the cookie request carries the nonce' );
	await app.close();
} );

test( 'manual Connect with an Application Password, after a wrong one', async () => {
	const app = await openApp( browser, { connectAs: null } );
	const { page } = app;
	const { editor } = credentials();

	await page.click( '#connect-manual summary' );
	await page.fill( '#manual-username', editor.username );
	await page.fill( '#manual-password', 'wrong wrong wrong wrong' );
	await page.click( '#manual-submit' );
	await page.waitForSelector( '#manual-status.is-error' );
	assert.match( await page.textContent( '#manual-status' ), /didn’t work/ );
	assert.equal( await page.locator( '#view-connect' ).isVisible(), true );

	await page.fill( '#manual-password', editor.password );
	await page.click( '#manual-submit' );
	await page.waitForSelector( '#views:not([hidden])' );
	await page.click( '.tab[data-view="settings"]' );
	assert.match( await page.textContent( '#settings-who' ), /editor/ );
	assert.equal( await page.inputValue( '#manual-password' ), '', 'the typed password is not left in the form' );
	await drain( app );
	assertNeverLeaked( app, editor.password );
	await app.close();
} );

test( 'a revoked password returns to Connect and keeps the text', async () => {
	const app = await openApp( browser, { connectAs: null } );
	const { page } = app;
	await page.click( '#connect-button' );
	await page.waitForURL( /wp-login\.php/ );
	await logIn( page, 'author' );
	await page.waitForSelector( '#views:not([hidden])', { timeout: 20000 } );

	assert.deepEqual( inspect( 'revoke', 'author', 'WriteNow (' ).length, 1 );

	await typeNote( page, 'Written after the password was revoked.' );
	await page.click( '#note-post' );
	await page.waitForSelector( '#view-connect:not([hidden])' );
	assert.match( await page.textContent( '#connect-status' ), /Nothing you wrote has been lost/ );
	assert.match( await page.textContent( '#connect-safe' ), /note in progress is saved/ );

	// Connecting again brings the note back, unsent.
	await page.click( '#connect-button' );
	await page.waitForSelector( '#views:not([hidden])', { timeout: 20000 } );
	assert.equal( await page.inputValue( '#note-text' ), 'Written after the password was revoked.' );
	assert.deepEqual( inspect( 'notes-with-markdown', 'Written after the password was revoked.' ), [] );
	await app.close();
} );

test( 'Disconnect forgets the password on the phone and points to the profile', async () => {
	const app = await openApp( browser, { connectAs: 'editor' } );
	const { page } = app;
	await page.click( '.tab[data-view="settings"]' );
	await page.click( '#settings-disconnect' );
	await page.click( '#settings-disconnect' );
	await page.waitForSelector( '#view-connect:not([hidden])' );
	assert.equal( await page.evaluate( () => localStorage.getItem( 'writenow.credential' ) ), null );
	const revoke = page.locator( '#connect-status a' );
	assert.match( await revoke.getAttribute( 'href' ), /profile\.php#application-passwords-section$/ );
	await app.close();
} );
