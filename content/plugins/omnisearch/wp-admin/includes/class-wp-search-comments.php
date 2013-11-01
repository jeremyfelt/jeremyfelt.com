<?php

if( ! class_exists( 'WP_Comments_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-comments-list-table.php' );

class WP_Search_Comments extends WP_Comments_List_Table {
	static $instance;
	var $checkbox = false;

	function __construct() {
		self::$instance = $this;
		add_filter( 'wp_search_results', array( $this, 'search' ), 10, 2 );
	}

	function search( $results, $search_term ) {
		add_filter( 'comment_row_actions', array( $this, 'comment_row_actions' ) );
		parent::__construct();

		$this->prepare_items();
		$this->_column_headers = array( $this->get_columns(), array(), array() );

		$html = '<h2>' . esc_html__( 'Comments' ) . $this->search_link_maybe( $search_term ) . '</h2>';

		ob_start();
		if ( $this->items ) {
			$this->display();
		} else {
			?>
			<p class="no-results"><?php $this->no_items(); ?></p>
			<?php
		}
		$html .= ob_get_clean();

		$label = __( 'Comments' );
		$results[ $label ] = $html;
		return $results;
	}

	function search_link_maybe( $search_term ) {
		if ( $this->has_items() ) {
			$search_url = esc_url( admin_url( sprintf( 'edit-comments.php?s=%s', urlencode( $search_term ) ) ) );
			return sprintf( ' <a href="%s" class="add-new-h2">%s</a>', $search_url, esc_html__( 'Search Comments' ) );
		}
		return false;
	}

	function comment_row_actions( $actions ) {
		unset( $actions['quickedit'] );
		unset( $actions['reply'] );
		return $actions;
	}

	function get_per_page( $comment_status = 'all' ) {
		return apply_filters( 'wp_search_num_results', 5 );
	}

	function get_sortable_columns() {
		return array();
	}

	function get_bulk_actions() {
		return array();
	}

	function pagination( $which ) {}

	function extra_tablenav( $which ) {}
}
