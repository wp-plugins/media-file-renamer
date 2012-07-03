=== Plugin Name ===
Contributors: TigrouMeow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JAWE2XWH7ZE5U
Tags: rename, file, media, management, image, renamer, wpml, wp-retina-2x
Requires at least: 3.0.4
Tested up to: 3.2.1
Stable tag: 0.4

Allows you to rename physically the media files by updating their titles. The related posts will be also updated automatically.

== Description ==

Thanks to this plugin, by updating the name of the image / media, it will :

*	rename physically the filename nicely
*	update all the references to that media in the associated posts (WPML is supported)
*	modify the guid of the media

To avoid issues, the filename will not be changed if you are changing the media name while editing the post. The media will be "marked" as "to be renamed" in WordPress and you will have a "Rename" link appearing in your Media Manager.


== Installation ==

1. Upload `media-file-renamer.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Upgrade Notice ==

Simply replace `media-file-renamer.php` by the new one.


== Frequently Asked Questions ==

No questions yet.


== Screenshots ==

1. Very easy.


== Changelog ==

= 0.4 =
* Support for WPML.
* Support for Retina plugins such as WP Retina 2x.
* Adds a '-' between the filename and counter in case of similar files.
* Mark the media as to be renamed when its name is changed outside the Media Library (avoid all the issues we had before).

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
