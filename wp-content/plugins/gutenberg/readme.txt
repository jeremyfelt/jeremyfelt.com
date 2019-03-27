=== Gutenberg ===
Contributors: matveb, joen, karmatosed
Requires at least: 5.0.0
Tested up to: 5.0
Stable tag: 5.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A new editing experience for WordPress is in the works, with the goal of making it easier than ever to make your words, pictures, and layout look just right. This is the beta plugin for the project.

== Description ==

Gutenberg is more than an editor. While the editor is the focus right now, the project will ultimately impact the entire publishing experience including customization (the next focus area).

<a href="https://wordpress.org/gutenberg">Discover more about the project</a>.

= Editing focus =

> The editor will create a new page- and post-building experience that makes writing rich posts effortless, and has “blocks” to make it easy what today might take shortcodes, custom HTML, or “mystery meat” embed discovery. — Matt Mullenweg

One thing that sets WordPress apart from other systems is that it allows you to create as rich a post layout as you can imagine -- but only if you know HTML and CSS and build your own custom theme. By thinking of the editor as a tool to let you write rich posts and create beautiful layouts, we can transform WordPress into something users _love_ WordPress, as opposed something they pick it because it's what everyone else uses.

Gutenberg looks at the editor as more than a content field, revisiting a layout that has been largely unchanged for almost a decade.This allows us to holistically design a modern editing experience and build a foundation for things to come.

Here's why we're looking at the whole editing screen, as opposed to just the content field:

1. The block unifies multiple interfaces. If we add that on top of the existing interface, it would _add_ complexity, as opposed to remove it.
2. By revisiting the interface, we can modernize the writing, editing, and publishing experience, with usability and simplicity in mind, benefitting both new and casual users.
3. When singular block interface takes center stage, it demonstrates a clear path forward for developers to create premium blocks, superior to both shortcodes and widgets.
4. Considering the whole interface lays a solid foundation for the next focus, full site customization.
5. Looking at the full editor screen also gives us the opportunity to drastically modernize the foundation, and take steps towards a more fluid and JavaScript powered future that fully leverages the WordPress REST API.

= Blocks =

Blocks are the unifying evolution of what is now covered, in different ways, by shortcodes, embeds, widgets, post formats, custom post types, theme options, meta-boxes, and other formatting elements. They embrace the breadth of functionality WordPress is capable of, with the clarity of a consistent user experience.

Imagine a custom “employee” block that a client can drag to an About page to automatically display a picture, name, and bio. A whole universe of plugins that all extend WordPress in the same way. Simplified menus and widgets. Users who can instantly understand and use WordPress  -- and 90% of plugins. This will allow you to easily compose beautiful posts like <a href="http://moc.co/sandbox/example-post/">this example</a>.

Check out the <a href="https://wordpress.org/gutenberg/handbook/reference/faq/">FAQ</a> for answers to the most common questions about the project.

= Compatibility =

Posts are backwards compatible, and shortcodes will still work. We are continuously exploring how highly-tailored metaboxes can be accommodated, and are looking at solutions ranging from a plugin to disable Gutenberg to automatically detecting whether to load Gutenberg or not. While we want to make sure the new editing experience from writing to publishing is user-friendly, we’re committed to finding  a good solution for highly-tailored existing sites.

= The stages of Gutenberg =

Gutenberg has three planned stages. The first, aimed for inclusion in WordPress 5.0, focuses on the post editing experience and the implementation of blocks. This initial phase focuses on a content-first approach. The use of blocks, as detailed above, allows you to focus on how your content will look without the distraction of other configuration options. This ultimately will help all users present their content in a way that is engaging, direct, and visual.

These foundational elements will pave the way for stages two and three, planned for the next year, to go beyond the post into page templates and ultimately, full site customization.

Gutenberg is a big change, and there will be ways to ensure that existing functionality (like shortcodes and meta-boxes) continue to work while allowing developers the time and paths to transition effectively. Ultimately, it will open new opportunities for plugin and theme developers to better serve users through a more engaging and visual experience that takes advantage of a toolset supported by core.

= Contributors =

Gutenberg is built by many contributors and volunteers. Please see the full list in <a href="https://github.com/WordPress/gutenberg/blob/master/CONTRIBUTORS.md">CONTRIBUTORS.md</a>.

== Frequently Asked Questions ==

= How can I send feedback or get help with a bug? =

We'd love to hear your bug reports, feature suggestions and any other feedback! Please head over to <a href="https://github.com/WordPress/gutenberg/issues">the GitHub issues page</a> to search for existing issues or open a new one. While we'll try to triage issues reported here on the plugin forum, you'll get a faster response (and reduce duplication of effort) by keeping everything centralized in the GitHub repository.

= How can I contribute? =

We’re calling this editor project "Gutenberg" because it's a big undertaking. We are working on it every day in GitHub, and we'd love your help building it.You’re also welcome to give feedback, the easiest is to join us in <a href="https://make.wordpress.org/chat/">our Slack channel</a>, `#core-editor`.

See also <a href="https://github.com/WordPress/gutenberg/blob/master/CONTRIBUTING.md">CONTRIBUTING.md</a>.

= Where can I read more about Gutenberg? =

- <a href="http://matiasventura.com/post/gutenberg-or-the-ship-of-theseus/">Gutenberg, or the Ship of Theseus</a>, with examples of what Gutenberg might do in the future
- <a href="https://make.wordpress.org/core/2017/01/17/editor-technical-overview/">Editor Technical Overview</a>
- <a href="https://wordpress.org/gutenberg/handbook/reference/design-principles/">Design Principles and block design best practices</a>
- <a href="https://github.com/Automattic/wp-post-grammar">WP Post Grammar Parser</a>
- <a href="https://make.wordpress.org/core/tag/gutenberg/">Development updates on make.wordpress.org</a>
- <a href="https://wordpress.org/gutenberg/handbook/">Documentation: Creating Blocks, Reference, and Guidelines</a>
- <a href="https://wordpress.org/gutenberg/handbook/reference/faq/">Additional frequently asked questions</a>


== Changelog ==

For 5.3.0.

= Features =

 - Add the [block management modal](https://github.com/WordPress/gutenberg/pull/14224): Ability to hide/show blocks in the inserter. 
 - Support [nested blocks for the Cover Block](https://github.com/WordPress/gutenberg/pull/13822).
 - Add an experimental [Legacy Widget Block](https://github.com/WordPress/gutenberg/pull/13511) (enabled only in the plugin for the moment).

= Enhancements =

 - Update the [block outlines](https://github.com/WordPress/gutenberg/pull/14145) for the hover and selected states.
 - Allow [undoing automatic pattern block transformations](https://github.com/WordPress/gutenberg/pull/13917).
 - Add a [RichText collapsed format toolbar](https://github.com/WordPress/gutenberg/pull/14233) for code, inline image and strikethrough formats.
 - Allow [collapsing inserter panels](https://github.com/WordPress/gutenberg/pull/13884) when searching.
 - Add ability to transform [video shortcodes to video blocks](https://github.com/WordPress/gutenberg/pull/14042).
 - Add ability to transform [audio shortcodes to audio blocks](https://github.com/WordPress/gutenberg/pull/14045).
 - Add new @wordpress/data actions to [invalidate the resolvers cache](https://github.com/WordPress/gutenberg/pull/14225).
 - Support [custom classNames in the ToggleControl](https://github.com/WordPress/gutenberg/pull/13804) component.
 - Clarify the [button to exit the post lock](https://github.com/WordPress/gutenberg/pull/14347) modal.
 - Improve the [block validation error message](https://github.com/WordPress/gutenberg/pull/13499).
 - [Automatically use the WordPress](https://github.com/WordPress/gutenberg/pull/13877)  [babel config](https://github.com/WordPress/gutenberg/pull/14168) when using @wordpress/scripts CLI.
 - Add keyboard [shortcuts to indent/outdent](https://github.com/WordPress/gutenberg/pull/14343) list items.
 - Use [links instead of buttons](https://github.com/WordPress/gutenberg/pull/10815) in the document outline.
 - Use [`<s>` for strikethrough](https://github.com/WordPress/gutenberg/pull/14389), [not `<del>`](https://github.com/WordPress/gutenberg/pull/14430).
 - Center the [tooltips content](https://github.com/WordPress/gutenberg/pull/14473).
 - Update wording of the [block switcher tooltip](https://github.com/WordPress/gutenberg/pull/14470).
 - Add [support for the reduced motion](https://github.com/WordPress/gutenberg/pull/14021) browser mode.

= Bug Fixes =

 - Always show the [current month in the Calendar](https://github.com/WordPress/gutenberg/pull/13873) block for All CPTs but post.
 - In the Latest posts block, [avoid full line clickable titles](https://github.com/WordPress/gutenberg/pull/14109). 
 - Avoid relying on DOM nodes to add the [empty line in RichText](https://github.com/WordPress/gutenberg/pull/13850)  [component](https://github.com/WordPress/gutenberg/pull/14315). This fixes a number of lingering empty lines.
 - Fix the [MediaPlaceholder icon color](https://github.com/WordPress/gutenberg/pull/14257) on dark backgrounds.
 - Fix the [Classic block toolbar in RTL](https://github.com/WordPress/gutenberg/pull/14088) languages.
 - Fix the [more tag in the Classic block](https://github.com/WordPress/gutenberg/pull/14173).
 - Fix the [quote to heading](https://github.com/WordPress/gutenberg/pull/14348) block transformation.
 - Fix “null” appearing when [merging empty headings](https://github.com/WordPress/gutenberg/pull/13981) and paragraphs.
 - Fix the [block insertion restrictions](https://github.com/WordPress/gutenberg/pull/14020) in the global inserter.
 - Fix the [prepareEditableTree](https://github.com/WordPress/gutenberg/pull/14284) custom RichText Format API.
 - [Changes to the internal RichText format](https://github.com/WordPress/gutenberg/pull/14380) representation to separate objects (inline image..) from formats (bold…). This fixes a number of RichText issues.
 - Fix the [Spinner component styling](https://github.com/WordPress/gutenberg/pull/14418) in RTL languages.
 - Fix [focus loss when using the Set Featured Image](https://github.com/WordPress/gutenberg/pull/14415) buttons.
 - Fix [template lock](https://github.com/WordPress/gutenberg/pull/14390) not being taken into consideration.
 - Fix [composed characters](https://github.com/WordPress/gutenberg/pull/14449) at the beginning of RichText.
 - Fix several [block multi-selection](https://github.com/WordPress/gutenberg/pull/14448)  [bugs](https://github.com/WordPress/gutenberg/pull/14453).
 - Allow using a [float number as a step](https://github.com/WordPress/gutenberg/pull/14322) when using the RangeControl component.
 - Fix error when pasting a [caption shortcode without an image](https://github.com/WordPress/gutenberg/pull/14365) tag.
 - Fix [focus loss](https://github.com/WordPress/gutenberg/pull/14444) when combining sidebars and modals (or popovers).
 - Escape the [greater than character](https://github.com/WordPress/gutenberg/pull/9963) when serializing the blocks content into HTML.
 - Fix [pasting links into the classic block](https://github.com/WordPress/gutenberg/pull/14485).
 - Include missing [CSS in the classic block](https://github.com/WordPress/gutenberg/pull/12441).

= Documentation =

 - Enhance the [i18n process documentation](https://github.com/WordPress/gutenberg/pull/13909) with a complete example.
 - Add design guidelines to several components:
 - The [Button](https://github.com/WordPress/gutenberg/pull/14194) component
 - The [CheckboxControl](https://github.com/WordPress/gutenberg/pull/14153) component
 - The [MenuItemsChoice](https://github.com/WordPress/gutenberg/pull/14465) component.
 - The [MenuGroup](https://github.com/WordPress/gutenberg/pull/14466) component.
 - Update the [JavaScript setup tutorial](https://github.com/WordPress/gutenberg/pull/14440) to rely on the @wordpress/scripts package.
 - Lowercase [block editor](https://github.com/WordPress/gutenberg/pull/14205) and [classic editor](https://github.com/WordPress/gutenberg/pull/14203) terms to conform to the copy guidelines.
 - Use [a central script](https://github.com/WordPress/gutenberg/pull/14216) to generate the JavaScript API documentation and run [in parallel](https://github.com/WordPress/gutenberg/pull/14295).
 - Update the [packages release](https://github.com/WordPress/gutenberg/pull/14136)  [process](https://github.com/WordPress/gutenberg/pull/14260).
 - Update the plugin release docs to rely on a [lighter SVN checkout](https://github.com/WordPress/gutenberg/pull/14259).
 - Add automatic generation of JavaScript API documentation for:
   - [@wordpress/element](https://github.com/WordPress/gutenberg/pull/14269)
   - [@wordpress/escape-html](https://github.com/WordPress/gutenberg/pull/14268)
   - [@wordpress/html-entities](https://github.com/WordPress/gutenberg/pull/14267)
   - [@wordpress/keycodes](https://github.com/WordPress/gutenberg/pull/14265)
   - [@wordpress/a11y](https://github.com/WordPress/gutenberg/pull/14288)
   - [@wordpress/blob](https://github.com/WordPress/gutenberg/pull/14286)
   - [@wordpress/block-library](https://github.com/WordPress/gutenberg/pull/14282)
   - [@wordpress/compose](https://github.com/WordPress/gutenberg/pull/14278)
   - [@wordpress/dom](https://github.com/WordPress/gutenberg/pull/14273)
   - [@wordpress/i18n](https://github.com/WordPress/gutenberg/pull/14266)
   - [@wordpress/autop](https://github.com/WordPress/gutenberg/pull/14287)
   - [@wordpress/dom-ready](https://github.com/WordPress/gutenberg/pull/14272)
   - [@wordpress/block-editor](https://github.com/WordPress/gutenberg/pull/14285)
   - [@wordpress/rich-text](https://github.com/WordPress/gutenberg/pull/14220)
   - [@wordpress/blocks](https://github.com/WordPress/gutenberg/pull/14279)
   - [@wordpress/deprecated](https://github.com/WordPress/gutenberg/pull/14275)
   - [@wordpress/priority-queue](https://github.com/WordPress/gutenberg/pull/14262)
   - [@wordpress/shortcode](https://github.com/WordPress/gutenberg/pull/14218)
   - [@wordpress/viewport](https://github.com/WordPress/gutenberg/pull/14214)
   - [@wordpress/url](https://github.com/WordPress/gutenberg/pull/14217)
   - [@wordpress/redux-routine](https://github.com/WordPress/gutenberg/pull/14228)
   - [@wordpress/date](https://github.com/WordPress/gutenberg/pull/14276)
   - [@wordpress/block-serialization-default-parser](https://github.com/WordPress/gutenberg/pull/14280)
   - [@wordpress/plugins](https://github.com/WordPress/gutenberg/pull/14263)
   - [@wordpress/wordcount](https://github.com/WordPress/gutenberg/pull/14213)
   - [@wordpress/edit-post](https://github.com/WordPress/gutenberg/pull/14271)
 - Link to the [editor user documentation](https://github.com/WordPress/gutenberg/pull/14316) and remove the user documentation [markdown file](https://github.com/WordPress/gutenberg/pull/14318/files).
 - Typos and tweaks: [1](https://github.com/WordPress/gutenberg/pull/14321), [2](https://github.com/WordPress/gutenberg/pull/14355), [3](https://github.com/WordPress/gutenberg/pull/14382), [4](https://github.com/WordPress/gutenberg/pull/14439), [5](https://github.com/WordPress/gutenberg/pull/14471).

= Various =

 - Upgrade to [React 16.8.4](https://github.com/WordPress/gutenberg/pull/14400) ([React Hooks](https://github.com/WordPress/gutenberg/pull/14425)).
 - Fix the [dependencies of the e2e-tests](https://github.com/WordPress/gutenberg/pull/14212) and the [e2e-test-utils](https://github.com/WordPress/gutenberg/pull/14374) npm packages.
 - Avoid disabling [regeneratorRuntime in the babel config](https://github.com/WordPress/gutenberg/pull/14130) to avoid globals in npm packages.
 - [Work](https://github.com/WordPress/gutenberg/pull/14244)  [on](https://github.com/WordPress/gutenberg/pull/14247)  [various](https://github.com/WordPress/gutenberg/pull/14340)  [e2e tests](https://github.com/WordPress/gutenberg/pull/14219)  [stability](https://github.com/WordPress/gutenberg/pull/14230) improvements.
 - Regenerate RSS/Search block [test fixtures](https://github.com/WordPress/gutenberg/pull/14122).
 - [Move to travis.com](https://github.com/WordPress/gutenberg/pull/14250) as a CI server.
 - Add [clickBlockToolbarButton](https://github.com/WordPress/gutenberg/pull/14254) e2e test utility.
 - Add e2e tests:
   - to check the [keyboard navigation](https://github.com/WordPress/gutenberg/pull/13455) through blocks.
   - to verify that [the default block is selected](https://github.com/WordPress/gutenberg/pull/14191) after removing all the blocks.
   - to check the InnerBlocks [allowed blocks restrictions](https://github.com/WordPress/gutenberg/pull/14054).
 - Add unit tests [for the isKeyboardEvent](https://github.com/WordPress/gutenberg/pull/14073) utility.
 - Remove [CC-BY-3.0](https://github.com/WordPress/gutenberg/pull/14329) from the GPLv2 compatible licenses.
 - Polish the @wordpress/block-editor module:
   - Move the [block specific components](https://github.com/WordPress/gutenberg/pull/14112) to the package.
   - [Update the classnames](https://github.com/WordPress/gutenberg/pull/14420) to follow the CSS guidelines.
 - Update [eslint rules npm](https://github.com/WordPress/gutenberg/pull/14077)  [packages](https://github.com/WordPress/gutenberg/pull/14339).
 - Simplify the [hierarchical term selector strings](https://github.com/WordPress/gutenberg/pull/13938).
 - Update the [Latest comments block to use the “align support config”](https://github.com/WordPress/gutenberg/pull/11411) instead of a custom implementation.
 - Remove the [block snapshots tests](https://github.com/WordPress/gutenberg/pull/14349).
 - Remove [post install scripts](https://github.com/WordPress/gutenberg/pull/14353) and only run these in CI to improve test performance.
 - Tweak the plugin build zip script to [avoid prompting](https://github.com/WordPress/gutenberg/pull/14352) when the build environment is clean.
 - Add [withRegistry](https://github.com/WordPress/gutenberg/pull/14370) higher-order component to the @wordpress/data module.
 - Add missing [module entry point to the notices](https://github.com/WordPress/gutenberg/pull/14388) package.json.
 - Remove the Gutenberg [5.3 deprecated functions](https://github.com/WordPress/gutenberg/pull/14380).
 - Ensure [sourcemaps published to npm](https://github.com/WordPress/gutenberg/pull/14409) contain safe relative paths.
 - Remove the [replace_block filter usage](https://github.com/WordPress/gutenberg/pull/13569) and extend core editor settings instead.
 - Improve handling of [transpiled packages in unit tests](https://github.com/WordPress/gutenberg/pull/14432).
 - Add CLI arguments to launch [e2e tests in interactive mode](https://github.com/WordPress/gutenberg/pull/14129) more easily.
 -  Select a [unique radio input](https://github.com/WordPress/gutenberg/pull/14128) in a group when using the tabbables utility.