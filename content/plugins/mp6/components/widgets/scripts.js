( function( $, window, document, undefined ) {

	$(document).ready( function() {
		$( '#widgets-right .widgets-sortables .sidebar-description' ).each( function(index, element) {
			$(this).appendTo( $(element).parent().prev() );
		});

		// Available Widget Descriptions
		$( '#available-widgets .widget-top' ).append( '<div class="more-info"></div>' );

		$( '#available-widgets .more-info' ).on( 'mouseenter', function() {
			var $this = $( this ),
				description = $this.closest( '.widget' ).find('.widget-description'),
				description_timer = setTimeout( function() {
					description.fadeIn(200);
				}, 400 );
			$this.on( 'mouseleave', function() {
				clearTimeout( description_timer );
				description.fadeOut(200);
			} );
		} );
	});

})( jQuery, window, document );