## 1.2.2

* Fixed a bug that caused some style and script assets to not load correctly on some web host configurations

## 1.2.1

* Fixed issue where Page Builder was hidden in certain situations when adding a new page
* Updated theme screenshot with CC0-compatible image
* Added missing text domain strings
* Removed query string parameters from Make Plus links

## 1.2.0

* Added ability to override some auxiliary stylesheets and scripts in child theme
* Added ability for CPTs to use the builder
* Added a "Maintain aspect ratio" option for banner sections for better responsive control
* Added IDs for individual text columns
* Added menu in the header bar
* Added filters to control font size
* Added notice for users trying to install Make Plus as a theme
* Fixed issue where captions on non-linked gallery items would not reveal on iOS
* Fixed issue where HTML added to Header/Footer text fields appeared as plain text in the Customizer
* Fixed alignment issues with submenus
* Fixed issue that caused submenus to fall below some content
* Fixed JS errors that occurred when rich text editor was turned off
* Fixed issue with broken default background color
* Improved the responsiveness of banner sections
* Improved consistency of textdomain handling

## 1.1.1

* Added Japanese translations
* Added license information file
* Fixed an incorrect label in the Customizer
* Fixed issue where footer text was double sanitized
* Fixed issue with dropdown menus being unreachable on an iPad

## 1.1.0

* Added control for showing comment count
* Added controls for positioning author, date, and comment count
* Added control for aligning featured images

## 1.0.11

* Improved messaging about Make Plus
* Improved sorting of footer links in builder sections
* Fixed ID sanitization bugs where ID values were greater than the maximum allowed integer value
* Fixed bug that did not allow anyone but Super Admins to save banner sections in Multisite
* Fixed a bug that defaulted comments to being hidden on posts
* Removed unnecessary class from banner sections
* Added a notice about sidebars not being available on builder template pages
* Added more social icons

## 1.0.10

* Improved consistency in styling between custom menus and default menus
* Improved JetPack share button styling
* Fixed an issue with dynamically added TinyMCE instances affecting already added instances
* Added link to social menu support documentation

## 1.0.9

* Fixed PHP notice edge case when $post object is not set when saving post
* Fixed issue of white font not showing on TinyMCE background
* Updated Font Awesome to 4.1.0

## 1.0.8

* Removed Make Plus information from the admin bar
* Added Make Plus information to the Customizer
* Improved aspects of the builder to prepare for additional premium features

## 1.0.7

* Fixed bug that prevented default font from showing in the editor styles
* Fixed Photon conflict that rendered custom logo functionality unusable
* Added filter builder section footer action links
* Added builder API function for removing builder sections
* Added information about Style Kits, Easy Digital Downloads, and Page Duplicator
* Added German and Finnish translations

## 1.0.6

* Added Make Plus information
* Fixed bug with images not displaying properly when aspect ratio was set to none in the Gallery section
* Removed sanitization of Customizer description section as these never receive user input

## 1.0.5

* Improved styling of widgets
* Improved whitespacing in the builder interface
* Improved language in builder
* Improved builder icons
* Added styles to make sure empty text columns hold their width
* Added functionality to disable header items in the font select lists
* Added filter for showing/hiding footer credit
* Added styling for WooCommerce product tag cloud

## 1.0.4

* Improved banner slide image position
* Added underline for footer link
* Added function to determine if companion plugin is installed
* Added TinyMCE buttons from builder to other TinyMCE instances
* Builder API improvements
  * Added ability for templates to exist outside of a parent or child theme
  * Added class for noting whether a builder page is displayed or not
  * Added wrapper functions for getting images used in the builder for easier filterability
  * Added actions for altering builder from 3rd party code
  * Added event for after section is removed
  * Removed save post actions when builder isn't being saved
  * Improved the abstraction of data saving functions for easier global use
  * Improved timing of events to prevent unfortunate code loading issues
  * Fixed bug with determining next/prev section that could cause a fatal error

## 1.0.3

* Improved tagline to be more readable
* Improved CSS code styling without any functional changes

## 1.0.2

* Removed RTL stylesheet as it was just a placeholder
* Improved testimonial display in the TinyMCE editor
* Fixed bug with broken narrow menu when using default menu

## 1.0.1

* Improved builder section descriptions
* Improved compatibility for JetPack "component" plugins
* Improved margin below widgets in narrow view
* Improved spacing of elements in the customizer
* Fixed bug with overlay in gallery section
* Fixed bug with secondary color being applied to responsive menus

## 1.0.0

* Initial release