=== Media File Renamer ===
Contributors: TigrouMeow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JAWE2XWH7ZE5U
Tags: rename, file, media, management, image, renamer, wpml, wp-retina-2x
Requires at least: 3.5
Tested up to: 3.6
Stable tag: 1.4.2

This plugins allows you to rename physically the media files by updating their titles. It also updates theirs links in the posts automatically.

== Description ==

By updating the name of the image / media, this plugin will rename physically the filename nicely, update all the references to that media in the associated post if there is any (img, src, url...). Using File Renamer (in Tools), you can bulk-rename all your files all at once, or only the flagged files, but it's recommended to do the renaming through the Media Library directly.

The plugin has been tested with many plugins (including WP Retina 2x and WPML) and supports Windows, Linux, BSD and OSX.

Languages: English, French.

== Installation ==

1. Upload `media-file-renamer.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Upgrade Notice ==

Simply replace `media-file-renamer.php` by the new one.

== Frequently Asked Questions ==

= I donated, can I get rid of the donation button? =
Of course. I don't like to see too many of those buttons neither ;) You can disable the donation buttons from all my plugins by adding this to your wp-config.php:
`define('WP_HIDE_DONATION_BUTTONS', true);`

= Can I contact you? =
Yes, sure, you can! But since the plugin got popular, I get many emails everyday which consume almost 10% of my whole lifetime (not kidding! + it's rarely issues coming from the plugin itself). So now I ask you to visit my website <a href='http://www.totorotimes.com'>Totoro Times</a>, pick a page you like, and share it on Facebook, Twitter or your own website. I hope you understand :) Thanks a lot!

== Screenshots ==

1. Type in the name of your media, that is all.
2. Special screen for bulk actions.
3. Has to be renamed.

== Changelog ==

= 1.4 =
* Fix: GUID issue.

= 1.3.4 =
* Fix: issue with attachments without metadata.
* Fix: UTF-8 title name (i.e. Japanese or Chinese characters).

= 1.3.0 =
* Add: option to rename the files automatically when a post is published.

= 1.2.2 =
* Fix: the 'to be renamed' flag was not removed in a few cases.

= 1.2.0 =
* Fix: issue with strong-caching with WP header images.
* Fix: now ignore missing files.
* Change: renaming is now part of the Media Library with nice buttons.
* Change: the dashboard has been moved to Tools (users should use the Media Library mostly).
* Change: no bubble counter on the dashboard menu; to avoid plugin to consume any resources.

= 1.0.4 =
* Fix: '<?' to '<?php'.
* Add: French translation.
* Change: Donation button (can be removed, check the FAQ).

= 1.0.2 =
* Fix: Ignore 'Header Image' to avoid related issues.
* Change: Updated screenshots.
* Change: 'To be renamed' filter removed (useless feature).

= 1.0.0 =
* Perfectly stable version :)
* Change: Rename Dashboard enhanced.
* Change: Scanning function now displays the results nicely.
* Change: Handle the media with 'physical' issues.

= 0.9.4 =
* Fix: Works better on Windows (file case).
* Fix: doesn't add numbering when the file exists already - was way too dangerous.
* Change: warns you if the Media title exists.

= 0.9.2 =
* Fix: Removed a 'warning'.

= 0.9 =
* Fix: Media were not flagged "as to be renamed" when the title was changed during editing a post.
* Change: Internal optimization.
* Add: Settings page.
* Add: Option to rename the slug or not (default: yes).

= 0.8 =
* Fix: Works with WP 3.5.
* Change: Update the links in DB directly.

= 0.6 =
* Fix: number of flagged media not updated straight after the mass rename.

= 0.54 =
* Fix: the "file name" in the media info was empty.

= 0.52 =
* Fix: SQL optimization & memory usage huge improvement.

= 0.5 =
* Add: New view "To be renamed" in the Media Library.
* Add: a nice counter to show the number of files that need to be renamed.
* Fix: the previous update (0.4) was actually not containing all the changes.

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

== Wishlist ==

Do you have suggestions? Feel free to contact me at <a href='http://www.totorotimes.com'>Totoro Times</a>.