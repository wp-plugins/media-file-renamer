=== Media File Renamer ===
Contributors: TigrouMeow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H2S7S3G4XMJ6J
Tags: rename, file, media, management, image, renamer, wpml, wp-retina-2x
Requires at least: 3.5
Tested up to: 4.0
Stable tag: 1.9.8

This plugins allows you to rename physically the media files by updating their titles. It also updates theirs links in the posts automatically.

== Description ==

By updating the name/title of the image or the media, this plugin will rename the filename nicely and attempt to update all the references to that media in the associated posts if any. Depending on the plugins you use and your settings, the plugin might not find all the references so you need to backup your website first then try. If the references are not being updated, please let me know in the forum with details.

Using the File Renamer in Tools, you can also bulk-rename all your files all at once.

The plugin has been tested with many plugins (including WP Retina 2x and WPML) and supports Windows, Linux, BSD and OSX. Please check the FAQ as it contains important information and backup your website before using the plugin for the first time.

Languages: English, French.

== Installation ==

1. Upload `media-file-renamer.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Upgrade Notice ==

Simply replace `media-file-renamer.php` by the new one.

== Frequently Asked Questions ==

= In the Media Library, the "File name" is wrong! =
Yes, this is a WordPress issue. This "File name" doesn't come from the real filename but from a database entry called GUID. This GUID should be unique and is only managed by WordPress. Before, the plugin used to modify this GUID accordingly but recently WordPress tries to prevent the plugins to do so. After some research, it appears that modifying this GUID is a very bad idea after all (please check this URL: http://pods.io/2013/07/17/dont-use-the-guid-field-ever-ever-ever/). Media File Manager will not do anything to the GUID from now. In short, don't think this "File name" as an actual filename, it is not. It is an identifier. 

I added an option if you really want to rename that but really, you shouldn't.

= What does the option "Rename on save?" =
Let's say you modify the titles of your media while editing a post. The plugin cannot rename the files yet, because you are in the process of editing your post and the image links are in there. If that option is checked, when the post is actually saved (drafted, scheduled or published), then the images will be renamed and the links updated. If you don't check this option, you would have to go to the Media Manager and click on the button "Rename Now" next to that image.

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

= 1.9.8 =
* Fix: Versioning.

= 1.9.4 =
* Add: New option to avoid to modify database (no updates, only renaming).
* Add: New option to force update the GUID (aka "File name"...). Not recommended _at all_.
* Fix: Options were without effect.

= 1.9.2 =
* Works with WordPress 4.0.

= 1.9.1 =
* Works with WordPress 3.9.

= 1.7.0 =
* Change: removed support for the GUID (and therefore the "File name"). Check the FAQ.

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