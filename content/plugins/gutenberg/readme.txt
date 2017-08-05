=== Gutenberg ===
Contributors: matveb, joen
Requires at least: 4.8
Tested up to: 4.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Block editor for WordPress. This is the development plugin for the new block editor in core. Warning: This is beta software, do not run on real sites!

== Description ==

The goal of the block editor is to make adding rich content to WordPress simple and enjoyable.

<strong>Warning: This is beta software, do not run on production sites!</strong>

The new post and page building experience will make writing rich posts effortless, making it easy to do what today might take shortcodes, custom HTML, or "mystery meat" embed discovery.

WordPress already supports a large amount of "blocks", but doesn't surface them very well, nor does it give them much in the way of layout options. By embracing the blocky nature of rich post content, we will surface the blocks that already exist, as well as provide more advanced layout options for each of them. This will allow you to easily compose beautiful posts like <a href="http://moc.co/sandbox/example-post/">this example</a>.

Gutenberg is built by many contributors and volunteers. You can see the full list of contributors in <a href="https://github.com/WordPress/gutenberg/blob/master/CONTRIBUTORS.md">the GitHub CONTRIBUTORS.md file</a> which we are continuously updating. You can follow along on <a href="https://github.com/WordPress/gutenberg">github.com/WordPress/gutenberg</a> and on the <a href="https://make.wordpress.org/core/tag/editor/">#editor tag on the make.wordpress.org blog</a>.

== Frequently Asked Questions ==

= How can I send feedback or get help with a bug? =

We'd love to hear your bug reports, feature suggestions and any other feedback! Please head over to <a href="https://github.com/WordPress/gutenberg/issues">the GitHub issues page</a> to search for existing issues or open a new one. While we'll try to triage issues reported here on the plugin forum, you'll get a faster response (and reduce duplication of effort) by keeping everything centralized in the GitHub repository.

= How can I contribute? =

The more the merrier! To get started, check out our <a href="https://github.com/WordPress/gutenberg/blob/master/CONTRIBUTING.md">guide for contributors</a>.

== Changelog ==

= 0.7.1 =
* Address problem with the freeform block and Jetpack's contact form.

= 0.7.0 =
* Hide placeholders on focus—reduces visual distractions while writing.
* Add PostAuthor dropdown to the UI.
* Add theme support for customized color palettes and a shared component (applies to cover text and button blocks).
* Add theme support for wide images.
* Report on missing headings in the document outline feature.
* Update block validation to make it less prone to over-eagerness with trivial changes (like whitespace and new lines).
* Attempt to create an embed block automatically when pasting URL on a single line.
* Save post before previewing.
* Improve operations with "lists", enter on empty item creates new paragraph block, handling backspace, etc.
* Don't serialize attributes that match default attributes.
* Order link suggestions by relevance.
* Order embeds for easier discoverability.
* Added "keywords" property for searching blocks with aliases.
* Added responsive styles for Table block in the front end.
* Set default list type to be unordered list.
* Improve accessibility of UrlInput component.
* Improve accessibility and keyboard interaction of DropdownMenu.
* Improve Popover component and use for PostVisibility.
* Added higher order component for managing spoken messages.
* Localize schema for WP API, avoiding initialization delay if schema is present.
* Do not expose editor.settings to block authors.
* Do not remove tables on pasting.
* Consolidate block server-side files with client ones in the same directory.
* Removed array of paragraphs structure from text block.
* Trim whitespace when searching for blocks.
* Document, test, and refactor DropdownMenu component.
* Use separate mousetrap instance per component instance.
* Add npm organization scope to WordPress dependencies.
* Expand utilities around fixture regeneration.
* Renamed "Text" to "Paragraph".
* Fix multi-selection "delete" functionality.
* Fix text color inline style.
* Fix issue caused by changes with React build process.
* Fix splitting editable without child nodes.
* Use addQueryArgs in oEmbed proxy url.
* Update dashicons with new icons.
* Clarify enqueuing block assets functions.
* Added code coverage information to docs.
* Document how to create new docs.
* Add example of add_theme_support in docs.
* Added opt-in mechanism for learning what blocks are being added to the content.

= 0.6.0 =
* Split paragraphs on enter—we have been exploring different behaviours here.
* Added grid layout option for latest posts with columns slider control.
* Show internal posts / pages results when creating links.
* Added "Cover Text" block with background, text color, and full-width options.
* Autosaving drafts.
* Added "Read More" block.
* Added color options to the button block.
* Added mechanism for validating and protecting blocks that may have suffered unrecognized edits.
* Add patterns plugin for text formatting shortcuts: create lists by adding * at the beginning of a text line, use # to create headings, and backticks for code.
* Implement initial support for Cmd/Ctrl+Z (undo) and Cmd/Ctrl+Shift+Z (redo).
* Improve pasting experience from outside editors by transforming content before converting to blocks.
* Improve gallery creation flow by opening into "gallery" mode from placeholder.
* Added page attributes with menu order setting.
* Use two distinct icons for quote style variations.
* Created KeyboardShortcuts component to handle keyboard events.
* Add support for custom icons (non dashicons) on blocks.
* Initialize new posts with auto-draft to match behaviour of existing editor.
* Don't display "save" button for published posts.
* Added ability to set a block as "use once" only (example: "read more" block).
* Hide gallery display settings in media modal.
* Simplify "cover image" markup and resolve conflict state in demo.
* Introduce PHP classes for interacting with block types.
* Announce block search results to assistive technologies.
* Reveal "continue writing" shortcuts on focus.
* Update document.title when the post title changes.
* Added focus styles to several elements in the UI.
* Added external-link component to handle links opening in new tabs or windows.
* Improve responsive video on embed previews.
* Improve "speak" messages for tag suggestions.
* Make sure newly created blocks are marked as valid.
* Preserve valid state during transformations.
* Allow tabbing away from table.
* Improve display of focused panel titles.
* Adjust padding and margins across various design elements for consistency and normalization.
* Fix pasting freeform content.
* Fix proper propagation of updated block attributes.
* Fix parsing and serialization of multi-paragraph pullquotes.
* Fix a case where toggling pending preview would consider post as saved.
* Fix positioning of block mover on full-width blocks.
* Fix line height regression in quote styles.
* Fix IE11 with polyfill for fetch method.
* Fix case where blocks are created with isTyping and it never clears.
* Fix block warning display in IE11.
* Polish inspector visual design.
* Prevent unhandled actions from returning new state reference.
* Prevent unintentionally clearing link input value.
* Added focus styles to switch toggle components.
* Avoid navigating outside the editor with arrow keys.
* Add short description to Verse block.
* Initialize demo content only for new demo posts.
* Improve insert link accessibility.
* Improve version compare checks for plugin compatibility.
* Clean up obsolete poststoshowattribute in LatestPosts block.
* Consolidate addQueryArgs usage.
* Add unit tests to inserter.
* Update fixtures with latest modifications and ensure all end in newlines.
* Added codecov for code coverage.
* Clean up JSDoc comments.
* Link to new docs within main readme.

= 0.5.0 =
* New tabs mode for the sidebar to switch between post settings and block inspector.
* Implement recent blocks display.
* Mobile implementation of block mover, settings, and delete actions.
* Search through all tabs on the inserter and hide tabs.
* New documentation app to serve all tutorials, faqs, docs, etc.
* Enable ability to add custom classes to blocks (via inspector).
* Add ability to drag-and-drop on image block placeholders to upload images.
* Add "table of contents" document outline for headings (with empty heading validation).
* Refactor tests to use Jest API.
* New block: Verse (intended for poetry, respecting whitespace).
* Avoid showing UI when typing and starting a new paragraph (text block).
* Display warning message when navigating away from the editor with unsaved changes.
* Use old editor as "freeform".
* Improve PHP parser compatibility with different server configurations ("mbstring" extension and PCRE settings).
* Improve PostVisibility markup and accessibility.
* Add shortcuts to manage indents and levels in List block.
* Add alignment options to latest posts block.
* Add focus styles for quick tags buttons in text mode.
* Add way to report PHP parsing performance.
* Add labels and roles to UrlInput.
* Add ability to set custom placeholders for text and headings as attributes.
* Show error message when trashing action fails.
* Pass content to dynamic block render functions in PHP.
* Fix various z-index issues and clarify reasonings.
* Fix DropdownMenu arrows navigation and add missing aria-label.
* Update sandboxed iframe size calculations.
* Export inspector controls component under wp.blocks.
* Adjust Travis JS builds to improve task allocation.
* Fix warnings during tests.
* Fix caret jumping when switching formatting in Editable.
* Explicitly define prop-types as dependency.
* Update list of supported browsers for consistency with core.

= 0.4.0 =
* Initial FAQ (in progress).
* API for handling pasted content. (Aim is to have specific handling for converting Word, Markdown, Google Docs to native WordPress blocks.)
* Added support for linking to a url on image blocks.
* Navigation between blocks using arrow keys.
* Added alternate Table block with TinyMCE functionality for adding/removing rows/cells, etc. Retired previous one.
* Parse more/noteaser comment tokens from core.
* Re-engineer the approach for rendering embed frames.
* First pass at adding aria-labels to blocks list.
* Setting up Jest for better testing environment.
* Improve performance of server-side parsing.
* Update blocks documentation with latest API functions and clearer examples.
* Use fixed position for notices.
* Make inline mode the default for Editable.
* Add actions for plugins to register frontend and editor assets.
* Supress gallery settings sidebar on media library when editing gallery.
* Validate save and edit render when registering a block.
* Prevent media library modal from opening when loading placeholders.
* Update to sidebar design and behaviour on mobile.
* Improve font-size in inserter and latest posts block.
* Improve rendering of button block in the front end.
* Add aria-label to edit image button.
* Add aria-label to embed input url input.
* Use pointer cursor for tabs in inserter.
* Update design docs with regard to selected/unselected states.
* Improve generation of wp-block-* classes for consistency.
* Select first cell of table block when initializing.
* Fix wide and full alignment on the front-end when images have no caption.
* Fix initial state of freeform block.
* Fix ability to navigate to resource on link viewer.
* Fix clearing floats on inserter.
* Fix loading of images in library.
* Fix auto-focusing on table block being too agressive.
* Clean double reference to pegjs in dependencies.
* Include messages to ease debugging parser.
* Check for exact match for serialized content in parser tests.
* Add allow-presentation to fix issue with sandboxed iframe in Chrome.
* Declare use of classnames module consistently.
* Add translation to embed title.
* Add missing text domains and adjust PHPCS to warn about them.
* Added template for creating new issues including mentions of version number.

= 0.3.0 =
* Added framework for notices and implemented publishing and saving ones.
* Implemented tabs on the inserter.
* Added text and image quick inserts next to inserter icon at the end of the post.
* Generate front-end styles for core blocks and enqueue them.
* Include generated block classname in edit environment.
* Added "edit image" button to image and cover image blocks.
* Added option to visually crop images in galleries for nicer alignment.
* Added option to disable dimming the background in cover images.
* Added buffer for multi-select flows.
* Added option to display date and to configure number of posts in LatestPosts block.
* Added PHP parser based on PEG.js to unify grammars.
* Split block styles for display so they can be loaded on the theme.
* Auto-focusing for inserter search field.
* Added text formatting to CoverImage block.
* Added toggle option for fixed background in CoverImage.
* Switched to store attributes in unescaped JSON format within the comments.
* Added placeholder for all text blocks.
* Added placeholder text for headings, quotes, etc.
* Added BlockDescription component and applied it to several blocks.
* Implemented sandboxing iframe for embeds.
* Include alignment classes on embeds with wrappers.
* Changed the block name declaration for embeds to be "core-embed/name-of-embed".
* Simplified and made more robust the rendering of embeds.
* Different fixes for quote blocks (parsing and transformations).
* Improve display of text within cover image.
* Fixed placeholder positioning in several blocks.
* Fixed parsing of HTML block.
* Fixed toolbar calculations on blocks without toolbars.
* Added heading alignments and levels to inspector.
* Added sticky post setting and toggle.
* Added focus styles to inserter search.
* Add design blueprints and principles to the storybook.
* Enhance FormTokenField with accessibility improvements.
* Load word-count module.
* Updated icons for trash button, and Custom HTML.
* Design tweaks for inserter, placeholders, and responsiveness.
* Improvements to sidebar headings and gallery margins.
* Allow deleting selected blocks with "delete" key.
* Return more than 10 categories/tags in post settings.
* Accessibility improvements with FormToggle.
* Fix media button in gallery placeholder.
* Fix sidebar breadcrumb.
* Fix for block-mover when blocks are floated.
* Fixed inserting Freeform block (now classic text).
* Fixed missing keys on inserter.
* Updated drop-cap class implementation.
* Showcasing full-width cover image in demo content.
* Copy fixes on demo content.
* Hide meta-boxes icons for screen readers.
* Handle null values in link attributes.

= 0.2.0 =
* Include "paste" as default plugin in Editable.
* Extract block alignment controls as a reusable component.
* Added button to delete a block.
* Added button to open block settings in the inspector.
* New block: Custom HTML (to write your own HTML and preview it).
* New block: Cover Image (with text over image support).
* Rename "Freeform" block to "Classic Text".
* Added support for pages and custom post types.
* Improve display of "saving" label while saving.
* Drop usage of controls property in favor of components in render.
* Add ability to select all blocks with ctrl/command+A.
* Automatically generate wrapper class for styling blocks.
* Avoid triggering multi-select on right click.
* Improve target of post previewing.
* Use imports instead of accessing the wp global.
* Add block alignment and proper placeholders to pullquote block.
* Wait for wp.api before loading the editor. (Interim solution.)
* Adding several reusable inspector controls.
* Design improvements to floats, switcher, and headings.
* Add width classes on figure wrapper when using captions in images.
* Add image alt attributes.
* Added html generation for photo type embeds.
* Make sure plugin is run on WP 4.8.
* Update revisions button to only show when there are revisions.
* Parsing fixes on do_blocks.
* Avoid being keyboard trapped on editor content.
* Don't show block toolbars when pressing modifier keys.
* Fix overlapping controls in Button block.
* Fix post-title line height.
* Fix parsing void blocks.
* Fix splitting inline Editable instances with shift+enter.
* Fix transformation between text and list, and quote and list.
* Fix saving new posts by making post-type mandatory.
* Render popovers above all elements.
* Improvements to block deletion using backspace.
* Changing the way block outlines are rendered on hover.
* Updated PHP parser to handle shorthand block syntax, and fix newlines.
* Ability to cancel adding a link from link menu.

= 0.1.0 =
* First release of the plugin.
