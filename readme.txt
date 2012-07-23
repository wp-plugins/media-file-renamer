=== Plugin Name ===
Contributors: TigrouMeow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JAWE2XWH7ZE5U
Tags: rename, file, media, management, image, renamer, wpml, wp-retina-2x
Requires at least: 3.0.4
Tested up to: 3.4
Stable tag: 0.54

This plugins allows you to rename physically the media files by updating their titles. It also updates theirs links in the posts automatically.

== Description ==

By updating the name of the image / media, this plugin will rename physically the filename nicely, update all the references to that media in the associated post if there is any (img, src, url...) and modify the guid of the media. You can bulk rename all your files all at once. The plugin has been tested with many plugins, including WP Retina 2x and WPML.


== Installation ==

1. Upload `media-file-renamer.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Upgrade Notice ==

Simply replace `media-file-renamer.php` by the new one.


== Frequently Asked Questions ==

No questions yet.


== Screenshots ==

1. Type in the name of your media, that is all.
2. Special screen for bulk actions.
3. Has to be renamed.

== Changelog ==

= 0.54 =
* Fix: the "file name" in the media info was empty.

= 0.52 =
* Fix: SQL optimization & memory usage huge improvement.

= 0.5 =
* New view "To be renamed" in the Media Library.
* Added a nice counter to show the number of files that need to be renamed.
* Fixed: the previous update (0.4) was actually not containing all the changes.

= 0.4 =
* Support for WPML
* Support for Retina plugins such as WP Retina 2x
* Adds a '-' between the filename and counter in case of similar files
* Mark the media as to be renamed when its name is changed outside the Media Library (avoid all the issues we had before)

= 0.34 =
* The GUID is now updated using the URL of the images and not the post ID + title (http://wordpress.org/support/topic/plugin-media-file-renamer-incorrect-guid-fix-serious-bug?replies=2#post-2239192).

= 0.32 =
* Double-check before physically renaming the files.

= 0.3 =
* Corrections + improvements.
* Handles well the 'special cases' now.

= 0.2 =
* Tiny corrections.

= 0.1 =
* First release.
