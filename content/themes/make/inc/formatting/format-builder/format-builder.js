/* global jQuery, _, ttfmakeFormatBuilder */
var ttfmakeFormatBuilder = ttfmakeFormatBuilder || {};

( function( $ ) {
	var formatWindow, formatInsert, formatRemove;

	/**
	 * The Format Builder object
	 *
	 * This holds all the functionality of the format builder except the bits that
	 * explicitly hook into TinyMCE. Those are found in plugin.js.
	 *
	 * @since 1.4.1.
	 */
	ttfmakeFormatBuilder = {
		/**
		 * Stores the activeEditor instance for usage within the class.
		 *
		 * @since 1.4.1.
		 */
		editor: {},

		/**
		 * Stores the parameters for each format to be registered with the TinyMCE Formatter.
		 *
		 * @since 1.4.1.
		 */
		definitions: {},

		/**
		 * Stores the selectors that identify the HTML wrappers for each format
		 * and associates them with format models.
		 *
		 * @since 1.4.1.
		 */
		nodes: {},

		/**
		 * Stores the items that appear in the Format Builder listbox.
		 *
		 * @since 1.4.1.
		 */
		choices: {},

		/**
		 * Stores the models for each available format.
		 *
		 * @since 1.4.1.
		 */
		formats: {},

		/**
		 * The current format model when the Format Builder window is open.
		 *
		 * @since 1.4.1.
		 */
		currentFormat: {},

		/**
		 * Data associated with the current position/selection of the cursor
		 * in the TinyMCE editor.
		 *
		 * @since 1.4.1.
		 */
		currentSelection: {},

		/**
		 * Opens the TinyMCE modal window, and initializes all of the Format Builder
		 * functionality.
		 *
		 * @since 1.4.1.
		 *
		 * @param editor
		 */
		open: function( editor ) {
			this.editor = editor;
			this.currentSelection = editor.selection;

			var format = this.parseNode( this.currentSelection.getNode() ),
				items = [],
				args;

			if ('' == format) {
				// No existing format. Show listbox to choose a new format.
				items = [
					{
						type: 'form',
						name: 'listboxForm',
						items: ttfmakeFormatBuilder.getFormatListBox()
					}
				];
			} else if ('undefined' !== typeof ttfmakeFormatBuilder.formats[format]) {
				// Cursor is on an existing format. Only show the option form for that particular format.
				ttfmakeFormatBuilder.currentFormat = new ttfmakeFormatBuilder.formats[format]({ update: true });
				items = [
					{
						type: 'form',
						name: 'optionsForm',
						items: ttfmakeFormatBuilder.currentFormat.getOptionFields()
					}
				];
			}

			// Define the window args.
			args = {
				title: 'Format Builder',
				id: 'ttfmake-format-builder',
				autoScroll: true,
				maxHeight: 500,
				items: {
					type: 'container',
					name: 'formatContainer',
					layout: 'flex',
					align: 'stretch',
					direction: 'column',
					items: items
				},
				buttons: [
					ttfmakeFormatBuilder.getRemoveButton(),
					ttfmakeFormatBuilder.getInsertButton()
				],
				onclose: function() {
					// Reset the dynamic stylesheet.
					ttfmakeFormatBuilder.editor.execCommand('Make_Reset_Dynamic_Stylesheet');

					// Clear the current* objects so there are no collisions when the Format Builder
					// is opened again.
					ttfmakeFormatBuilder.editor = {};
					ttfmakeFormatBuilder.currentFormat = {};
					ttfmakeFormatBuilder.currentSelection = {};
				}
			};

			// Open the window.
			formatWindow = editor.windowManager.open(args);
		},

		/**
		 * Check to see if the cursor is currently on an existing format.
		 *
		 * @since 1.4.1.
		 *
		 * @param editor
		 * @param node
		 * @returns string
		 */
		parseNode: function( node ) {
			var format = '';

			$.each(this.nodes, function( fmt, selector ) {
				var match = ttfmakeFormatBuilder.editor.dom.getParents( node, selector );
				if ( match.length > 0 ) {
					format = fmt;
					return false;
				}
			});

			return format;
		},

		/**
		 * Search through the parents of the current node (plus the current node itself)
		 * for one that matches the given selector.
		 *
		 * @since 1.4.1.
		 *
		 * @param selector
		 * @param node
		 * @param editor
		 * @returns {*}
		 */
		getParentNode: function(selector, node, editor) {
			var Node = node || ttfmakeFormatBuilder.currentSelection.getNode(),
				Editor = editor || ttfmakeFormatBuilder.editor,
				matchedParents = Editor.dom.getParents(Node, selector);

			if (matchedParents.length > 0) {
				return matchedParents.shift();
			} else {
				return false;
			}
		},

		/**
		 * Get the JSON definition for the format chooser listbox.
		 *
		 * @since 1.4.1.
		 *
		 * @returns object
		 */
		getFormatListBox: function() {
			var listbox = {
				type: 'listbox',
				name: 'format',
				id: 'ttfmake-format-builder-picker',
				minWidth: 300,
				values: this.getFormatChoices(),
				onselect: function() {
					var choice = this.value(),
						fields = {
							type: 'form',
							name: 'optionsForm'
						},
						maxHeight = 500,
						winWidth, winHeight, viewWidth, viewHeight, deltaW, deltaH;

					// Only proceed if the chosen format has a model.
					if ('undefined' !== typeof ttfmakeFormatBuilder.formats[choice]) {
						ttfmakeFormatBuilder.currentFormat = new ttfmakeFormatBuilder.formats[choice];

						// Generate the options fields
						fields.items = ttfmakeFormatBuilder.currentFormat.getOptionFields();

						// Remove previous option forms.
						formatWindow.find('#optionsForm').remove();

						// Add the new option form.
						formatWindow.find('#formatContainer')[0].append(fields).reflow();

						// Show the Insert button.
						// (The Remove button is unnecessary for new formats)
						formatInsert.visible( true );

						// Resize the window (automatically repaints as well)
						formatWindow.resizeToContent();
						winWidth = formatWindow.layoutRect().w;
						winHeight = formatWindow.layoutRect().h;
						viewWidth = ttfmakeFormatBuilder.editor.dom.getViewPort().w;
						viewHeight = ttfmakeFormatBuilder.editor.dom.getViewPort().h;
						if (winHeight > maxHeight) {
							formatWindow.resizeTo(winWidth, maxHeight);
							winHeight = formatWindow.layoutRect().h;
						}
						deltaW = (viewWidth - winWidth) / 2;
						deltaH = (viewHeight - winHeight) / 2;
						formatWindow.moveTo(deltaW, deltaH);

						// Repaint
						//formatWindow.repaint();
					}
				}
			};

			return listbox;
		},

		/**
		 * Get the list of available formats for use in the format chooser listbox.
		 *
		 * @since 1.4.1.
		 *
		 * @returns array
		 */
		getFormatChoices: function() {
			var choices = [
				{
					value: '',
					text: 'Choose a format',
					disabled: true,
					classes: 'listbox-placeholder'
				}
			];

			$.each( this.choices, function( fmt, f ) {
				choices.push( f() );
			} );

			return choices;
		},

		/**
		 * Get the Insert button for the modal window.
		 *
		 * @since 1.4.1.
		 *
		 * @returns object
		 */
		getInsertButton: function() {
			var button = {
				text: ( 'undefined' === typeof ttfmakeFormatBuilder.currentFormat.get ) ? 'Insert' : 'Update',
				id: 'ttfmake-format-builder-insert',
				name: 'formatSubmit',
				classes: 'button-primary',
				hidden: ( 'undefined' === typeof ttfmakeFormatBuilder.currentFormat.get ),
				onPostRender: function() {
					// Store this control so it can be accessed later.
					formatInsert = this;
				},
				onclick: function() {
					// Get the current data from the options form.
					var data = formatWindow.find( '#optionsForm' )[0].toJSON(),
						html;

					// Feed the current data into the model and sanitize it.
					ttfmakeFormatBuilder.currentFormat.sanitizeOptions( data );

					// Insert the HTML into the editor and close the modal.
					ttfmakeFormatBuilder.currentFormat.insert();
					formatWindow.fire( 'submit' );
				}
			};

			return button;
		},

		/**
		 * Get the Remove button for the modal window.
		 *
		 * @since 1.4.1.
		 *
		 * @returns object
		 */
		getRemoveButton: function() {
			var button = {
				text: 'Remove',
				id: 'ttfmake-format-builder-remove',
				name: 'formatRemove',
				classes: 'button-secondary',
				hidden: ( 'undefined' === typeof ttfmakeFormatBuilder.currentFormat.get || true !== ttfmakeFormatBuilder.currentFormat.get( 'update' ) ),
				onPostRender: function() {
					// Store this control so it can be accessed later.
					formatRemove = this;
				},
				onclick: function() {
					ttfmakeFormatBuilder.currentFormat.remove();
					formatWindow.fire( 'submit' );
				}
			};

			return button;
		},

		/**
		 * Generate the definitions for a control group that picks and sets a color.
		 *
		 * @since 1.4.1.
		 *
		 * @param name
		 * @param label
		 * @returns object
		 */
		getColorButton: function( name, label ) {
			var model = ttfmakeFormatBuilder.currentFormat,
				button = {
					type: 'container',
					label: label,
					items: [
						{
							type: 'button',
							name: name + 'Button',
							border: '1 1 1 1',
							style: 'background-color: ' + model.escape( name ) + '; border-color: #e5e5e5; box-shadow: none; width: 28px;',
							onclick: function() {
								var self = this, // Store the button for later access.
									ctrl = this.next(); // Get the hidden text field with the hex code.

								// Open the TinyMCE color picker plugin
								ttfmakeFormatBuilder.editor.settings.color_picker_callback( function( value ) {
									value = _.escape(value);
									self.getEl().style.backgroundColor = value;
									ctrl.value( value );
								}, ctrl.value() );
							}
						},
						{
							type: 'textbox',
							name: name,
							hidden: true,
							value: model.escape( name )
						}
					]
				};

			return button;
		},

		/**
		 * Generate the definitions for a control group that picks and sets an icon.
		 *
		 * @since 1.4.1.
		 *
		 * @param name
		 * @param label
		 * @returns object
		 */
		getIconButton: function( name, label ) {
			var model = ttfmakeFormatBuilder.currentFormat,
				noIcon = '<span class="mce-add-icon">Add icon</span>',
				yesIcon = '<div class="mce-icon-choice"><i class="fa"></i></div>',
				button = {
					type: 'container',
					label: label,
					items: [
						{
							type: 'container',
							minWidth: 36,
							minHeight: 36,
							html: '',
							onPostRender: function() {
								var ctrl = this.next(); // Get the hidden text field with the icon code.

								if ( ctrl.value() ) {
									// Show the existing icon, if one is set.
									$(this.getEl()).html(yesIcon).find('i').addClass(ctrl.value());
								} else {
									// Show the "Add icon" pseudo link.
									$(this.getEl()).html(noIcon);
								}
							},
							onclick: function() {
								var self = this, // Store the button for later access.
									ctrl = this.next(); // Get the hidden text field with the icon code.

								window.ttfmakeIconPicker.open(ttfmakeFormatBuilder.editor, function(value) {
									if ('' !== value) {
										// Show the chosen icon.
										$(self.getEl()).html(yesIcon).find('i').addClass(value);
									} else {
										// Show the "Add icon" pseudo link.
										$(self.getEl()).html(noIcon);
									}

									// Set the value of the hidden text field.
									ctrl.value(value);
								}, ctrl.value());
							}
						},
						{
							type: 'textbox',
							name: name,
							hidden: true,
							value: model.escape( name )
						}
					]
				};

			return button;
		}
	};
})( jQuery );