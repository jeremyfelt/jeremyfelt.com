;( function( $, window, document, undefined ) {

	'use strict';

	$(document).ready( function() {

		var $colorpicker = $( '#color-picker' );
		var $stylesheet = $( '#colors-css' );
		var user_id = $( 'input#user_id' ).val();
		var current_user_id = $( 'input[name="checkuser_id"]' ).val();

		// create extra <link> element, if current theme is MP6 (default)
		if ( ! $( '#colors-mp6-css' ).length ) {
			$( '<link/>', {
				id: 'colors-mp6-css',
				rel: 'stylesheet',
				href: $stylesheet.attr( 'href' ),
				type: 'text/css',
				media: 'all'
			}).insertBefore( $stylesheet );
			$stylesheet.attr( 'href', '' );
		}

		// dropdown toggle
		$colorpicker.on( 'click', '.dropdown-current', function() {
			$colorpicker.toggleClass( 'expanded' );
		});

		$colorpicker.on( 'click', '.color-option', function() {

			var color_scheme = $( this ).children( 'input[name="admin_color"]' ).val();

			// update selected
			$( this ).siblings( '.selected' ).removeClass( 'selected' )
			$( this ).addClass( 'selected' );
			$( this ).find( 'input' ).prop( 'checked', true );

			// update current
			$colorpicker.find( '.dropdown-current label' ).html( $( this ).children( 'label' ).html() );
			$colorpicker.find( '.dropdown-current table' ).html( $( this ).children( 'table' ).html() );
			$colorpicker.toggleClass( 'expanded' );

			// preview/save color scheme
			if ( user_id == current_user_id ) {

				// repaint icons
				if ( $( this ).children( 'input[name="admin_color"]' ).val() == 'mp6' ) {
					$stylesheet.attr( 'href', '' );
				} else {
					$stylesheet.attr( 'href', $( this ).children( '.css_url' ).val() );
				}
				svgPainter.setColors( $.parseJSON( $( this ).children( '.icon_colors' ).val() ) );
				svgPainter.paint();

				// update user option
				$.post( ajaxurl, {
					action: 'mp6_save_user_scheme',
					color_scheme: color_scheme,
					user_id: user_id
				});

			}

		});

	});

})( jQuery, window, document );
