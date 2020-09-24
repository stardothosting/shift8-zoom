<?php
/**
 * Shift8 Zoom Settings
 *
 * Declaration of plugin settings used throughout
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
}

// create custom plugin settings menu
add_action('admin_menu', 'shift8_zoom_create_menu');
function shift8_zoom_create_menu() {
        //create new top-level menu
        if ( empty ( $GLOBALS['admin_page_hooks']['shift8-settings'] ) ) {
                add_menu_page('Shift8 Settings', 'Shift8', 'administrator', 'shift8-settings', 'shift8_main_page' , 'dashicons-building' );
        }
        add_submenu_page('shift8-settings', 'Zoom Settings', 'Zoom Settings', 'manage_options', __FILE__.'/custom', 'shift8_zoom_settings_page');
        //call register settings function
        add_action( 'admin_init', 'register_shift8_zoom_settings' );
}

// Register admin settings
function register_shift8_zoom_settings() {
    //Register our settings
    register_setting( 'shift8-zoom-settings-group', 'shift8_zoom_enabled' );
    register_setting( 'shift8-zoom-settings-group', 'shift8_zoom_user_email' );
    register_setting( 'shift8-zoom-settings-group', 'shift8_zoom_api_key' );
    register_setting( 'shift8-zoom-settings-group', 'shift8_zoom_api_secret' );
    register_setting( 'shift8-zoom-settings-group', 'shift8_zoom_import_frequency');
}

// Register custom post type
function register_shift8_zoom_post_type() {
    $labels = array(
      'name'               => 'Shift8 Zoom',
      'singular_name'      => 'Zoom Webinar',
      'add_new'            => 'Add New',
      'add_new_item'       => 'Add New Webinar',
      'edit_item'          => 'Edit Webinar',
      'new_item'           => 'New Webinar',
      'all_items'          => 'All Webinars',
      'view_item'          => 'View Webinar',
      'search_items'       => 'Search Webinars',
      'not_found'          =>  'No webinars found',
      'not_found_in_trash' => 'No webinars found in Trash',
      'parent_item_colon'  => '',
      'menu_name'          => 'Shift8 Zoom'
    );

    $args = array(
      'labels'             => $labels,
      'public'             => true,
      'publicly_queryable' => true,
      'show_ui'            => true,
      'show_in_menu'       => true,
      'query_var'          => true,
      'rewrite'            => array( 'slug' => 'webinar' ),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
    );

    register_post_type( 'shift8_zoom', $args );
}
add_action( 'init', 'register_shift8_zoom_post_type' );

// Uninstall hook
function shift8_zoom_uninstall_hook() {
  // Delete setting values
  delete_option('shift8_zoom_enabled');
  delete_option('shift8_zoom_user_email');
  delete_option('shift8_zoom_api_key');
  delete_option('shift8_zoom_api_secret');
  delete_option('shift8_zoom_import_frequency');

  // Clear Cron tasks
  wp_clear_scheduled_hook( 'shift8_zoom_cron_hook' );
  // Delete transient data
  delete_transient(S8ZOOM_PAID_CHECK);
  // Deregister custom post type
  add_action('init','shift8_zoom_delete_post_type');
}
register_uninstall_hook( S8ZOOM_FILE, 'shift8_zoom_uninstall_hook' );

// Delete custom post type 
function shift8_zoom_delete_post_type(){
    unregister_post_type( 'shfit8_zoom' );
}

// Deactivation hook
function shift8_zoom_deactivation() {
  // Clear Cron tasks
  wp_clear_scheduled_hook( 'shift8_zoom_cron_hook' );
  // Delete transient
  delete_transient(S8ZOOM_PAID_CHECK);
}
register_deactivation_hook( S8ZOOM_FILE, 'shift8_zoom_deactivation' );

// Validate admin options
function shift8_zoom_check_enabled() {
  // If enabled is not set
  if(esc_attr( get_option('shift8_zoom_enabled') ) != 'on') return false;
  if(empty(sanitize_email(get_option('shift8_zoom_user_email') ))) return false;
  if(empty(esc_attr(get_option('shift8_zoom_api_key') ))) return false;
  if(empty(esc_attr(get_option('shift8_zoom_api_secret') ))) return false;
  if(empty(esc_attr(get_option('shift8_zoom_import_frequency') ))) return false;

  return true;
}

// Process all options and return array
function shift8_zoom_check_options() {
  $shift8_options = array();
  $shift8_options['zoom_user_email'] = sanitize_email( get_option('shift8_zoom_user_email') );
  $shift8_options['zoom_api_key'] = esc_attr( get_option('shift8_zoom_api_key') );
  $shift8_options['zoom_api_secret'] = esc_attr( get_option('shift8_zoom_api_secret') );
  $shift8_options['zoom_import_frequency'] = esc_attr( get_option('shift8_zoom_import_frequency') );
  
  return $shift8_options;
}

