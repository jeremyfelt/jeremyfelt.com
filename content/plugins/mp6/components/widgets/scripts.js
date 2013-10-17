;var widgets = ( function( $, window, document, undefined ) {

	$(document).ready( function() {
		$( '#widgets-right .widget-top' ).click( function() {
			$(this).toggleClass( 'open' );
		} );
		$( '#widgets-right .widgets-sortables .sidebar-description' ).each( function(index, element) {
			$(this).appendTo( $(element).parent().prev() );
		});

		var no_inactive = $( '#wp_inactive_widgets .widget' ).length;

		$( '.inactive-sidebar .sidebar-name h3' ).prepend( no_inactive + ' ' );
		$( '.inactive-sidebar' ).addClass( 'closed' );
	});

})( jQuery, window, document );
