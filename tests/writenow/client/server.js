// A tiny static server for the WriteNow client tests.
//
// It serves the shell, manifest and service worker the way the PHP side
// does (tokens filled in), the app/ directory as static files, the theme's
// fonts, and a stand-in wp-login.php that "logs in" by setting a cookie and
// redirecting back. The WordPress APIs are not served here: tests answer
// them by intercepting requests (see fake-wp.js).
//
// Run on its own with `npm run serve` to click around at
// http://localhost:8891/writenow/ (API calls will fail without the tests'
// interception).

const fs = require( 'fs' );
const http = require( 'http' );
const path = require( 'path' );

const APP_DIR = path.resolve( __dirname, '../../../mu-plugins/apps/writenow/app' );
const FONT_DIR = path.resolve( __dirname, '../../../../writemore/fonts' );

const TYPES = {
	'.html': 'text/html; charset=utf-8',
	'.css': 'text/css; charset=utf-8',
	'.js': 'text/javascript; charset=utf-8',
	'.json': 'application/json',
	'.webmanifest': 'application/manifest+json',
	'.svg': 'image/svg+xml',
	'.png': 'image/png',
	'.woff2': 'font/woff2',
	'.woff': 'font/woff',
};

function assetVersion() {
	let latest = 0;
	const walk = ( dir ) => {
		for ( const entry of fs.readdirSync( dir, { withFileTypes: true } ) ) {
			const full = path.join( dir, entry.name );
			if ( entry.isDirectory() ) {
				walk( full );
			} else {
				latest = Math.max( latest, fs.statSync( full ).mtimeMs );
			}
		}
	};
	walk( APP_DIR );
	return String( Math.floor( latest / 1000 ) );
}

function fill( template, values, escape ) {
	return template.replace( /\{\{([A-Z_]+)\}\}/g, ( match, name ) => ( name in values ? escape( values[ name ] ) : match ) );
}

const escapeAttr = ( value ) => String( value ).replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
const escapeJsonString = ( value ) => JSON.stringify( String( value ) ).slice( 1, -1 );

function start( { port = 8891, version = null, fonts = true } = {} ) {
	const state = { version: version || assetVersion(), shellRequests: 0 };

	const server = http.createServer( ( req, res ) => {
		const url = new URL( req.url, `http://${ req.headers.host }` );
		const origin = `http://${ req.headers.host }`;
		const hasFonts = fonts && fs.existsSync( FONT_DIR );
		const values = {
			ASSET_URL: `${ origin }/app/`,
			ASSET_VERSION: state.version,
			APP_VERSION: '1.0.0',
			REST_URL: `${ origin }/wp-json/`,
			ADMIN_URL: `${ origin }/wp-admin/`,
			LOGIN_URL: `${ origin }/wp-login.php`,
			FONT_SANS_URL: hasFonts ? `${ origin }/theme/fonts/Inter-VariableFont_slnt,wght.woff2` : '',
			FONT_MONO_URL: hasFonts ? `${ origin }/theme/fonts/MonaspaceArgon-Regular.woff` : '',
		};
		const send = ( status, type, body, headers = {} ) => {
			res.writeHead( status, { 'Content-Type': type, ...headers } );
			res.end( body );
		};

		if ( url.pathname === '/writenow' ) {
			return send( 301, 'text/plain', '', { Location: '/writenow/' + url.search } );
		}
		if ( url.pathname === '/writenow/' ) {
			state.shellRequests += 1;
			const html = fill( fs.readFileSync( path.join( APP_DIR, 'shell.html' ), 'utf8' ), values, escapeAttr );
			return send( 200, TYPES[ '.html' ], html, { 'Cache-Control': 'no-cache', 'X-Robots-Tag': 'noindex' } );
		}
		if ( url.pathname === '/writenow/manifest.webmanifest' ) {
			const json = fill( fs.readFileSync( path.join( APP_DIR, 'manifest.webmanifest' ), 'utf8' ), values, escapeJsonString );
			return send( 200, TYPES[ '.webmanifest' ], json );
		}
		if ( url.pathname === '/writenow/sw.js' ) {
			const js = fill( fs.readFileSync( path.join( APP_DIR, 'sw.js' ), 'utf8' ), values, escapeJsonString );
			return send( 200, TYPES[ '.js' ], js, { 'Cache-Control': 'no-cache' } );
		}
		if ( url.pathname === '/wp-login.php' ) {
			// Stand-in login: set a WordPress-style login cookie and go back.
			const back = url.searchParams.get( 'redirect_to' ) || '/writenow/';
			return send( 302, 'text/plain', '', {
				Location: back,
				'Set-Cookie': 'wordpress_logged_in_test=jeremy%7Ctoken; Path=/; HttpOnly; SameSite=Lax',
			} );
		}

		let file = null;
		if ( url.pathname.startsWith( '/app/' ) ) {
			file = path.join( APP_DIR, decodeURIComponent( url.pathname.slice( 5 ) ) );
			if ( ! file.startsWith( APP_DIR ) ) {
				file = null;
			}
		} else if ( url.pathname.startsWith( '/theme/fonts/' ) && fonts ) {
			file = path.join( FONT_DIR, path.basename( decodeURIComponent( url.pathname ) ) );
		}
		if ( file && fs.existsSync( file ) && fs.statSync( file ).isFile() ) {
			return send( 200, TYPES[ path.extname( file ) ] || 'application/octet-stream', fs.readFileSync( file ) );
		}
		if ( url.pathname.startsWith( '/wp-json/' ) ) {
			return send( 404, 'application/json', JSON.stringify( { code: 'rest_no_route', message: 'No route was found matching the URL and request method.' } ) );
		}
		return send( 404, 'text/plain', 'Not found' );
	} );

	return new Promise( ( resolve, reject ) => {
		const tryPort = ( p ) => {
			server.once( 'error', ( error ) => {
				if ( error.code === 'EADDRINUSE' && p < 8899 ) {
					tryPort( p + 1 );
				} else {
					reject( error );
				}
			} );
			server.listen( p, '127.0.0.1', () => {
				resolve( {
					origin: `http://localhost:${ p }`,
					state,
					close: () => new Promise( ( done ) => {
						server.closeAllConnections();
						server.close( done );
					} ),
				} );
			} );
		};
		tryPort( port );
	} );
}

module.exports = { start, APP_DIR };

if ( require.main === module ) {
	start( { port: Number( process.env.PORT ) || 8891 } ).then( ( { origin } ) => {
		console.log( `WriteNow test server at ${ origin }/writenow/` );
	} );
}
