/* global Backbone, jQuery, _, ttfmakeFormatBuilder */
var ttfmakeFormatBuilder = ttfmakeFormatBuilder || {};

( function ( window, Backbone, $, _, ttfmakeFormatBuilder ) {
	'use strict';

	ttfmakeFormatBuilder.FormatModel = Backbone.Model.extend({
		defaults: {},

		initialize: function() {},

		getOptionFields: function() {},

		parseAttributes: function() {},

		insert: function() {},

		remove: function() {},

		/**
		 * Wrap each control in a customized form item.
		 *
		 * This is way harder than it should be. :/
		 *
		 * @since 1.4.1.
		 *
		 * @param fields
		 * @returns {Array}
		 */
		wrapOptionFields: function(fields) {
			var wrapped = [],
				spacer = {
					type: 'spacer'
				},
				label, item, i, c, last = false;

			// Counter for identifying the last option field.
			i = c = fields.length;

			// Wrap each field.
			$.each(fields, function(index, field) {
				if (1 == i) last = true;

				label = {
					type: 'label',
					text: field.label,
					style: 'float: left; line-height: 30px;'
				};
				item = {
					type: 'formitem',
					layout: 'stack',
					minWidth: 300,
					maxHeight: 50,
					border: (last) ? '0 0 0 0' : '0 0 1 0',
					style: 'border-color: #e5e5e5; border-style: solid;',
					hidden: (true === field.hidden),
					defaults: {
						style: 'float: right; text-align: right;'
					},
					items: [
						label,
						field
					]
				};
				wrapped.push(item);
				wrapped.push(spacer);
				i--;
			});

			return wrapped;
		},

		/**
		 * Sanitize incoming form values and store them in the model.
		 *
		 * @since 1.4.1.
		 *
		 * @param data
		 */
		sanitizeOptions: function( data ) {
			var self = this;

			$.each(data, function(key, value) {
				if (self.has(key)) {
					var sanitized = _.escape(value);
					self.set(key, sanitized);
				}
			});
		},

		/**
		 * Generate an element ID based on the Unix timestamp.
		 *
		 * @since 1.4.1.
		 *
		 * @link http://stackoverflow.com/questions/221294/how-do-you-get-a-timestamp-in-javascript
		 *
		 * @returns string
		 */
		createID: function() {
			// Backcompat
			if (! Date.now) {
				Date.now = function() { return new Date().getTime(); };
			}

			// Get the number of milliseconds since the current epoch.
			var newID = Date.now();

			// Make it an ID.
			return 'ttfmake-' + Math.round(newID / 1000);
		}
	});

	// Set up this model as a "no URL model" where data is not synced with the server
	ttfmakeFormatBuilder.FormatModel.prototype.sync  = function () { return null; };
	ttfmakeFormatBuilder.FormatModel.prototype.fetch = function () { return null; };
	ttfmakeFormatBuilder.FormatModel.prototype.save  = function () { return null; };
})( window, Backbone, jQuery, _, ttfmakeFormatBuilder );