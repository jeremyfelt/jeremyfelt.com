<?php
/**
 * Block Quote Mentions
 *
 * Provides a Gutenberg block for quoting content from other posts
 * with automatic mention tracking.
 *
 * @package JeremyFelt\Plugins\BlockQuoteMentions
 */

namespace JeremyFelt\Plugins\BlockQuoteMentions;

add_action( 'init', __NAMESPACE__ . '\register_block' );
add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );
add_action( 'save_post', __NAMESPACE__ . '\track_mentions_on_save', 10, 2 );
add_filter( 'the_content', __NAMESPACE__ . '\append_mentioned_by', 20 );

/**
 * Register the block.
 */
function register_block() {
	register_block_type( __DIR__ . '/block-quote-mentions' );
}

/**
 * Register REST API routes.
 */
function register_rest_routes() {
	register_rest_route(
		'jeremyfelt/v1',
		'/posts/search',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\search_posts',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'args'                => array(
				'search' => array(
					'required'          => false,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'per_page' => array(
					'required'          => false,
					'type'              => 'integer',
					'default'           => 10,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'jeremyfelt/v1',
		'/posts/(?P<id>\d+)/blocks',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\get_post_blocks',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'args'                => array(
				'id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'jeremyfelt/v1',
		'/mentions/(?P<id>\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\get_post_mentions',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}

/**
 * Search posts for the block editor.
 *
 * @param \WP_REST_Request $request The request object.
 * @return \WP_REST_Response
 */
function search_posts( $request ) {
	$search   = $request->get_param( 'search' );
	$per_page = $request->get_param( 'per_page' );

	$args = array(
		'post_type'      => array( 'post', 'page' ),
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( ! empty( $search ) ) {
		$args['s'] = $search;
	}

	$query = new \WP_Query( $args );
	$posts = array();

	foreach ( $query->posts as $post ) {
		$posts[] = array(
			'id'        => $post->ID,
			'title'     => get_the_title( $post ),
			'url'       => get_permalink( $post ),
			'date'      => get_the_date( 'F j, Y', $post ),
			'excerpt'   => wp_trim_words( wp_strip_all_tags( $post->post_content ), 20 ),
			'post_type' => $post->post_type,
		);
	}

	return rest_ensure_response( $posts );
}

/**
 * Get blocks from a specific post.
 *
 * @param \WP_REST_Request $request The request object.
 * @return \WP_REST_Response|\WP_Error
 */
function get_post_blocks( $request ) {
	$post_id = $request->get_param( 'id' );
	$post    = get_post( $post_id );

	if ( ! $post ) {
		return new \WP_Error( 'post_not_found', 'Post not found', array( 'status' => 404 ) );
	}

	$blocks = parse_blocks( $post->post_content );
	$parsed = array();

	foreach ( $blocks as $index => $block ) {
		// Skip empty blocks.
		if ( empty( $block['blockName'] ) ) {
			continue;
		}

		$block_id = $block['attrs']['anchor'] ?? 'block-' . $index;
		$content  = render_block( $block );
		$preview  = wp_strip_all_tags( $content );
		$preview  = wp_trim_words( $preview, 30 );

		$parsed[] = array(
			'id'        => $block_id,
			'index'     => $index,
			'blockName' => $block['blockName'],
			'preview'   => $preview,
			'content'   => $content,
			'rawBlock'  => $block,
		);
	}

	return rest_ensure_response(
		array(
			'post'   => array(
				'id'    => $post->ID,
				'title' => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			),
			'blocks' => $parsed,
		)
	);
}

/**
 * Get mentions for a specific post (posts that quote this post).
 *
 * @param \WP_REST_Request $request The request object.
 * @return \WP_REST_Response
 */
function get_post_mentions( $request ) {
	$post_id  = $request->get_param( 'id' );
	$mentions = get_post_meta( $post_id, '_quoted_by', false );

	$results = array();
	foreach ( $mentions as $mention ) {
		$mentioning_post = get_post( $mention['post_id'] );
		if ( $mentioning_post && 'publish' === $mentioning_post->post_status ) {
			$results[] = array(
				'post_id'   => $mention['post_id'],
				'title'     => get_the_title( $mentioning_post ),
				'url'       => get_permalink( $mentioning_post ),
				'date'      => get_the_date( 'F j, Y', $mentioning_post ),
				'block_ids' => $mention['block_ids'] ?? array(),
			);
		}
	}

	return rest_ensure_response( $results );
}

/**
 * Track mentions when a post is saved.
 *
 * @param int      $post_id Post ID.
 * @param \WP_Post $post    Post object.
 */
function track_mentions_on_save( $post_id, $post ) {
	// Don't track on autosave or revisions.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Parse the post content for our block.
	$blocks = parse_blocks( $post->post_content );
	$quoted_posts = array();

	find_quote_mention_blocks( $blocks, $quoted_posts );

	// Get previously tracked quotes from this post.
	$previous_quotes = get_post_meta( $post_id, '_quotes_posts', true );
	if ( ! is_array( $previous_quotes ) ) {
		$previous_quotes = array();
	}

	// Remove old mentions from posts we no longer quote.
	foreach ( $previous_quotes as $old_quoted_post_id => $old_block_ids ) {
		if ( ! isset( $quoted_posts[ $old_quoted_post_id ] ) ) {
			remove_mention( $old_quoted_post_id, $post_id );
		}
	}

	// Add/update mentions for currently quoted posts.
	foreach ( $quoted_posts as $quoted_post_id => $block_ids ) {
		add_mention( $quoted_post_id, $post_id, $block_ids );
	}

	// Store the current quotes for future comparison.
	update_post_meta( $post_id, '_quotes_posts', $quoted_posts );
}

/**
 * Recursively find all quote mention blocks in parsed blocks.
 *
 * @param array $blocks       Array of parsed blocks.
 * @param array $quoted_posts Reference to array collecting quoted post data.
 */
function find_quote_mention_blocks( $blocks, &$quoted_posts ) {
	foreach ( $blocks as $block ) {
		if ( 'jeremyfelt/block-quote-mention' === $block['blockName'] ) {
			$source_post_id = $block['attrs']['sourcePostId'] ?? 0;
			$block_ids      = $block['attrs']['selectedBlockIds'] ?? array();

			if ( $source_post_id > 0 ) {
				if ( ! isset( $quoted_posts[ $source_post_id ] ) ) {
					$quoted_posts[ $source_post_id ] = array();
				}
				$quoted_posts[ $source_post_id ] = array_merge(
					$quoted_posts[ $source_post_id ],
					$block_ids
				);
			}
		}

		// Check inner blocks recursively.
		if ( ! empty( $block['innerBlocks'] ) ) {
			find_quote_mention_blocks( $block['innerBlocks'], $quoted_posts );
		}
	}
}

/**
 * Add a mention to a quoted post.
 *
 * @param int   $quoted_post_id    The post being quoted.
 * @param int   $mentioning_post_id The post doing the quoting.
 * @param array $block_ids          The block IDs being quoted.
 */
function add_mention( $quoted_post_id, $mentioning_post_id, $block_ids ) {
	$existing_mentions = get_post_meta( $quoted_post_id, '_quoted_by', false );

	// Check if this post already has a mention from the mentioning post.
	foreach ( $existing_mentions as $index => $mention ) {
		if ( $mention['post_id'] === $mentioning_post_id ) {
			// Update the existing mention.
			$mention['block_ids'] = array_unique( $block_ids );
			delete_post_meta( $quoted_post_id, '_quoted_by', $existing_mentions[ $index ] );
			add_post_meta( $quoted_post_id, '_quoted_by', $mention );
			return;
		}
	}

	// Add new mention.
	add_post_meta(
		$quoted_post_id,
		'_quoted_by',
		array(
			'post_id'   => $mentioning_post_id,
			'block_ids' => array_unique( $block_ids ),
			'date'      => current_time( 'mysql' ),
		)
	);
}

/**
 * Remove a mention from a quoted post.
 *
 * @param int $quoted_post_id     The post that was quoted.
 * @param int $mentioning_post_id The post that was doing the quoting.
 */
function remove_mention( $quoted_post_id, $mentioning_post_id ) {
	$existing_mentions = get_post_meta( $quoted_post_id, '_quoted_by', false );

	foreach ( $existing_mentions as $mention ) {
		if ( $mention['post_id'] === $mentioning_post_id ) {
			delete_post_meta( $quoted_post_id, '_quoted_by', $mention );
			return;
		}
	}
}

/**
 * Append "mentioned by" section to post content on singular views.
 *
 * @param string $content The post content.
 * @return string
 */
function append_mentioned_by( $content ) {
	if ( ! is_singular( array( 'post', 'page' ) ) ) {
		return $content;
	}

	$post_id  = get_the_ID();
	$mentions = get_post_meta( $post_id, '_quoted_by', false );

	if ( empty( $mentions ) ) {
		return $content;
	}

	$valid_mentions = array();
	foreach ( $mentions as $mention ) {
		$mentioning_post = get_post( $mention['post_id'] );
		if ( $mentioning_post && 'publish' === $mentioning_post->post_status ) {
			$valid_mentions[] = array(
				'post'      => $mentioning_post,
				'block_ids' => $mention['block_ids'] ?? array(),
				'date'      => $mention['date'] ?? '',
			);
		}
	}

	if ( empty( $valid_mentions ) ) {
		return $content;
	}

	// Sort by date, newest first.
	usort(
		$valid_mentions,
		function ( $a, $b ) {
			return strtotime( $b['date'] ) - strtotime( $a['date'] );
		}
	);

	$mentions_html = '<aside class="quote-mentions-referenced-by">';
	$mentions_html .= '<h3 class="quote-mentions-referenced-by__title">';
	$mentions_html .= esc_html(
		sprintf(
			_n(
				'Referenced by %d post',
				'Referenced by %d posts',
				count( $valid_mentions ),
				'jeremyfelt'
			),
			count( $valid_mentions )
		)
	);
	$mentions_html .= '</h3>';
	$mentions_html .= '<ul class="quote-mentions-referenced-by__list">';

	foreach ( $valid_mentions as $mention ) {
		$mentions_html .= sprintf(
			'<li><a href="%s">%s</a> <span class="quote-mentions-date">(%s)</span></li>',
			esc_url( get_permalink( $mention['post'] ) ),
			esc_html( get_the_title( $mention['post'] ) ),
			esc_html( get_the_date( 'F j, Y', $mention['post'] ) )
		);
	}

	$mentions_html .= '</ul>';
	$mentions_html .= '</aside>';

	return $content . $mentions_html;
}
