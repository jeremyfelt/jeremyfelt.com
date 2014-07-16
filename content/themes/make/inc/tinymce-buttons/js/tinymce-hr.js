(function(tinymce) {
	tinymce.PluginManager.add('ttfmake_mce_hr_button', function( editor, url ) {
		editor.addButton('ttfmake_mce_hr_button', {
			icon: 'hr',
			tooltip: 'Horizontal line',
			onclick: function() {
				editor.windowManager.open( {
					title: 'Insert Button',
					body: [
						{
							type: 'listbox',
							name: 'hr',
							label: 'Style',
							values: [
								{
									text: 'Dotted',
									value: 'ttfmake-line-dotted'
								},
								{
									text: 'Double',
									value: 'ttfmake-line-double'
								}
							]
						}
					],
					onsubmit: function( e ) {
						editor.insertContent( '<hr class="' + e.data.hr + '" />');
					}
				});
			}
		});
	});
})(tinymce);