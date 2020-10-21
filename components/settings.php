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
    register_setting( 'shift8-zoom-settings-group', 'shift8_zoom_import_frequency', 'shift8_zoom_cron_validate');
    register_setting( 'shift8-zoom-settings-group', 'shift8_zoom_permalinks_flushed');
}

// Activation hook
function shift8_zoom_plugin_activation() {
    update_option('shift8_zoom_permalinks_flushed', 0);
}
register_activation_hook( S8ZOOM_FILE, 'shift8_zoom_plugin_activation' );
 

// Uninstall hook
function shift8_zoom_uninstall_hook() {
  // Delete setting values
  delete_option('shift8_zoom_enabled');
  delete_option('shift8_zoom_user_email');
  delete_option('shift8_zoom_api_key');
  delete_option('shift8_zoom_api_secret');
  delete_option('shift8_zoom_import_frequency');
  delete_option('shift8_zoom_permalinks_flushed');

  // Clear Cron tasks
  wp_clear_scheduled_hook( 'shift8_zoom_cron_hook' );
  // Deregister custom post type
  add_action('init','shift8_zoom_delete_post_type');
}
register_uninstall_hook( S8ZOOM_FILE, 'shift8_zoom_uninstall_hook' );

// Deactivation hook
function shift8_zoom_deactivation() {
  // Clear Cron tasks
  wp_clear_scheduled_hook( 'shift8_zoom_cron_hook' );
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

// Force cron schedule change if detected
function shift8_zoom_cron_validate($data){
  $cron_schedule = esc_attr($data);
  if (get_transient(S8ZOOM_CRON_SCHEDULE) && get_transient(S8ZOOM_CRON_SCHEDULE) === $cron_schedule) {
    set_transient(S8ZOOM_CRON_SCHEDULE, $cron_schedule, 0);
    return $cron_schedule;
  } else {
    set_transient(S8ZOOM_CRON_SCHEDULE, $cron_schedule, 0);
    wp_clear_scheduled_hook( 'shift8_zoom_cron_hook' );
    return $cron_schedule;
  }
}
