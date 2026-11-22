const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const { setup, openApp, setOffline } = require( '../helpers' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

const minutesAgo = ( m ) => new Date( Date.now() - m * 60000 ).toISOString();

const ITEMS = [
	{ type: 'note', id: 11, status: 'draft', date: minutesAgo( 3 ), text: 'A draft in progress', link: 'https://jeremyfelt.com/?post_type=shortnote&p=11', edit_link: 'https://jeremyfelt.com/wp-admin/post.php?post=11&action=edit', editable: true },
	{ type: 'note', id: 12, status: 'future', date: minutesAgo( -90 ), text: 'Scheduled for later', link: 'https://jeremyfelt.com/?p=12', edit_link: 'https://jeremyfelt.com/wp-admin/post.php?post=12&action=edit', editable: false },
	{ type: 'like', id: 13, status: 'publish', date: minutesAgo( 120 ), text: 'Someone’s good post', url: 'https://www.example.com/good', link: 'https://jeremyfelt.com/liked/good/', edit_link: 'https://jeremyfelt.com/wp-admin/post.php?post=13&action=edit' },
	{ type: 'note', id: 14, status: 'publish', date: minutesAgo( 60 * 30 ), text: 'Published a while ago', link: 'https://jeremyfelt.com/notes/ab1214/', edit_link: 'https://jeremyfelt.com/wp-admin/post.php?post=14&action=edit', editable: false },
];

test( 'Recent lists notes and likes with status, time and edit links', async () => {
	const { page, context, wp } = await openApp( env );
	wp.seedRecent( ITEMS );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-list li[data-id="14"]' );
	const rows = page.locator( '#recent-list li' );
	assert.equal( await rows.count(), 4 );
	assert.match( await rows.nth( 0 ).textContent(), /Note\s*Draft\s*3 minutes ago\s*A draft in progress/ );
	assert.match( await rows.nth( 1 ).textContent(), /Scheduled/ );
	assert.match( await rows.nth( 2 ).textContent(), /Like.*2 hours ago.*Someone’s good post.*example\.com/ );
	assert.doesNotMatch( await rows.nth( 3 ).textContent(), /Draft|Scheduled/ );

	// Published items open their public link in a new context.
	assert.equal( await rows.nth( 3 ).locator( 'a.row-main' ).getAttribute( 'href' ), 'https://jeremyfelt.com/notes/ab1214/' );
	assert.equal( await rows.nth( 3 ).locator( 'a.row-main' ).getAttribute( 'target' ), '_blank' );
	// Drafts are buttons that open in Note.
	assert.equal( await rows.nth( 0 ).locator( 'button.row-main' ).count(), 1 );
	// Every row has the wp-admin secondary action with a real name.
	const edit = rows.nth( 2 ).locator( '.row-edit' );
	assert.match( await edit.getAttribute( 'href' ), /post=13&action=edit/ );
	assert.match( await edit.getAttribute( 'aria-label' ), /^Edit in WordPress: / );
	await context.close();
} );

test( 'Recent empty state', async () => {
	const { page, context, wp } = await openApp( env );
	wp.seedRecent( [] );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-list .empty' );
	assert.match( await page.textContent( '#recent-list' ), /Nothing here yet/ );
	await context.close();
} );

test( 'Recent shows the last list at once and explains a failed refresh', async () => {
	const { page, context, wp } = await openApp( env );
	wp.seedRecent( ITEMS );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-list li[data-id="14"]' );
	await page.click( '.tab[data-view="note"]' );

	wp.fail[ 'GET /writenow/v1/recent' ] = { status: 500, code: 'internal_server_error', message: 'There has been a critical error on this website.' };
	wp.delay[ 'GET /writenow/v1/recent' ] = 300;
	await page.click( '.tab[data-view="recent"]' );
	assert.equal( await page.locator( '#recent-list li' ).count(), 4, 'cached list is shown immediately' );
	assert.match( await page.textContent( '#recent-status' ), /Refreshing/ );
	await page.waitForSelector( '#recent-status.is-error' );
	assert.match( await page.textContent( '#recent-status' ), /Couldn’t refresh: There has been a critical error on this website\. This is the list from/ );
	assert.equal( await page.locator( '#recent-list li' ).count(), 4 );

	delete wp.fail[ 'GET /writenow/v1/recent' ];
	await page.click( '#recent-status button:has-text("Try again")' );
	await page.waitForFunction( () => ! document.querySelector( '#recent-status' ).textContent );
	await context.close();
} );

test( 'Recent offline shows the saved list with a notice', async () => {
	const app = await openApp( env );
	const { page, context, wp } = app;
	wp.seedRecent( ITEMS );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-list li[data-id="14"]' );
	await page.click( '.tab[data-view="note"]' );
	await setOffline( app, true );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForFunction( () => document.querySelector( '#recent-status' ).textContent.includes( 'offline' ) );
	assert.match( await page.textContent( '#recent-status' ), /You’re offline\. This is the list from/ );
	assert.equal( await page.locator( '#recent-list li' ).count(), 4 );
	await context.close();
} );

test( 'the refresh button and pull-down both refresh', async () => {
	const { page, context, wp } = await openApp( env );
	wp.seedRecent( ITEMS );
	await page.click( '.tab[data-view="recent"]' );
	await page.waitForSelector( '#recent-list li[data-id="14"]' );
	const before = wp.requests( 'GET', '/writenow/v1/recent' ).length;
	await page.click( '#recent-refresh' );
	await page.waitForFunction( () => ! document.querySelector( '#recent-refresh' ).classList.contains( 'is-spinning' ) );
	assert.equal( wp.requests( 'GET', '/writenow/v1/recent' ).length, before + 1 );

	// Pull down with a real touch sequence.
	const box = await page.locator( '#recent-scroll' ).boundingBox();
	const cdp = await context.newCDPSession( page );
	const x = box.x + box.width / 2;
	const y = box.y + 20;
	await cdp.send( 'Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [ { x, y } ] } );
	for ( let step = 1; step <= 10; step++ ) {
		await cdp.send( 'Input.dispatchTouchEvent', { type: 'touchMove', touchPoints: [ { x, y: y + step * 20 } ] } );
	}
	assert.equal( await page.textContent( '#recent-pull' ), 'Release to refresh' );
	await cdp.send( 'Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] } );
	await page.waitForFunction( () => document.querySelector( '#recent-pull' ).dataset.state === 'idle' );
	assert.equal( wp.requests( 'GET', '/writenow/v1/recent' ).length, before + 2 );
	await context.close();
} );

test( 'a draft that cannot be edited points to WordPress', async () => {
	const { page, context, wp } = await openApp( env );
	const draft = wp.addDraft( { content: 'No markdown' } );
	draft.hasMarkdown = false;
	wp.seedRecent( [ { ...ITEMS[ 0 ], id: draft.id, editable: true } ] );
	await page.click( '.tab[data-view="recent"]' );
	await page.locator( `#recent-list li[data-id="${ draft.id }"] .row-main` ).click();
	await page.waitForSelector( '.row-status.is-error' );
	assert.match( await page.textContent( '.row-status' ), /can’t be edited in WriteNow/ );
	assert.match( await page.getAttribute( '.row-status a', 'href' ), /action=edit/ );
	await context.close();
} );
