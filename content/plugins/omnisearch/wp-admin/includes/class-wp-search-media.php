<?php

if( ! class_exists( 'WP_Media_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );

class WP_Search_Media extends WP_Media_List_Table {
	static $instance;

	function __construct() {
		self::$instance = $this;
		add_filter( 'wp_search_results', array( $this, 'search' ), 10, 2 );
	}

	function search( $results, $search_term ) {
		parent::__construct();

		$this->prepare_items();
		$columns = $this->get_columns();
		unset( $columns['cb'] );
		$this->_column_headers = array( $columns, array(), array() );

		$html = '<h2>' . esc_html__( 'Media' ) . $this->search_link_maybe( $search_term ) . '</h2>';

		ob_start();
		if ( $this->items ) {
			$this->display();
		} else {
			?>
			<p class="no-results"><?php $this->no_items(); ?></p>
			<?php
		}
		$html .= ob_get_clean();

		$label = __( 'Media' );
		$results[ $label ] = $html;
		return $results;
	}

	function search_link_maybe( $search_term ) {
		if ( $this->has_items() ) {
			$search_url = esc_url( add_query_arg( 's', $search_term, admin_url( 'upload.php' ) ) );
			return sprintf( ' <a href="%s" class="add-new-h2">%s</a>', $search_url, esc_html__( 'Search Media' ) );
		}
		return false;
	}

	function has_items() {
		return ! empty( $this->items );
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
