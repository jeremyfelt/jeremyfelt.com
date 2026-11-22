// Shared setup for the WriteNow client tests: one static server and one
// Chromium per test file, a fresh iPhone-sized touch context per test.

const { chromium } = require( 'playwright' );
const { start } = require( './server' );
const { FakeWP, LOGIN_COOKIE, PASSWORD, USERNAME } = require( './fake-wp' );

const IPHONE = {
	viewport: { width: 390, height: 844 },
	screen: { width: 390, height: 844 },
	deviceScaleFactor: 3,
	isMobile: true,
	hasTouch: true,
	locale: 'en-US',
	timezoneId: 'America/Los_Angeles',
};

async function setup() {
	const server = await start();
	const browser = await chromium.launch();
	return {
		server,
		browser,
		async teardown() {
			await browser.close();
			await server.close();
		},
	};
}

// Opens the app. `connected` stores a working credential first.
async function openApp( env, { colorScheme = 'light', connected = true, loggedIn = false, serviceWorkers = 'block', query = '', reducedMotion = 'no-preference', wp: existing = null } = {} ) {
	const context = await env.browser.newContext( {
		...IPHONE,
		colorScheme,
		reducedMotion,
		serviceWorkers,
		permissions: [ 'clipboard-read', 'clipboard-write' ],
	} );
	const wp = existing || new FakeWP( { origin: env.server.origin } );
	await wp.install( context );
	if ( loggedIn ) {
		await context.addCookies( [ { name: LOGIN_COOKIE, value: 'jeremy%7Ctoken', url: env.server.origin } ] );
	}

	const page = await context.newPage();
	const consoleLines = [];
	const pageErrors = [];
	page.on( 'console', ( message ) => consoleLines.push( message.text() ) );
	page.on( 'pageerror', ( error ) => pageErrors.push( error.message ) );

	if ( connected ) {
		await page.goto( env.server.origin + '/writenow/' );
		await page.evaluate( ( [ username, password ] ) => {
			localStorage.setItem( 'writenow.credential', JSON.stringify( { username, password } ) );
		}, [ USERNAME, PASSWORD ] );
	}
	await page.goto( env.server.origin + '/writenow/' + query );
	await page.waitForSelector( 'html.is-ready' );

	return { context, page, wp, consoleLines, pageErrors };
}

// Take the phone off the network (and back): the browser's offline mode
// plus the stand-in server refusing intercepted requests.
async function setOffline( app, offline ) {
	app.wp.offline = offline;
	await app.context.setOffline( offline );
}

// The note composer's text area, typed into like a person would.
async function typeNote( page, text ) {
	await page.locator( '#note-text' ).click();
	await page.keyboard.type( text );
}

async function waitForIdle( page ) {
	await page.waitForFunction( () => ! document.querySelector( '.is-busy' ) );
}

// A JPEG or PNG made in the page, for the image tests.
async function makeImage( page, { width = 3200, height = 1800, type = 'image/png' } = {} ) {
	const base64 = await page.evaluate(
		async ( [ w, h, t ] ) => {
			const canvas = document.createElement( 'canvas' );
			canvas.width = w;
			canvas.height = h;
			const ctx = canvas.getContext( '2d' );
			const gradient = ctx.createLinearGradient( 0, 0, w, h );
			gradient.addColorStop( 0, '#2271b1' );
			gradient.addColorStop( 1, '#00c2e1' );
			ctx.fillStyle = gradient;
			ctx.fillRect( 0, 0, w, h );
			ctx.fillStyle = '#fbfbfb';
			ctx.font = `${ Math.round( h / 4 ) }px sans-serif`;
			ctx.fillText( 'W.', w / 3, h / 1.6 );
			const blob = await new Promise( ( resolve ) => canvas.toBlob( resolve, t, 0.9 ) );
			const bytes = new Uint8Array( await blob.arrayBuffer() );
			let binary = '';
			for ( const byte of bytes ) {
				binary += String.fromCharCode( byte );
			}
			return btoa( binary );
		},
		[ width, height, type ]
	);
	return Buffer.from( base64, 'base64' );
}

// Width and height from a JPEG's SOF marker.
function jpegSize( buffer ) {
	let offset = 2;
	while ( offset < buffer.length ) {
		if ( buffer[ offset ] !== 0xff ) {
			return null;
		}
		const marker = buffer[ offset + 1 ];
		const length = buffer.readUInt16BE( offset + 2 );
		if ( marker >= 0xc0 && marker <= 0xc3 ) {
			return { height: buffer.readUInt16BE( offset + 5 ), width: buffer.readUInt16BE( offset + 7 ) };
		}
		offset += 2 + length;
	}
	return null;
}

// True when a JPEG carries an APP1 (EXIF/XMP) segment.
function jpegHasApp1( buffer ) {
	let offset = 2;
	while ( offset < buffer.length && buffer[ offset ] === 0xff ) {
		const marker = buffer[ offset + 1 ];
		if ( marker === 0xe1 ) {
			return true;
		}
		if ( marker === 0xda ) {
			return false;
		}
		offset += 2 + buffer.readUInt16BE( offset + 2 );
	}
	return false;
}

module.exports = { setup, openApp, setOffline, typeNote, waitForIdle, makeImage, jpegSize, jpegHasApp1, IPHONE, USERNAME, PASSWORD, LOGIN_COOKIE };
