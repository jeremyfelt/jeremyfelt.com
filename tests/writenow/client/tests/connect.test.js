const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { setup, openApp, typeNote, USERNAME, PASSWORD } = require( '../helpers' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

test( 'first run shows only Connect', async () => {
	const { page, context } = await openApp( env, { connected: false } );
	assert.equal( await page.locator( '#view-connect' ).isVisible(), true );
	assert.equal( await page.locator( '#tabbar' ).isVisible(), false );
	assert.equal( await page.locator( '#connect-button' ).textContent(), 'Connect' );
	await context.close();
} );

test( 'automatic Connect goes through wp-login and comes back connected', async () => {
	const { page, context, wp } = await openApp( env, { connected: false } );
	await page.click( '#connect-button' );
	// Not logged in: off to wp-login.php, which sets the cookie and redirects back.
	await page.waitForSelector( '#views:not([hidden])' );
	assert.equal( new URL( page.url() ).search, '', 'the ?connect=1 query is stripped' );

	const stored = await page.evaluate( () => JSON.parse( localStorage.getItem( 'writenow.credential' ) ) );
	assert.equal( stored.username, USERNAME );
	assert.ok( stored.password.length > 8 );

	const created = wp.requests( 'POST', '/wp/v2/users/me/application-passwords' );
	assert.equal( created.length, 1 );
	assert.match( created[ 0 ].body.name, /^WriteNow \(/ );
	assert.equal( created[ 0 ].headers[ 'x-wp-nonce' ], 'a1b2c3d4e5' );
	assert.equal( created[ 0 ].headers.authorization, undefined, 'cookie requests never send Basic' );

	// From now on: Basic, and the login cookie stays home.
	await typeNote( page, 'After connecting.' );
	await page.click( '#note-post' );
	await page.waitForSelector( '#note-status.is-success' );
	const posts = wp.requests( 'POST', '/writenow/v1/notes' );
	assert.equal( posts.length, 1 );
	assert.match( posts[ 0 ].headers.authorization, /^Basic / );
	assert.equal( posts[ 0 ].headers.cookie, undefined, 'credentials: omit keeps the login cookie off' );
	for ( const entry of wp.log ) {
		assert.ok( ! entry.path.includes( stored.password ), 'password never in a URL' );
	}
	await context.close();
} );

test( 'automatic Connect when already logged in skips the login page', async () => {
	const { page, context, wp } = await openApp( env, { connected: false, loggedIn: true } );
	await page.click( '#connect-button' );
	await page.waitForSelector( '#views:not([hidden])' );
	assert.equal( wp.requests( 'GET', '/wp/v2/users/me' ).length, 1 );
	await context.close();
} );

test( 'returning from login still logged out explains instead of looping', async () => {
	const { page, context } = await openApp( env, { connected: false, query: '?connect=1' } );
	await page.waitForSelector( '#connect-status.is-error' );
	assert.match( await page.textContent( '#connect-status' ), /still says you’re logged out/ );
	assert.ok( page.url().endsWith( '/writenow/' ) );
	await context.close();
} );

test( 'manual Connect rejects a wrong password, then accepts the right one', async () => {
	const { page, context, consoleLines } = await openApp( env, { connected: false } );
	await page.click( '#connect-manual summary' );
	await page.fill( '#manual-username', USERNAME );
	await page.fill( '#manual-password', 'wrong pass word' );
	await page.click( '#manual-submit' );
	await page.waitForSelector( '#manual-status.is-error' );
	assert.match( await page.textContent( '#manual-status' ), /didn’t work/ );
	assert.equal( await page.evaluate( () => localStorage.getItem( 'writenow.credential' ) ), null );

	await page.fill( '#manual-password', PASSWORD );
	await page.click( '#manual-submit' );
	await page.waitForSelector( '#views:not([hidden])' );
	assert.equal( await page.inputValue( '#manual-password' ), '', 'the password field is cleared' );
	assert.ok( ! consoleLines.some( ( line ) => line.includes( PASSWORD ) ) );
	await context.close();
} );

test( 'Disconnect asks for a second tap, then offers the revoke link', async () => {
	const { page, context } = await openApp( env );
	await page.click( '.tab[data-view="settings"]' );
	await page.waitForSelector( '#settings-who:has-text("Jeremy Felt")' );
	await page.click( '#settings-disconnect' );
	assert.equal( await page.textContent( '#settings-disconnect' ), 'Tap again to disconnect' );
	await page.click( '#settings-disconnect' );
	await page.waitForSelector( '#view-connect:not([hidden])' );
	const link = page.locator( '#connect-status a' );
	assert.match( await link.getAttribute( 'href' ), /profile\.php#application-passwords-section$/ );
	assert.equal( await page.evaluate( () => localStorage.getItem( 'writenow.credential' ) ), null );
	await context.close();
} );

test( 'a 401 returns to Connect without losing text', async () => {
	const { page, context, wp } = await openApp( env );
	await typeNote( page, 'Words I do not want to lose.' );
	wp.fail[ 'POST /writenow/v1/notes' ] = { status: 401, code: 'incorrect_password', message: 'The provided password is an invalid application password.', times: 1 };
	await page.click( '#note-post' );
	await page.waitForSelector( '#view-connect:not([hidden])' );
	assert.match( await page.textContent( '#connect-status' ), /Nothing you wrote has been lost/ );
	assert.match( await page.textContent( '#connect-safe' ), /note in progress is saved/ );
	const outbox = await page.evaluate( () => new Promise( ( resolve ) => {
		const req = indexedDB.open( 'writenow' );
		req.onsuccess = () => {
			const r = req.result.transaction( 'outbox' ).objectStore( 'outbox' ).getAll();
			r.onsuccess = () => resolve( r.result.length );
		};
	} ) );
	assert.equal( outbox, 0, 'the refused note went back to the composer, not the outbox' );

	await page.click( '#connect-manual summary' );
	await page.fill( '#manual-username', USERNAME );
	await page.fill( '#manual-password', PASSWORD );
	await page.click( '#manual-submit' );
	await page.waitForSelector( '#views:not([hidden])' );
	assert.equal( await page.inputValue( '#note-text' ), 'Words I do not want to lose.' );

	await page.reload();
	await page.waitForSelector( 'html.is-ready' );
	assert.equal( await page.inputValue( '#note-text' ), 'Words I do not want to lose.' );
	await context.close();
} );
