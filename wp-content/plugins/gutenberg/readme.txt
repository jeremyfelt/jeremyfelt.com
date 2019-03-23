=== Gutenberg ===
Contributors: matveb, joen, karmatosed
Requires at least: 5.0.0
Tested up to: 5.0
Stable tag: 5.1.1
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

For 5.2.0.

= Enhancements =

- Update the [button block description](https://github.com/WordPress/gutenberg/pull/13933) wording.
- Design and a11y [improvements for the custom color picker](https://github.com/WordPress/gutenberg/pull/13708).
- Tweak the [FontSizePicker height](https://github.com/WordPress/gutenberg/pull/11555) to match regular select elements.
- Improvements to the [local state persistence](https://github.com/WordPress/gutenberg/pull/13951) behavior.
- Improvements to the [URL input popove](https://github.com/WordPress/gutenberg/pull/13973) [design](https://github.com/WordPress/gutenberg/pull/14015).
- Disable [block navigation and document outline items](https://github.com/WordPress/gutenberg/pull/14081) in text mode.
- Improve the [quote block icons](https://github.com/WordPress/gutenberg/pull/14091).
- Animate the [sidebar tabs switching](https://github.com/WordPress/gutenberg/pull/13956).

= Bug Fixes =

- Select [the last block](https://github.com/WordPress/gutenberg/pull/13294) when pasting content.
- Fix the block validation when the [default attribute value](https://github.com/WordPress/gutenberg/pull/12757) of a block is changed.
- Forces the [min/max value validation](https://github.com/WordPress/gutenberg/pull/12952) in the RangeControl component.
- Display HTML properly in [the post titles](https://github.com/WordPress/gutenberg/pull/13622) of the latest posts block.
- Fix drag and [dropping a column](https://github.com/WordPress/gutenberg/pull/13941) block on itself.
- Fix [new lines](https://github.com/WordPress/gutenberg/pull/13799) in the preformatted block.
- Fix [text underline shortcut](https://github.com/WordPress/gutenberg/pull/14008).
- Fix calling [gutenberg plugin functions in the frontend](https://github.com/WordPress/gutenberg/pull/14096) context.
- Fix [pasting a single line](https://github.com/WordPress/gutenberg/pull/14138) from Google Docs (ignoring the strong element).
- Fix FocalPointPicker rendering [unlabelled input fields](https://github.com/WordPress/gutenberg/pull/14152).
- Show the [images uploaded in the gallery block](https://github.com/WordPress/gutenberg/pull/12435) in the media modal.
- Fix [wordwise selection](https://github.com/WordPress/gutenberg/pull/14184) on Windows.
- [Preserve empty table cells](https://github.com/WordPress/gutenberg/pull/14137) when pasting content.
- Fix [focus loss](https://github.com/WordPress/gutenberg/pull/14189) when deleting the last block.

= Documentation =

- Add [the Block specific toolbar button](https://github.com/WordPress/gutenberg/pull/14113) sample to the format api tutorial.
- Introduce a package to automatically generate the [API documentation](https://github.com/WordPress/gutenberg/pull/13329).
- Tweaks: [1](https://github.com/WordPress/gutenberg/pull/13906), [2](https://github.com/WordPress/gutenberg/pull/13920), [3](https://github.com/WordPress/gutenberg/pull/13940), [4](https://github.com/WordPress/gutenberg/pull/13954), [5](https://github.com/WordPress/gutenberg/pull/13993), [6](https://github.com/WordPress/gutenberg/pull/13995), [7](https://github.com/WordPress/gutenberg/pull/14083), [8](https://github.com/WordPress/gutenberg/pull/14099), [9](https://github.com/WordPress/gutenberg/pull/14089), [10](https://github.com/WordPress/gutenberg/pull/14177).

= Various =

- Introduce [a](https://github.com/WordPress/gutenberg/pull/14082) [generic](https://github.com/WordPress/gutenberg/pull/13088) [block](https://github.com/WordPress/gutenberg/pull/13105) [editor](https://github.com/WordPress/gutenberg/pull/14116) [module](https://github.com/WordPress/gutenberg/pull/14161).
- Creates [an empty page](https://github.com/WordPress/gutenberg/pull/13912) that will contain the future widget screen explorations.
- Fix [emoji in the demo content](https://github.com/WordPress/gutenberg/pull/13969).
- Warn when the user is using an [inline element as a RichText container](https://github.com/WordPress/gutenberg/pull/13921).
- Make Babel [import JSX pragma plugin](https://github.com/WordPress/gutenberg/pull/13809/) [aware](https://github.com/WordPress/gutenberg/pull/14106) of the createElement usage.
- [Include the JSX pragma plugin](https://github.com/WordPress/gutenberg/pull/13540) into the default WordPress babel config.
- Update the [non-embeddable URLs](https://github.com/WordPress/gutenberg/pull/13715) wording.

= Chore =

- Refactoring of the [block fixtures tests](https://github.com/WordPress/gutenberg/pull/13658).
- Refactoring the eslint [custom import lint rule](https://github.com/WordPress/gutenberg/pull/13937).
- Refactoring the selection of [previous/next blocks actions](https://github.com/WordPress/gutenberg/pull/13924).
- Refactoring the [post editor effects](https://github.com/WordPress/gutenberg/pull/13716) to use actions and resolvers instead.
- Use [forEach instead of map](https://github.com/WordPress/gutenberg/pull/13953) when appropriate and enforce it with an [eslint rule](https://github.com/WordPress/gutenberg/pull/14154).
- Remove [TinyMCE external dependency](https://github.com/WordPress/gutenberg/pull/13971) mapping.
- Extract [webpack config](https://github.com/WordPress/gutenberg/pull/13814) into the scripts package.
- Improve [e2e](https://github.com/WordPress/gutenberg/pull/14048) [tests](https://github.com/WordPress/gutenberg/pull/14108) stability.t
- Avoid mutating [webpack imported config](https://github.com/WordPress/gutenberg/pull/14039).
- [Upgrade Jest](https://github.com/WordPress/gutenberg/pull/13922) to version 24.
- Add [repository.directory field](https://github.com/WordPress/gutenberg/pull/14059) to the npm packages and an [linting rule](https://github.com/WordPress/gutenberg/pull/14200) to enforce it.
- Update [server blocks script to use core](https://github.com/WordPress/gutenberg/pull/14097) equivalent function.
- Remove the [vendor scripts registration](https://github.com/WordPress/gutenberg/pull/13573).
- Use the editor settings to pass a [mediaUpload handler](https://github.com/WordPress/gutenberg/pull/14115).
- Remove [deprecated Gutenberg plugin functions](https://github.com/WordPress/gutenberg/pull/14090) and [features](https://github.com/WordPress/gutenberg/pull/14144) moved to core.
- Remove unnecessary [Enzyme React 16 workarounds](https://github.com/WordPress/gutenberg/pull/14156) from the unit tests.
- Remove [wp-editor-font stylesheet override](https://github.com/WordPress/gutenberg/pull/14176).
- [Preserve inline scripts](https://github.com/WordPress/gutenberg/pull/13581) when overriding core scripts.
- Support [referencing the IconButton](https://github.com/WordPress/gutenberg/pull/14163) component.
- Refactor the [i18n setup](https://github.com/WordPress/gutenberg/pull/12559) of the Gutenberg plugin.

= Mobile =

- Add an [image placeholder](https://github.com/WordPress/gutenberg/pull/13777) when the size is being computed.
- Update the [image thumbnail](https://github.com/WordPress/gutenberg/pull/13764) when the image is being uploaded.
- Support the [Format Library](https://github.com/WordPress/gutenberg/pull/12249).
- Bottom Sheet [design](https://github.com/WordPress/gutenberg/pull/13855) [improvements](https://github.com/WordPress/gutenberg/pull/13882).
- Update the default [block appender placehoder](https://github.com/WordPress/gutenberg/pull/13880).
- Support [pasting content](https://github.com/WordPress/gutenberg/pull/13841) using the Gutenberg paste handler.
- Fix [alignment issues](https://github.com/WordPress/gutenberg/pull/13945) for the appender and paragraph block placeholders.
