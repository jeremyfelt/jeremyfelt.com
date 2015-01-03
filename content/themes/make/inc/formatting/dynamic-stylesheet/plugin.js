( function( tinymce ) {
	if ( 'undefined' !== typeof window.ttfmakeDynamicStylesheet ) {
		tinymce.PluginManager.add('ttfmake_dynamic_stylesheet', function (editor, url) {
			if ('undefined' !== typeof ttfmakeDynamicStylesheetVars && ttfmakeDynamicStylesheetVars.tinymce) {
				editor.on('init', function () {
					ttfmakeDynamicStylesheet.tinymceInit(editor);
				});

				editor.addCommand('Make_Reset_Dynamic_Stylesheet', function () {
					ttfmakeDynamicStylesheet.resetStylesheet();
				});
			}
		});
	}
} )( tinymce );