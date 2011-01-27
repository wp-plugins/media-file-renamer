=== Plugin Name ===
Contributors: tigroumeow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JAWE2XWH7ZE5U
Tags: rename, file, media
Requires at least: 3.0.4
Tested up to: 3.0.4
Stable tag: trunk

This plugins allows you to rename physically the media files using their titles, and update theirs links in the posts.

== Description ==

This plugins allows you to rename physically the media files using their titles, and update theirs references in the posts automatically. This is done using the Media Manager.

It's very similar to the plugin called rename-media (0.1) except:
- it works with Windows and IIS
- in the case there is nothing to do (title wasn't renamed), it will consume less processor time
- it modifies the post in which the media is attached by updating the links (img, url, etc...)
- the guid of the media is the filename + the id instead of the shortlink to the file
- it logs warnings

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

= 0.1 =
* First release.
