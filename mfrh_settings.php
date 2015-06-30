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
    jordy_meow_donation(true);
	echo "<div id='icon-options-general' class='icon32'><br></div><h2>Media File Renamer";
    by_jordy_meow();
    echo "</h2>";
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

function mfrh_setoption( $option, $section, $value ) {
    $options = get_option( $section );
    if ( empty( $options ) ) {
        $options = array();
    }
    $options[$option] = $value;
    update_option( $section, $options );
}

function mfrh_admin_init() {
	require( 'mfrh_class.settings-api.php' );
    
    if ( isset( $_GET['reset'] ) ) {
        if ( file_exists( plugin_dir_path( __FILE__ ) . '/media-file-renamer.log' ) ) {
            unlink( plugin_dir_path( __FILE__ ) . '/media-file-renamer.log' );
        }
        if ( file_exists( plugin_dir_path( __FILE__ ) . '/mfrh_sql.log' ) ) {
            unlink( plugin_dir_path( __FILE__ ) . '/mfrh_sql.log' );
        }
        if ( file_exists( plugin_dir_path( __FILE__ ) . '/mfrh_sql_revert.log' ) ) {
            unlink( plugin_dir_path( __FILE__ ) . '/mfrh_sql_revert.log' );
        }
    }

    // Default Auto-Generate
    $auto_rename = mfrh_getoption( 'auto_rename', 'mfrh_basics', null );
    if ( $auto_rename === null )
        mfrh_setoption( 'auto_rename', 'mfrh_basics', 'on' );

    // Default Rename Slug
    $rename_slug = mfrh_getoption( 'rename_slug', 'mfrh_basics', null );
    if ( $rename_slug === null )
        mfrh_setoption( 'rename_slug', 'mfrh_basics', 'on' );
    
    // Default Rename Slug
    $update_posts = mfrh_getoption( 'update_posts', 'mfrh_basics', null );
    if ( $update_posts === null )
        mfrh_setoption( 'update_posts', 'mfrh_basics', 'on' );

    if ( isset( $_POST ) && isset( $_POST['mfrh_pro'] ) )
        mfrh_validate_pro( $_POST['mfrh_pro']['subscr_id'] );
    $pro_status = get_option( 'mfrh_pro_status', "Not Pro." );

	$sections = array(
        array(
            'id' => 'mfrh_basics',
            'title' => __( 'Basics', 'media-file-renamer' )
        ),
        array(
            'id' => 'mfrh_pro',
            'title' => __( 'Pro', 'media-file-renamer' )
        )
    );
	$fields = array(
        'mfrh_basics' => array(
            array(
                'name' => 'auto_rename',
                'label' => __( 'Auto Rename', 'media-file-renamer' ),
                'desc' => __( 'The files will be renamed automatically depending on the title.<br /><small>If the plugin considers that it is dangerous to rename the file directly at this point, it will flag it as "to be renamed". The list of the files which has to be renamed can be found in Media > File Renamer.</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => true
            ), array(
                'name' => 'manual_rename',
                'label' => __( 'Manual Rename (Pro)', 'media-file-renamer' ),
                'desc' => __( 'You can rename manually the files using the Media edit screen.<br /><small>This feature is only for Pro users (check the Pro tab).</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            ), array(
                'name' => 'side_updates',
                'label' => '',
                'desc' => __( '<h2>Side-Updates</h2><small>When the files are renamed, many links to them on your WordPress might be broken. By default, the plugin updates all the references in the posts. As the plugin evolves (thanks to the Pro version), more and more plugins/themes will be covered by those updates as we discover them together.</small>', 'wp-retina-2x' ),
                'type' => 'html'
            ), array(
                'name' => 'update_posts',
                'label' => __( 'Update Posts', 'media-file-renamer' ),
                'desc' => __( 'Update the references to the renamed files in the posts (pages and custom types included).', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            ), array(
                'name' => 'update_something',
                'label' => __( 'Update XYZ', 'media-file-renamer' ),
                'desc' => __( '<i>Something is not updated when you rename a file? Please contact me and I will add support for it.</i>', 'media-file-renamer' ),
                'type' => 'html',
                'default' => false
            ), array(
                'name' => 'rename_slug',
                'label' => __( 'Rename Slug', 'media-file-renamer' ),
                'desc' => __( 'The image slug will be renamed like the new filename.<br /><small>Better to keep this un-checked as the link might have been referenced somewhere else.</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => true
            ), array(
                'name' => 'rename_guid',
                'label' => __( 'Rename GUID<br /><small>(aka "File name")</small>', 'media-file-renamer' ),
                'desc' => __( 'The GUID will be renamed like the new filename.<br /><small>Better to keep this un-checked. Have a look a the FAQ.</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            ), array(
                'name' => 'advanced',
                'label' => '',
                'desc' => __( '<h2>Advanced</h2><small>If you are geeky this section might be more interesting for you. <b>Want to clear/reset the logs? Click <a href="?page=mfrh_settings&reset=true">here</a>.</b></small>', 'wp-retina-2x' ),
                'type' => 'html'
            ), array(
                'name' => 'rename_on_save',
                'label' => __( 'Rename On Save', 'media-file-renamer' ),
                'desc' => __( 'Attachments will be renamed automatically when published posts/pages are saved.<br /><small>You can change the names of your media while editing a post but that wouldn\'t let the plugin updates the HTML, of course. With this option, the plugin will check for any changes in the media names and will update your post right after you saved it.</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            ), array(
                'name' => 'log',
                'label' => __( 'Logs', 'media-file-renamer' ),
                'desc' => __( 'Simple logging that explains which actions has been run. The file is <a target="_blank" href="' . plugins_url("media-file-renamer") . '/media-file-renamer.log">media-file-renamer.log</a>.', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            ), array(
                'name' => 'logsql',
                'label' => __( 'SQL Logs (Pro)<br />+ Revert SQL', 'media-file-renamer' ),
                'desc' => __( 'The files <a target="_blank" href="' . plugins_url("media-file-renamer") . '/mfrh_sql.log">mfrh_sql.log</a> and <a target="_blank" href="' . plugins_url("media-file-renamer") . '/mfrh_sql_revert.log">mfrh_sql_revert.log</a> will be created and they will include the raw SQL queries which were run by the plugin. If there is an issue, the revert file can help you reverting the changes more easily. <br /><small>This feature is only for Pro users (check the Pro tab).</small>', 'media-file-renamer' ),
                'type' => 'checkbox',
                'default' => false
            )
        ),
        'mfrh_pro' => array(
            array(
                'name' => 'pro',
                'label' => '',
                'desc' => __( sprintf( 'Status: %s', $pro_status ), 'media-file-renamer' ),
                'type' => 'html'
            ),
            array(
                'name' => 'subscr_id',
                'label' => __( 'Serial', 'media-file-renamer' ),
                'desc' => __( '<br />Enter your serial or subscription ID here. If you don\'t have one yet, get one <a target="_blank" href="http://apps.meow.fr/media-file-renamer/">right here</a>.', 'media-file-renamer' ),
                'type' => 'text',
                'default' => ""
            ),
        )
    );
    global $mfrh_settings_api;
	$mfrh_settings_api = new WeDevs_Settings_API;
    $mfrh_settings_api->set_sections( $sections );
    $mfrh_settings_api->set_fields( $fields );
    $mfrh_settings_api->admin_init();
}

?>
