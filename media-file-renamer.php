<?php
/*
Plugin Name: Media File Renamer
Plugin URI: http://www.meow.fr/media-file-renamer
Description: Renames media files based on their titles and updates the associated posts links.
Version: 0.34
Author: Jordy Theiller
Author URI: http://www.meow.fr
Remarks: John Godley originaly developed rename-media (http://urbangiraffe.com/plugins/rename-media/), but it wasn't working on Windows, had issues with apostrophes, and was not updating the links in the posts. That's why Media File Renamer exists.
*/

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

function mfrh_attachment_fields_to_save( $post, $attachment ) {
	
	// NEW MEDIA FILE INFO (depending on the title of the media)
	$sanitized_media_title = sanitize_title( $attachment['post_title'] );
	
	// MEDIA TITLE
	if ( $post['post_name'] == $sanitized_media_title || empty( $sanitized_media_title ) ) {
		// Don't do anything if the media title didn't change or if it would turn to an empty string
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
	$media_library_mode = !isset($attachment['image-size']);
	
	$new_filename = strtolower( wp_unique_filename( $directory, $sanitized_media_title . '.' . $ext ) );
	$new_filepath = $directory . '/' . $new_filename ; // '/' should be used EVEN on a Windows based server
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
	rename( $old_filepath, $new_filepath );
	
	// Update the attachment meta
	$meta = wp_get_attachment_metadata( $post['ID'] );
	$meta['file'] = str_replace( $noext_old_filename, $noext_new_filename, $meta['file'] );
	$meta["url"] = str_replace( $noext_old_filename, $noext_new_filename, $attachment['url'] );

	// Get the article to which belongs this media
	$article = "";
	if ( $media_library_mode && !empty($post['post_parent']) ) {
		$article = get_post( $post['post_parent'] );
		$article->post_content = str_replace( $old_filename, $new_filename, $article->post_content );
	}
	
	// Loop through the different sizes in the case of an image, and rename them.
	// Also change the article links if there are any
	foreach ( $meta['sizes'] as $size => $meta_size ) {
		$meta_old_filename = $meta['sizes'][$size]['file'];
		$meta_old_filepath = $directory . '/' . $meta_old_filename;
		$meta_new_filename = str_replace( $noext_old_filename, $noext_new_filename, $meta_old_filename );
		$meta_new_filepath = $directory . '/' . $meta_new_filename;
		
		// ak: Double check files exist before trying to rename.
		if ( file_exists($meta_old_filepath) && ((!file_exists($meta_new_filepath)) || is_writable($meta_new_filepath)) ) {
		   rename($meta_old_filepath, $meta_new_filepath);
		   $meta['sizes'][$size]['file'] = $meta_new_filename;
		}
		
		if ( $media_library_mode && !empty( $article ) ) {
			$article->post_content = str_replace( $meta_old_filename, $meta_new_filename, $article->post_content );
		}
		
	}
	
	wp_update_attachment_metadata( $post['ID'], $meta );
	update_attached_file( $post['ID'], $new_filepath );

	// Posts should be updated.
	$post['post_name'] = $sanitized_media_title;
	//[TigrouMeow] The GUID should be updated, let's use the post id and the sanitized title.
	//[alx359] That's not true for post_type=attachments|post_mime_type=image/*. The expected GUID here is [url]
	//$post['guid'] = $sanitized_media_title . " [" . $post['ID'] . "]";
	$post['guid'] = $meta["url"];
	wp_update_post( $post );
	if ( $media_library_mode && !empty( $article ) ) {
		wp_update_post( $article );
	}
	
	// HTTP REFERER set to the new media link
	if ( isset( $_REQUEST['_wp_original_http_referer'] ) && strpos( $_REQUEST['_wp_original_http_referer'], '/wp-admin/' ) === false ) {
		$_REQUEST['_wp_original_http_referer'] = get_permalink( $post['ID'] );
	}
	
	return $post;
}

add_filter( 'attachment_fields_to_save', 'mfrh_attachment_fields_to_save', 1, 2 );
add_filter( 'media_send_to_editor', 'mfrh_media_send_to_editor', 20, 3 );
