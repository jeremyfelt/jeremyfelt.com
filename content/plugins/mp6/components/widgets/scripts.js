;var widgets = ( function( $, window, document, undefined ) {

	$(document).ready( function() {
		var sidebars = $( '#widgets-right .widgets-holder-wrap' ),
			active_sidebar_count = 0;
		sidebars.each( function( index, element ) {
			if ( $(this).hasClass( 'inactive-sidebar' ) )
				return false;
			active_sidebar_count++;
		} );

		if( active_sidebar_count == 1 )
			$( '#widgets-right' ).addClass( 'single-sidebar' );

		// Move inactive sidebars next to the normal sidebars
		var inactive = $( '.inactive-sidebar' );
		inactive.detach();
		inactive.appendTo( $( '#widgets-right' ) );
		inactive.each( function( index, element ) {
			if ( index == 0 )
				$(this).addClass('first');
			$(this).find( '.sidebar-description' ).appendTo( $(this).find( '.sidebar-name' ) );
		} );

		// Move the sidebar descriptions up in HTML for better styling
		$( '#widgets-right .widgets-sortables .sidebar-description' ).each( function(index, element) {
			$(this).appendTo( $(element).parent().prev() );
		});

		$( '#available-widgets .widget-top' ).append( '<div class="more-info"></div>' );
		$( '#available-widgets .more-info' ).on( 'click', function() {
			$( this ).closest( '.widget' ).find('.widget-description').fadeToggle(200);
		} );
		$( '#available-widgets .more-info' ).on( 'mouseleave', function() {
			$( this ).closest( '.widget' ).find('.widget-description').fadeOut(200);
		} );

		// Prevent scrolling the parent while scrolling the available widgets.
		// "Don't cross the streams." -Spengler
		$( '#available-widgets' ).on('DOMMouseScroll mousewheel', function(ev) {
			var $this = $(this),
				scrollTop = this.scrollTop,
				scrollHeight = this.scrollHeight,
				height = $this.height(),
				delta = (ev.type == 'DOMMouseScroll' ?
					ev.originalEvent.detail * -40 :
					ev.originalEvent.wheelDelta),
				up = delta > 0;

			var prevent = function() {
				ev.stopPropagation();
				ev.preventDefault();
				ev.returnValue = false;
				return false;
			}

			if (!up && -delta > scrollHeight - height - scrollTop) {
				// Scrolling down, but this will take us past the bottom.
				$this.scrollTop(scrollHeight);
				return prevent();
			} else if (up && delta > scrollTop) {
				// Scrolling up, but this will take us past the top.
				$this.scrollTop(0);
				return prevent();
			}
		});

		// Clicking save will close the widget inside
		$( '.widget-control-save' ).on( 'click', function() {
			var widget = $(this).closest( '.widget' );

			var close_check = setInterval( function() {
				if ( widget.find( '.spinner' ).is( ':hidden' ) ) {
					widget.find( '.widget-title' ).click();
					clearInterval(close_check);
				}
			}, 200 );
		} );
	});

})( jQuery, window, document );
