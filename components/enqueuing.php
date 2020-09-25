<?php
/**
 * Shift8 Enqueuing Files
 *
 * Function to load styles and front end scripts
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
}

// Register admin scripts for custom fields
function load_shift8_zoom_wp_admin_style() {
        // admin always last
        wp_enqueue_style( 'shift8_zoom_css', plugin_dir_url(dirname(__FILE__)) . 'css/shift8_zoom_admin.css', array(), '1.5' );
        wp_enqueue_script( 'shift8_zoom_script', plugin_dir_url(dirname(__FILE__)) . 'js/shift8_zoom_admin.js', array(), '1.3' );

        wp_localize_script( 'shift8_zoom_script', 'the_ajax_script', array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( "shift8_zoom_response_nonce"),
        ));  
}
add_action( 'admin_enqueue_scripts', 'load_shift8_zoom_wp_admin_style' );
