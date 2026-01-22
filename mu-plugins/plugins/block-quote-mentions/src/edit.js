/**
 * Edit component for the Quote Mention block.
 */

import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback } from '@wordpress/element';
import {
	useBlockProps,
	BlockControls,
	InspectorControls,
	RichText,
} from '@wordpress/block-editor';
import {
	Placeholder,
	Button,
	Spinner,
	TextControl,
	PanelBody,
	CheckboxControl,
	ToolbarGroup,
	ToolbarButton,
	Notice,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { useDebounce } from '@wordpress/compose';

/**
 * Get a friendly block type name.
 *
 * @param {string} blockName The block name (e.g., 'core/paragraph').
 * @return {string} Friendly name.
 */
function getBlockTypeName( blockName ) {
	const names = {
		'core/paragraph': 'Paragraph',
		'core/heading': 'Heading',
		'core/list': 'List',
		'core/quote': 'Quote',
		'core/code': 'Code',
		'core/preformatted': 'Preformatted',
		'core/pullquote': 'Pullquote',
		'core/image': 'Image',
		'core/gallery': 'Gallery',
		'core/video': 'Video',
		'core/audio': 'Audio',
		'core/embed': 'Embed',
		'core/html': 'HTML',
		'core/table': 'Table',
		'core/columns': 'Columns',
		'core/column': 'Column',
		'core/group': 'Group',
		'core/separator': 'Separator',
		'core/spacer': 'Spacer',
	};

	return names[ blockName ] || blockName.replace( 'core/', '' ).replace( '/', ' - ' );
}

/**
 * Edit component.
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Set attributes function.
 * @return {JSX.Element} Edit component.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		sourcePostId,
		sourcePostTitle,
		sourcePostUrl,
		selectedBlockIds,
		quotedContent,
		quotedContentHtml,
		citation,
	} = attributes;

	const blockProps = useBlockProps();

	// Component state.
	const [ step, setStep ] = useState( sourcePostId ? 'preview' : 'search' );
	const [ searchQuery, setSearchQuery ] = useState( '' );
	const [ searchResults, setSearchResults ] = useState( [] );
	const [ isSearching, setIsSearching ] = useState( false );
	const [ selectedPost, setSelectedPost ] = useState( null );
	const [ postBlocks, setPostBlocks ] = useState( [] );
	const [ isLoadingBlocks, setIsLoadingBlocks ] = useState( false );
	const [ localSelectedBlockIds, setLocalSelectedBlockIds ] = useState( [] );
	const [ error, setError ] = useState( null );

	/**
	 * Search for posts.
	 */
	const searchPosts = useCallback( async ( query ) => {
		setIsSearching( true );
		setError( null );

		try {
			const results = await apiFetch( {
				path: `/jeremyfelt/v1/posts/search?search=${ encodeURIComponent( query ) }&per_page=10`,
			} );
			setSearchResults( results );
		} catch ( err ) {
			setError( __( 'Failed to search posts. Please try again.', 'jeremyfelt' ) );
			setSearchResults( [] );
		}

		setIsSearching( false );
	}, [] );

	const debouncedSearch = useDebounce( searchPosts, 300 );

	/**
	 * Handle search input change.
	 */
	useEffect( () => {
		if ( step === 'search' ) {
			debouncedSearch( searchQuery );
		}
	}, [ searchQuery, step, debouncedSearch ] );

	/**
	 * Load initial search results.
	 */
	useEffect( () => {
		if ( step === 'search' && searchResults.length === 0 && ! isSearching ) {
			searchPosts( '' );
		}
	}, [ step, searchResults.length, isSearching, searchPosts ] );

	/**
	 * Load blocks from selected post.
	 *
	 * @param {Object} post The selected post object.
	 */
	const loadPostBlocks = async ( post ) => {
		setIsLoadingBlocks( true );
		setError( null );
		setSelectedPost( post );
		setStep( 'select-blocks' );

		try {
			const response = await apiFetch( {
				path: `/jeremyfelt/v1/posts/${ post.id }/blocks`,
			} );
			setPostBlocks( response.blocks );
		} catch ( err ) {
			setError( __( 'Failed to load blocks from post.', 'jeremyfelt' ) );
			setPostBlocks( [] );
		}

		setIsLoadingBlocks( false );
	};

	/**
	 * Toggle block selection.
	 *
	 * @param {string} blockId The block ID to toggle.
	 */
	const toggleBlockSelection = ( blockId ) => {
		setLocalSelectedBlockIds( ( prev ) => {
			if ( prev.includes( blockId ) ) {
				return prev.filter( ( id ) => id !== blockId );
			}
			return [ ...prev, blockId ];
		} );
	};

	/**
	 * Confirm block selection and save to attributes.
	 */
	const confirmSelection = () => {
		if ( localSelectedBlockIds.length === 0 ) {
			setError( __( 'Please select at least one block to quote.', 'jeremyfelt' ) );
			return;
		}

		// Get the selected blocks content.
		const selectedBlocks = postBlocks.filter( ( block ) =>
			localSelectedBlockIds.includes( block.id )
		);

		// Sort by original index to maintain order.
		selectedBlocks.sort( ( a, b ) => a.index - b.index );

		// Combine content.
		const combinedHtml = selectedBlocks.map( ( block ) => block.content ).join( '\n' );
		const combinedText = selectedBlocks.map( ( block ) => block.preview ).join( '\n\n' );

		setAttributes( {
			sourcePostId: selectedPost.id,
			sourcePostTitle: selectedPost.title,
			sourcePostUrl: selectedPost.url,
			selectedBlockIds: localSelectedBlockIds,
			quotedContent: combinedText,
			quotedContentHtml: combinedHtml,
			citation: selectedPost.title,
		} );

		setStep( 'preview' );
	};

	/**
	 * Reset the block to start over.
	 */
	const resetBlock = () => {
		setAttributes( {
			sourcePostId: 0,
			sourcePostTitle: '',
			sourcePostUrl: '',
			selectedBlockIds: [],
			quotedContent: '',
			quotedContentHtml: '',
			citation: '',
		} );
		setSelectedPost( null );
		setPostBlocks( [] );
		setLocalSelectedBlockIds( [] );
		setSearchQuery( '' );
		setStep( 'search' );
	};

	/**
	 * Render the search step.
	 */
	const renderSearchStep = () => (
		<Placeholder
			icon="format-quote"
			label={ __( 'Quote Mention', 'jeremyfelt' ) }
			instructions={ __( 'Search for a post to quote from.', 'jeremyfelt' ) }
		>
			<div className="quote-mention-search">
				<TextControl
					label={ __( 'Search posts', 'jeremyfelt' ) }
					value={ searchQuery }
					onChange={ setSearchQuery }
					placeholder={ __( 'Type to search...', 'jeremyfelt' ) }
				/>

				{ error && (
					<Notice status="error" isDismissible={ false }>
						{ error }
					</Notice>
				) }

				{ isSearching && <Spinner /> }

				{ ! isSearching && searchResults.length > 0 && (
					<ul className="quote-mention-search__results">
						{ searchResults.map( ( post ) => (
							<li key={ post.id } className="quote-mention-search__result">
								<Button
									variant="secondary"
									onClick={ () => loadPostBlocks( post ) }
									className="quote-mention-search__result-button"
								>
									<span className="quote-mention-search__result-title">
										{ post.title || __( '(No title)', 'jeremyfelt' ) }
									</span>
									<span className="quote-mention-search__result-meta">
										{ post.date } &middot; { post.post_type }
									</span>
								</Button>
							</li>
						) ) }
					</ul>
				) }

				{ ! isSearching && searchResults.length === 0 && searchQuery && (
					<p className="quote-mention-search__no-results">
						{ __( 'No posts found.', 'jeremyfelt' ) }
					</p>
				) }
			</div>
		</Placeholder>
	);

	/**
	 * Render the block selection step.
	 */
	const renderSelectBlocksStep = () => (
		<Placeholder
			icon="format-quote"
			label={ __( 'Select Blocks to Quote', 'jeremyfelt' ) }
			instructions={
				selectedPost
					? `${ __( 'From:', 'jeremyfelt' ) } ${ selectedPost.title }`
					: ''
			}
		>
			<div className="quote-mention-blocks">
				{ error && (
					<Notice status="error" isDismissible={ false }>
						{ error }
					</Notice>
				) }

				{ isLoadingBlocks && <Spinner /> }

				{ ! isLoadingBlocks && postBlocks.length > 0 && (
					<>
						<ul className="quote-mention-blocks__list">
							{ postBlocks.map( ( block ) => (
								<li key={ block.id } className="quote-mention-blocks__item">
									<CheckboxControl
										label={
											<span className="quote-mention-blocks__item-label">
												<strong>{ getBlockTypeName( block.blockName ) }:</strong>{ ' ' }
												{ block.preview }
											</span>
										}
										checked={ localSelectedBlockIds.includes( block.id ) }
										onChange={ () => toggleBlockSelection( block.id ) }
									/>
								</li>
							) ) }
						</ul>

						<div className="quote-mention-blocks__actions">
							<Button variant="secondary" onClick={ () => setStep( 'search' ) }>
								{ __( 'Back', 'jeremyfelt' ) }
							</Button>
							<Button
								variant="primary"
								onClick={ confirmSelection }
								disabled={ localSelectedBlockIds.length === 0 }
							>
								{ __( 'Insert Quote', 'jeremyfelt' ) }
							</Button>
						</div>
					</>
				) }

				{ ! isLoadingBlocks && postBlocks.length === 0 && (
					<>
						<p>{ __( 'No quotable blocks found in this post.', 'jeremyfelt' ) }</p>
						<Button variant="secondary" onClick={ () => setStep( 'search' ) }>
							{ __( 'Back', 'jeremyfelt' ) }
						</Button>
					</>
				) }
			</div>
		</Placeholder>
	);

	/**
	 * Render the preview/edit step.
	 */
	const renderPreviewStep = () => (
		<figure className="wp-block-quote-mention">
			<blockquote className="wp-block-quote-mention__quote">
				<div
					className="wp-block-quote-mention__content"
					dangerouslySetInnerHTML={ { __html: quotedContentHtml } }
				/>
			</blockquote>
			{ ( citation || sourcePostUrl ) && (
				<figcaption className="wp-block-quote-mention__citation">
					<cite>
						{ sourcePostUrl ? (
							<a href={ sourcePostUrl } className="wp-block-quote-mention__source-link">
								{ citation }
							</a>
						) : (
							citation
						) }
					</cite>
				</figcaption>
			) }
		</figure>
	);

	return (
		<>
			<BlockControls>
				{ step === 'preview' && (
					<ToolbarGroup>
						<ToolbarButton
							icon="edit"
							label={ __( 'Change quoted content', 'jeremyfelt' ) }
							onClick={ resetBlock }
						/>
					</ToolbarGroup>
				) }
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Quote Settings', 'jeremyfelt' ) }>
					{ sourcePostId > 0 && (
						<>
							<p>
								<strong>{ __( 'Source:', 'jeremyfelt' ) }</strong>{ ' ' }
								<a href={ sourcePostUrl } target="_blank" rel="noopener noreferrer">
									{ sourcePostTitle }
								</a>
							</p>
							<TextControl
								label={ __( 'Citation Text', 'jeremyfelt' ) }
								value={ citation }
								onChange={ ( value ) => setAttributes( { citation: value } ) }
								help={ __( 'The text shown as the quote attribution.', 'jeremyfelt' ) }
							/>
							<Button
								variant="secondary"
								onClick={ resetBlock }
								isDestructive
							>
								{ __( 'Choose Different Post', 'jeremyfelt' ) }
							</Button>
						</>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ step === 'search' && renderSearchStep() }
				{ step === 'select-blocks' && renderSelectBlocksStep() }
				{ step === 'preview' && renderPreviewStep() }
			</div>
		</>
	);
}
