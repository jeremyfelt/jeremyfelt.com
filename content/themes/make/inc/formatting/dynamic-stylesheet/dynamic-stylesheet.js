/**
 * Script for adding dynamic style rules to a page.
 *
 * Most useful for adding rules that use pseudo-selectors, which can't be inlined,
 * or other rules that can't be added to the normal stylesheet.
 *
 * @since 1.4.1.
 */
/* global jQuery, ttfmakeDynamicStylesheet */

var ttfmakeDynamicStylesheet;

(function($) {
	'use strict';

	ttfmakeDynamicStylesheet = {
		/**
		 * Container for caching jQuery objects.
		 *
		 * @since 1.4.1.
		 */
		cache: {
			$document: $(document)
		},

		/**
		 * List of selectors to use when populating the cache.
		 *
		 * @since 1.4.1.
		 */
		cacheSelector: {
			$button: 'a.ttfmake-button[data-hover-color], a.ttfmake-button[data-hover-background-color]',
			$list: 'ul.ttfmake-list[data-icon-color]'
		},

		/**
		 * Container for callbacks that add rules to the dynamic stylesheet.
		 *
		 * @since 1.4.1.
		 */
		builder: {
			button: function(self) {
				if (self.cache.$button.length > 0) {
					self.createStylesheet();

					self.cache.$button.each(function() {
						var buttonID = $(this).attr('id'),
							backgroundColor = $(this).data('hover-background-color'),
							color = $(this).data('hover-color');

						if (buttonID) {
							if (backgroundColor) self.addCSSRule(self.stylesheet, '#' + buttonID + ':hover', 'background-color: ' + backgroundColor + ' !important');
							if (color) self.addCSSRule(self.stylesheet, '#' + buttonID + ':hover', 'color: ' + color + ' !important');
						}
					});
				}
			},
			list: function(self) {
				if (self.cache.$list.length > 0) {
					self.createStylesheet();

					self.cache.$list.each(function() {
						var listID = $(this).attr('id'),
							iconColor = $(this).attr('data-icon-color');

						if (listID && iconColor) {
							self.addCSSRule(self.stylesheet, '#' + listID + ' li:before', 'color: ' + iconColor);
						}
					});
				}
			}
		},

		/**
		 * Initialize the dynamic stylesheet functionality.
		 *
		 * Note that this only does something if the ttfmakeDynamicStylesheetVars object isn't present,
		 * which indicates that it's not loaded in the admin.
		 *
		 * @since 1.4.1.
		 *
		 * @return void
		 */
		init: function() {
			if ('undefined' === typeof ttfmakeDynamicStylesheetVars || ! ttfmakeDynamicStylesheetVars.tinymce) {
				this.root = this.cache.$document;

				var self = this;
				this.cache.$document.ready(function() {
					self.cacheElements();
					self.buildStyles();
				} );
			}
		},

		/**
		 * Initialize the dynamic stylesheet functionality in a TinyMCE instance.
		 *
		 * @since 1.4.1.
		 *
		 * @param editor
		 * @return void
		 */
		tinymceInit: function(editor) {
			this.root = $(editor.getBody());

			this.cacheElements();
			this.buildStyles();
		},

		/**
		 * Run through the list of selectors and populate the cache.
		 *
		 * @since 1.4.1.
		 *
		 * @return void
		 */
		cacheElements: function() {
			var self = this;

			$.each(this.cacheSelector, function(name, selector) {
				self.cache[name] = $(selector, self.root);
			});
		},

		/**
		 * Run through the list of callbacks and build the dynamic stylesheet.
		 *
		 * @since 1.4.1.
		 *
		 * @return void
		 */
		buildStyles: function() {
			var self = this;

			$.each(this.builder, function(name, f) {
				f(self);
			});
		},

		/**
		 * Create a stylesheet element and append it to the root element of the current context.
		 *
		 * On the front end, the context will just be document, but in TinyMCE, the context will be the body
		 * element within the iframe.
		 *
		 * @since 1.4.1.
		 *
		 * @link http://davidwalsh.name/add-rules-stylesheets
		 *
		 * @return object
		 */
		createStylesheet: function() {
			var self = this;

			this.stylesheet = this.stylesheet || (function() {
				// Create the <style> tag
				var $style = $('<style type="text/css">');

				// Add an id
				$style.attr('id', 'ttfmake-dynamic-styles');

				// WebKit hack :(
				//style.appendChild(document.createTextNode(''));
				$style.text('');

				// Add the <style> element to the page
				if (self.root.find('head').length > 0) {
					self.root.find('head').append($style);
				} else {
					self.root.parent().find('head').append($style);
				}

				return $style.get(0).sheet;
			})();
		},

		/**
		 * Remove the stylesheet element from the root and the property from the class.
		 *
		 * @since 1.4.1.
		 *
		 * @return void
		 */
		removeStylesheet: function() {
			if (this.root.find('head').length > 0) {
				$('#ttfmake-dynamic-styles', this.root).remove();
			} else {
				this.root.parent().find('#ttfmake-dynamic-styles').remove();
			}
			delete this.stylesheet;
		},

		/**
		 * Wrapper for removing the current stylesheet and generating a new one.
		 *
		 * @since 1.4.1.
		 *
		 * @return void
		 */
		resetStylesheet: function() {
			this.removeStylesheet();
			this.cacheElements();
			this.buildStyles();
		},

		/**
		 * Add a rule to the dynamic stylesheet.
		 *
		 * @since 1.4.1.
		 *
		 * @link http://davidwalsh.name/add-rules-stylesheets
		 *
		 * @param sheet
		 * @param selector
		 * @param rules
		 * @param index
		 * @return void
		 */
		addCSSRule: function(sheet, selector, rules, index) {
			var ruleIndex = index || 0;

			if('insertRule' in sheet) {
				sheet.insertRule(selector + '{' + rules + '}', ruleIndex);
			}
			else if('addRule' in sheet) {
				sheet.addRule(selector, rules, ruleIndex);
			}
		}
	};

	ttfmakeDynamicStylesheet.init();
})(jQuery);