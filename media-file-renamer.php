<?php
/*
Plugin Name: Media File Renamer
Plugin URI: http://www.meow.fr/media-file-renamer
Description: Renames media files based on their titles and updates the associated posts links.
Version: 0.9.4
Author: Jordy Meow
Author URI: http://www.meow.fr
Remarks: John Godley originaly developed rename-media (http://urbangiraffe.com/plugins/rename-media/), but it wasn't working on Windows, had issues with apostrophes, and was not updating the links in the posts. That's why Media File Renamer exists.

Dual licensed under the MIT and GPL licenses:
http://www.opensource.org/licenses/mit-license.php
http://www.gnu.org/licenses/gpl.html

Originally developed for two of my websites: 
- Totoro Times (http://www.totorotimes.com) 
- Haikyo (http://www.haikyo.org)
*/


add_action( 'media_row_actions', 'mfrh_media_row_actions', 10, 2 );
add_action( 'admin_head', 'mfrh_admin_head' );
add_action( 'admin_menu', 'mfrh_admin_menu' );
add_action( 'wp_ajax_mfrh_rename_media', 'mfrh_wp_ajax_mfrh_rename_media' );
add_filter( 'views_upload', 'mfrh_views_upload' );
add_action( 'pre_get_posts', 'mfrh_pre_get_posts' );
add_filter( 'media_send_to_editor', 'mfrh_media_send_to_editor', 20, 3 );
add_action( 'admin_notices', 'mfrh_admin_notices' );

// Column for Media Library
add_filter( 'manage_media_columns', 'mfrh_add_media_columns');
add_action( 'manage_media_custom_column', 'mfrh_manage_media_custom_column', 10, 2 );

// Attachment is saved (can be automatic, when the user switch between fields)
add_action( 'edit_attachment', 'mfrh_edit_attachment' );
add_action( 'add_attachment', 'mfrh_edit_attachment' );

// Media form is submitted
add_filter( 'attachment_fields_to_save', 'mfrh_rename_media', 1, 2 );

require( 'jordy_meow_footer.php' );
require( 'mfrh_settings.php' );

/**
 *
 * ERROR/INFO MESSAGE HANDLING
 *
 */

function mfrh_admin_notices() {
	//The class "updated" will display the message with a yellow background.
	//The class "error" will display the message with a red background.

	global $pagenow;

	if (is_admin()) {
		$screen = get_current_screen();
		if ( $screen->base == 'post' && $screen->post_type == 'attachment' ) {
			if ( mfrh_check_attachment( $_GET['post'], $output ) ) {

				if ($output['desired_filename_exists']) {
					echo '<div class="error">
	       				<p>
	       					The file ' . $output['desired_filename'] . ' already exists. Please give
	       					a new title for this media.
	       				</p>
	    			</div>';
				}
			}
    	}
	}
}

/**
 *
 * 'RENAME' LINK
 *
 */

function mfrh_pre_get_posts ( $query ) {
	if ( !empty( $_GET['renameflag'] ) && $_GET['renameflag'] == true ) {
		$query->query_vars['meta_key'] = '_require_file_renaming';
		$query->query_vars['meta_value'] = true;
	}
	return $query;
}
 
function mfrh_views_upload( $views ) {
	mfrh_file_counter( $flagged, $total );
	$views['mfrh_flagged'] = sprintf("<a href='upload.php?renameflag=1'>%s</a> (%d)", __("To be renamed", 'media-file-renamer'), $flagged);
    return $views;
}

function mfrh_add_media_columns($columns) {
    $columns['mfrh_column'] = 'File Renamer';
    return $columns;
}

function mfrh_manage_media_custom_column( $column_name, $id ) {
    if ( $column_name == 'mfrh_column' ) {
		if ( mfrh_check_attachment( $id, $output ) ) {
			if ( $output['desired_filename_exists'] )
				echo "<b>Another media with the same title exists. This media's title needs to be <span style='color: red;'>modified</span>.</b><br />";
			else
				echo "<b>This media</a> is ready to be renamed. <a href='?renameflag=1&mfrh_rename=" . $id . "'>Rename now.</b><br />";
			echo "<br />";
			echo "Current file: " . $output['current_filename'] . "<br />";
			echo "Desired file: " . $output['desired_filename'] . "<br />";
		} else {
			echo "<img style='margin-top: -2px; margin-bottom: 2px; width: 16px; height: 16px;' src='" . trailingslashit( WP_PLUGIN_URL ) . trailingslashit( 'media-file-renamer/img') . "tick-circle.png' />";
		}
    }
}

function mfrh_media_row_actions( $actions, $post ) {
	$require_file_renaming = get_post_meta( $post->ID, '_require_file_renaming', true );
	if ( $require_file_renaming ) {
		if ( mfrh_check_attachment( $post->ID, $output ) && !$output['desired_filename_exists'] ) {
			$newaction['mfrh_rename_file'] = '<a href="?mfrh_rename=' . $post->ID . '" title="Rename" rel="permalink">' . __( "Rename", 'media-file-renamer' ) . '</a>';
			return array_merge( $actions, $newaction );
		}
	}
	return $actions;
}
 
function mfrh_admin_head() {
	if ( ! empty( $_GET['mfrh_rename'] ) ) {
		$mfrh_rename = $_GET['mfrh_rename'];
		mfrh_rename_media( get_post( $mfrh_rename, ARRAY_A ), null );
		$_SERVER['REQUEST_URI'] = remove_query_arg(array('mfrh_rename'), $_SERVER['REQUEST_URI']);
	}
	
	?>
	<script type="text/javascript" >
	
		var current;
		var ids = [];
	
		function mfrh_process_next () {
			var data = { action: 'mfrh_rename_media', subaction: 'renameMediaId', id: ids[current - 1] };
			jQuery('#mfrh_progression').text(current + "/" + ids.length);
			jQuery.post(ajaxurl, data, function (response) {
				if (++current <= ids.length)
					mfrh_process_next();
				else
					jQuery('#mfrh_progression').text("<?php echo __( "Done.", 'media-file-renamer' ); ?>");
					jQuery('.mfrh-flagged').text("0");
					jQuery('.mfrh-flagged-icon').html("");
			});
		}
	
		function mfrh_rename_media (all) {
			current = 1;
			ids = [];
			var data = { action: 'mfrh_rename_media', subaction: 'getMediaIds', all: all ? '1' : '0' };
			jQuery('#mfrh_progression').text("<?php echo __( "Please wait...", 'media-file-renamer' ); ?>");
			jQuery.post(ajaxurl, data, function (response) {
				reply = jQuery.parseJSON(response);
				ids = reply.ids;
				jQuery('#mfrh_progression').html(current + "/" + ids.length);
				mfrh_process_next();
			});
		}
	</script>
	<?php
}

/**
 *
 * BULK MEDIA RENAME PAGE
 *
 */

 function mfrh_wp_ajax_mfrh_rename_media() {
	$subaction = $_POST['subaction'];
	
	if ($subaction == 'getMediaIds') {
		$all = intval( $_POST['all'] );
		$ids = array();
		$total = 0;
		global $wpdb;
		$postids = $wpdb->get_col( "SELECT p.ID FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'" );
		foreach ( $postids as $id ) {
			if ($all)
				array_push( $ids, $id );
			else if ( get_post_meta( $id, '_require_file_renaming', true ) )
				array_push( $ids, $id );
			$total++;
		}
		$reply = array();
		$reply['ids'] = $ids;
		$reply['total'] = $total;
		echo json_encode( $reply );
		die;
	}
	else if ($subaction == 'renameMediaId') {
		$id = intval( $_POST['id'] );
		mfrh_rename_media( get_post( $id, ARRAY_A ), null );
		echo 1;
		die();
	}
	
    echo 0;
	die();
}
 
function mfrh_admin_menu() {
	mfrh_file_counter( $flagged, $total );
	$warning_count = $flagged;
	$warning_title = "Flagged to be renamed";
	$menu_label = sprintf( __( 'File Renamer %s' ), "<span class='update-plugins count-$flagged mfrh-flagged-icon' title='$warning_title'><span class='update-count'>" . number_format_i18n( $flagged ) . "</span></span>" );
	add_media_page( 'Media File Renamer', $menu_label, 'manage_options', 'rename_media_files', 'mfrh_rename_media_files' ); 
	add_options_page( 'Media File Renamer', 'File Renamer', 'manage_options', 'mfrh_settings', 'mfrh_settings_page' );
}

function mfrh_file_counter( &$flagged, &$total, $force = false ) {
	global $wpdb;
	$postids = $wpdb->get_col( "SELECT p.ID FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'" );
	static $calculated = false;
	static $sflagged = 0;
	static $stotal = 0;
	if ( !$calculated || $force ) {
		$stotal = 0;
		$sflagged = 0;
		foreach ( $postids as $id ) {
			$require_file_renaming = get_post_meta( $id, '_require_file_renaming', true );
			$stotal++;
			if ( $require_file_renaming )
				$sflagged++;
		}
	}
	$calculated = true;
	$flagged = $sflagged;
	$total = $stotal;
}


// Return false if everything is fine, otherwise return true with an output.
function mfrh_check_attachment( $id, &$output ) {
	$post = get_post( $id, ARRAY_A );
	$sanitized_media_title = sanitize_title( $post['post_title'] );
	$old_filepath = get_attached_file( $post['ID'] );
	$path_parts = pathinfo( $old_filepath );
	
	// Filename is equal to sanitized title
	if ( $sanitized_media_title == $path_parts['filename'] ) {
		return false;
	}

	$directory = $path_parts['dirname']; // '2011/01'
	$ext = str_replace( 'jpeg', 'jpg', $path_parts['extension'] );;
	$desired_filename = $sanitized_media_title . '.' . $ext;

	// Send info to the requester function
	$output['post_title'] = $post['post_title'];
	$output['current_filename'] = $path_parts['filename'] . "." . $path_parts['extension'];
	$output['desired_filename'] = $desired_filename;
	$output['desired_filename_exists'] = false;
	if ( file_exists( $directory . "/" . $desired_filename ) ) {
		$output['desired_filename_exists'] = true;
		if ( strtolower( $output['current_filename'] ) == strtolower( $output['desired_filename'] ) ) {
			// If Windows, let's be careful about the fact that case doesn't affect files
			delete_post_meta( $post['ID'], '_require_file_renaming' );
			return false;
		}
	}

	if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) )
		add_post_meta( $post['ID'], '_require_file_renaming', true );

	return true;
}

function mfrh_check_text() {
	global $wpdb;
	$ids = $wpdb->get_col( "
		SELECT p.ID
		FROM $wpdb->posts p
		WHERE post_status = 'inherit'
		AND post_type = 'attachment'
	" );
	echo "<div style='font-size: 11px; margin-top: 15px;'>";
	foreach ( $ids as $id ) {
		if (mfrh_check_attachment( $id, $output )) {
			echo "post_title: " . $output['post_title'] . "<br />";
			echo "current_filename: " . $output['current_filename'] . "<br />";
			echo "desired_filename: " . $output['desired_filename'] . "<br />";
			if ( $output['desired_filename_exists'] )
				echo "<b><a href='post.php?post=" . $id . "&action=edit'>This media</a>'s title needs to be <span style='color: red;'>modified</span>.</b><br />";
			else
				echo "<b><a href='post.php?post=" . $id . "&action=edit'>This media</a> is ready to be renamed.</b><br />";
			echo "<br />";
		}
	}
	echo "Scanning done.";
	echo "</div>";
}

function mfrh_rename_media_files() {
	?>
	<div class='wrap'>
	<div id="icon-upload" class="icon32"><br></div>
	<h2>Media File Renamer &#8226; Dashboard</h2>
	
	<?php
	if ( isset( $_GET ) && isset( $_GET['check'] ) )
		mfrh_check_text();
	
	mfrh_file_counter( $flagged, $total, true );
	?>
	<p>
		<b>There are <span class='mfrh-flagged' style='color: red;'><?php _e( $flagged ); ?></span> media files flagged for renaming out of <?php _e( $total ); ?> in total.</b> Those are the files that couldn't be renamed on the fly when their names were updated. You can now rename those flagged media, or rename all of them (which should actually done when you install the plugin for the first time). <span style='color: red;'>Please backup your WordPress upload folder and database before using these functions.</span>
	</p>
	<? if ($flagged > 0): ?>
		<a onclick='mfrh_rename_media(false)' id='mfrh_rename_dued_images' class='button-primary'>
			<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
		</a>
	<?php else: ?>
		<a id='mfrh_rename_dued_images' class='button-primary'>
			<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
		</a>
	<?php endif; ?>
	
	<a onclick='mfrh_rename_media(true)' id='mfrh_rename_all_images' class='button-primary' 
		style='margin-left: 10px; margin-right: 10px'>
		<?php echo sprintf( __( "Rename all %d media", 'media-file-renamer' ), $total ); ?>
	</a>
	<span id='mfrh_progression'></span>
	<a id='mfrh_rename_all_images' class='button-secondary' href="?page=rename_media_files&check"
		style='float:right; margin-left: 10px; margin-right: 10px'>
		<?php echo sprintf( __( "Check Test", 'media-file-renamer' ), $total ); ?>
	</a>
	</div>
	<?php
	jordy_meow_footer();
}

/**
 *
 * EDITOR
 *
 */

function mfrh_edit_attachment( $post_ID ) {
	mfrh_check_attachment( $post_ID, $output );
}
 
function mfrh_media_send_to_editor( $html, $attachment_id, $attachment ) {
	mfrh_check_attachment( $attachment_id, $output );
	return $html;
}

/**
 *
 * THE FUNCTION THAT MAKES COFFEE, BROWNIES AND GIVE MASSAGES ALL AT THE SAME TIME WITH NO COMPLAIN
 * Rename Files + Update Posts
 *
 */

function mfrh_rename_media( $post, $attachment ) {

	// NEW MEDIA FILE INFO (depending on the title of the media)
	$sanitized_media_title = sanitize_title( $post['post_title'] );
	
	// MEDIA TITLE & FILE PARTS
	$meta = wp_get_attachment_metadata( $post['ID'] );
	$old_filepath = get_attached_file( $post['ID'] ); // '2011/01/whatever.jpeg'
	$path_parts = pathinfo( $old_filepath );

	// Don't do anything if the media title didn't change or if it would turn to an empty string
	if ( $path_parts['filename'] == $sanitized_media_title ) {
		// This media DOES NOT require renaming
		delete_post_meta( $post['ID'], '_require_file_renaming' );
		return $post; 
	}
	
	// PREVIOUS MEDIA FILE INFO
	$directory = $path_parts['dirname']; // '2011/01'
	$old_filename = $path_parts['basename']; // 'whatever.jpeg'
	$ext = str_replace( 'jpeg', 'jpg', $path_parts['extension'] ); // In case of a jpeg extension, rename it to jpg
	
	// MEDIA LIBRARY USAGE DETECTION
	// Detects if the user is using the Media Library or 'Add an Image' (while a post edit)
	// If it is not the Media Library, we don't rename, to avoid issues
	$media_library_mode = !isset($attachment['image-size']);
	if ( !$media_library_mode ) {
		// This media requires renaming
		if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) )
			add_post_meta( $post['ID'], '_require_file_renaming', true );
		return $post;
	}
	
	$new_filename = $sanitized_media_title . '.' . $ext;

	// NEW DESTINATION FILES ALREADY EXISTS - WE DON'T DO NOTHING
	if ( file_exists( $directory . "/" . $new_filename ) ) {
		if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) )
			add_post_meta( $post['ID'], '_require_file_renaming', true );
		return $post;
	}

	// RENAMING
	$new_filepath = trailingslashit( $directory ) . $new_filename ; // '/' should be used EVEN on a Windows based server
	// If the new file already exists, it's a weird case, let's do nothing.
	if ( file_exists( $new_filepath ) === true ) {
		trigger_error( "Media File Renamer wants to rename a file to " + $new_filepath + " but it already exists.", E_USER_NOTICE );
		return $post;
	}
	
	// Filenames without extensions
	$noext_old_filename = str_replace( '.' . $ext, '', $old_filename );
	$noext_new_filename = str_replace( '.' . $ext, '', $new_filename );
	$post['old_filename'] = $noext_old_filename;
	$post['new_filename'] = $noext_new_filename;
	
	// Exact same code as rename-media, it's a good idea to keep track of the original filename.
	$original_filename = get_post_meta( $post['ID'], '_original_filename', true );
	if ( empty( $original_filename ) )
		add_post_meta( $post['ID'], '_original_filename', $old_filename );

	// Rename the main media file.
	if ( !rename( $old_filepath, $new_filepath ) ) {
		trigger_error( "Media File Renamer could not find the file" + $old_filepath + ".", E_USER_ERROR );
		return $post;
	}
	
	// Update the attachment meta
	$meta['file'] = str_replace( $noext_old_filename, $noext_new_filename, $meta['file'] );
	if ( isset( $meta["url"] ) && $meta["url"] != "" )
		$meta["url"] = str_replace( $noext_old_filename, $noext_new_filename, $meta["url"] );
	else
		$meta["url"] = $noext_new_filename . "." . $ext;
	
	// Images
	if ( wp_attachment_is_image( $post['ID'] ) ) {
		// Loop through the different sizes in the case of an image, and rename them.
		$orig_image_urls = array();
		$orig_image_data = wp_get_attachment_image_src( $post['ID'], 'full' );
		$orig_image_urls['full'] = $orig_image_data[0];
		foreach ( $meta['sizes'] as $size => $meta_size ) {
			$meta_old_filename = $meta['sizes'][$size]['file'];
			$meta_old_filepath = trailingslashit( $directory ) . $meta_old_filename;
			$meta_new_filename = str_replace( $noext_old_filename, $noext_new_filename, $meta_old_filename );
			$meta_new_filepath = trailingslashit( $directory ) . $meta_new_filename;
			$orig_image_data = wp_get_attachment_image_src( $post['ID'], $size );
			$orig_image_urls[$size] = $orig_image_data[0];
			
			// ak: Double check files exist before trying to rename.
			if ( file_exists( $meta_old_filepath ) && ( (!file_exists( $meta_new_filepath ) ) 
				|| is_writable( $meta_new_filepath ) ) ) {
			
				// WP Retina 2x is detected, let's rename those files as well
				if ( function_exists( 'wr2x_generate_images' ) ) {
					$wr2x_old_filepath = str_replace( '.' . $ext, '@2x.' . $ext, $meta_old_filepath );
					$wr2x_new_filepath = str_replace( '.' . $ext, '@2x.' . $ext, $meta_new_filepath );
					if ( file_exists( $wr2x_old_filepath ) && ( (!file_exists( $wr2x_new_filepath ) ) || is_writable( $wr2x_new_filepath ) ) ) {
						rename( $wr2x_old_filepath, $wr2x_new_filepath );
					}
				}
				rename( $meta_old_filepath, $meta_new_filepath );
				$meta['sizes'][$size]['file'] = $meta_new_filename;
			}
		}
	} else {
		$orig_attachment_url = wp_get_attachment_url( $post['ID'] );
	}
	
	// This media DOES NOT require renaming anymore
	delete_post_meta( $post['ID'], '_require_file_renaming' );
	
	// Update metadata
	wp_update_attachment_metadata( $post['ID'], $meta );
	update_attached_file( $post['ID'], $new_filepath );
	
	// Slug update
	if ( mfrh_getoption( "rename_slug", "mfrh_basics", 'media-file-renamer' ) === 'on' )
		$post['post_name'] = $sanitized_media_title;
	
	//[TigrouMeow] The GUID should be updated, let's use the post id and the sanitized title.
	//[alx359] That's not true for post_type=attachments|post_mime_type=image/*. The expected GUID here is [url]
	//$post['guid'] = $sanitized_media_title . " [" . $post['ID'] . "]";
	$post['guid'] = $meta["url"];
	wp_update_post( $post );
	if ( !empty( $article ) ) {
		wp_update_post( $article );
	}
	
	// Mass update of all the articles with the new filenames
	// For images, we have to go through all the sizes
	global $wpdb;
	if ( wp_attachment_is_image( $post['ID'] ) ) {
		$orig_image_url = $orig_image_urls['full'];
		$new_image_data = wp_get_attachment_image_src( $post['ID'], 'full' );
		$new_image_url = $new_image_data[0];
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, '%s', '%s');", $orig_image_url, $new_image_url ) );
		foreach ( $meta['sizes'] as $size => $meta_size ) {
			$orig_image_url = $orig_image_urls[$size];
			$new_image_data = wp_get_attachment_image_src( $post['ID'], $size );
			$new_image_url = $new_image_data[0];
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, '%s', '%s');", $orig_image_url, $new_image_url ) );
		}
	} else {
		$new_attachment_url = wp_get_attachment_url( $post['ID'] );
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, '%s', '%s');", $orig_attachment_url, $new_attachment_url ) );
	}
	
	// HTTP REFERER set to the new media link
	if ( isset( $_REQUEST['_wp_original_http_referer'] ) && strpos( $_REQUEST['_wp_original_http_referer'], '/wp-admin/' ) === false ) {
		$_REQUEST['_wp_original_http_referer'] = get_permalink( $post['ID'] );
	}

	return $post;
}
