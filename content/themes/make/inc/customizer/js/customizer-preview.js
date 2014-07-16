/**
 * @package Make
 */

( function( $ ) {
	var api = wp.customize;

	/**
	 * Asynchronous updating
	 */
	// Site Title
	api( 'blogname', function( value ) {
		value.bind( function( to ) {
			var $content = $('.site-title a');
			if ( ! $content.length ) {
				$('.site-title').prepend('<a>' + to + '</a>');
			}
			if ( ! to ) {
				$content.remove();
			}
			$content.text( to );
		} );
	} );

	// Tagline
	api( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			var $content = $('.site-description');
			if ( ! $content.length ) {
				$('.site-branding').append('<span class="site-description">' + to + '</span>');
			}
			if ( ! to ) {
				$content.remove();
			}
			$content.text( to );
		} );
	} );

	// Mobile Menu Label
	api( 'navigation-mobile-label', function( value ) {
		value.bind( function( to ) {
			var $content = $('.menu-toggle');
			$content.text( to );
		} );
	} );

	// Sticky Label
	api( 'general-sticky-label', function( value ) {
		value.bind( function( to ) {
			var $content = $('.sticky-post-label');
			if ( ! $content.length ) {
				$('.post .entry-header').append('<span class="sticky-post-label">' + to + '</span>');
			}
			if ( ! to ) {
				$content.remove();
			}
			$content.text( to );
		} );
	} );

	// Header Text
	api( 'header-text', function( value ) {
		value.bind( function( to ) {
			var $content = $('.header-text');
			if ( ! $content.length ) {
				// Check for sub header
				var $container = $('.header-bar');
				if ( ! $container.length ) {
					$('#site-header').prepend('<div class="header-bar"><div class="container"></div></div>');
				}

				$('.header-bar .container').append('<span class="header-text">' + to + '</span>');
			}
			if ( ! to ) {
				$content.remove();
			}
			$content.text( to );
		} );
	} );

	// Footer Text
	api( 'footer-text', function( value ) {
		value.bind( function( to ) {
			var $content = $('.footer-text');
			if ( ! $content.length ) {
				$('.site-info').before('<div class="footer-text">' + to + '</div>');
			}
			if ( ! to ) {
				$content.remove();
			}
			$content.text( to );
		} );
	} );
} )( jQuery );
