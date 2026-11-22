// The Connect screen (spec section 5).
//
// Automatic path: ask admin-ajax for a REST nonce with the login cookie. If
// WordPress says nobody is logged in, go to wp-login.php and come back to
// /writenow/?connect=1. With a nonce, create an Application Password named
// "WriteNow" plus the date, check it with /status, and store it.
//
// Manual path: a username and an Application Password typed or pasted in.

import * as api from './api.js';
import { config, siteHost } from './config.js';
import * as credentials from './credentials.js';
import { busy, setStatus } from './ui.js';

const RETURN_FLAG = 'writenow.connectReturn';

const $ = ( id ) => document.getElementById( id );

let onConnected = () => {};
let describeSafe = async () => '';

export function init( options ) {
	onConnected = options.onConnected;
	describeSafe = options.describeSafe;

	$( 'connect-lede' ).textContent = `Post notes and likes to ${ siteHost }.`;
	$( 'connect-button' ).addEventListener( 'click', () => connect( false ) );
	$( 'connect-manual-form' ).addEventListener( 'submit', ( event ) => {
		event.preventDefault();
		connectManually();
	} );
}

// Called whenever the Connect screen is shown. `notice` explains why.
export async function show( notice = null ) {
	const username = $( 'manual-username' );
	if ( ! username.value ) {
		username.value = credentials.lastUsername();
	}
	setStatus( $( 'connect-status' ), notice );
	const safe = await describeSafe();
	$( 'connect-safe' ).textContent = safe;
	$( 'connect-safe' ).hidden = ! safe;
}

function passwordName() {
	const date = new Intl.DateTimeFormat( 'en', { year: 'numeric', month: 'short', day: 'numeric' } ).format( new Date() );
	return `WriteNow (${ date })`;
}

function loginUrl() {
	const url = new URL( config.loginUrl, location.href );
	const back = new URL( config.appUrl );
	back.searchParams.set( 'connect', '1' );
	url.searchParams.set( 'redirect_to', back.href );
	return url.href;
}

export function isReturningFromLogin() {
	let flagged = false;
	try {
		flagged = sessionStorage.getItem( RETURN_FLAG ) === '1';
		sessionStorage.removeItem( RETURN_FLAG );
	} catch {}
	return flagged;
}

// `resuming` is true when we have just come back from wp-login.php, so a
// missing login is reported instead of sending Jeremy round again.
export async function connect( resuming ) {
	const button = $( 'connect-button' );
	const status = $( 'connect-status' );
	if ( button.disabled ) {
		return;
	}
	const restore = busy( button, 'Connecting…' );
	setStatus( status, 'Checking your WordPress login…' );

	try {
		const nonce = await api.fetchRestNonce();
		if ( ! nonce ) {
			if ( resuming ) {
				restore();
				setStatus( status, {
					tone: 'error',
					text: 'WordPress still says you’re logged out, so WriteNow couldn’t connect. Try again, or use an Application Password below.',
				} );
				return;
			}
			setStatus( status, 'Opening the WordPress login. You’ll come straight back here.' );
			try {
				sessionStorage.setItem( RETURN_FLAG, '1' );
			} catch {}
			location.assign( loginUrl() );
			return;
		}

		setStatus( status, 'Creating a password for WriteNow…' );
		const cred = await api.createPasswordWithCookie( nonce, passwordName() );
		const siteStatus = await api.getStatus( cred );
		credentials.saveCredential( cred.username, cred.password, siteStatus );
		restore();
		setStatus( status, { tone: 'success', text: 'Connected.' } );
		onConnected( { how: 'automatic' } );
	} catch ( error ) {
		restore();
		const reason = error instanceof api.ApiError ? error.message : 'Something went wrong.';
		setStatus( status, {
			tone: 'error',
			text: error.offline
				? `${ reason } Connecting needs a connection; your writing stays on this phone.`
				: `Couldn’t connect: ${ reason } You can use an Application Password below instead.`,
			actions: error.offline ? [ { label: 'Try again', onClick: () => connect( false ) } ] : [],
		} );
	}
}

async function connectManually() {
	const username = $( 'manual-username' ).value.trim();
	const password = $( 'manual-password' ).value.trim();
	const status = $( 'manual-status' );

	if ( ! username || ! password ) {
		setStatus( status, { tone: 'error', text: 'Enter both your username and the Application Password.' } );
		( username ? $( 'manual-password' ) : $( 'manual-username' ) ).focus();
		return;
	}

	const restore = busy( $( 'manual-submit' ), 'Checking…' );
	setStatus( status, 'Checking the password with your site…' );
	try {
		const siteStatus = await api.getStatus( { username, password } );
		credentials.saveCredential( username, password, siteStatus );
		$( 'manual-password' ).value = '';
		restore();
		setStatus( status, { tone: 'success', text: 'Connected.' } );
		onConnected( { how: 'manual' } );
	} catch ( error ) {
		restore();
		let text = `Couldn’t connect: ${ error.message }`;
		if ( error.status === 401 || error.status === 403 ) {
			text = 'That username and Application Password didn’t work. Check them and try again.';
		} else if ( error.offline ) {
			text = `${ error.message } Connecting needs a connection.`;
		}
		setStatus( status, { tone: 'error', text } );
	}
}
