(function(tinymce, $) {
	if ( 'undefined' !== typeof window.ttfmakeFormatBuilder ) {
		tinymce.PluginManager.add('ttfmake_format_builder', function (editor, url) {
			editor.addCommand('Make_Format_Builder', function () {
				window.ttfmakeFormatBuilder.open(editor);
			});

			editor.addButton('ttfmake_format_builder', {
				icon   : 'ttfmake-format-builder',
				tooltip: 'Format Builder',
				cmd    : 'Make_Format_Builder'
			});

			editor.on('init', function () {
				$.each(ttfmakeFormatBuilder.definitions, function (name, defs) {
					editor.formatter.register(name, defs);
				});
			});
		});
	}
})(tinymce, jQuery);