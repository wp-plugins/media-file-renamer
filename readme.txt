=== Media File Renamer ===
Contributors: TigrouMeow
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H2S7S3G4XMJ6J
Tags: rename, file, media, management, image, renamer, wpml, wp-retina-2x
Requires at least: 3.5
Tested up to: 4.3.1
Stable tag: 2.4.2

This plugin physically renames the filenames of you media when their titles are updated. Theirs links in the posts, pages, widgets (and more) will be also updated accordingly. This behavior can be tweaked through filters.

== Description ==

The Media File Renamer is a WordPress plugin that renames media files nicely for a cleaner system and for a better SEO.

It automatically renames your media filenames depending on their titles. When files are renamed, the references to it are also updated (posts, pages, custom types and their metadata). There is also a little dashboard called File Renamer in Media that will help you rename all your files at once. Advanced users can change the way the files are renamed by using the plugin's filter (check the FAQ).

The Pro users are given a few more features like manual renaming and advanced logs (SQL queries). A good way to use the plugin is to actually let it do the renaming automatically (like in the free version) and to do it manually for a few files for fine tuning.

BE CAREFUL. File renaming is a dangerous process. Before renaming everything automatically, try to rename a few files first and check if all the references to this file are still alright on your website. Depending on your plugins, theme or specific settings, the plugin might not find all the references. I strongly advise you to backup your database and your uploaded files first. If references aren't updated properly, please contact me with details about it. I will try my best to cover more and more special cases.

NOTE. This plugin will not allow you to change the filename directly. You need to change the title of the media in the standard WordPress Media Library. This plugin will then change the filename be the same as the new title. If you want to rename the filename directly, you will need to upgrade to the Pro version (http://apps.meow.fr/media-file-renamer/).

This plugin works perfectly with WP Retina 2x, WPML and many more. Is has been tested in Windows, Linux, BSD and OSX systems.

Languages: English, French.

== Installation ==

1. Upload `media-file-renamer.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Try it with one file first! :)

== Upgrade Notice ==

Simply replace `media-file-renamer.php` by the new one.

== Frequently Asked Questions ==

Check the FAQ on the official website, here: http://apps.meow.fr/media-file-renamer/faq/.

If you are a developer and willing to customize the way the file are renamed, please use the mfrh_new_filename filter. The $new is the new filename proposed by the plugin, $old is the previous one and $post contains the information about the current attachment.

`
add_filter( 'mfrh_new_filename', 'my_filter_filename', 10, 3 );

function filter_filename( $new, $old, $post ) {
  return "renamed-" . $new;
}
`

You are welcome to create plugins using Media File Renamer using special rules for renaming. Please tell me you so if you make one and I will list those plugins here.

== Screenshots ==

1. Type in the name of your media, that is all.
2. Special screen for bulk actions.
3. Has to be renamed.

== Changelog ==

= 2.4.2 =
* Fix: There was a glitch when .jpeg extension were used. Now keep them as .jpeg.

= 2.4.0 =
* Fix: There was a possibility that the image sizes filenames could be overwritten wrongly.

= 2.3.8 =
* Update: Rename the GUID (File Name) is now the default. Too many people think it is a bug while it is not.

= 2.3.6 =
* Add: UTF-8 support for renaming files. Before playing with this, give it a try. Windows-based hosting service will probably not work well with this.
* Info: I would be also really happy if you could review the plugin (https://wordpress.org/support/view/plugin-reviews/media-file-renamer), share your current issues with me and also the features you would like the most. Thanks a lot! :)

= 2.3.4 =
* Fix: Auto-Rename was renaming files even though it was disabled.
* Update: If Auto-Rename is disabled, the Media Library column is not shown anymore, neither is the dashboard (they are useless in that case).

= 2.3.2 =
* Add: Metadata containing '%20' instead of spaces are now considered too during the renaming.

= 2.3.0 =
* Add: Update the metadata (true by default).

= 2.2.8 =
* Fix: Guid was renamed wrongly in one rare case.

= 2.2.6 =
* Fix: Double extension issue with manual renaming.

= 2.2.4 =
* Fix: Couldn't rename automatically the files without changing the titles, now the feature is back.
* Fix: Better 'explanations' before renaming.
* Fix: Should work with WPML Media now.
* Fix: Manage empty filenames by naming them 'empty'.

= 2.2.2 =
* Add: Option to automatically sync the alternative text with the title.
* Add: Filters and Actions to allow plugins (or custom code) to customize the renaming.
* Fix: Avoid to rename file if title is not changed (annoying if you previously manually updated it).
* Change: Plugin functions are only loaded if the user is using the admin.

= 2.2.0 =
* Add: Many new options.
* Add: Pro version.
* Add: Manual file rename (Pro).
* Update: Use actions for renaming (to faciliate support for more renaming features).

= 2.0.0 =
* Fix: Texts.

= 1.9.8 =
* Fix: Versioning.

= 1.9.4 =
* Add: New option to avoid to modify database (no updates, only renaming).
* Add: New option to force update the GUID (aka "File name"...). Not recommended _at all_.
* Fix: Options were without effect.

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
