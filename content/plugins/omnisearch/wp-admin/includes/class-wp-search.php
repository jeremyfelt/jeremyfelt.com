<?php

// Include this here so that other plugins can extend it if they like.
require_once( dirname( __FILE__ ) . '/class-wp-search-posts.php' );

class WP_Search {
	static $instance;
	static $num_results = 5;

	function __construct() {
		self::$instance = $this;
		add_action( 'admin_init',                array( $this, 'add_providers' ) );
		add_action( 'admin_menu',                array( $this, 'admin_menu' ), 20 );
		add_action( 'admin_bar_menu',            array( $this, 'admin_bar_search' ), 4 );
		add_filter( 'wp_search_num_results',     array( $this, 'search_num_results' ) );
		add_filter( 'wp_search_auto_post_types', array( $this, 'filter_post_types' ) );
	}

	static function add_providers() {
		// class-wp-search-posts.php is included above, so that other plugins can more easily extend it.
		$post_types = get_post_types( array(
			'show_in_menu' => 'true'
		) );
		$post_types = apply_filters( 'wp_search_auto_post_types', $post_types );
		foreach ( $post_types as $post_type ) {
			new WP_Search_Posts( $post_type );
		}

		require_once( dirname( __FILE__ ) . '/class-wp-search-comments.php' );
		new WP_Search_Comments;

		if ( current_user_can( 'upload_files' ) ) {
			require_once( dirname( __FILE__ ) . '/class-wp-search-media.php' );
			new WP_Search_Media;
		}

		if ( current_user_can( 'install_plugins' ) ) {
			require_once( dirname( __FILE__ ) . '/class-wp-search-plugins.php' );
			new WP_Search_Plugins;
		}

		do_action( 'wp_search_add_providers' );
	}

	function filter_post_types( $post_types ) {
		return array_diff( $post_types, array( 'attachment' ) );
	}

	static function search_num_results( $num ) {
		return self::$num_results;
	}

	function admin_menu() {
		$this->slug = add_submenu_page( null, __( 'Search' ), '', 'edit_posts', 'search', array( $this, 'search_page' ) );
		add_action( "admin_print_styles-{$this->slug}", array( $this, 'admin_print_styles' ) );
	}

	function admin_print_styles() {
		wp_enqueue_style( 'global-search', plugins_url( '../css/global-search.css', __FILE__ ) );
	}

	function search_page() {
		$results = array();
		$s = isset( $_GET['s'] ) ? $_GET['s'] : '';
		if( $s ) {
			$results = apply_filters( 'wp_search_results', $results, $s );
		}
		?>
		<div class="wrap">
			<h2 class="page-title"><?php esc_html_e( 'Search' ); ?></h2>
			<br class="clear" />
			<?php echo self::get_wp_search_form( array(
							'form_class'         => 'wp-search-form',
							'search_class'       => 'wp-search',
							'search_placeholder' => '',
							'submit_class'       => 'wp-search-submit',
							'alternate_submit'   => true,
						) ); ?>
			<?php if( ! empty( $results ) ): ?>
				<h3 id="results-title"><?php esc_html_e( 'Results:' ); ?></h3>
				<div class="jump-to"><strong><?php esc_html_e( 'Jump to:' ); ?></strong>
					<?php foreach( $results as $label => $result ) : ?>
						<a href="#result-<?php echo sanitize_title( $label ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</div>
				<br class="clear" />
				<script>var search_term = '<?php echo esc_js( $s ); ?>', num_results = <?php echo intval( apply_filters( 'wp_search_num_results', 5 ) ); ?>;</script>
				<ul class="wp-search-results">
					<?php foreach( $results as $label => $result ) : ?>
						<li id="result-<?php echo sanitize_title( $label ); ?>" data-label="<?php echo esc_attr( $label ); ?>">
							<?php echo $result; ?>
							<a class="back-to-top" href="#results-title"><?php esc_html_e( 'Back to Top &uarr;' ); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div><!-- /wrap -->
		<?php
	}

	function admin_bar_search( $wp_admin_bar ) {
		if( ! is_admin() || ! current_user_can( 'edit_posts' ) || wp_is_mobile() )
			return;

		$form = self::get_wp_search_form( array(
			'form_id'      => 'adminbarsearch',
			'search_id'    => 'adminbar-search',
			'search_class' => 'adminbar-input',
			'submit_class' => 'adminbar-button',
		) );

		$wp_admin_bar->add_menu( array(
			'parent' => 'top-secondary',
			'id'     => 'search',
			'title'  => $form,
			'meta'   => array(
				'class'    => 'admin-bar-search',
				'tabindex' => -1,
			)
		) );
	}

	static function get_wp_search_form( $args = array() ) {
		$defaults = array(
			'form_id'            => null,
			'form_class'         => null,
			'search_class'       => null,
			'search_id'          => null,
			'search_value'       => isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : null,
			'search_placeholder' => null,
			'submit_class'       => 'button',
			'submit_value'       => __( 'Search' ),
			'alternate_submit'   => false,
		);
		extract( array_map( 'esc_attr', wp_parse_args( $args, $defaults ) ) );

		$rand = rand();
		if( empty( $form_id ) )
			$form_id = "wp_search_form_$rand";
		if( empty( $search_id ) )
			$search_id = "wp_search_search_$rand";

		ob_start();
		?>

		<form action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" method="get" class="<?php echo $form_class; ?>" id="<?php echo $form_id; ?>">
			<input type="hidden" name="page" value="search" />
			<input name="s" type="search" class="<?php echo $search_class; ?>" id="<?php echo $search_id; ?>" value="<?php echo $search_value; ?>" placeholder="<?php echo $search_placeholder; ?>" />
			<?php if ( $alternate_submit ) : ?>
				<button type="submit" class="<?php echo $submit_class; ?>"><span><?php echo $submit_value; ?></span></button>
			<?php else : ?>
				<input type="submit" class="<?php echo $submit_class; ?>" value="<?php echo $submit_value; ?>" />
			<?php endif; ?>
		</form>

		<?php
		return apply_filters( 'get_wp_search_form', ob_get_clean(), $args, $defaults );
	}

}
new WP_Search;
