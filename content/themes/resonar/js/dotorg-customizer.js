/**
 * Theme Customizer enhancements for a better user experience in the background option.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
	// Background color.
	wp.customize( 'background_color', function( value ) {
		value.bind( function( to ) {
			$( 'body, .site-header .sub-menu li, .sidebar' ).css( {
					'background-color': to,
			} );

			$( '.widget_calendar tbody a, .page-links a' ).css( {
					'color': to,
			} );

			$( '<style>.site-header .nav-menu > li > .sub-menu:after { border-color:' + to + ' transparent; }</style>' ).appendTo( 'head' );

			$( '<style>.pagination .prev:hover, .pagination .prev:focus, .pagination .next:hover, .pagination .next:focus, .widget_calendar tbody a:hover, .widget_calendar tbody a:focus, .page-links a:hover, .page-links a:focus { color:' + to + '; }</style>' ).appendTo( 'head' );
		} );
	} );
} )( jQuery );
