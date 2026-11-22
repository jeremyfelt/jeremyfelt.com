// WriteNow: boot, navigation, keyboard tracking, deep links and the
// outbox triggers.

import * as api from './api.js';
import { config } from './config.js';
import * as connect from './connect.js';
import * as credentials from './credentials.js';
import * as outbox from './outbox.js';
import * as like from './views/like.js';
import * as note from './views/note.js';
import * as recent from './views/recent.js';
import * as settings from './views/settings.js';

const $ = ( id ) => document.getElementById( id );
const root = document.documentElement;

const VIEWS = [ 'note', 'like', 'recent', 'settings' ];
let currentView = 'note';

// ---- Fonts: the theme's, when the server says it has them ----

function loadFonts() {
	if ( ! ( 'FontFace' in window ) ) {
		return;
	}
	const faces = [
		[ 'inter', config.fontSans, { weight: '300 900', display: 'fallback' } ],
		[ 'monaspace', config.fontMono, { weight: '300 400', display: 'fallback' } ],
	];
	for ( const [ family, url, descriptors ] of faces ) {
		if ( ! url ) {
			continue;
		}
		const face = new FontFace( family, `url("${ url.replace( /["\\]/g, '' ) }")`, descriptors );
		document.fonts.add( face );
		face.load().catch( () => {} );
	}
}

// ---- Keyboard and viewport ----
//
// iOS does not resize the layout viewport for the on-screen keyboard. The
// app is position: fixed and sized to the visual viewport instead, so the
// action bar sits directly above the keyboard and nothing behind scrolls.

function trackViewport() {
	const vv = window.visualViewport;
	const update = () => {
		const height = vv ? vv.height : window.innerHeight;
		const top = vv ? vv.offsetTop : 0;
		root.style.setProperty( '--app-height', `${ Math.round( height ) }px` );
		root.style.setProperty( '--app-top', `${ Math.round( top ) }px` );
		const focused = document.activeElement;
		const typing = focused && ( focused.tagName === 'TEXTAREA' || ( focused.tagName === 'INPUT' && focused.type !== 'file' ) );
		const keyboard = Boolean( vv ) && typing && window.innerHeight - vv.height > 120;
		root.toggleAttribute( 'data-keyboard', keyboard );
	};
	if ( vv ) {
		vv.addEventListener( 'resize', update );
		vv.addEventListener( 'scroll', update );
	}
	window.addEventListener( 'resize', update );
	document.addEventListener( 'focusin', update );
	document.addEventListener( 'focusout', () => setTimeout( update, 50 ) );
	// Keep the document itself from scrolling when iOS tries to bring an
	// input into view; the app's own scroll areas handle that.
	window.addEventListener( 'scroll', () => {
		if ( window.scrollY !== 0 && ! vv ) {
			window.scrollTo( 0, 0 );
		}
	} );
	update();
}

// ---- Views ----

export function show( name, { focus = true } = {} ) {
	if ( ! VIEWS.includes( name ) ) {
		name = 'note';
	}
	const previous = currentView;
	currentView = name;
	for ( const view of VIEWS ) {
		$( `view-${ view }` ).hidden = view !== name;
	}
	for ( const tab of document.querySelectorAll( '.tab' ) ) {
		if ( tab.dataset.view === name ) {
			tab.setAttribute( 'aria-current', 'page' );
		} else {
			tab.removeAttribute( 'aria-current' );
		}
	}
	if ( previous === 'settings' && name !== 'settings' ) {
		settings.hidden();
	}
	if ( name === 'recent' ) {
		recent.shown();
	}
	if ( name === 'settings' ) {
		settings.shown();
	}
	if ( focus ) {
		if ( name === 'note' ) {
			note.focusText();
		} else {
			$( `${ name }-heading` ).focus( { preventScroll: true } );
		}
	}
}

function showApp( view = currentView ) {
	$( 'view-connect' ).hidden = true;
	$( 'views' ).hidden = false;
	$( 'tabbar' ).hidden = false;
	show( view );
}

async function describeSafe() {
	const parts = [];
	if ( note.hasUnsavedWork() ) {
		parts.push( 'Your note in progress is saved on this phone.' );
	}
	const waiting = await outbox.count().catch( () => 0 );
	if ( waiting ) {
		parts.push( waiting === 1 ? '1 item is waiting to send.' : `${ waiting } items are waiting to send.` );
	}
	return parts.join( ' ' );
}

function showConnect( notice = null ) {
	$( 'views' ).hidden = true;
	$( 'tabbar' ).hidden = true;
	$( 'view-connect' ).hidden = false;
	connect.show( notice );
	$( 'connect-heading' ).focus( { preventScroll: true } );
}

async function updateBadge() {
	const waiting = await outbox.count().catch( () => 0 );
	const badge = $( 'tab-badge' );
	const tab = document.querySelector( '.tab[data-view="recent"]' );
	badge.hidden = waiting === 0;
	badge.textContent = String( waiting );
	if ( waiting ) {
		tab.setAttribute( 'aria-label', `Recent, ${ waiting } waiting to send` );
	} else {
		tab.removeAttribute( 'aria-label' );
	}
}

// ---- Outbox triggers ----

let flushTimer = null;
function flushSoon() {
	clearTimeout( flushTimer );
	flushTimer = setTimeout( () => {
		if ( credentials.isConnected() ) {
			outbox.flush();
			note.retryWaitingUploads();
		}
	}, 300 );
}

// ---- Status check ----

async function checkStatus() {
	if ( ! credentials.isConnected() ) {
		return;
	}
	try {
		credentials.saveStatus( await api.getStatus() );
		if ( currentView === 'settings' ) {
			settings.shown();
		}
	} catch {
		// Offline or a server hiccup: keep the cached status. A 401 has
		// already sent the app back to Connect.
	}
}

// ---- Deep links ----

function readDeepLink() {
	const params = new URLSearchParams( location.search );
	const link = {
		connect: params.get( 'connect' ) === '1',
		like: params.get( 'like' ),
		title: params.get( 'title' ),
		text: params.get( 'text' ),
	};
	if ( location.search ) {
		history.replaceState( null, '', config.appUrl + location.hash );
	}
	return link;
}

// ---- Service worker ----

function registerServiceWorker() {
	if ( ! ( 'serviceWorker' in navigator ) ) {
		return;
	}
	navigator.serviceWorker.register( new URL( 'sw.js', config.appUrl ).href, { scope: config.appUrl } ).catch( () => {} );
}

// ---- Boot ----

function boot() {
	loadFonts();
	trackViewport();

	api.setAuth( {
		credential: credentials.getCredential,
		unauthorized: () => credentials.clearCredential( 'unauthorized' ),
	} );

	const link = readDeepLink();

	connect.init( {
		describeSafe,
		onConnected: () => {
			checkStatus();
			showApp( currentView );
			flushSoon();
		},
	} );
	note.init( { show } );
	like.init( { show } );
	recent.init( { show, openDraft: note.openDraft } );
	settings.init( {
		onDisconnect: () => {
			recent.clearCache();
			showConnect( {
				tone: 'info',
				text: 'Disconnected. The password still exists on your site until you revoke it.',
				actions: [ { label: 'Revoke in your profile', href: settings.profileUrl() } ],
			} );
		},
	} );

	for ( const tab of document.querySelectorAll( '.tab' ) ) {
		tab.addEventListener( 'click', () => show( tab.dataset.view ) );
	}

	credentials.onCredentialChange( ( { connected, reason } ) => {
		if ( ! connected && reason === 'unauthorized' ) {
			showConnect( {
				tone: 'error',
				text: 'Your site no longer accepts WriteNow’s password, so it needs to connect again. Nothing you wrote has been lost.',
			} );
		} else if ( ! connected ) {
			showConnect();
		}
	} );

	outbox.subscribe( ( event ) => {
		if ( event.type === 'change' ) {
			updateBadge();
		}
		if ( event.type === 'sent' && event.background ) {
			note.announceSent( event.item, event.data );
		}
	} );

	if ( link.text ) {
		note.prefill( link.text );
	}
	if ( link.like ) {
		like.prefill( link.like, link.title || '' );
		currentView = 'like';
	}

	updateBadge();

	if ( credentials.isConnected() ) {
		showApp( currentView );
		checkStatus();
		flushSoon();
	} else {
		const returning = connect.isReturningFromLogin() || link.connect;
		showConnect();
		if ( returning ) {
			connect.connect( true );
		}
	}

	window.addEventListener( 'online', flushSoon );
	document.addEventListener( 'visibilitychange', () => {
		if ( document.visibilityState === 'visible' ) {
			flushSoon();
			checkStatus();
			if ( currentView === 'recent' ) {
				recent.refresh( { quiet: true } );
			}
		}
	} );

	if ( navigator.storage && navigator.storage.persist ) {
		navigator.storage.persist().catch( () => {} );
	}
	registerServiceWorker();
	root.classList.add( 'is-ready' );
}

boot();
