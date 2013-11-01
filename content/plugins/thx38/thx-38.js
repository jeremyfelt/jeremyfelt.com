( function($) {

	// Set up our namespace...
	var thx = thx || {};

	// Store the theme data and settings for organized and quick access
	// Set ups shortcuts to regularly used checks
	thx.Data = _THX38;
	// Shortcut for (bool) isBrowsing check
	thx.browsing = thx.Data.settings['isBrowsing'];

	thx.Theme = Backbone.Model.extend({});

	// Main view controller for themes.php and theme-install.php
	// Unifies and renders all available views
	//
	// Hooks to #appearance and organizes the views to be rendered
	// based on which screen we are currently viewing
	thx.Appearance = Backbone.View.extend({

		// Set container
		el: '#appearance',

		window: $( window ),
		page: 0,

		events: {
			'click .themes-bulk-edit': 'editMode',
			'click .theme .delete-theme': 'deleteTheme'
		},

		// Sets up a throttler for binding to 'scroll'
		initialize: function() {
			var self = this;

			// Keep a boolean check so that we don't run
			// too much code on every event trigger
			this.window.bind( 'scroll.themes', function() {
				this.throttle = true;
			});

			setInterval( function() {
				if ( this.throttle ) {
					// Once the case is the case, the action occurs and the fact is no more
					this.throttle = false;
					self.scroller();
				}
			}, 300 );
		},

		// Main render control
		render: function() {
			// If this is a browsing view bypass all the rest of the render function
			// by returning the specific renderBrowsing function
			if ( thx.browsing )
				return this.install();

			// Setups the main theme view
			// with the current theme collection
			this.view = new thx.ThemesView({
				collection: this.collection,
				parent: this
			});
			// Render and append
			this.view.render();
			this.$el.append( this.view.el );

			// Search form
			this.search();
		},

		// Search input and view
		// for current theme collection
		search: function() {
			var self = this,
				view;
			view = new thx.Search({ collection: self.collection });

			// Render and append after screen title
			view.render();
			self.$el.find( '> h2' ).after( view.el );
		},

		// Handles all the rendering of the public theme directory
		install: function() {
			var self = this;

			// Set ups the view and passes the section argument
			self.view = new thx.BrowseThemesView({
				collection: self.collection,
				section: self.options.section
			});
			// Render and append
			self.view.render();
			self.$el.append( self.view.el );
		},

		// Checks when the user gets close to the bottom
		// of the mage and triggers a theme:scroll event
		scroller: function() {
			var self = this,
				bottom, threshold;

			bottom = this.window.scrollTop() + self.window.height();
			threshold = self.$el.offset().top + self.$el.outerHeight( false ) - self.window.height();
			threshold = Math.round( threshold * 0.9 );

			if ( bottom > threshold )
				this.trigger( 'theme:scroll' );
		},

		editMode: function() {
			$( 'body' ).toggleClass( 'edit-mode' );
			this.$el.find( '.themes-bulk-edit' ).toggleClass( 'mp6-text-highlight' );
		},

		deleteTheme: function() {
			return confirm( thx.Data.settings['confirmDelete'] );
		}
	});

	// Set up the Collection for our theme data
	// @has 'id' 'name' 'screenshot' 'author' 'authorURI' 'version' 'active' ...
	thx.Themes = Backbone.Collection.extend({

		model: thx.Theme,

		// Search terms
		terms: '',

		// Controls searching on the current theme collection
		// and triggers an update event
		doSearch: function( value ) {
			var results;

			// Updates terms with the value passed
			this.terms = value;

			// If we have terms, run a search...
			if ( this.terms.length > 0 ) {
				this.search( this.terms );
			}

			// If search is blank, show all themes
			// Useful for resetting the views when you clean the input
			if ( this.terms === '' ) {
				this.reset( thx.Data.themes );
			}

			// Trigger an 'update' event
			this.trigger( 'update' );
		},
		// Performs a search within the collection
		// @uses RegExp
		search: function( term ) {
			var self = this,
				match, results;

			// Start with a full collection
			self.reset( thx.Data.themes );

			// The RegExp to match
			match = new RegExp( term, 'i' );

			// Find results
			// _.filter and .test
			results = self.filter( function( data ) {
				return match.test( data.get( 'name' ) );
			});

			self.reset( results );
		},

		// Paginates the collection with a helper method
		// that slices the collection
		paginate: function( instance ) {
			var collection = this,
				instance = instance || 0;

			// Themes per instance are set at 15
			collection = _( collection.rest( 15 * instance ) );
			collection = _( collection.first( 15 ) );

			return collection;
		}
	});

	// This is the view that controls each theme item
	// that will be displayed on the screen
	thx.ThemeView = Backbone.View.extend({

		// Wrap theme data on a div.theme element
		className: 'theme',

		// Reflects which theme view we have
		// 'grid' (default) or 'detail'
		state: 'grid',

		// The HTML template for each element to be rendered
		html: _.template(
			$( '#theme-template, #public-theme-template' ).html()
		),

		events: {
			'click': 'expand'
		},

		render: function() {
			var data = this.model.toJSON();
			// Render themes using the html template
			this.$el.html( this.html( data ) );
			// Renders active theme styles
			this.activeTheme();
		},

		// Adds a class to the currently active theme
		// and to the overlay in detailed view mode
		activeTheme: function() {
			if ( this.model.has( 'active' ) ) {
				this.$el.addClass( 'active' );
				this.$el.find( '.theme-name' ).addClass( 'mp6-primary' );
				$( '#theme-overlay' ).addClass( 'active' );
			}
		},

		// Single theme overlay screen
		// It's shown when clicking a theme
		expand: function( event ) {
			var self = this;

			event = event || window.event;

			// Prevent the modal from showing when the user clicks
			// one of the direct action buttons
			if ( $( event.target ).is( '.theme-actions a, .delete-theme' ) )
				return;

			this.trigger( 'theme:expand', self.model.cid );
		}
	});

	// Theme Details view
	// Set ups a modal overlay with the expanded theme data
	thx.ThemeDetails = Backbone.View.extend({

		// Wrap theme data on a div.theme element
		id: 'theme-overlay',

		events: {
			'click': 'collapse',
			'click .delete-theme': 'deleteTheme'
		},

		// The HTML template for the theme overlay
		html: _.template(
			$( '#theme-single-template, #public-theme-single-template' ).html()
		),

		render: function() {
			var data = this.model.toJSON();

			this.$el.html( this.html( data ) );
			// Renders active theme styles
			this.activeTheme();
			// Set up navigation events
			this.navigation();
		},

		// Adds a class to the currently active theme
		// and to the overlay in detailed view mode
		activeTheme: function() {
			// Check the model has the active property
			if ( this.model.has( 'active' ) ) {
				this.$el.addClass( 'active' );
			} else {
				$( '#theme-overlay' ).removeClass( 'active' );
			}
		},

		// Single theme overlay screen
		// It's shown when clicking a theme
		collapse: function( event ) {
			var self = this,
				scroll;

			event = event || window.event;

			// Detect if the click is inside the overlay
			// and don't close it unless the target was
			// the div.back button
			if ( $( event.target ).is( '.theme-backdrop' ) || $( event.target ).is( 'div.back' ) ) {

				// Add a temporary closing class while overlay fades out
				$( 'body' ).addClass( 'closing-overlay' );

				// With a quick fade out animation
				this.$el.fadeOut( 130, function() {
					// Clicking outside the modal box closes the overlay
					$( 'body' ).removeClass( 'theme-overlay closing-overlay' );
					// Handle event cleanup
					self.closeOverlay();

					// Get scroll position to avoid jumping to the top
					scroll = document.body.scrollTop;

					// Clean the url structure
					thx.router.navigate( '' );

					// Restore scroll position
					document.body.scrollTop = scroll;
				});
			}
		},

		// Handles arrow keys navigation for the overlay
		// Triggers theme:next and theme:previous events
		navigation: function() {
			var self = this;

			$( 'body' ).on( 'keyup', function( event ) {

				// Pressing the right arrow key fires a theme:next event
				if ( event.keyCode == 39 ) {
					self.trigger( 'theme:next', self.model.cid );
				}

				// Pressing the left arrow key fires a theme:previous event
				if ( event.keyCode == 37 ) {
					self.trigger( 'theme:previous', self.model.cid );
				}
			});
		},

		// Performs the actions to effectively close
		// the theme details overlay
		closeOverlay: function() {
			this.remove();
			this.unbind();
			this.trigger( 'theme:collapse' );
		},

		// Setups an image gallery using the theme screenshots supplied by a theme
		screenshotGallery: function() {
			var screenshots = $( '#theme-screenshots' ),
				img;

			screenshots.find( 'div.first' ).next().addClass( 'selected' );

			// Clicking on a screenshot thumbnail drops it
			// at the top of the stack in a larger size
			screenshots.on( 'click', 'div.thumb', function() {
				current = $( this );
				img = $( this ).find( 'img' ).clone();

				current.siblings( '.first' ).html( img );
				current.siblings( '.selected' ).removeClass( 'selected' );
				current.addClass( 'selected' );
			});
		},

		// Confirmation dialoge for deleting a theme
		deleteTheme: function() {
			return confirm( thx.Data.settings['confirmDelete'] );
		}
	});

	// Controls the rendering of div#themes,
	// a wrapper that will hold all the theme elements
	thx.ThemesView = Backbone.View.extend({

		id: 'themes',

		// Number to keep track of scroll position
		// while in theme-overlay mode
		index: 0,

		// The theme count element
		count: $( '#theme-count' ),

		initialize: function( options ) {
			var self = this;

			// Set up parent
			this.parent = options.parent;

			// Set current view to [grid]
			this.setView( 'grid' );

			// Move the active theme to the beginning of the collection
			self.currentTheme();

			// When the collection is updated by user input...
			this.listenTo( self.collection, 'update', function() {
				self.parent.page = 0;
				self.currentTheme();
				self.render( this );
			});

			this.listenTo( this.parent, 'theme:scroll', function() {
				self.renderThemes( self.parent.page );
			});
		},

		render: function() {
			var self = this,
				view, page;

			// Clear the DOM, please
			self.$el.html( '' );

			// Generate the themes
			this.renderThemes( this.parent.page );

			// Display a live theme count for the collection
			self.count.text( '(' + self.collection.length + ')' );
		},

		// Iterates through each instance of the collection
		// and renders each theme module
		renderThemes: function( page ) {
			var self = this;

			self.instance = self.collection.paginate( page );

			// If we have no more themes bail
			if ( self.instance.length == 0 )
				return;

			// Make sure the add-new stays at the end
			if ( page >= 1 )
				$( '#add-new' ).remove();

			// Loop through the themes and setup each theme view
			self.instance.each( function( theme ) {
				self.theme = new thx.ThemeView({
					model: theme
				});

				// Render the views...
				self.theme.render();
				// and append them to div#themes
				self.$el.append( self.theme.el );

				// Binds to theme:expand to show the modal box
				// with the theme details
				self.listenTo( self.theme, 'theme:expand', self.expand, self );
			});

			// 'Add new theme' element shown at the end of the grid
			this.$el.append( '<div id="add-new" class="theme add-new"><a href="' + thx.Data.settings['install_uri'] + '"><div class="theme-screenshot"><span></span></div><h3 class="theme-name">' + thx.Data.i18n['add_new'] + '</h3></a></div>' );

			this.parent.page++;
		},

		// Grabs current theme and puts it at the beginning of the collection
		currentTheme: function() {
			var self = this,
				current;

			current = self.collection.findWhere({ active: true });

			// Move the active theme to the beginning of the collection
			if ( current ) {
				self.collection.remove( current );
				self.collection.add( current, { at:0 } );
			}
		},

		// Sets current view
		setView: function( view ) {
			return view;
		},

		// Renders the overlay with the ThemeDetails view
		// Uses the current model data
		expand: function( id ) {
			var self = this,
				slug, data, top, indexof, scroll;

			// Set the current theme model
			this.model = self.collection.get( id );

			// Trigger a route update for the current model
			thx.router.navigate( 'theme/' + this.model.id );

			// Sets this.view to 'detail'
			this.setView( 'detail' );
			$( 'body' ).addClass( 'theme-overlay' );

			// Set up the theme details view
			this.overlay = new thx.ThemeDetails({
				model: self.model,
				className: 'theme-' + self.model.id,
			});

			this.overlay.render();
			this.$el.append( this.overlay.el );

			this.overlay.screenshotGallery();

			// Resets counter whenever the overlay is opened
			this.index = 0;

			// Bind to theme:next and theme:previous
			// triggered by the arrow keys
			//
			// The index keep track of where we are at
			// any given time
			this.listenTo( this.overlay, 'theme:next', function() {
				// Bump the index number to keep track of how far
				// we should go for the next theme
				self.index++;
				// Renders the next theme on the overlay
				self.next( [ self.model.cid, self.index ] );
				self.overlay.screenshotGallery();

			})
			.listenTo( this.overlay, 'theme:previous', function() {
				// Decrease the index number to keep track of how far
				// we should go for the previous theme
				self.index--;
				// Renders the previous theme on the overlay
				self.previous( [ self.model.cid, self.index ] );
				self.overlay.screenshotGallery();
			});

		},

		// This method renders the next theme on the overlay modal
		// based on the current position in the collection
		// @params [model cid] and [index]
		next: function( args ) {
			var self = this,
				model, nextModel;

			// Get the current theme
			model = self.collection.get( args[0] );
			// Get the next one
			nextModel = self.collection.at( self.collection.indexOf( model ) + args[1] );

			// Sanity check which also serves as a boundary test
			if ( nextModel !== undefined ) {

				// We have a new theme...
				// Clean the overlay
				this.overlay.$el.html('');

				// Create the view
				this.nextTheme = new thx.ThemeDetails({
					model: nextModel,
					className: 'theme-' + nextModel.id
				});

				// Trigger a route update for the current model
				thx.router.navigate( 'theme/' + nextModel.id );

				// Render and fill this.overlay with the new html
				this.nextTheme.render();
				return this.overlay.$el.html( this.nextTheme.el );
			}

			// If we got this far it means we have no other themes
			// so move back the counter to keep it sane
			self.index--;
		},

		// This method renders the previous theme on the overlay modal
		// based on the current position in the collection
		// @params [model cid] and [index]
		previous: function( args ) {
			var self = this,
				model, previousModel;

			// Get the current theme
			model = self.collection.get( args[0] );
			previousModel = self.collection.at( self.collection.indexOf( model ) + args[1] );

			if ( previousModel !== undefined ) {

				// We have a new theme...
				// Clean the overlay
				this.overlay.$el.html( '' );

				// Create the view
				this.previousTheme = new thx.ThemeDetails({
					model: previousModel,
					className: 'theme-' + previousModel.id,
				});

				// Trigger a route update for the current model
				thx.router.navigate( 'theme/' + previousModel.id );

				// Render and fill this.overlay with the new html
				this.previousTheme.render();
				return this.overlay.$el.html( this.previousTheme.el );
			}

			// If we got this far it means we have no other themes
			// so move back the counter to keep it sane
			self.index++;
		}
	});

	// Renders the different Browse Themes sections
	// Thse are submitted by the Data.browse['sections'] property
	thx.BrowseThemesView = Backbone.View.extend({

		className: 'theme-linear-grid',

		initialize: function() {
			var self = this;

			// When the collection is updated by user input...
			this.listenTo( self.collection, 'update', function() {
				self.render( this );
			});
		},

		render: function() {
			var self = this,
				view;

			// Clear the DOM, please
			self.$el.html( '' );

			// Sets up the theme section titles
			// using the passed option argument
			self.$el.append( '<h3 class="theme-section">' + thx.Data.browse['sections'][ self.options.section ] + '</h3><div class="themes"></div>' );

			// Loop through the themes and setup each theme view
			self.collection.each( function( theme ) {
				view = new thx.ThemeView({
					model: theme
				});

				// Render the views...
				view.render();
				// and append them to div#themes
				self.$el.find( '.themes' ).append( view.el );
			});

			// 'Show more themes' element at the end of the stripe
			self.$el.append( '<div class="show-more-themes"><span class="dashicons dashicons-arr-right"></span></div>' );
			self.showMore();
		},

		// The show more button slides new themes into view
		// It will trigger the Ajax calls to themes_api...
		showMore: function() {
			var self = this,
				height;

			// Adjusts the size of the 'show more' button based on screenshot size
			// $( window ).on( 'load resize', function(){
			// 	height = self.$el.height();
			// 	$( '.show-more-themes' ).height( height );
			// });
		}

	});

	// Search input view controller
	// renders #search-form
	thx.Search = Backbone.View.extend({

		className: 'search-form',

		// 'keyup' triggers search
		events: {
			'keyup #theme-search': 'search'
		},

		// Grabs template file
		html: _.template( $( '#theme-search-template' ).html() ),

		// Render the search form
		render: function() {
			var self = this;
			self.$el.html( self.html );
		},

		// Runs a search on the theme collection
		// bind on 'keyup' event
		search: function() {
			this.collection.doSearch( $( '#theme-search' ).val() );
		}
	});

	// Sets up the routes events for relevant url queries
	// Listens to [theme] and [search] params
	thx.Routes = Backbone.Router.extend({

		routes: {
			"search/:query": "search",
			"theme/:slug": "theme",
		},

		// Set the search input value based on url
		search: function( query ) {
			$( '#theme-search' ).val( query );
		}
	});

	// Execute and setup the application
	thx.Run = {
		init: function() {
			var self = this;

			// Initializes the blog's theme library view
			//
			// If it's the 'browsing themes' screen, render the directory instead

			if ( ! thx.browsing ) {

				// Create a new collection with data
				self.themes = new thx.Themes( thx.Data.themes );

				// Set up the view
				self.view = new thx.Appearance({
					collection: self.themes
				});

				// Render results
				self.view.render();

			} else {

				// Loop through the different theme sections
				// and sets up each one of them to be rendered
				for ( var section in thx.Data.browse['sections'] ) {

					// Create a new collection with the proper theme data
					// for each section
					self.themes = new thx.Themes( thx.Data.browse['publicThemes'][ section ]['themes'] );

					// Set up the view
					// Passes the 'section' as an option
					self.view = new thx.Appearance({
						collection: self.themes,
						section: section
					});

					// Render results
					self.view.render();
				}

			}

			// Calls the routes functionality
			self.routes();
			// Set ups history with pushState and our root
			Backbone.history.start({ root: thx.Data.settings['root'] });
		},

		routes: function() {
			var self = this;
			// Bind to our global thx object
			// so that the object is available to sub-views
			thx.router = new thx.Routes;

			// Handles theme details route event
			thx.router.on( 'route:theme', function( slug ) {
				self.view.view.expand( slug );
			});

			// Handles search route event
			thx.router.on( 'route:search', function( query ) {
				self.themes.doSearch( query );
			});
		}
	};

	// Ready...
	jQuery( document ).ready( function() {

		// Bring on the themes
		thx.Run.init();

	});

})( jQuery );