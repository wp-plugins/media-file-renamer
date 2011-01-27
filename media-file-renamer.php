<?php
/*
Plugin Name: Media File Renamer
Plugin URI: http://www.meow.fr/media-file-renamer
Description: Rename media files based on their titles and updates the associated posts links.
Version: 0.1
Author: Jordy Theiller
Author URI: http://www.meow.fr
Remarks: John Godley originaly developed rename-media (http://urbangiraffe.com/plugins/rename-media/), but it wasn't working on Windows, had issues with apostrophes, and was not updating the links in the posts. That's why Media File Renamer exists.
*/

function media_file_renamer_handler( $post, $attachment ) {
	
	// MEDIA TITLE
	if ( $post['post_name'] == $post['post_title'] )
		return; // don't do anything if the media title didn't change

	// PREVIOUS MEDIA FILE INFO
	$old_filepath = get_attached_file( $post['ID'] ); // '2011/01/whatever.jpeg'
	$path_parts = pathinfo( $old_filepath );
	$directory = $path_parts['dirname']; // '2011/01'
	$old_filename = $path_parts['basename']; // 'whatever.jpeg'
	$ext = str_replace( 'jpeg', 'jpg', $path_parts['extension'] ); // In case of a jpeg extension, rename it to jpg
	
	// NEW MEDIA FILE INFO (depending on the title of the media)
	$sanitized_media_title = sanitize_title( $attachment['post_title'] );
	
	// The new filename would be... empty! Let's do nothing.
	if ( empty( $sanitized_media_title ) ) {
		return $post;
	}
	
	$new_filename = strtolower( wp_unique_filename( $directory, $sanitized_media_title . '.' . $ext ) );
	$new_filepath = $directory . '/' . $new_filename ; // '/' should be used EVEN on a Windows based server
	// If the new file already exists, it's a weird case, let's do nothing.
	if ( file_exists( $new_filepath ) === true ) {
		trigger_error( "Media File Renamer wants to rename a file to " + $new_filepath + " but it already exists.", E_USER_NOTICE );
		return $post;
	}
	
	$meta = wp_get_attachment_metadata( $post['ID'] );
	$meta['file'] = str_replace( $old_filename, $new_filename, $meta['file'] );
	
	// Exact same code as rename-media, it's a good idea to keep track of the original filename.
	$original_filename = get_post_meta( $post['ID'], '_original_filename', true );
	if ( empty( $original_filename ) )
		add_post_meta( $post['ID'], '_original_filename', $old_filename );

	// Rename the main media file.
	rename( $old_filepath, $new_filepath );
	update_attached_file( $post['ID'], $new_filepath );
	
	// Get the article to which belongs this media
	$article = "";
	if ( !empty($post['post_parent']) ) {
		$article = get_post( $post['post_parent'] );
		$article->post_content = str_replace( $old_filename, $new_filename, $article->post_content );
	}
	
	// Loop through the different sizes in the case of an image, and rename them.
	// Also change the article links if there are any
	$noext_old_filename = str_replace( '.' . $ext, '', $old_filename );
	$noext_new_filename = str_replace( '.' . $ext, '', $new_filename );
	foreach ( $meta['sizes'] as $size => $meta_size ) {
		$meta_old_filename = $meta['sizes'][$size]['file'];
		$meta_old_filepath = $directory . '/' . $meta_old_filename;
		$meta_new_filename = str_replace( $noext_old_filename, $noext_new_filename, $meta_old_filename );
		$meta_new_filepath = $directory . '/' . $meta_new_filename;
		
		rename( $meta_old_filepath, $meta_new_filepath );
		$meta['sizes'][$size]['file'] = $meta_new_filename;
		
		if ( !empty( $article ) ) {
			$article->post_content = str_replace( $meta_old_filename, $meta_new_filename, $article->post_content );
		}
		
	}
	wp_update_attachment_metadata( $post['ID'], $meta );

	// Posts should be updated.
	$post['post_name'] = $sanitized_media_title;
	// The GUID should be updated, let's use the post id and the sanitized title.
	$post['guid'] = $sanitized_media_title . " [" . $post['ID'] . "]";
	wp_update_post( $post );
	if ( !empty( $article ) ) {
		wp_update_post( $article );
	}
	
	// HTTP REFERER set to the new media link
	if ( isset( $_REQUEST['_wp_original_http_referer'] ) && strpos( $_REQUEST['_wp_original_http_referer'], '/wp-admin/' ) === false ) {
		$_REQUEST['_wp_original_http_referer'] = get_permalink( $post['ID'] );
	}
	
	return $post;
}

add_filter( 'attachment_fields_to_save', 'media_file_renamer_handler', 10, 2 );