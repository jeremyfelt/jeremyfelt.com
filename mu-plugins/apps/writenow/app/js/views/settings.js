// The Settings view: who is connected, Disconnect, the Shortcut setup
// panel (spec section 7), and the app version.

import * as api from '../api.js';
import { config, siteHost } from '../config.js';
import * as credentials from '../credentials.js';
import { busy, confirmTap, copyText, el, hostOf, setStatus } from '../ui.js';

const $ = ( id ) => document.getElementById( id );

let onDisconnect = () => {};

export function profileUrl() {
	return new URL( 'profile.php#application-passwords-section', config.adminUrl ).href;
}

export function init( options ) {
	onDisconnect = options.onDisconnect;

	$( 'settings-admin' ).href = config.adminUrl;
	$( 'shortcut-like-url' ).value = api.restUrl( 'writenow/v1/likes' );
	$( 'shortcut-note-url' ).value = api.restUrl( 'writenow/v1/notes' );

	confirmTap( $( 'settings-disconnect' ), {
		confirmLabel: 'Tap again to disconnect',
		onConfirm: () => {
			credentials.clearCredential( 'disconnect' );
			onDisconnect();
		},
	} );

	$( 'shortcut-create' ).addEventListener( 'click', createShortcutPassword );

	for ( const button of document.querySelectorAll( '[data-copy]' ) ) {
		button.addEventListener( 'click', async () => {
			const input = $( button.dataset.copy );
			const ok = await copyText( input.value, input );
			const status = button.dataset.copy === 'shortcut-header' ? $( 'shortcut-status' ) : $( 'shortcut-copy-status' );
			setStatus( status, ok ? { tone: 'success', text: 'Copied.' } : { tone: 'error', text: 'Couldn’t copy. Select the text and copy it by hand.' } );
			button.textContent = ok ? 'Copied' : 'Copy';
			setTimeout( () => ( button.textContent = 'Copy' ), 2000 );
		} );
	}
}

export function shown() {
	const status = credentials.getStatus();
	const who = $( 'settings-who' );
	const name = status && status.user ? status.user.name : '';
	// The site's address reads better than its name, which is often the person's.
	const site = ( status && status.site && ( hostOf( status.site.url ) || status.site.name ) ) || siteHost;
	const cred = credentials.getCredential();
	who.replaceChildren(
		'Connected',
		name ? ' as ' : '',
		name ? el( 'strong', { text: name } ) : '',
		' to ',
		el( 'strong', { text: site } ),
		cred ? el( 'span', { class: 'hint block', text: `Using an Application Password for ${ cred.username }.` } ) : ''
	);

	const serverVersion = status && status.version ? ` · site API ${ status.version }` : '';
	$( 'settings-version' ).textContent = `WriteNow ${ config.appVersion || 'dev' }${ serverVersion }`;
}

// The header value is shown once and never stored.
export function hidden() {
	$( 'shortcut-header' ).value = '';
	$( 'shortcut-header-field' ).hidden = true;
	setStatus( $( 'shortcut-status' ), null );
}

async function createShortcutPassword() {
	const button = $( 'shortcut-create' );
	const status = $( 'shortcut-status' );
	const restore = busy( button, 'Creating…' );
	setStatus( status, 'Creating a password named “WriteNow Shortcut”…' );
	try {
		const cred = await api.createShortcutPassword( 'WriteNow Shortcut' );
		$( 'shortcut-header' ).value = api.authorizationHeader( cred );
		$( 'shortcut-header-field' ).hidden = false;
		setStatus( status, { tone: 'success', text: 'Created. Copy the header value below into the shortcut.' } );
	} catch ( error ) {
		setStatus( status, {
			tone: 'error',
			text: `Couldn’t create it: ${ error.message } You can also create one in your profile.`,
			actions: [ { label: 'Open profile', href: profileUrl() } ],
		} );
	} finally {
		restore();
	}
}
