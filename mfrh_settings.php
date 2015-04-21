<?php

add_action( 'admin_init', 'mfrh_admin_init' );

/**
 *
 * SETTINGS PAGE
 *
 */
 
function mfrh_settings_page() {
    global $mfrh_settings_api;
	echo '<div class="wrap">';
    jordy_meow_donation();
	echo "<div id='icon-options-general' class='icon32'><br></div><h2>Media File Renamer</h2>";
    $mfrh_settings_api->show_navigation();
    $mfrh_settings_api->show_forms();
    echo '</div>';
	jordy_meow_footer();
}

function mfrh_getoption( $option, $section, $default = '' ) {
    $options = get_option( $section );
    if ( isset( $options[$option] ) ) {
        if ( $options[$option] == "off" ) {
            return false;
        }
        if ( $options[$option] == "on" ) {
            return true;
        }
        return $options[$option];
    }
    return $default;
}

function mfrh_admin_init() {
	require( 'mfrh_class.settings-api.php' );
	$sections = array(
        array(
            'id' => 'mfrh_basics',
            'title' => __( 'Basics', 'media-file-renamer' )
        )
    );
	$fields = array(
        'mfrh_basics' => array(
            array(
                'name' => 'rename_slug',
                'label' => __( 'Rename Slug', 'media-file-renamer' ),
                'desc' => __( 'The image slug will be renamed like the new filename.<br /><small>Better to keep this un-checked as the link might have been referenced somewhere else.</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => true
            ), array(
                'name' => 'rename_guid',
                'label' => __( 'Rename GUID (File name)', 'media-file-renamer' ),
                'desc' => __( 'The GUID will be renamed like the new filename.<br /><small>Better to keep this un-checked. Have a look a the FAQ.</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            ), array(
                'name' => 'no_update',
                'label' => __( 'No Update', 'media-file-renamer' ),
                'desc' => __( 'Don\'t update anything besides the filename.', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            ), array(
                'name' => 'rename_on_save',
                'label' => __( 'Rename On Save', 'media-file-renamer' ),
                'desc' => __( 'Attachments will be renamed automatically when published posts/pages are saved.<br /><small>You can change the names of your media while editing a post but that wouldn\'t let the plugin updates the HTML, of course. With this option, the plugin will check for any changes in the media names and will update your post right after you saved it.</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            )
        )
    );
    global $mfrh_settings_api;
	$mfrh_settings_api = new WeDevs_Settings_API;
    $mfrh_settings_api->set_sections( $sections );
    $mfrh_settings_api->set_fields( $fields );
    $mfrh_settings_api->admin_init();
}

?>
