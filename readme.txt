=== Plugin Name ===
Contributors: TigrouMeow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JAWE2XWH7ZE5U
Tags: rename, file, media, management, image, renamer
Requires at least: 3.0.4
Tested up to: 3.2.1
Stable tag: 0.34

This plugins allows you to rename physically the media files by updating their titles. It also updates theirs links in the posts automatically.

== Description ==

With this plugin, by updating the name of the image / media, you will also :

*	rename physically the filename
*	modify the guid of the media
*	update all the references to that media in the associated post if there is any (img, src, url...)

It has been tested and works ALSO with Windows and IIS. If the name cannot be renamed, a warning will be write to the error log but there will be no crash.


== Installation ==

1. Upload `media-file-renamer.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Upgrade Notice ==

Simply replace `media-file-renamer.php` by the new one.


== Frequently Asked Questions ==

No questions yet.


== Screenshots ==

1. Just like this. Note that the screenshot is very similar to the one from rename-media (0.1).


== Changelog ==

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
