<?php
/*
Plugin Name: Media File Renamer
Plugin URI: http://www.meow.fr/media-file-renamer
Description: Renames media files based on their titles and updates the associated posts links.
Version: 1.4.2
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

add_action( 'plugins_loaded', 'mfrh_init' );
add_action( 'admin_head', 'mfrh_admin_head' );
add_action( 'admin_menu', 'mfrh_admin_menu' );
add_action( 'wp_ajax_mfrh_rename_media', 'mfrh_wp_ajax_mfrh_rename_media' );
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

// Rename media on publish
add_action( 'save_post', 'mfrh_save_post' );

require( 'jordy_meow_footer.php' );
require( 'mfrh_settings.php' );

/**
 *
 * INIT
 *
 */

function mfrh_init() {
	if ( is_admin() ) {
		load_plugin_textdomain( 'media-file-renamer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
 *
 * MEDIA LIBRARY FILTER
 *
 */

add_filter( 'views_upload', 'mfrh_views_upload' );
add_action( 'pre_get_posts', 'mfrh_pre_get_posts' );

function mfrh_pre_get_posts ( $query ) {
	if ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) {
		$query->query_vars['meta_key'] = '_require_file_renaming';
		$query->query_vars['meta_value'] = true;
	}
	return $query;
}

function mfrh_views_upload( $views ) {
	mfrh_file_counter( $flagged, $total );
	if ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) {
		if ( isset( $views['all'] ) )
			$views['all'] = str_replace( "current", "", $views['all'] );
		$views['to_rename'] = sprintf("<a class='current' href='upload.php?to_rename=1'>%s</a> (%d)", __("Rename", 'media-file-renamer'), $flagged);
	}
	else {
		$views['to_rename'] = sprintf("<a href='upload.php?to_rename=1'>%s</a> (%d)", __("Rename", 'media-file-renamer'), $flagged);
	}
    return $views;
}

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
		if ( ( $screen->base == 'post' && $screen->post_type == 'attachment' ) || 
			( $screen->base == 'media' && isset( $_GET['attachment_id'] ) ) ) {
			$attachmentId = isset( $_GET['post'] ) ? $_GET['post'] : $_GET['attachment_id'];
			if ( mfrh_check_attachment( $attachmentId, $output ) ) {
				if ( $output['desired_filename_exists'] ) {
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

function mfrh_add_media_columns($columns) {
    $columns['mfrh_column'] = __( 'Rename', 'media-file-renamer' );
    return $columns;
}

function mfrh_manage_media_custom_column( $column_name, $id ) {
    if ( $column_name == 'mfrh_column' ) {
		if ( mfrh_check_attachment( $id, $output ) ) {
			mfrh_generate_explanation( $output );
		} else {
			echo "<a href='media.php?attachment_id=" . $id . "&action=edit''><img style='margin-bottom: 2px; width: 16px; height: 16px;' src='" . trailingslashit( WP_PLUGIN_URL ) . trailingslashit( 'media-file-renamer/img') . "tick-circle.png' /></a>";
		}
    }
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
				if (++current <= ids.length) {
					mfrh_process_next();
				}
				else {
					jQuery('#mfrh_progression').html("<?php echo __( "Done. Please <a href='javascript:history.go(0)'>refresh</a> this page.", 'media-file-renamer' ); ?>");
				}
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
	add_management_page( 'Media File Renamer', __( 'File Renamer', 'media-file-renamer' ), 'manage_options', 'rename_media_files', 'mfrh_rename_media_files' ); 
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


function mfrh_is_header_image ( $id ) {
	static $headers = false;
	if ( $headers == false ) {
		global $wpdb;
		$headers = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_is_custom_header'" );
	}
	return in_array( $id, $headers );
}

function mfrh_sanitize( $name ) {
	return str_replace("%", "-", sanitize_title( $name ));
}

// Return false if everything is fine, otherwise return true with an output.
function mfrh_check_attachment( $id, &$output ) {

	// Skip header images
	if ( mfrh_is_header_image( $id ) ) {
		delete_post_meta( $id, '_require_file_renaming' );
		return false;
	}

	// Get information
	$post = get_post( $id, ARRAY_A );
	$sanitized_media_title = mfrh_sanitize( $post['post_title'] );
	$old_filepath = get_attached_file( $post['ID'] );
	$path_parts = pathinfo( $old_filepath );
	
	// Dead file, let's forget it!
	if ( !file_exists( $old_filepath ) ) {
		delete_post_meta( $id, '_require_file_renaming' );
		return false;
	}

	// Filename is equal to sanitized title
	if ( $sanitized_media_title == $path_parts['filename'] ) {
		delete_post_meta( $id, '_require_file_renaming' );
		return false;
	}

	$directory = $path_parts['dirname']; // '2011/01'
	$ext = str_replace( 'jpeg', 'jpg', $path_parts['extension'] );;
	$desired_filename = $sanitized_media_title . '.' . $ext;

	// Send info to the requester function
	$output['post_id'] = $post['ID'];
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

	// It seems it could be renamed :)
	if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) ) {
		add_post_meta( $post['ID'], '_require_file_renaming', true );
	}
	return true;
}

function mfrh_check_text() {
	$issues = array();
	global $wpdb;
	$ids = $wpdb->get_col( "
		SELECT p.ID
		FROM $wpdb->posts p
		WHERE post_status = 'inherit'
		AND post_type = 'attachment'
	" );
	foreach ( $ids as $id )
		if ( mfrh_check_attachment( $id, $output ) )
			array_push( $issues, $output );
	return $issues;
}

function mfrh_generate_explanation ( $file ) {
	if ( $file['post_title'] == "" ) {
		echo " <a class='button-primary' href='media.php?attachment_id=" . $file['post_id'] . "&action=edit'>" . __( 'Add title', 'media-file-renamer' ) . "</a><p style='margin-top: 5px; font-size: 9px; line-height: 11px;'>" . __( 'No title for this media, please add one.', 'media-file-renamer' ) . "</p>";
	}
	else if ( $file['desired_filename_exists'] ) {
		echo "<a class='button-primary' href='media.php?attachment_id=" . $file['post_id'] . "&action=edit'>" . __( 'Edit title', 'media-file-renamer' ) . "</a><p style='margin-top: 5px; font-size: 9px; line-height: 11px;'>" . __( 'Current title already exists.', 'media-file-renamer' ) . "</p>";
	}
	else {
		$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";
		$mfrh_scancheck = ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) ) ? '&mfrh_scancheck' : '';
		$mfrh_to_rename = ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) ? '&to_rename=1' : '';
		$modify_url = "media.php?attachment_id=" . $file['post_id'] . "&action=edit";
		echo "<a class='button-primary' href='?" . $page . $mfrh_scancheck . $mfrh_to_rename . "&mfrh_rename=" . $file['post_id'] . "'>" . __( 'Rename now', 'media-file-renamer' ) . "</a><p style='margin-top: 5px; font-size: 9px; line-height: 11px;'>" . sprintf( __( 'Will be renamed to %s.<br />Alternatively, you can <a href="%s">modify the title</a>.', 'media-file-renamer' ), $file['desired_filename'], $modify_url ) . "</p>";
	}
}

function mfrh_rename_media_files() {
	?>
	<div class='wrap'>
	<?php jordy_meow_donation(); ?>
	<div id="icon-upload" class="icon32"><br></div>
	<h2>Media File Renamer</h2>
	
	<?php
	$checkFiles = null;
	if ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) )
		$checkFiles = mfrh_check_text();
	
	mfrh_file_counter( $flagged, $total, true );
	?>
	
	<div style='margin-top: 12px; background: #EEE; padding: 5px; border-radius: 4px; height: 24px; box-shadow: 0px 0px 3px #575757;'>
		<?php if ($flagged > 0) { ?>
			<a onclick='mfrh_rename_media(false)' id='mfrh_rename_dued_images' class='button-primary'>
				<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
			</a>
		<?php } else { ?>
			<a id='mfrh_rename_dued_images' class='button-secondary'>
				<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
			</a>
		<?php } ?>
		
		<a onclick='mfrh_rename_media(true)' id='mfrh_rename_all_images' class='button-secondary' 
			style='margin-left: 10px; margin-right: 10px'>
			<?php echo sprintf( __( "Rename all %d media", 'media-file-renamer' ), $total ); ?>
		</a>
		<span id='mfrh_progression'></span>
	</div>

	<p>
		<b>There are <span class='mfrh-flagged' style='color: red;'><?php _e( $flagged ); ?></span> media files flagged for renaming out of <?php _e( $total ); ?> in total.</b> Those are the files that couldn't be renamed on the fly when their names were updated. You can now rename those flagged media, or rename all of them (which should actually done when you install the plugin for the first time). <span style='color: red;'>Please backup your WordPress upload folder and database before using these functions.</span>
	</p>

	<table class='wp-list-table widefat fixed media' style='margin-top: 15px;'>
		<thead>
			<tr><th><?php _e( 'Title', 'media-file-renamer' ); ?></th><th><?php _e( 'Current Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Desired Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Action', 'media-file-renamer' ); ?></th></tr>
		</thead>
		<tfoot>
			<tr><th><?php _e( 'Title', 'media-file-renamer' ); ?></th><th><?php _e( 'Current Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Desired Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Action', 'media-file-renamer' ); ?></th></tr>
		</tfoot>
		<tbody>
			<?php
				if ( $checkFiles != null ) {
					foreach ( $checkFiles as $file ) {
						echo "<tr><td><a href='media.php?attachment_id=" . $file['post_id'] . "&action=edit'>" . ( $file['post_title'] == "" ? "(no title)" : $file['post_title'] ) . "</a></td>"
							. "<td>" . $file['current_filename'] . "</td>"
							. "<td>" . $file['desired_filename'] . "</td>";
						echo "<td>";
						mfrh_generate_explanation( $file );
						echo "</td></tr>";
					}
				}
				else if ( isset( $_GET['mfrh_scancheck'] ) && ( $checkFiles == null || count( $checkFiles ) < 1 ) ) {
					?><tr><td colspan='4'><div style='width: 100%; margin-top: 15px; margin-bottom: 15px; text-align: center;'>
						<div style='margin-top: 15px;'><?php _e( 'There are no issues.<br />Cool! Let\'s go visit <a href=\'http://www.totorotimes.com\'>Totoro Times</a> :)', 'media-file-renamer' ); ?></div>
					</div></td><?php
				}
				else if ( $checkFiles == null ) {
					?><tr><td colspan='4'><div style='width: 100%; text-align: center;'>
						<a class='button-secondary' href="?page=rename_media_files&mfrh_scancheck" style='margin-top: 15px; margin-bottom: 15px; height: 35px; padding: 5px; width: 200px;'>
							<?php echo sprintf( __( "Scan All & List Issues", 'media-file-renamer' ) ); ?>
						</a>
					</div></td><?php
				}
			?>
		</tbody>
	</table>

	

	</div>
	<?php
	jordy_meow_footer();
}


/**
 *
 * RENAME ON SAVE / PUBLISH
 * Originally proposed by Ben Heller
 * Added and modified by Jordy Meow
 */

function mfrh_rename_media_on_publish ( $post_id ) {
	$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' =>'any', 'post_parent' => $post_id ); 
	$attachments = get_posts($args);
	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$attachment = get_post( $attachment, ARRAY_A );
			mfrh_rename_media( $attachment, $attachment, true );
		}
	}
}

function mfrh_save_post( $post_id ) {
	if ( mfrh_getoption( "rename_on_save", "mfrh_basics", 'media-file-renamer' ) != 'on' )
		return;
	$status = get_post_status( $post_id );
	if ( !in_array( $status, array( 'publish', 'future' ) ) )
		return;
	mfrh_rename_media_on_publish( $post_id );
	//add_action( 'rename_media_on_publish', 'mfrh_rename_media_on_publish' );
	//wp_schedule_single_event( time(), 'rename_media_on_publish' );
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

function mfrh_rename_media( $post, $attachment, $disableMediaLibraryMode = false ) {

	if ( $post['post_title'] == "" ) {
		return $post;
	}

	// Skip header images
	if ( mfrh_is_header_image( $post['ID'] ) ) {
		delete_post_meta( $post['ID'], '_require_file_renaming' );
		return $post;
	}

	// NEW MEDIA FILE INFO (depending on the title of the media)
	$sanitized_media_title = mfrh_sanitize( $post['post_title'] );
	
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
	$media_library_mode = !isset( $attachment['image-size'] ) || $disableMediaLibraryMode;
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
	try {
		if ( !file_exists( $old_filepath ) || !rename( $old_filepath, $new_filepath ) ) {
			//trigger_error( "Media File Renamer could not find the file" + $old_filepath + ".", E_USER_ERROR );
			return $post;
		}
	}
	catch (Exception $e) {
		return $post;
	}
	
	// Update the attachment meta
	if ($meta) {
		$meta['file'] = str_replace( $noext_old_filename, $noext_new_filename, $meta['file'] );
		if ( isset( $meta["url"] ) && $meta["url"] != "" && count( $meta["url"] ) > 4 )
			$meta["url"] = str_replace( $noext_old_filename, $noext_new_filename, $meta["url"] );
		else
			$meta["url"] = $noext_new_filename . "." . $ext;
	}

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
	if ( $meta ) {
		wp_update_attachment_metadata( $post['ID'], $meta );
	}
	update_attached_file( $post['ID'], $new_filepath );
	
	// Slug update
	if ( mfrh_getoption( "rename_slug", "mfrh_basics", 'media-file-renamer' ) === 'on' )
		$post['post_name'] = $sanitized_media_title;
	
	//[TigrouMeow] The GUID should be updated, let's use the post id and the sanitized title.
	//[alx359] That's not true for post_type=attachments|post_mime_type=image/*. The expected GUID here is [url]
	//$post['guid'] = $sanitized_media_title . " [" . $post['ID'] . "]";
	if ( $meta ) {
		//$post['guid'] = $meta["url"];
		//[Carrasco] With this little change, the GUID is updated fine.
		$upload_dir = wp_upload_dir();
		$post['guid'] = $upload_dir['url'] . "/" . $meta["url"];
	}
	
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
