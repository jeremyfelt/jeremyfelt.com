// Shared setup for the WriteNow end-to-end tests: the real app, served by
// the real local WordPress that run.sh builds, driven in Chromium. Nothing
// is intercepted or mocked. Checks inside WordPress go through
// bin/inspect.php, which loads the same site from the command line.

const { execFileSync } = require( 'node:child_process' );
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const { chromium } = require( 'playwright' );

const BASE = ( process.env.WRITENOW_TEST_URL || '' ).replace( /\/$/, '' );
const SITE = process.env.WRITENOW_TEST_SITE || '';

if ( ! BASE || ! SITE ) {
	throw new Error( 'Run the end-to-end tests with tests/writenow/run.sh e2e, which builds and serves the site.' );
}

const APP = BASE + '/writenow/';

const IPHONE = {
	viewport: { width: 390, height: 844 },
	screen: { width: 390, height: 844 },
	deviceScaleFactor: 2,
	isMobile: true,
	hasTouch: true,
	locale: 'en-US',
	timezoneId: 'America/Los_Angeles',
};

function inspect( command, ...args ) {
	const output = execFileSync( 'php', [ path.join( __dirname, '..', 'bin', 'inspect.php' ), command, ...args.map( String ) ], {
		env: process.env,
		encoding: 'utf8',
	} );
	return JSON.parse( output );
}

function credentials() {
	return JSON.parse( fs.readFileSync( path.join( SITE, 'credentials.json' ), 'utf8' ) );
}

function basic( username, password ) {
	return 'Basic ' + Buffer.from( `${ username }:${ password }` ).toString( 'base64' );
}

async function launch() {
	const options = {};
	if ( process.env.CHROME_PATH ) {
		options.executablePath = process.env.CHROME_PATH;
	}
	return chromium.launch( options );
}

// A fresh phone. `connectAs` stores that test user's Application Password
// first, as if the app had connected earlier.
async function openApp( browser, { connectAs = 'admin', serviceWorkers = 'block', query = '', colorScheme = 'light' } = {} ) {
	const context = await browser.newContext( {
		...IPHONE,
		colorScheme,
		serviceWorkers,
		permissions: [ 'clipboard-read', 'clipboard-write' ],
	} );
	const page = await context.newPage();
	const traffic = [];
	const errors = [];
	page.on( 'pageerror', ( error ) => errors.push( error.message ) );
	// Every request the app makes, with the headers it really sent.
	context.on( 'request', ( request ) => {
		const entry = { method: request.method(), url: request.url(), headers: null, status: null, responseHeaders: null };
		traffic.push( entry );
		request.allHeaders().then( ( headers ) => ( entry.headers = headers ) ).catch( () => {} );
		request
			.response()
			.then( async ( response ) => {
				if ( response ) {
					entry.responseHeaders = await response.allHeaders();
					entry.status = response.status();
				} else {
					entry.failed = true;
				}
			} )
			.catch( () => ( entry.failed = true ) );
	} );

	let credential = null;
	if ( connectAs ) {
		credential = credentials()[ connectAs ];
		await page.goto( APP );
		await page.evaluate( ( [ username, password ] ) => {
			localStorage.setItem( 'writenow.credential', JSON.stringify( { username, password } ) );
		}, [ credential.username, credential.password ] );
	}
	await page.goto( APP + query );
	await page.waitForSelector( 'html.is-ready' );

	return {
		context,
		page,
		traffic,
		errors,
		credential,
		api: ( pathPart, method = null ) => traffic.filter( ( entry ) => entry.url.includes( '/wp-json/' + pathPart ) && ( ! method || entry.method === method ) ),
		async close() {
			await context.close();
		},
	};
}

async function logIn( page, user = 'admin', password = 'password' ) {
	await page.waitForSelector( '#user_login' );
	await page.fill( '#user_login', user );
	await page.fill( '#user_pass', password );
	await Promise.all( [ page.waitForNavigation(), page.click( '#wp-submit' ) ] );
}

async function typeNote( page, text ) {
	await page.locator( '#note-text' ).click();
	await page.keyboard.type( text );
}

// Wait for a status line to settle on success, error or a queued notice.
async function settled( page, selector, { timeout = 20000 } = {} ) {
	await page.waitForFunction(
		( sel ) => {
			const node = document.querySelector( sel );
			if ( ! node ) {
				return false;
			}
			if ( node.classList.contains( 'is-success' ) || node.classList.contains( 'is-error' ) ) {
				return true;
			}
			return node.classList.contains( 'is-info' ) && /saved on this phone/.test( node.textContent );
		},
		selector,
		{ timeout }
	);
	return page.textContent( selector );
}

async function setOffline( app, offline ) {
	await app.context.setOffline( offline );
	await app.page.evaluate( ( value ) => window.dispatchEvent( new Event( value ? 'offline' : 'online' ) ), offline );
}

// Wait until every request the app started has its headers and response recorded.
async function drain( app ) {
	for ( let i = 0; i < 50; i++ ) {
		if ( app.traffic.every( ( entry ) => entry.headers && ( entry.status !== null || entry.failed ) ) ) {
			return;
		}
		await new Promise( ( resolve ) => setTimeout( resolve, 100 ) );
	}
}

// A JPEG made in the page, with an Exif segment carrying GPS-like data
// spliced in, so the tests can prove re-encoding strips it.
async function makeJpegWithExif( page, { width = 3000, height = 2000 } = {} ) {
	const base64 = await page.evaluate(
		async ( [ w, h ] ) => {
			const canvas = document.createElement( 'canvas' );
			canvas.width = w;
			canvas.height = h;
			const ctx = canvas.getContext( '2d' );
			ctx.fillStyle = '#2271b1';
			ctx.fillRect( 0, 0, w, h );
			ctx.fillStyle = '#fbfbfb';
			ctx.fillRect( w / 4, h / 4, w / 2, h / 2 );
			const blob = await new Promise( ( resolve ) => canvas.toBlob( resolve, 'image/jpeg', 0.9 ) );
			const bytes = new Uint8Array( await blob.arrayBuffer() );
			let binary = '';
			for ( const byte of bytes ) {
				binary += String.fromCharCode( byte );
			}
			return btoa( binary );
		},
		[ width, height ]
	);
	const jpeg = Buffer.from( base64, 'base64' );
	// APP1 "Exif\0\0" followed by a little-endian TIFF header and a marker string.
	const payload = Buffer.concat( [ Buffer.from( 'Exif\0\0II*\0\x08\0\0\0', 'binary' ), Buffer.from( 'GPS 45.5231 -122.6765 WRITENOW-SECRET-LOCATION' ) ] );
	const segment = Buffer.alloc( 4 );
	segment.writeUInt16BE( 0xffe1, 0 );
	segment.writeUInt16BE( payload.length + 2, 2 );
	return Buffer.concat( [ jpeg.subarray( 0, 2 ), segment, payload, jpeg.subarray( 2 ) ] );
}

function unique( prefix ) {
	return `${ prefix } ${ Date.now().toString( 36 ) }${ Math.random().toString( 36 ).slice( 2, 6 ) }`;
}

module.exports = { BASE, APP, SITE, inspect, credentials, basic, launch, openApp, logIn, typeNote, settled, setOffline, drain, makeJpegWithExif, unique };
