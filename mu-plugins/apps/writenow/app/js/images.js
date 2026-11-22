// Re-encode a picked photo before it is stored or uploaded: JPEG, long edge
// at most 2400px. Drawing through a canvas applies the EXIF orientation
// (browsers orient <img> by default) and drops all metadata, including
// location. HEIC from the iOS photo library decodes in Safari's <img>.

const MAX_EDGE = 2400;
const QUALITY = 0.85;

function loadImage( url ) {
	return new Promise( ( resolve, reject ) => {
		const img = new Image();
		img.decoding = 'async';
		img.onload = () => resolve( img );
		img.onerror = () => reject( new Error( 'decode' ) );
		img.src = url;
	} );
}

export async function reencode( file, maxEdge = MAX_EDGE ) {
	const url = URL.createObjectURL( file );
	try {
		const img = await loadImage( url );
		const width = img.naturalWidth;
		const height = img.naturalHeight;
		if ( ! width || ! height ) {
			throw new Error( 'empty' );
		}
		const scale = Math.min( 1, maxEdge / Math.max( width, height ) );
		const w = Math.max( 1, Math.round( width * scale ) );
		const h = Math.max( 1, Math.round( height * scale ) );

		const canvas = document.createElement( 'canvas' );
		canvas.width = w;
		canvas.height = h;
		const ctx = canvas.getContext( '2d' );
		// JPEG has no transparency; give PNGs a white page instead of black.
		ctx.fillStyle = '#ffffff';
		ctx.fillRect( 0, 0, w, h );
		ctx.imageSmoothingEnabled = true;
		ctx.imageSmoothingQuality = 'high';
		ctx.drawImage( img, 0, 0, w, h );

		const blob = await new Promise( ( resolve ) => canvas.toBlob( resolve, 'image/jpeg', QUALITY ) );
		// Release the canvas memory promptly; iOS is strict about it.
		canvas.width = 0;
		canvas.height = 0;
		if ( ! blob ) {
			throw new Error( 'encode' );
		}
		return { blob, width: w, height: h };
	} finally {
		URL.revokeObjectURL( url );
	}
}

export function jpegName( original ) {
	const base = String( original || 'photo' )
		.replace( /\.[^.]+$/, '' )
		.replace( /[^A-Za-z0-9_-]+/g, '-' )
		.replace( /^-+|-+$/g, '' )
		.slice( 0, 60 );
	return ( base || 'photo' ) + '.jpg';
}
