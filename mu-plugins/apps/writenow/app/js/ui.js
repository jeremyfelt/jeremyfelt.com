// Small DOM helpers shared by the views. Everything is built with
// textContent; nothing from the server or the clipboard becomes HTML.

const SVG = 'http://www.w3.org/2000/svg';

export function el( tag, attrs = {}, ...children ) {
	const node = document.createElement( tag );
	for ( const [ key, value ] of Object.entries( attrs ) ) {
		if ( value === null || value === undefined || value === false ) {
			continue;
		}
		if ( key === 'class' ) {
			node.className = value;
		} else if ( key === 'text' ) {
			node.textContent = value;
		} else if ( key.startsWith( 'on' ) && typeof value === 'function' ) {
			node.addEventListener( key.slice( 2 ), value );
		} else if ( key === 'dataset' ) {
			Object.assign( node.dataset, value );
		} else {
			node.setAttribute( key, value === true ? '' : String( value ) );
		}
	}
	for ( const child of children.flat() ) {
		if ( child === null || child === undefined || child === false ) {
			continue;
		}
		node.append( child instanceof Node ? child : document.createTextNode( String( child ) ) );
	}
	return node;
}

export function icon( name ) {
	const svg = document.createElementNS( SVG, 'svg' );
	svg.setAttribute( 'aria-hidden', 'true' );
	svg.setAttribute( 'focusable', 'false' );
	const use = document.createElementNS( SVG, 'use' );
	use.setAttribute( 'href', `#i-${ name }` );
	svg.append( use );
	return svg;
}

// Show a plain sentence next to the control that caused it, optionally
// followed by a link or a button. `tone` is 'info', 'success' or 'error'.
// Passing null clears it.
export function setStatus( node, message ) {
	node.replaceChildren();
	node.classList.remove( 'is-info', 'is-success', 'is-error', 'is-shown' );
	if ( ! message ) {
		return;
	}
	const { text, tone = 'info', actions = [] } = typeof message === 'string' ? { text: message } : message;
	node.classList.add( `is-${ tone }`, 'is-shown' );
	node.append( el( 'span', { class: 'status-text', text } ) );
	for ( const action of actions ) {
		node.append( ' ' );
		if ( action.href ) {
			node.append( el( 'a', { href: action.href, target: '_blank', rel: 'noopener', class: 'status-action', text: action.label } ) );
		} else {
			node.append( el( 'button', { type: 'button', class: 'status-action', onclick: action.onClick, text: action.label } ) );
		}
	}
}

// Put a button into its working state at once. Returns a function that
// restores it.
export function busy( button, label ) {
	const original = button.textContent;
	button.textContent = label;
	button.disabled = true;
	button.setAttribute( 'aria-disabled', 'true' );
	button.classList.add( 'is-busy' );
	return () => {
		button.textContent = original;
		button.disabled = false;
		button.removeAttribute( 'aria-disabled' );
		button.classList.remove( 'is-busy' );
	};
}

// A destructive button that asks for a second tap instead of a modal.
export function confirmTap( button, { confirmLabel, onConfirm, timeout = 4000 } ) {
	const label = button.textContent;
	let armed = false;
	let timer = null;
	const disarm = () => {
		armed = false;
		clearTimeout( timer );
		button.textContent = label;
		button.classList.remove( 'is-armed' );
	};
	button.addEventListener( 'click', () => {
		if ( ! armed ) {
			armed = true;
			button.textContent = confirmLabel;
			button.classList.add( 'is-armed' );
			timer = setTimeout( disarm, timeout );
			return;
		}
		disarm();
		onConfirm();
	} );
	return disarm;
}

const rtf = typeof Intl !== 'undefined' && Intl.RelativeTimeFormat ? new Intl.RelativeTimeFormat( 'en', { numeric: 'auto' } ) : null;
const dayFormat = new Intl.DateTimeFormat( 'en', { month: 'short', day: 'numeric' } );
const yearFormat = new Intl.DateTimeFormat( 'en', { month: 'short', day: 'numeric', year: 'numeric' } );

export function relativeTime( value, now = Date.now() ) {
	const date = new Date( value );
	const time = date.getTime();
	if ( Number.isNaN( time ) ) {
		return '';
	}
	const seconds = Math.round( ( time - now ) / 1000 );
	const abs = Math.abs( seconds );
	if ( abs < 45 ) {
		return seconds > 0 ? 'in a moment' : 'just now';
	}
	if ( rtf ) {
		if ( abs < 3600 ) {
			return rtf.format( Math.round( seconds / 60 ), 'minute' );
		}
		if ( abs < 86400 ) {
			return rtf.format( Math.round( seconds / 3600 ), 'hour' );
		}
		if ( abs < 7 * 86400 ) {
			return rtf.format( Math.round( seconds / 86400 ), 'day' );
		}
	}
	return date.getFullYear() === new Date( now ).getFullYear() ? dayFormat.format( date ) : yearFormat.format( date );
}

export async function copyText( text, fallbackInput ) {
	try {
		await navigator.clipboard.writeText( text );
		return true;
	} catch {
		if ( fallbackInput ) {
			fallbackInput.focus();
			fallbackInput.select();
			fallbackInput.setSelectionRange( 0, text.length );
			try {
				return document.execCommand( 'copy' );
			} catch {
				return false;
			}
		}
		return false;
	}
}

export function hostOf( url ) {
	try {
		return new URL( url ).hostname.replace( /^www\./, '' );
	} catch {
		return '';
	}
}

// Accept what people paste: a bare domain gets https://, surrounding text
// is ignored if a URL is inside it.
export function normalizeUrl( input ) {
	const text = String( input || '' ).trim();
	if ( ! text ) {
		return '';
	}
	const found = text.match( /https?:\/\/[^\s<>"']+/i );
	let candidate = found ? found[ 0 ] : text;
	if ( ! /^https?:\/\//i.test( candidate ) && /^[\w-]+(\.[\w-]+)+(\/\S*)?$/.test( candidate ) ) {
		candidate = 'https://' + candidate;
	}
	try {
		const url = new URL( candidate );
		// Send it as written; the server normalises, and dedupes against
		// what earlier likes stored.
		return ( url.protocol === 'http:' || url.protocol === 'https:' ) && url.hostname.includes( '.' ) ? candidate : '';
	} catch {
		return '';
	}
}
