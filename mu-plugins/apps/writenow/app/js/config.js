// Values the PHP side writes into the shell's <html> data attributes.
// A token that was never filled reads as empty, never as a URL.

const root = document.documentElement;

function read( name ) {
	const value = ( root.dataset[ name ] || '' ).trim();
	return value.includes( '{{' ) ? '' : value;
}

function withSlash( url ) {
	if ( ! url ) {
		return '';
	}
	return url.includes( '?' ) || url.endsWith( '/' ) ? url : url + '/';
}

export const config = Object.freeze( {
	assetVersion: read( 'assetVersion' ),
	appVersion: read( 'appVersion' ),
	restUrl: withSlash( read( 'restUrl' ) || new URL( '/wp-json/', location.href ).href ),
	adminUrl: withSlash( read( 'adminUrl' ) || new URL( '/wp-admin/', location.href ).href ),
	loginUrl: read( 'loginUrl' ) || new URL( '/wp-login.php', location.href ).href,
	fontSans: read( 'fontSans' ),
	fontMono: read( 'fontMono' ),
	// The app's own address, /writenow/, without any query.
	appUrl: new URL( './', location.href ).href,
} );

export const siteHost = location.hostname.replace( /^www\./, '' );
