(function(tinymce, $) {
	tinymce.PluginManager.add('ttfmake_hr', function( editor, url ) {
		var hrWindow, hrListBox, hrSubmit;

		editor.addButton('ttfmake_hr', {
			icon: 'hr',
			tooltip: 'Horizontal line',
			onclick: function() {
				hrWindow = editor.windowManager.open( {
					title: 'Insert Horizontal Line',
					id: 'ttfmake-hr-picker',
					body: [
						{
							type: 'listbox',
							name: 'hr',
							minWidth: 240,
							values: [
								{
									text: 'Choose a line style',
									value: '',
									classes: 'listbox-placeholder',
									disabled: true
								},
								{
									text: 'solid',
									value: 'solid-1'
								},
								{
									text: 'dotted',
									value: 'dotted-2'
								},
								{
									text: 'dashed',
									value: 'dashed-2'
								},
								{
									text: 'double',
									value: 'double-6'
								}
							],
							onPostRender: function() {
								hrListBox = this;
							},
							onselect: function() {
								if (this.value()) {
									hrSubmit.disabled(false);
								} else {
									hrSubmit.disabled(true);
								}
							}
						}
					],
					buttons: [
						{
							text: 'Insert',
							name: 'hrSubmit',
							classes: 'button-primary',
							disabled: true,
							onPostRender: function() {
								hrSubmit = this;
							},
							onclick: function() {
								if (hrListBox.value()) {
									var selection = hrListBox.value(),
										$hr = $('<hr>'),
										styles, html;

									styles = selection.split(/-/);

									$hr.addClass('ttfmake-hr');
									$hr.css({
										borderStyle: styles[0],
										borderTopWidth: styles[1] + 'px'
									});
									html = $hr.wrap('<div>').parent().html();

									editor.insertContent(html);
									hrWindow.fire('submit');
								}
							}
						}
					]
				});
			}
		});
	});
})(tinymce, jQuery);