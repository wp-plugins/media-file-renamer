<?php
/*
Plugin Name: Media File Renamer
Plugin URI: http://www.meow.fr/media-file-renamer
Description: Renames media files based on their titles and updates the associated posts links.
Version: 0.8
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

add_filter( 'attachment_fields_to_save', 'mfrh_attachment_fields_to_save', 1, 2 );
add_action( 'media_row_actions', 'mfrh_media_row_actions', 10, 2 );
add_action( 'admin_head', 'mfrh_admin_head' );
add_action( 'admin_menu', 'mfrh_admin_menu' );
add_action( 'wp_ajax_mfrh_rename_media', 'mfrh_wp_ajax_mfrh_rename_media' );
add_filter( 'views_upload', 'mfrh_views_upload' );
add_action( 'pre_get_posts', 'mfrh_pre_get_posts' );

// This was used for ON THE FLY RENAME while editing a post and an image at the same time
// Too dangerous :)
//add_filter( 'media_send_to_editor', 'mfrh_media_send_to_editor', 20, 3 );

require( 'jordy_meow_footer.php' );

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
 
 function mfrh_media_row_actions( $actions, $post ) {
	$require_file_renaming = get_post_meta( $post->ID, '_require_file_renaming', true );
	if ( $require_file_renaming ) {
		$newaction['mfrh_rename_file'] = '<a href="?mfrh_rename=' . $post->ID . '" title="File Renamer" rel="permalink">' . __( "File Renamer", 'media-file-renamer' ) . '</a>';
		return array_merge( $actions, $newaction );
	}
	return $actions;
}
 
function mfrh_admin_head() {
	if ( ! empty( $_GET['mfrh_rename'] ) ) {
		$mfrh_rename = $_GET['mfrh_rename'];
		mfrh_attachment_fields_to_save( get_post( $mfrh_rename, ARRAY_A ), null );
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
		$postids = $wpdb->get_col( $wpdb->prepare ( "
			SELECT p.ID
			FROM $wpdb->posts p
			WHERE post_status = 'inherit'
			AND post_type = 'attachment'
		", 0, 0 ) );
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
		mfrh_attachment_fields_to_save( get_post( $id, ARRAY_A ), null );
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
}

function mfrh_file_counter( &$flagged, &$total, $force = false ) {
	global $wpdb;
	$postids = $wpdb->get_col( $wpdb->prepare ( "
		SELECT p.ID
		FROM $wpdb->posts p
		WHERE post_status = 'inherit'
		AND post_type = 'attachment'
	", 0, 0 ) );
	static $calculated = false;
	static $sflagged = 0;
	static $stotal = 0;
	if ( !$calculated || $force ) {
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

function mfrh_check_text() {
	global $wpdb;
	$ids = $wpdb->get_col( $wpdb->prepare ( "
		SELECT p.ID
		FROM $wpdb->posts p
		WHERE post_status = 'inherit'
		AND post_type = 'attachment'
	", 0, 0 ) );
	echo "<div style='font-size: 11px; margin-top: 15px;'>";
	foreach ( $ids as $id ) {
		$post = get_post( $id, ARRAY_A );
		$meta = wp_get_attachment_metadata( $post['ID'] );
		$sanitized_media_title = sanitize_title( $post['post_title'] );
		$old_filepath = get_attached_file( $post['ID'] );
		$path_parts = pathinfo( $old_filepath );
		
		// Don't do anything if the media title didn't change or if it would turn to an empty string
		if ( $post['post_name'] == $sanitized_media_title || 
			empty( $sanitized_media_title ) || ( isset( $meta["sanitized_title"] ) 
			&& $meta["sanitized_title"] === $sanitized_media_title ) ) {
			// This media DOES NOT require renaming
		}
		else {
			if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) )
				add_post_meta( $post['ID'], '_require_file_renaming', true );
			echo "post_title: " . $post['post_title'] . "<br />";
			echo "sanitized_media_title: " . $sanitized_media_title . "<br />";
			echo "post_name: " . $post['post_name'] . "<br />";
			echo "sanitized_title: " . ( isset( $meta['sanitized_title'] ) ? $meta['sanitized_title'] : "-"  ) . "<br />";
			echo "filename: " . $path_parts['filename'] . "<br />";
			echo "<b>File needs to be renamed.</b><br />";
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

 /*
function mfrh_media_send_to_editor($html, $attachment_id, $attachment) {
	$post =& get_post($attachment_id);
	if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
		$url = wp_get_attachment_url($attachment_id);
		$align = !empty($attachment['align']) ? $attachment['align'] : 'none';
		$size = !empty($attachment['image-size']) ? $attachment['image-size'] : 'medium';
		$alt = !empty($attachment['image_alt']) ? $attachment['image_alt'] : '';
		$rel = ( $url == get_attachment_link($attachment_id) );
		return get_image_send_to_editor($attachment_id, $attachment['post_excerpt'], $attachment['post_title'], $align, $url, $rel, $size, $alt);
	}
	return $html;
}
*/

/**
 *
 * RETURN AN UNIQUE FILENAME
 *
 */

// This is a modified copy of 'wp_unique_filename' in functions.php
function mfrh_unique_filename( $dir, $filename ) {
	$number = 2;
	$info = pathinfo($filename);
	$ext = !empty($info['extension']) ? '.' . $info['extension'] : '';
	while ( file_exists( $dir . "/$filename" ) ) {
		if ( $number == 2 )
			$filename = str_replace( $ext, "-" . $number++ . $ext, $filename );
		else {
			$filename = str_replace( "-" . ($number - 1) . $ext, "-" . $number++ . $ext, $filename );
		}
	}
	return $filename;
}

/**
 *
 * THE FUNCTION THAT MAKES COFFEE, BROWNIES AND GIVE MASSAGES ALL AT THE SAME TIME WITH NO COMPLAIN
 * Rename Files + Update Posts
 *
 */

function mfrh_attachment_fields_to_save( $post, $attachment ) {

	// NEW MEDIA FILE INFO (depending on the title of the media)
	$sanitized_media_title = sanitize_title( $post['post_title'] );
	
	// MEDIA TITLE
	// Get attachment meta data
	$meta = wp_get_attachment_metadata( $post['ID'] );

	// Don't do anything if the media title didn't change or if it would turn to an empty string
	if ( $post['post_name'] == $sanitized_media_title || empty( $sanitized_media_title ) || ( isset( $meta["sanitized_title"] ) && $meta["sanitized_title"] == $sanitized_media_title ) ) {
		// This media DOES NOT require renaming
		delete_post_meta( $post['ID'], '_require_file_renaming' );
		return $post; 
	}
	
	// PREVIOUS MEDIA FILE INFO
	$old_filepath = get_attached_file( $post['ID'] ); // '2011/01/whatever.jpeg'
	$path_parts = pathinfo( $old_filepath );
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
	
	// RENAMING
	$new_filename = strtolower( mfrh_unique_filename( $directory, $sanitized_media_title . '.' . $ext ) );
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
	$meta["sanitized_title"] = $sanitized_media_title;
	
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
	
	// Posts should be updated.
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
		echo "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, 
			'$orig_image_url', '$new_image_url');";
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, 
			'$orig_image_url', '$new_image_url');", 0 ) );
		foreach ( $meta['sizes'] as $size => $meta_size ) {
			$orig_image_url = $orig_image_urls[$size];
			$new_image_data = wp_get_attachment_image_src( $post['ID'], $size );
			$new_image_url = $new_image_data[0];
			$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, 
				'$orig_image_url', '$new_image_url');", 0 ) );
		}
	} else {
		$new_attachment_url = wp_get_attachment_url( $post['ID'] );
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_content = REPLACE(post_content, '$orig_attachment_url', '$new_attachment_url');", 0 ) );
	}
	
	// HTTP REFERER set to the new media link
	if ( isset( $_REQUEST['_wp_original_http_referer'] ) && strpos( $_REQUEST['_wp_original_http_referer'], '/wp-admin/' ) === false ) {
		$_REQUEST['_wp_original_http_referer'] = get_permalink( $post['ID'] );
	}

	return $post;
}
