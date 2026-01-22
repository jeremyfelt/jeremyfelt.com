/**
 * Quote Mention Block
 *
 * A Gutenberg block for quoting content from other posts
 * with automatic mention tracking.
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

import Edit from './edit';
import metadata from '../block.json';

import './style.scss';
import './editor.scss';

/**
 * Block icon - a quote with a link indicator.
 */
const blockIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		width="24"
		height="24"
	>
		<path d="M13 6v6h5.2v4c0 .8-.2 1.4-.5 1.7-.6.6-1.6.6-2.5.5h-.3v1.5h.5c1.2 0 2.3-.1 3.1-1 .6-.6 1-1.6 1-2.8V6H13zm-9 6h5.2v4c0 .8-.2 1.4-.5 1.7-.6.6-1.6.6-2.5.5h-.3v1.5h.5c1.2 0 2.3-.1 3.1-1 .6-.6 1-1.6 1-2.8V6H4v6z" />
	</svg>
);

/**
 * Register the block.
 */
registerBlockType( metadata.name, {
	icon: blockIcon,
	edit: Edit,
	save: () => null, // Dynamic block - rendered via PHP.
} );
