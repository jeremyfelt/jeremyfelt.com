/*!
 * Script for adding functionality to the Create Gallery view.
 *
 * @since 1.0.0
 */

(function($){
	var media = wp.media,
		renderFn = media.view.Settings.Gallery.prototype.render;

	media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
		render: function() {
			var self = this,
				atts = self.model.attributes;

			// Begin with default function
			renderFn.apply( this, arguments );

			// Append the template
			this.$el.append( media.template( 'ttfmake-gallery-settings' ) );

			// Set up inputs
			// slider
			media.gallery.defaults.ttfmake_slider = false;
			this.update.apply( this, ['ttfmake_slider'] );
			// Autoplay
			media.gallery.defaults.ttfmake_autoplay = false;
			this.update.apply( this, ['ttfmake_autoplay'] );
			// prevnext
			media.gallery.defaults.ttfmake_prevnext = false;
			this.update.apply( this, ['ttfmake_prevnext'] );
			// pager
			media.gallery.defaults.ttfmake_pager = false;
			this.update.apply( this, ['ttfmake_pager'] );
			// delay
			media.gallery.defaults.ttfmake_delay = 6000;
			if ('undefined' === typeof this.model.attributes.ttfmake_delay) {
				this.model.attributes.ttfmake_delay = media.gallery.defaults.ttfmake_delay;
			}
			this.update.apply( this, ['ttfmake_delay'] );
			// effect
			media.gallery.defaults.ttfmake_effect = 'scrollHorz';
			this.update.apply( this, ['ttfmake_effect'] );

			// Toggle slider settings
			if ('undefined' === typeof atts.ttfmake_slider || false == atts.ttfmake_slider) {
				this.$el.find('#ttfmake-slider-settings').hide();
			}
			this.model.on('change', function(t) {
				// Only proceed if the slider toggle changed
				if ('undefined' === typeof this.changed.ttfmake_slider) {
					return;
				}

				var toggle = this.changed.ttfmake_slider,
					$settingsDiv = $('#ttfmake-slider-settings');

				if ( true === toggle ) {
					$settingsDiv.show();
				} else {
					$settingsDiv.hide();
				}
			});

			return this;
		}
	});
}(jQuery));