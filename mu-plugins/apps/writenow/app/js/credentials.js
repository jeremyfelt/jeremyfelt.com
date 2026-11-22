// The stored Application Password, and the last /status response.
//
// The credential lives in localStorage on the phone (spec section 5). It is
// read only by the API client and never written anywhere else.

const CREDENTIAL_KEY = 'writenow.credential';
const STATUS_KEY = 'writenow.status';
const USERNAME_KEY = 'writenow.lastUsername';

const listeners = new Set();

function readJson( key ) {
	try {
		const raw = localStorage.getItem( key );
		return raw ? JSON.parse( raw ) : null;
	} catch {
		return null;
	}
}

function writeJson( key, value ) {
	try {
		if ( value === null ) {
			localStorage.removeItem( key );
		} else {
			localStorage.setItem( key, JSON.stringify( value ) );
		}
		return true;
	} catch {
		return false;
	}
}

// Kept in memory too, so a phone that refuses localStorage still works for
// the session.
let current = readJson( CREDENTIAL_KEY );

export function getCredential() {
	return current && current.username && current.password ? current : null;
}

export function isConnected() {
	return getCredential() !== null;
}

export function saveCredential( username, password, status = null ) {
	current = { username: String( username ).trim(), password: String( password ).trim() };
	writeJson( CREDENTIAL_KEY, current );
	try {
		localStorage.setItem( USERNAME_KEY, current.username );
	} catch {}
	if ( status ) {
		saveStatus( status );
	}
	emit();
}

// `reason` is 'disconnect' (Jeremy chose it) or 'unauthorized' (a 401).
export function clearCredential( reason = 'disconnect' ) {
	const had = current !== null;
	current = null;
	writeJson( CREDENTIAL_KEY, null );
	if ( had ) {
		emit( reason );
	}
}

export function lastUsername() {
	try {
		return localStorage.getItem( USERNAME_KEY ) || '';
	} catch {
		return '';
	}
}

export function getStatus() {
	return readJson( STATUS_KEY );
}

export function saveStatus( status ) {
	if ( ! status || typeof status !== 'object' ) {
		return;
	}
	writeJson( STATUS_KEY, {
		user: status.user || null,
		site: status.site || null,
		features: status.features || null,
		note_soft_limit: Number( status.note_soft_limit ) || 500,
		version: status.version || '',
	} );
}

export function onCredentialChange( listener ) {
	listeners.add( listener );
	return () => listeners.delete( listener );
}

function emit( reason ) {
	for ( const listener of listeners ) {
		listener( { connected: isConnected(), reason } );
	}
}

// Another window changed the credential.
window.addEventListener( 'storage', ( event ) => {
	if ( event.key === CREDENTIAL_KEY ) {
		current = readJson( CREDENTIAL_KEY );
		emit( current ? undefined : 'disconnect' );
	}
} );
