<?php
/**
 * Shift8 Oauth Redirect
 *
 * Establish rewrite for oauth redirect URL
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
}

add_action('init', 'shift8_zoom_add_rewrite_rules');
function shift8_zoom_add_rewrite_rules() {
    add_rewrite_rule('^shift8-zoom-oauth$', 'index.php?shift8_zoom_oauth=1', 'top');
    add_rewrite_tag('%shift8_zoom_oauth%', '1');
}

add_action('template_redirect', 'shift8_zoom_handle_oauth_redirect');
function shift8_zoom_handle_oauth_redirect() {
    if (get_query_var('shift8_zoom_oauth')) {
        // Handle OAuth callback
        shift8_zoom_oauth_callback();
        exit;
    }
}