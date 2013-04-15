<?php

add_action( 'admin_init', 'mfrh_admin_init' );

/**
 *
 * SETTINGS PAGE
 *
 */
 
function mfrh_settings_page() {
    $settings_api = mfrh_WeDevs_Settings_API::getInstance();
	echo '<div class="wrap">';
    jordy_meow_donation();
	echo "<div id='icon-options-general' class='icon32'><br></div><h2>Media File Renamer</h2>";
    //settings_errors();
    $settings_api->show_navigation();
    $settings_api->show_forms();
    echo '</div>';
	jordy_meow_footer();
}

function mfrh_getoption( $option, $section, $default = '' ) {
	$options = get_option( $section );
	if ( isset( $options[$option] ) )
		return $options[$option];
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
                'desc' => __( 'The image slug will also be renamed.', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => true
            ), array(
                'name' => 'rename_on_save',
                'label' => __( 'Rename On Save', 'media-file-renamer' ),
                'desc' => __( 'Attachments will be renamed automatically when published posts/pages are saved.', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            )
        )
    );
	$settings_api = mfrh_WeDevs_Settings_API::getInstance();
    $settings_api->set_sections( $sections );
    $settings_api->set_fields( $fields );
    $settings_api->admin_init();
}

?>
