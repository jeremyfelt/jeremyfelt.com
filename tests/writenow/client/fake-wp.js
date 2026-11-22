// An in-memory stand-in for the WordPress side, answering intercepted
// requests the way spec section 4.3 and section 5 describe:
//   writenow/v1/status, likes, notes, notes/<id>, recent
//   wp/v2/media, wp/v2/users/me, wp/v2/users/me/application-passwords
//   wp-admin/admin-ajax.php?action=rest-nonce
//
// It also behaves like WordPress where the client could get it wrong: a
// request carrying both a login cookie and a Basic header is a 401, and the
// cookie routes need the nonce.
//
// Every request is logged so tests can count and inspect them.

const USERNAME = 'jeremy';
const PASSWORD = 'abcd EFGH 1234 ijkl MNOP 5678';
const NONCE = 'a1b2c3d4e5';
const LOGIN_COOKIE = 'wordpress_logged_in_test';

class FakeWP {
	constructor( { origin, loggedIn = false } = {} ) {
		this.origin = origin;
		this.loggedIn = loggedIn;
		this.passwords = new Map( [ [ PASSWORD.replace( / /g, '' ), 'Existing' ] ] );
		this.log = [];
		this.notes = new Map();
		this.likes = [];
		this.media = new Map();
		this.nextId = 100;
		this.recentOverride = null;
		this.offline = false;
		// One-shot or persistent behaviours a test can set:
		//   this.fail['POST /writenow/v1/notes'] = { status, code, message, times }
		this.fail = {};
		// Delay per route in ms, e.g. this.delay['POST /wp/v2/media'] = 800
		this.delay = {};
		// Drop the response after doing the work, like a lost connection.
		this.dropAfter = {};
		this.statusBody = {
			user: { id: 1, name: 'Jeremy Felt' },
			site: { name: 'Jeremy Felt', url: 'https://jeremyfelt.com' },
			features: { notes: true, likes: true },
			note_soft_limit: 500,
			version: '1.0.0',
		};
	}

	static get USERNAME() {
		return USERNAME;
	}

	static get PASSWORD() {
		return PASSWORD;
	}

	basicHeader( username = USERNAME, password = PASSWORD ) {
		return 'Basic ' + Buffer.from( `${ username }:${ password }` ).toString( 'base64' );
	}

	async install( context ) {
		await context.route( /\/(wp-json|wp-admin)\//, ( route ) => this.handle( route ) );
	}

	// Requests that reached the server (not those refused while offline).
	requests( method, pathPart ) {
		return this.log.filter( ( entry ) => ! entry.offline && entry.method === method && entry.path.includes( pathPart ) );
	}

	seedRecent( items ) {
		this.recentOverride = items;
	}

	addDraft( { content, reply_to_url = '', reply_to_name = '', media = [] } ) {
		const id = this.nextId++;
		const note = {
			id,
			status: 'draft',
			content,
			reply_to_url,
			reply_to_name,
			media,
			client_id: null,
			date: new Date().toISOString(),
			modified: new Date().toISOString(),
			hasMarkdown: true,
		};
		this.notes.set( id, note );
		return note;
	}

	noteJson( note ) {
		return {
			id: note.id,
			status: note.status,
			title: note.status === 'publish' ? 'Note: ' + note.content.slice( 0, 40 ) : 'Auto Draft',
			link: note.status === 'publish' ? `https://jeremyfelt.com/notes/ab12${ note.id }/` : `https://jeremyfelt.com/?post_type=shortnote&p=${ note.id }`,
			edit_link: `https://jeremyfelt.com/wp-admin/post.php?post=${ note.id }&action=edit`,
			editable: note.status === 'draft' && note.hasMarkdown,
		};
	}

	authenticate( headers ) {
		const cookie = headers.cookie || '';
		const auth = headers.authorization || '';
		if ( auth.startsWith( 'Basic ' ) ) {
			if ( cookie.includes( LOGIN_COOKIE ) ) {
				// rest_cookie_check_errors() demotes the cookie user, and
				// application passwords are skipped: WordPress answers 401.
				return { error: [ 401, 'rest_not_logged_in', 'You are not currently logged in.' ] };
			}
			const decoded = Buffer.from( auth.slice( 6 ), 'base64' ).toString( 'utf8' );
			const [ user, ...rest ] = decoded.split( ':' );
			const password = rest.join( ':' ).replace( / /g, '' );
			if ( user !== USERNAME || ! this.passwords.has( password ) ) {
				return { error: [ 401, 'incorrect_password', 'The provided password is an invalid application password.' ] };
			}
			return { user: USERNAME, via: 'basic' };
		}
		if ( cookie.includes( LOGIN_COOKIE ) && this.loggedIn ) {
			if ( headers[ 'x-wp-nonce' ] !== NONCE ) {
				return { error: [ 401, 'rest_not_logged_in', 'You are not currently logged in.' ] };
			}
			return { user: USERNAME, via: 'cookie' };
		}
		return { error: [ 401, 'rest_not_logged_in', 'You are not currently logged in.' ] };
	}

	async handle( route ) {
		const request = route.request();
		const url = new URL( request.url() );
		const headers = await request.allHeaders();
		const method = request.method();
		let route_path = url.pathname.replace( /^\/wp-json/, '' );
		const key = `${ method } ${ route_path.replace( /\/\d+$/, '/<id>' ) }`;
		const buffer = request.postDataBuffer();
		let body = null;
		if ( buffer && ( headers[ 'content-type' ] || '' ).includes( 'json' ) ) {
			try {
				body = JSON.parse( buffer.toString( 'utf8' ) );
			} catch {}
		}
		const entry = { method, path: url.pathname + url.search, key, headers, body, raw: buffer, at: Date.now() };
		if ( this.offline ) {
			// Playwright keeps routing while a context is offline, so the
			// stand-in server refuses like an unreachable one would.
			entry.offline = true;
			this.log.push( entry );
			return route.abort( 'internetdisconnected' );
		}
		this.log.push( entry );

		if ( this.delay[ key ] ) {
			await new Promise( ( resolve ) => setTimeout( resolve, this.delay[ key ] ) );
		}

		const json = ( status, data ) => route.fulfill( { status, contentType: 'application/json', body: JSON.stringify( data ) } );
		const error = ( status, code, message ) => json( status, { code, message, data: { status } } );

		// admin-ajax nonce, cookie only.
		if ( url.pathname === '/wp-admin/admin-ajax.php' ) {
			const loggedIn = this.loggedIn || ( headers.cookie || '' ).includes( LOGIN_COOKIE );
			if ( url.searchParams.get( 'action' ) === 'rest-nonce' && loggedIn ) {
				this.loggedIn = true;
				return route.fulfill( { status: 200, contentType: 'text/html', body: NONCE } );
			}
			return route.fulfill( { status: 400, contentType: 'text/html', body: '0' } );
		}
		if ( ! url.pathname.startsWith( '/wp-json/' ) ) {
			return route.fulfill( { status: 404, body: 'Not found' } );
		}

		const failure = this.fail[ key ];
		if ( failure && failure.times !== 0 ) {
			if ( failure.times ) {
				failure.times -= 1;
			}
			if ( failure.abort ) {
				return route.abort( 'connectionreset' );
			}
			return error( failure.status, failure.code || 'writenow_test_error', failure.message || 'Test failure.' );
		}

		const auth = this.authenticate( headers );
		if ( auth.error ) {
			return error( ...auth.error );
		}

		const result = this.dispatch( method, route_path, body, buffer, headers, auth, url );
		if ( this.dropAfter[ key ] ) {
			this.dropAfter[ key ] -= 1;
			return route.abort( 'connectionreset' );
		}
		return json( result[ 0 ], result[ 1 ] );
	}

	dispatch( method, path, body, buffer, headers, auth, url ) {
		const err = ( status, code, message ) => [ status, { code, message, data: { status } } ];

		if ( method === 'GET' && path === '/wp/v2/users/me' ) {
			return [ 200, { id: 1, name: 'Jeremy Felt', ...( url.searchParams.get( 'context' ) === 'edit' ? { username: USERNAME } : {} ) } ];
		}
		if ( method === 'POST' && path === '/wp/v2/users/me/application-passwords' ) {
			const password = `new${ this.nextId++ }pass word ${ Math.random().toString( 36 ).slice( 2, 6 ) }`;
			this.passwords.set( password.replace( / /g, '' ), body && body.name );
			return [ 201, { uuid: 'uuid-' + this.nextId, name: body && body.name, password } ];
		}

		if ( auth.via !== 'basic' ) {
			return err( 401, 'rest_not_logged_in', 'You are not currently logged in.' );
		}

		if ( method === 'GET' && path === '/writenow/v1/status' ) {
			return [ 200, this.statusBody ];
		}

		if ( method === 'POST' && path === '/wp/v2/media' ) {
			const id = this.nextId++;
			this.media.set( id, { id, type: headers[ 'content-type' ], size: buffer ? buffer.length : 0, bytes: buffer, disposition: headers[ 'content-disposition' ] } );
			return [ 201, { id, source_url: `${ this.origin }/app/icons/icon-512.png`, media_details: { sizes: { thumbnail: { source_url: `${ this.origin }/app/icons/icon-192.png` } } } } ];
		}

		if ( method === 'POST' && path === '/writenow/v1/likes' ) {
			const likeUrl = String( ( body && body.url ) || '' ).trim();
			if ( ! /^https?:\/\/[^/\s]+\.[^\s]+/.test( likeUrl ) ) {
				return err( 400, 'writenow_invalid_url', 'That doesn’t look like a web address.' );
			}
			const existing = this.likes.find( ( like ) => like.url === likeUrl );
			if ( existing ) {
				return [ 200, { ...existing, duplicate: true, message: `Already liked: ${ existing.title }` } ];
			}
			const id = this.nextId++;
			const title = ( body && body.title ) || 'A post title';
			const like = { id, url: likeUrl, title, link: `https://jeremyfelt.com/liked/post-${ id }/`, date: new Date().toISOString() };
			this.likes.push( like );
			return [ 201, { ...like, duplicate: false, message: `Liked: ${ title }` } ];
		}

		if ( method === 'POST' && path === '/writenow/v1/notes' ) {
			const existing = body && body.client_id ? [ ...this.notes.values() ].find( ( note ) => note.client_id === body.client_id ) : null;
			if ( existing ) {
				return [ 200, { ...this.noteJson( existing ), message: existing.status === 'publish' ? 'Posted.' : 'Saved as a draft.' } ];
			}
			if ( ! body || ( ! String( body.content || '' ).trim() && ! ( body.media || [] ).length ) ) {
				return err( 400, 'writenow_empty_note', 'A note needs some text or an image.' );
			}
			const id = this.nextId++;
			const note = {
				id,
				status: body.status === 'publish' ? 'publish' : 'draft',
				content: body.content,
				reply_to_url: body.reply_to_url || '',
				reply_to_name: body.reply_to_name || '',
				media: ( body.media || [] ).map( ( m ) => ( { id: m.id, alt: m.alt, thumbnail: `${ this.origin }/app/icons/icon-192.png` } ) ),
				client_id: body.client_id || null,
				date: new Date().toISOString(),
				modified: new Date().toISOString(),
				hasMarkdown: true,
			};
			this.notes.set( id, note );
			return [ 201, { ...this.noteJson( note ), message: note.status === 'publish' ? 'Posted.' : 'Saved as a draft.' } ];
		}

		const noteMatch = path.match( /^\/writenow\/v1\/notes\/(\d+)$/ );
		if ( noteMatch ) {
			const note = this.notes.get( Number( noteMatch[ 1 ] ) );
			if ( ! note ) {
				return err( 404, 'writenow_not_found', 'That note doesn’t exist.' );
			}
			const json = this.noteJson( note );
			if ( method === 'GET' ) {
				return [ 200, { ...json, content: note.content, reply_to_url: note.reply_to_url, reply_to_name: note.reply_to_name, media: note.media, modified: note.modified } ];
			}
			if ( ! json.editable ) {
				return err( 409, 'writenow_not_editable', 'This note can’t be edited in WriteNow. Edit it in WordPress instead.' );
			}
			note.status = body.status === 'publish' ? 'publish' : 'draft';
			note.content = body.content;
			note.reply_to_url = body.reply_to_url || '';
			note.reply_to_name = body.reply_to_name || '';
			note.media = ( body.media || [] ).map( ( m ) => ( { id: m.id, alt: m.alt, thumbnail: `${ this.origin }/app/icons/icon-192.png` } ) );
			note.modified = new Date().toISOString();
			return [ 200, { ...this.noteJson( note ), message: note.status === 'publish' ? 'Posted.' : 'Saved as a draft.' } ];
		}

		if ( method === 'GET' && path === '/writenow/v1/recent' ) {
			if ( this.recentOverride ) {
				return [ 200, this.recentOverride ];
			}
			const notes = [ ...this.notes.values() ].map( ( note ) => ( {
				type: 'note',
				id: note.id,
				status: note.status,
				date: note.date,
				text: note.content.replace( /\s+/g, ' ' ).slice( 0, 140 ),
				...this.noteJson( note ),
			} ) );
			const likes = this.likes.map( ( like ) => ( {
				type: 'like',
				id: like.id,
				status: 'publish',
				date: like.date,
				text: like.title,
				url: like.url,
				link: like.link,
				edit_link: `https://jeremyfelt.com/wp-admin/post.php?post=${ like.id }&action=edit`,
			} ) );
			return [ 200, [ ...notes, ...likes ].sort( ( a, b ) => ( a.date < b.date ? 1 : -1 ) ) ];
		}

		return err( 404, 'rest_no_route', 'No route was found matching the URL and request method.' );
	}
}

module.exports = { FakeWP, USERNAME, PASSWORD, NONCE, LOGIN_COOKIE };
