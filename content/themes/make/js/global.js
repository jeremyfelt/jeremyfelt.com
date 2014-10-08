/*!
 * Script for initializing globally-used functions and libs.
 *
 * @since 1.0.0
 */
/* global jQuery, ttfmakeFitVids */
(function($) {
	'use strict';

	var ttfmake = {
		/**
		 *
		 */
		cache: {},

		/**
		 *
		 */
		init: function() {
			this.cacheElements();
			this.bindEvents();
		},

		/**
		 *
		 */
		cacheElements: function() {
			this.cache = {
				$window: $(window),
				$document: $(document)
			};
		},

		/**
		 *
		 */
		bindEvents: function() {
			var self = this;

			this.cache.$document.on( 'ready', function() {
				self.navigationInit();
				self.skipLinkFocusFix();
				self.fitVidsInit();
			} );
		},

		/**
		 * Initialize the mobile menu functionality.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		navigationInit: function() {
			var container, button, menu;

			container = document.getElementById( 'site-navigation' );
			if ( ! container ) {
				return;
			}

			button = container.getElementsByTagName( 'span' )[0];
			if ( 'undefined' === typeof button ) {
				return;
			}

			menu = container.getElementsByTagName( 'ul' )[0];

			// Hide menu toggle button if menu is empty and return early.
			if ( 'undefined' === typeof menu ) {
				button.style.display = 'none';
				return;
			}

			if ( -1 === menu.className.indexOf( 'nav-menu' ) ) {
				menu.className += ' nav-menu';
			}

			button.onclick = function() {
				if ( -1 !== container.className.indexOf( 'toggled' ) ) {
					container.className = container.className.replace( ' toggled', '' );
				} else {
					container.className += ' toggled';
				}
			};
		},

		/**
		 * Fix tab destination after 'Skip to content' link has been clicked.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		skipLinkFocusFix: function() {
			var is_webkit = navigator.userAgent.toLowerCase().indexOf( 'webkit' ) > -1,
				is_opera  = navigator.userAgent.toLowerCase().indexOf( 'opera' )  > -1,
				is_ie     = navigator.userAgent.toLowerCase().indexOf( 'msie' )   > -1,
				eventMethod;

			if ( ( is_webkit || is_opera || is_ie ) && 'undefined' !== typeof( document.getElementById ) ) {
				eventMethod = ( window.addEventListener ) ? 'addEventListener' : 'attachEvent';
				window[ eventMethod ]( 'hashchange', function() {
					var element = document.getElementById( location.hash.substring( 1 ) );

					if ( element ) {
						if ( ! /^(?:a|select|input|button|textarea)$/i.test( element.tagName ) ) {
							element.tabIndex = -1;
						}

						element.focus();
					}
				}, false );
			}
		},

		/**
		 * Initialize FitVids.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		fitVidsInit: function() {
			// Make sure lib is loaded.
			if (!$.fn.fitVids) {
				return;
			}

			// Update the cache
			this.cache.$container = $('.container');

			var args = {};

			// Get custom selectors
			if ('object' === typeof ttfmakeFitVids) {
				args.customSelector = ttfmakeFitVids.selectors;
			}

			// Run FitVids
			this.cache.$container.fitVids(args);

			// Fix padding issue with Blip.tv. Note that this *must* happen after Fitvids runs.
			// The selector finds the Blip.tv iFrame, then grabs the .fluid-width-video-wrapper div sibling.
			this.cache.$container.find('.fluid-width-video-wrapper:nth-child(2)').css({ 'paddingTop': 0 });
		}
	};

	ttfmake.init();
})(jQuery);