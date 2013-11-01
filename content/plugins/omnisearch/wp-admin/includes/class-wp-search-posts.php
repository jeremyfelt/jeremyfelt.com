<?php

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if ( ! class_exists( 'WP_Posts_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );

class WP_Search_Posts extends WP_Posts_List_Table {
	var $post_type,
		$post_type_object,
		$is_trash;

	function __construct( $post_type = 'post' ) {
		$this->post_type = $post_type;
		$this->is_trash  = false;
		add_filter( 'wp_search_results', array( $this, 'search' ), 10, 2 );
	}

	function search( $results, $search_term ) {
		add_action( 'page_row_actions', array( $this, 'filter_row_actions' ) );
		add_action( 'post_row_actions', array( $this, 'filter_row_actions' ) );
		if( ! post_type_exists( $this->post_type ) )
			return $results;

		$this->post_type_object = get_post_type_object( $this->post_type );

		set_current_screen( 'edit.php' );

		parent::__construct( array(
			'screen' => get_current_screen(),
		) );
		$this->screen = (object) array( 'post_type' => $this->post_type );

		$args = array(
			's'              => $search_term,
			'post_type'      => $this->post_type,
			'posts_per_page' => apply_filters( 'wp_search_num_results', 5 ),
			'post_status'    => 'any',
		);
		$this->items = get_posts( $args );

		$columns = $this->get_columns();
		unset( $columns['cb'] );
		$this->_column_headers = array( $columns, array(), array() );

		$html = '<h2>' . esc_html( $this->post_type_object->labels->name ) . $this->search_link_maybe( $search_term ) .'</h2>';

		ob_start();
		if ( $this->items ) {
			$this->display();
		} else {
			?>
			<p class="no-results"><?php $this->no_items(); ?></p>
			<?php
		}
		$html .= ob_get_clean();

		$results[ $this->post_type_object->labels->name ] = $html;
		return $results;
	}

	function search_link_maybe( $search_term ) {
		if ( $this->has_items() ) {
			$search_url = esc_url( admin_url( sprintf( 'edit.php?post_type=%s&s=%s', urlencode( $this->post_type_object->name ), urlencode( $search_term ) ) ) );
			return sprintf( ' <a href="%s" class="add-new-h2">%s</a>', $search_url, esc_html( $this->post_type_object->labels->search_items ) );
		}
		return false;
	}

	function has_items() {
		return ! empty( $this->items );
	}

	function no_items() {
		echo $this->post_type_object->labels->not_found;
	}

	function filter_row_actions( $actions ) {
		unset( $actions['inline hide-if-no-js'] );
		return $actions;
	}

	function display_rows( $posts = array(), $level = 0 ) {
		if ( empty( $posts ) )
			$posts = $this->items;

		add_filter( 'the_title', 'esc_html' );

		if ( $this->hierarchical_display ) {
			$this->_display_rows_hierarchical( $posts, $this->get_pagenum(), apply_filters( 'wp_search_num_results', 5 ) );
		} else {
			$this->_display_rows( $posts, $level );
		}
	}

	function get_bulk_actions() {
		return array();
	}

	function bulk_actions() {}

	function pagination( $which ) {}

	function extra_tablenav( $which ) {}
}
