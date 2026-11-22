// Automated accessibility scan (axe-core) of every view in both colour
// schemes, plus checks axe cannot make: input sizes that would make iOS
// zoom, touch target sizes, and a Content-Security-Policy clean run.

const { test, before, after } = require( 'node:test' );
const assert = require( 'node:assert/strict' );
const path = require( 'path' );
const { setup, openApp, makeImage, typeNote } = require( '../helpers' );

const AXE = require.resolve( 'axe-core/axe.min.js', { paths: [ path.resolve( __dirname, '..' ) ] } );
const AXE_SOURCE = require( 'fs' ).readFileSync( AXE, 'utf8' );

let env;
before( async () => ( env = await setup() ) );
after( () => env.teardown() );

async function scan( page, label ) {
	// Let status lines finish fading in; mid-fade colours are not the real ones.
	await page.waitForTimeout( 300 );
	// The shell's CSP blocks injected <script> tags (as it should), so axe is
	// evaluated through the DevTools protocol instead.
	await page.evaluate( AXE_SOURCE );
	const result = await page.evaluate( async () => {
		// eslint-disable-next-line no-undef
		const r = await axe.run( document, { resultTypes: [ 'violations' ] } );
		return r.violations.map( ( v ) => ( { id: v.id, impact: v.impact, help: v.help, nodes: v.nodes.map( ( n ) => n.target.join( ' ' ) ).slice( 0, 5 ) } ) );
	} );
	const serious = result.filter( ( v ) => v.impact === 'serious' || v.impact === 'critical' );
	assert.deepEqual( serious, [], `${ label }: ${ JSON.stringify( serious, null, 1 ) }` );
	return result;
}

async function visibleSmallTargets( page ) {
	return page.evaluate( () =>
		[ ...document.querySelectorAll( 'button, a, summary, input:not([type=file])' ) ]
			.filter( ( node ) => node.offsetParent !== null && ! node.closest( '.steps' ) && ! node.closest( 'p:not(.status)' ) )
			.map( ( node ) => ( { node, rect: node.getBoundingClientRect() } ) )
			.filter( ( { rect } ) => rect.width > 0 && ( rect.width < 43.5 || rect.height < 43.5 ) )
			.map( ( { node, rect } ) => `${ node.tagName.toLowerCase() }#${ node.id || node.className } ${ Math.round( rect.width ) }x${ Math.round( rect.height ) }` )
	);
}

for ( const colorScheme of [ 'light', 'dark' ] ) {
	test( `no serious accessibility violations in any view (${ colorScheme })`, async () => {
		const { page, context, wp, consoleLines } = await openApp( env, { colorScheme } );
		const img = await makeImage( page, { width: 600, height: 400 } );
		await typeNote( page, 'x'.repeat( 520 ) );
		await page.click( '#reply-toggle' );
		await page.setInputFiles( '#image-input', [ { name: 'a.png', mimeType: 'image/png', buffer: img } ] );
		await page.waitForSelector( '#attachments li[data-state="uploaded"]' );
		await page.click( '#note-save' );
		await page.waitForSelector( '#note-status.is-success' );
		await typeNote( page, 'x'.repeat( 520 ) );
		await scan( page, 'note' );
		assert.deepEqual( await visibleSmallTargets( page ), [], 'note touch targets' );

		await page.click( '.tab[data-view="like"]' );
		await page.fill( '#like-url', 'nonsense' );
		await page.click( '#like-submit' );
		await scan( page, 'like' );
		assert.deepEqual( await visibleSmallTargets( page ), [], 'like touch targets' );

		await page.click( '.tab[data-view="recent"]' );
		await page.waitForSelector( '#recent-list li' );
		await scan( page, 'recent' );
		assert.deepEqual( await visibleSmallTargets( page ), [], 'recent touch targets' );

		await page.click( '.tab[data-view="settings"]' );
		await page.click( '#shortcut-details summary' );
		await page.click( '#shortcut-create' );
		await page.waitForSelector( '#shortcut-header-field:not([hidden])' );
		await scan( page, 'settings' );

		const inputs = await page.evaluate( () =>
			[ ...document.querySelectorAll( 'input:not([type=file]), textarea' ) ].map( ( node ) => parseFloat( getComputedStyle( node ).fontSize ) )
		);
		assert.ok( inputs.every( ( size ) => size >= 16 ), 'every field is at least 16px, so iOS does not zoom on focus' );
		assert.ok( ! consoleLines.some( ( line ) => /Content Security Policy/i.test( line ) ), 'no CSP violations' );
		assert.ok( wp.log.length > 0 );
		await context.close();
	} );
}

test( `no serious accessibility violations on Connect`, async () => {
	for ( const colorScheme of [ 'light', 'dark' ] ) {
		const { page, context } = await openApp( env, { colorScheme, connected: false } );
		await page.click( '#connect-manual summary' );
		await page.click( '#manual-submit' );
		await scan( page, `connect ${ colorScheme }` );
		await context.close();
	}
} );

test( 'views and controls have names a screen reader can announce', async () => {
	const { page, context } = await openApp( env );
	const snapshot = await page.locator( 'body' ).ariaSnapshot();
	for ( const name of [ 'Add images', 'Reply to a URL', 'Save draft', 'Post', 'Note', 'Like', 'Recent', 'Settings', 'Note text' ] ) {
		assert.ok( snapshot.includes( name ), `${ name } is announced` );
	}
	assert.match( snapshot, /navigation "WriteNow"/ );
	await page.click( '.tab[data-view="recent"]' );
	assert.equal( await page.evaluate( () => document.activeElement.id ), 'recent-heading', 'focus moves to the view heading' );
	await context.close();
} );
