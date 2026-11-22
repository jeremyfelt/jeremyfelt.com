/**
 * Open the real block editor on the local test site in Chromium and ask it
 * whether WriteNow's block markup is valid.
 *
 * 1. Every converter fixture (written by the PHP tests to
 *    <site>/block-fixtures.json) is parsed with wp.blocks.parse(), and every
 *    block and inner block must come back with isValid true.
 * 2. A note created through the WriteNow API over Basic auth is opened in
 *    the editor, and every block the editor loaded must be valid.
 *
 * Run by tests/writenow/run.sh after the PHP tests. Needs Playwright and a
 * Chromium-based browser (CHROME_PATH, or Playwright's own, or google-chrome).
 */

const fs = require( 'fs' );
const { chromium } = require( 'playwright' );

const SITE = process.env.WRITENOW_TEST_SITE;
const URL = process.env.WRITENOW_TEST_URL;

function executablePath() {
	if ( process.env.CHROME_PATH ) {
		return process.env.CHROME_PATH;
	}

	try {
		const bundled = chromium.executablePath();
		if ( fs.existsSync( bundled ) ) {
			return undefined;
		}
	} catch ( error ) {
		// Fall through to a system browser.
	}

	return [ '/usr/bin/google-chrome', '/usr/bin/chromium', '/usr/bin/chromium-browser' ].find( ( path ) => fs.existsSync( path ) );
}

async function main() {
	const fixtures = JSON.parse( fs.readFileSync( `${ SITE }/block-fixtures.json`, 'utf8' ) );
	const credentials = JSON.parse( fs.readFileSync( `${ SITE }/credentials.json`, 'utf8' ) ).admin;
	const auth = 'Basic ' + Buffer.from( `${ credentials.username }:${ credentials.password }` ).toString( 'base64' );

	// A note with most of what the converter does, created the way the app creates one.
	const created = await fetch( `${ URL }/wp-json/writenow/v1/notes`, {
		method: 'POST',
		headers: { 'Content-Type': 'application/json', Authorization: auth },
		body: JSON.stringify( {
			content: [
				'Hello **bold** and *italic* with `code` and a [link](https://example.com/a?b=1&c=2).',
				'A bare https://example.com/ link.',
				'- one\n- two',
				'1. first\n2. second',
				'> quoted *text*',
				'```\nconst a = [1];\n```',
				'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
				'#hashtag <b>not html</b> & more',
			].join( '\n\n' ),
			status: 'draft',
		} ),
	} );
	const note = await created.json();

	if ( 201 !== created.status ) {
		throw new Error( `Could not create a note: ${ JSON.stringify( note ) }` );
	}

	const browser = await chromium.launch( { executablePath: executablePath() } );
	const page = await browser.newPage();
	const failures = [];

	await page.goto( `${ URL }/wp-login.php` );
	await page.fill( '#user_login', 'admin' );
	await page.fill( '#user_pass', 'password' );
	await Promise.all( [ page.waitForNavigation(), page.click( '#wp-submit' ) ] );

	// 1. Parse every fixture in the editor.
	await page.goto( `${ URL }/wp-admin/post-new.php?post_type=shortnote` );
	await page.waitForFunction( () => window.wp && wp.blocks && wp.blocks.getBlockType( 'core/paragraph' ), null, { timeout: 60000 } );

	const results = await page.evaluate( ( all ) => {
		const problems = [];
		const check = ( name, blocks ) => {
			for ( const block of blocks ) {
				if ( ! block.isValid || 'core/missing' === block.name || 'core/freeform' === block.name ) {
					problems.push( { name, block: block.name, issues: ( block.validationIssues || [] ).map( ( issue ) => issue.args && issue.args.join( ' ' ) ) } );
				}
				check( name, block.innerBlocks );
			}
		};

		let count = 0;
		for ( const [ name, markup ] of Object.entries( all ) ) {
			const blocks = wp.blocks.parse( markup );
			count += blocks.length;
			check( name, blocks );
		}

		return { problems, count };
	}, fixtures );

	failures.push( ...results.problems.map( ( problem ) => `Fixture "${ problem.name }": ${ problem.block } is invalid. ${ problem.issues.join( ' | ' ) }` ) );
	console.log( `  ${ results.problems.length ? 'FAIL' : 'pass' }  editor: ${ Object.keys( fixtures ).length } fixtures (${ results.count } top-level blocks) parse as valid blocks` );

	// 2. Open the note WriteNow created and check what the editor loaded.
	await page.goto( `${ URL }/wp-admin/post.php?post=${ note.id }&action=edit` );
	await page.waitForFunction( () => window.wp && wp.data && wp.data.select( 'core/block-editor' ).getBlocks().length > 0, null, { timeout: 60000 } );

	const loaded = await page.evaluate( () => {
		const flat = [];
		const walk = ( blocks ) => blocks.forEach( ( block ) => {
			flat.push( { name: block.name, isValid: block.isValid } );
			walk( block.innerBlocks );
		} );
		walk( wp.data.select( 'core/block-editor' ).getBlocks() );
		return flat;
	} );

	const invalid = loaded.filter( ( block ) => ! block.isValid || 'core/missing' === block.name || 'core/freeform' === block.name );
	failures.push( ...invalid.map( ( block ) => `Note ${ note.id } in the editor: ${ block.name } is invalid` ) );
	console.log( `  ${ invalid.length ? 'FAIL' : 'pass' }  editor: a WriteNow note opens with ${ loaded.length } blocks, all valid (${ [ ...new Set( loaded.map( ( block ) => block.name ) ) ].join( ', ' ) })` );

	await browser.close();

	if ( failures.length ) {
		console.log( '\n' + failures.join( '\n' ) );
		process.exit( 1 );
	}
}

main().catch( ( error ) => {
	console.error( error );
	process.exit( 1 );
} );
