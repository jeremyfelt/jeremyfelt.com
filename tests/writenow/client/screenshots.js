// Screenshots of every view, light and dark, at iPhone size, into
// screenshots/. Run: npm run screenshots
//
// These are Chromium renderings, not iOS Safari. The "keyboard" shot fakes
// the keyboard by shrinking the app the way main.js does from
// visualViewport on a phone.

const fs = require( 'fs' );
const path = require( 'path' );
const { setup, openApp, setOffline, makeImage } = require( './helpers' );

const OUT = path.join( __dirname, 'screenshots' );
const minutesAgo = ( m ) => new Date( Date.now() - m * 60000 ).toISOString();

const RECENT = [
	{ type: 'note', id: 21, status: 'draft', date: minutesAgo( 4 ), text: 'Thinking about how the best tools disappear while you use them. A pencil never asks you to sign in.', link: '#', edit_link: 'https://jeremyfelt.com/wp-admin/post.php?post=21&action=edit', editable: true },
	{ type: 'like', id: 22, status: 'publish', date: minutesAgo( 52 ), text: 'The Web Is Fine, Actually', url: 'https://www.example.com/web-is-fine', link: '#', edit_link: '#' },
	{ type: 'note', id: 23, status: 'future', date: minutesAgo( -180 ), text: 'Weekly note: a short walk, a long read, and the first frost on the garden.', link: '#', edit_link: '#', editable: false },
	{ type: 'note', id: 24, status: 'publish', date: minutesAgo( 60 * 26 ), text: 'Coffee, then the bike, then the inbox. In that order or not at all.', link: '#', edit_link: '#', editable: false },
	{ type: 'like', id: 25, status: 'publish', date: minutesAgo( 60 * 24 * 3 ), text: 'Small Software for Small Teams', url: 'https://notes.example.org/small', link: '#', edit_link: '#' },
	{ type: 'note', id: 26, status: 'publish', date: minutesAgo( 60 * 24 * 9 ), text: 'Replied to a thread about RSS readers.', link: '#', edit_link: '#', editable: false },
];

const NOTE = 'Spent the morning rewiring how I post from my phone. The old way took a few seconds and felt good; the new way should too.\n\nNext up: making the share sheet do the boring part.';

// Stands in for iOS's visualViewport so the app can be shown with a
// keyboard up: window.__setKeyboard( 336 ) shrinks it, 0 restores it.
function fakeKeyboard() {
	const listeners = {};
	let keyboard = 0;
	const fake = {
		get height() {
			return window.innerHeight - keyboard;
		},
		get width() {
			return window.innerWidth;
		},
		offsetTop: 0,
		addEventListener( type, fn ) {
			( listeners[ type ] = listeners[ type ] || [] ).push( fn );
		},
		removeEventListener() {},
	};
	Object.defineProperty( window, 'visualViewport', { configurable: true, get: () => fake } );
	window.__setKeyboard = ( height ) => {
		keyboard = height;
		( listeners.resize || [] ).forEach( ( fn ) => fn() );
	};
}

async function shoot( page, name, scheme ) {
	await page.waitForTimeout( 350 );
	await page.screenshot( { path: path.join( OUT, `${ name }-${ scheme }.png` ) } );
	console.log( 'wrote', `${ name }-${ scheme }.png` );
}

( async () => {
	fs.mkdirSync( OUT, { recursive: true } );
	const env = await setup();
	for ( const scheme of [ 'light', 'dark' ] ) {
		// Connect
		let app = await openApp( env, { colorScheme: scheme, connected: false } );
		await shoot( app.page, '01-connect', scheme );
		await app.context.close();

		app = await openApp( env, { colorScheme: scheme, initScript: fakeKeyboard } );
		const { page, wp } = app;
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '02-note-empty', scheme );

		// Composing: text, reply, two images, one uploading.
		const one = await makeImage( page, { width: 1600, height: 1200 } );
		const two = await makeImage( page, { width: 1200, height: 1600, type: 'image/jpeg' } );
		await page.fill( '#note-text', NOTE );
		await page.click( '#reply-toggle' );
		await page.fill( '#reply-url', 'https://example.com/a-post-about-phones' );
		await page.fill( '#reply-name', 'Sam' );
		await page.setInputFiles( '#image-input', [ { name: 'one.png', mimeType: 'image/png', buffer: one } ] );
		await page.waitForSelector( '#attachments li[data-state="uploaded"]' );
		wp.delay[ 'POST /wp/v2/media' ] = 60000;
		await page.setInputFiles( '#image-input', [ { name: 'two.jpg', mimeType: 'image/jpeg', buffer: two } ] );
		await page.waitForSelector( '#attachments li[data-state="uploading"]' );
		await page.getByLabel( 'Alt text for image 1' ).fill( 'A blue gradient with a white W' );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '03-note-composing', scheme );
		await page.click( '#attachments li:nth-child(2) .attachment-remove' );
		delete wp.delay[ 'POST /wp/v2/media' ];

		// Over the soft limit, with the keyboard up.
		await page.fill( '#note-text', NOTE + '\n\n' + 'And a longer aside that keeps going past what Mastodon usually allows, because sometimes a thought needs the room. '.repeat( 3 ) );
		await page.click( '#reply-toggle' );
		await page.locator( '#note-text' ).focus();
		await page.evaluate( () => window.__setKeyboard( 336 ) );
		await shoot( page, '04-note-keyboard', scheme );
		await page.evaluate( () => window.__setKeyboard( 0 ) );

		// Posted.
		await page.fill( '#note-text', 'Posting from WriteNow.' );
		await page.click( '#reply-toggle' );
		await page.click( '#note-post' );
		await page.waitForSelector( '#note-status.is-success' );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '05-note-posted', scheme );

		// A failure, with Retry.
		await page.fill( '#note-text', 'This one will be refused.' );
		wp.fail[ 'POST /writenow/v1/notes' ] = { status: 400, code: 'writenow_test', message: 'The note could not be saved.', times: 1 };
		await page.click( '#note-post' );
		await page.waitForSelector( '#note-status.is-error' );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '06-note-error', scheme );
		await page.fill( '#note-text', '' );
		await page.evaluate( () => document.querySelector( '#note-text' ).dispatchEvent( new Event( 'input' ) ) );

		// Queued offline.
		await setOffline( app, true );
		await page.fill( '#note-text', 'Written underground, on the train.' );
		await page.click( '#note-post' );
		await page.waitForFunction( () => document.querySelector( '#note-status' ).textContent.includes( 'saved on this phone' ) );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '07-note-queued', scheme );

		// Recent while offline, with the queue.
		wp.seedRecent( RECENT );
		await setOffline( app, false );
		wp.fail[ 'POST /writenow/v1/notes' ] = { status: 503, message: 'Down for maintenance.' };
		await page.evaluate( () => localStorage.setItem( 'writenow.recent', 'null' ) );
		await page.click( '.tab[data-view="recent"]' );
		await page.waitForSelector( '#recent-list li' );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '08-recent', scheme );
		await setOffline( app, true );
		await page.click( '.tab[data-view="note"]' );
		await page.click( '.tab[data-view="recent"]' );
		await page.waitForFunction( () => document.querySelector( '#recent-status' ).textContent.includes( 'offline' ) );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '09-recent-offline', scheme );
		await setOffline( app, false );
		delete wp.fail[ 'POST /writenow/v1/notes' ];

		// Editing a draft.
		const draft = wp.addDraft( { content: RECENT[ 0 ].text } );
		await page.evaluate( async () => {
			const req = indexedDB.open( 'writenow' );
			await new Promise( ( r ) => ( req.onsuccess = r ) );
			await new Promise( ( r ) => ( req.result.transaction( 'outbox', 'readwrite' ).objectStore( 'outbox' ).clear().onsuccess = r ) );
		} );
		await page.reload();
		await page.waitForSelector( 'html.is-ready' );
		await page.click( '.tab[data-view="recent"]' );
		wp.seedRecent( [ { ...RECENT[ 0 ], id: draft.id }, ...RECENT.slice( 1 ) ] );
		await page.click( '#recent-refresh' );
		await page.locator( `#recent-list li[data-id="${ draft.id }"] .row-main` ).click();
		await page.waitForSelector( '#note-banner:not([hidden])' );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '10-note-editing-draft', scheme );
		await page.click( '#note-discard' );
		await page.click( '#note-discard' );

		// Like.
		await page.click( '.tab[data-view="like"]' );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '11-like', scheme );
		await page.fill( '#like-url', 'https://www.example.com/web-is-fine' );
		await page.fill( '#like-title', 'The Web Is Fine, Actually' );
		await page.click( '#like-submit' );
		await page.waitForSelector( '#like-status.is-success' );
		await page.evaluate( () => document.activeElement.blur() );
		await shoot( page, '12-like-done', scheme );

		// Settings.
		await page.click( '.tab[data-view="settings"]' );
		await page.waitForSelector( '#settings-who strong' );
		await shoot( page, '13-settings', scheme );
		await page.click( '#shortcut-details summary' );
		await page.click( '#shortcut-create' );
		await page.waitForSelector( '#shortcut-header-field:not([hidden])' );
		await page.evaluate( () => document.querySelector( '#shortcut-header-field' ).scrollIntoView( { block: 'center' } ) );
		await shoot( page, '14-settings-shortcut', scheme );
		await app.context.close();
	}
	await env.teardown();
} )();
