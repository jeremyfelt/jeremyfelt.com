// Exports the committed PNG icons from the SVG sources in app/icons.
// Run: node tests/writenow/client/tools/render-icons.js
const fs = require( 'fs' );
const path = require( 'path' );
const { chromium } = require( 'playwright' );

const dir = path.resolve( __dirname, '../../../../mu-plugins/apps/writenow/app/icons' );
const jobs = [
	[ 'icon.svg', 192, 'icon-192.png', true ],
	[ 'icon.svg', 512, 'icon-512.png', true ],
	[ 'icon-maskable.svg', 512, 'icon-maskable-512.png', false ],
	// iOS rounds the corners itself and wants an opaque square.
	[ 'icon-maskable.svg', 180, 'apple-touch-icon.png', false, 0.86 / 0.74 ],
];

( async () => {
	const browser = await chromium.launch();
	const page = await browser.newPage( { deviceScaleFactor: 1 } );
	for ( const [ source, size, out, transparent, zoom = 1 ] of jobs ) {
		const svg = fs.readFileSync( path.join( dir, source ), 'utf8' );
		const data = 'data:image/svg+xml;base64,' + Buffer.from( svg ).toString( 'base64' );
		await page.setViewportSize( { width: size, height: size } );
		await page.setContent(
			`<style>html,body{margin:0;background:transparent;overflow:hidden}div{width:${ size }px;height:${ size }px;background:#222 url(${ data }) center/${ size * zoom }px no-repeat}</style><div></div>`
		);
		if ( transparent ) {
			await page.setContent( `<style>html,body{margin:0;background:transparent}</style><img src="${ data }" width="${ size }" height="${ size }">` );
		}
		await page.screenshot( { path: path.join( dir, out ), omitBackground: transparent, clip: { x: 0, y: 0, width: size, height: size } } );
		console.log( 'wrote', out );
	}
	await browser.close();
} )();
