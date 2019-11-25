=== Gutenberg ===
Contributors: matveb, joen, karmatosed
Requires at least: 5.1.0
Tested up to: 5.2
Stable tag: 6.8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The block editor was introduced in core WordPress with version 5.0. This beta plugin allows you to test bleeding-edge features around editing and customization projects before they land in future WordPress releases.

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

### Features

* Support changing the [image title attribute](https://github.com/WordPress/gutenberg/pull/11070) in the Image block.

### Bugs

* Fix  [invalid Pullquote blocks](https://github.com/WordPress/gutenberg/pull/18194) when setting a color from the palette presets.
* Fix the columns left/right [full-width margins](https://github.com/WordPress/gutenberg/pull/18021).
* Prevent [fast consecutive updates](https://github.com/WordPress/gutenberg/pull/18219) from triggering blocks reset.
* Fix block [movers for floated blocks](https://github.com/WordPress/gutenberg/pull/18230).
* Fix [Radio buttons styling](https://github.com/WordPress/gutenberg/pull/18183) in meta boxes.
* Fix the [default image sizes used for featured images](https://github.com/WordPress/gutenberg/pull/15844) displayed in the editor.
* Prevent the unsaved changes warning from popping-up when [deleting posts](https://github.com/WordPress/gutenberg/pull/18275).
* Revert [img and iframe styles](https://github.com/WordPress/gutenberg/pull/18287) to block editor container scope.
* Block Merge: guard for [undefined attribute definition](https://github.com/WordPress/gutenberg/pull/17937).

### Enhancements

* Inserter: [Immediately insert block](https://github.com/WordPress/gutenberg/pull/16708) when only one block type is allowed.
* Update the list of the [default available gradients](https://github.com/WordPress/gutenberg/pull/18214).
* [Disable indent/outdent buttons](https://github.com/WordPress/gutenberg/pull/17819) when necessary in the List block.

### New APIs

* Add theme support API to define [custom gradients presets](https://github.com/WordPress/gutenberg/pull/17841).
* Mark the [AsyncMode](https://github.com/WordPress/gutenberg/pull/18154) data module API as stable.
* Mark the [mediaUpload @wordpress/block-editor setting](https://github.com/WordPress/gutenberg/pull/18156) as stable.
* Add a **wpenv.json** [config file support for](https://github.com/WordPress/gutenberg/pull/18121) [@wordpress/env](https://github.com/WordPress/gutenberg/pull/18294).

### Various

* Refactor the way [HTML is escaped by the RichText](https://github.com/WordPress/gutenberg/pull/17994) component.
* Refactor and [simplify the block margins CSS](https://github.com/WordPress/gutenberg/pull/18346) in the editor.
* Use [HTTPS git clone](https://github.com/WordPress/gutenberg/pull/18136) in the Gutenberg release tool for more stability.
* Update [ExternalLink](https://github.com/WordPress/gutenberg/pull/18142), [BaseControl and FormTokenField](https://github.com/WordPress/gutenberg/pull/18165) components to use the VisuallyHidden component for the screen reader text.
* Add several components to Storybook: 
  * [Spinner](https://github.com/WordPress/gutenberg/pull/18145),
  * [Draggable](https://github.com/WordPress/gutenberg/pull/18070),
  * [RangeControl](https://github.com/WordPress/gutenberg/pull/17846),
  * [FontSizePicker](https://github.com/WordPress/gutenberg/pull/18149),
  * [Modal](https://github.com/WordPress/gutenberg/pull/18083),
  * [Snackbar](https://github.com/WordPress/gutenberg/pull/18386),
  * [ToggleControl](https://github.com/WordPress/gutenberg/pull/18388),
  * [ResizableBox](https://github.com/WordPress/gutenberg/pull/18097/files).
* Refactor the [block-directory search to insert](https://github.com/WordPress/gutenberg/pull/17576) as an Inserter plugin.
* Improve the experimental [useColors React](https://github.com/WordPress/gutenberg/pull/18147) [hook](https://github.com/WordPress/gutenberg/pull/18286).
* Upgrade [Puppeteer](https://github.com/WordPress/gutenberg/pull/18205) to the last version.
* Update to the [last version of npm-package-json-lint](https://github.com/WordPress/gutenberg/pull/18160).
* **i18n**: Fix string concatenation in the [Verse block example](https://github.com/WordPress/gutenberg/pull/18365) and add `translators` string.
* Change Detection: Add an [e2e test case for post trashing](https://github.com/WordPress/gutenberg/pull/18290).
* Fix the [e2e tests watch command](https://github.com/WordPress/gutenberg/pull/18391).

### Experimental

* Block Content Areas:
  * Support [loading block templates](https://github.com/WordPress/gutenberg/pull/18247) from themes.
* Navigation block:
  * Add [default frontend styles](https://github.com/WordPress/gutenberg/pull/18094) for the Navigation block.
  * Use [RichText for navigation menu item](https://github.com/WordPress/gutenberg/pull/18182) instead of TextControl.
  * Add [block navigator](https://github.com/WordPress/gutenberg/pull/18202) to the inspector panel.
  * Use an [SVG icon](https://github.com/WordPress/gutenberg/pull/18222) for the color selector.
  * Add a new API for [horizontal movers](https://github.com/WordPress/gutenberg/pull/16615) and [use](https://github.com/WordPress/gutenberg/pull/18234) it for the navigation block.
  * Add a new [Link creation](https://github.com/WordPress/gutenberg/pull/17846) [and](https://github.com/WordPress/gutenberg/pull/18405) [edition](https://github.com/WordPress/gutenberg/pull/18225) [UI](https://github.com/WordPress/gutenberg/pull/18285) and [use](https://github.com/WordPress/gutenberg/pull/18062) it for the navigation block.
  * Add an [appender](https://github.com/WordPress/gutenberg/pull/18100) to the block navigator.
  * Add a block [placeholder](https://github.com/WordPress/gutenberg/pull/18363).
  * Various fixes and refactorings: [1](https://github.com/WordPress/gutenberg/pull/18189), [2](https://github.com/WordPress/gutenberg/pull/18178), [3](https://github.com/WordPress/gutenberg/pull/18188), [4](https://github.com/WordPress/gutenberg/pull/18153), [5](https://github.com/WordPress/gutenberg/pull/18221), [6](https://github.com/WordPress/gutenberg/pull/18278), [7](https://github.com/WordPress/gutenberg/pull/18172), [8](https://github.com/WordPress/gutenberg/pull/18346), [9](https://github.com/WordPress/gutenberg/pull/18376), [10](https://github.com/WordPress/gutenberg/pull/18150), [11](https://github.com/WordPress/gutenberg/pull/18292), [12](https://github.com/WordPress/gutenberg/pull/18374), [13](https://github.com/WordPress/gutenberg/pull/18367), [14](https://github.com/WordPress/gutenberg/pull/18350), [15](https://github.com/WordPress/gutenberg/pull/18412).
* Add [ResponsiveBlockControl](https://github.com/WordPress/gutenberg/pull/16790) component.
* Add initial [API for block patterns](https://github.com/WordPress/gutenberg/pull/18270).

### Documentation

* Add an introduction [README for Storybook](https://github.com/WordPress/gutenberg/pull/18245).
* Typos and fixes: [1](https://github.com/WordPress/gutenberg/pull/18187), [2](https://github.com/WordPress/gutenberg/pull/18198), [3](https://github.com/WordPress/gutenberg/pull/18204https://github.com/WordPress/gutenberg/pull/18204), [4](https://github.com/WordPress/gutenberg/pull/18218), [5](https://github.com/WordPress/gutenberg/pull/18221), [6](https://github.com/WordPress/gutenberg/pull/18226).


