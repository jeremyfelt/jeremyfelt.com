;( function( $, window, document, Backbone, _, moby6Data, undefined ) {

	'use strict';
	
	var $document = $( document );

	var moby6 = {

		init: function() {
			// cached selectors
			this.$window = $( window );
			this.$html = $( document.documentElement );
			this.$body = $( document.body );
			this.$wpwrap = $( '#wpwrap' );
			this.$wpbody = $( '#wpbody' );
			this.$adminmenu = $( '#adminmenu' );
			this.$overlay = $( '#moby6-overlay' );
			this.$toolbar = $( '#wp-toolbar' );
			this.$toolbarPopups = this.$toolbar.find( 'a[aria-haspopup="true"]' );

			// jQuery Mobile swipe event
			$.event.special.swipe.scrollSupressionThreshold = 100; // Default: 30px; More than this horizontal displacement, and we will suppress scrolling.
			$.event.special.swipe.durationThreshold = 1000; // Default: 1000ms;  More time than this, and it isn't a swipe.
			$.event.special.swipe.horizontalDistanceThreshold = 100; // Default: 30px; Swipe horizontal displacement must be more than this.
			$.event.special.swipe.verticalDistanceThreshold = 75; // Default: 75px; Swipe vertical displacement must be less than this.

			// Modify functionality based on custom activate/deactivate event
			this.$html
				.on( 'activate.moby6', function() { moby6.activate(); } )
				.on( 'deactivate.moby6', function() { moby6.deactivate(); } );

			// Maybe activate the menu
			this.toggleMenu();

			// Trigger custom events based on active media query.
			var lazyResize = _.debounce( this.toggleMenu, 200 )
			$( window ).on( 'resize', lazyResize );
		},

		activate: function() {

			window.stickymenu && stickymenu.disable();

			if ( ! moby6.$body.hasClass( 'auto-fold' ) )
				moby6.$body.addClass( 'auto-fold' );

			$document.on( 'swiperight.moby6', function() {
				moby6.$wpwrap.addClass( 'moby6-open' );
			}).on( 'swipeleft.moby6', function() {
				moby6.$wpwrap.removeClass( 'moby6-open' );
			});

			this.modifySidebarEvents();
			this.disableDraggables();
			this.insertHamburgerButton();
			this.movePostSearch();

		},

		deactivate: function() {

			window.stickymenu && stickymenu.enable();

			$document.off( 'swiperight.moby6 swipeleft.moby6' );

			this.enableDraggables();
			this.removeHamburgerButton();
			this.restorePostSearch();

		},

		toggleMenu: function() {
			if ( ! window.matchMedia ) {
				return;
			}

			if ( window.matchMedia( '(max-width: 782px)' ).matches ) {
				if ( this.$html.hasClass( 'touch' ) ) {
					return;
				}

				this.$html.addClass( 'touch' ).trigger( 'activate.moby6' );
			} else {
				if ( ! this.$html.hasClass( 'touch' ) ) {
					return;
				}

				this.$html.removeClass( 'touch' ).trigger( 'deactivate.moby6' );
			}

			if ( window.matchMedia( '(max-width: 480px)' ).matches ) {
				this.enableOverlay();
			} else {
				this.disableOverlay();
			}
		},

		enableOverlay: function() {
			if ( this.$overlay.length == 0 ) {
				this.$overlay = $( '<div id="moby6-overlay"></div>' )
					.insertAfter( '#wpcontent' )
					.hide()
					.on( 'click.moby6', function() {
						moby6.$toolbar.find( '.menupop.hover' ).removeClass( 'hover' );
						$( this ).hide();
					});
			}
			this.$toolbarPopups.on( 'click.moby6', function() {
				moby6.$overlay.show();
			});
		},

		disableOverlay: function() {
			this.$toolbarPopups.off( 'click.moby6' );
			this.$overlay.hide();
		},

		modifySidebarEvents: function() {
			this.$body.off( '.wp-mobile-hover' );
			this.$adminmenu.find( 'a.wp-has-submenu' ).off( '.wp-mobile-hover' );

			var scrollStart = 0;
			this.$adminmenu.on( 'touchstart.moby6', 'li.wp-has-submenu > a', function() {
				scrollStart = moby6.$window.scrollTop();
			});

			this.$adminmenu.on( 'touchend.moby6', 'li.wp-has-submenu > a', function( e ) {
				e.preventDefault();

				if ( moby6.$window.scrollTop() !== scrollStart )
					return;

				var $this = $( this );
				$this.find( 'li.wp-has-submenu' ).removeClass( 'selected' );
				$this.parent( 'li' ).addClass( 'selected' );
			});
		},

		disableDraggables: function() {
			this.$wpbody
				.find( '.hndle' )
				.removeClass( 'hndle' )
				.addClass( 'hndle-disabled' );
		},

		enableDraggables: function() {
			this.$wpbody
				.find( '.hndle-disabled' )
				.removeClass( 'hndle-disabled' )
				.addClass( 'hndle' );
		},

		insertHamburgerButton: function() {
			if ( null == this.hamburgerButtonView ) {
				this.hamburgerButtonView = new Moby6HamburgerButton();
			} else {
				this.hamburgerButtonView.show();
			}
		},

		removeHamburgerButton: function() {
			this.hamburgerButtonView.hide();
		},

		movePostSearch: function() {
			this.searchBox = this.$wpbody.find( 'p.search-box' );
			if ( this.searchBox.length ) {
				this.searchBox.hide();
				if ( this.searchBoxClone === undefined ) {
					this.searchBoxClone = this.searchBox.first().clone().insertAfter( 'div.tablenav.bottom' );
				}
				this.searchBoxClone.show();
			}
		},

		restorePostSearch: function() {
			if ( this.searchBox !== undefined ) {
				this.searchBox.show();
				if ( this.searchBoxClone !== undefined )
					this.searchBoxClone.hide();
			}
		}

	};

	// make Windows 8 devices playing along nicely
	if ( '-ms-user-select' in document.documentElement.style && navigator.userAgent.match(/IEMobile\/10\.0/) ) {
		var msViewportStyle = document.createElement( 'style' );
		msViewportStyle.appendChild(
			document.createTextNode( '@-ms-viewport{width:auto!important}' )
		);
		document.getElementsByTagName( 'head' )[0].appendChild( msViewportStyle );
	}

	/* Hamburger button view */
	var Moby6HamburgerButton = Backbone.View.extend({

		id: 'moby6-toggle',

		tmpl: _.template( '<a href="#" title="<%- menuLabel %>"></a>' ),

		events: {
			'click a': 'toggleSidebar'
		},

		initialize: function() {
			this.$wpwrap = $( '#wpwrap' );
			this.render();
		},

		render: function() {
			this.$el.html( this.tmpl( moby6Data ) );
			$( '.wrap', '#wpbody' ).prepend( this.el );
			return this;
		},

		toggleSidebar: function(e) {
			e.preventDefault();
			this.$wpwrap.toggleClass( 'moby6-open' );
		},

		show: function() {
			this.$el.show();
		},

		hide: function() {
			this.$el.hide();
		}

	});

	// Init moby6 immediately
	moby6.init();

})( jQuery, window, document, Backbone, _, moby6Data );