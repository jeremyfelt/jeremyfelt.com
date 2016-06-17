( function( $ ) {
	$( document ).ready( function() {
		var $widgetsWrapper = $( 'div#widgets-wrapper' ),
			$widgetsTrigger = $( '.widgets-trigger a' ),
			$socialLinksWrapper = $( 'div#social-links-wrapper' ),
			$socialLinksTrigger = $( '.social-links-trigger a' ),
			$searchWrapper = $( 'div#search-wrapper' ),
			$searchTrigger = $( '.search-trigger a' );

		function scrollTop() {
			$( 'body,html' ).animate( {
				scrollTop: 0
			}, 400 );
		};

		/*
		 * Click events for toggling the top-panels.
		 * Each of them checks if other panels are already opened,
		 * and if any of them is opened, it will be hidden.
		 * Also it makes sure the page goes back to top
		 * in case the widget panel is really long.
		 */
		$widgetsTrigger.click( function( e ) {
			e.preventDefault();
			scrollTop();
			$widgetsWrapper.toggleClass( 'hide' );

			if ( ! $socialLinksWrapper.hasClass( 'hide' ) ) {
				$socialLinksWrapper.addClass( 'hide' );
			}

			if ( ! $searchWrapper.hasClass( 'hide' ) ) {
				$searchWrapper.addClass( 'hide' );
			}
		} );

		$socialLinksTrigger.click( function( e ) {
			e.preventDefault();
			scrollTop();
			$socialLinksWrapper.toggleClass( 'hide' );

			if ( ! $widgetsWrapper.hasClass( 'hide' ) ) {
				$widgetsWrapper.addClass( 'hide' );
			}

			if ( ! $searchWrapper.hasClass( 'hide' ) ) {
				$searchWrapper.addClass( 'hide' );
			}
		} );

		$searchTrigger.click( function( e ) {
			e.preventDefault();
			scrollTop();
			$searchWrapper.toggleClass( 'hide' );

			if ( ! $widgetsWrapper.hasClass( 'hide' ) ) {
				$widgetsWrapper.addClass( 'hide' );
			}

			if ( ! $socialLinksWrapper.hasClass( 'hide' ) ) {
				$socialLinksWrapper.addClass( 'hide' );
			}
		} );

	} );

	/*
	 * A function to adjust the height of the horizontal band for video format post.
	 * Check the height of .entry-info and add 34px for top and bottom padding.
	 */
	 function videoBand() {
		$( 'article.format-video .entry-info' ).each( function() {
			var $entryInfo = $( this ),
				$entryInfoHeight = $( this ).height() + 34;
			$( this ).parents( 'article.format-video' ).find( 'div.band' ).css( 'height', $entryInfoHeight );
		} );
	 }

	// Call videoBand() after a page load completely.
	$( window ).load( videoBand );

	// Call videoBand() after IS loads posts.
	$( document ).on( 'post-load', videoBand );
} )( jQuery );