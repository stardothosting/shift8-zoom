<?php
/**
 * Shift8 Zoom Main Functions
 *
 * Collection of functions used throughout the operation of the plugin
 *
 */
use \Firebase\JWT\JWT;

if ( !defined( 'ABSPATH' ) ) {
    die();
}

// Function to encrypt session data
function shift8_zoom_encrypt($key, $payload) {
    if (!empty($key) && !empty($payload)) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    } else {
        return false;
    }
}

// Function to decrypt session data
function shift8_zoom_decrypt($key, $garble) {
    if (!empty($key) && !empty($garble)) {
        list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    } else {
        return false;
    }
}

// Handle the ajax trigger
add_action( 'wp_ajax_shift8_zoom_push', 'shift8_zoom_push' );
function shift8_zoom_push() {
    // Register
    if ( wp_verify_nonce($_GET['_wpnonce'], 'process') && $_GET['type'] == 'check') {
        shift8_zoom_poll('check');
        die();
    } else {
        die();
    } 
}

// Handle the actual GET
function shift8_zoom_poll($shift8_action) {
    if (current_user_can('administrator')) {
        global $wpdb;
        global $shift8_zoom_table_name;
        $current_user = wp_get_current_user();

        $zoom_jwt_token = shift8_zoom_generate_jwt();
        $zoom_user_email = sanitize_email(get_option('shift8_zoom_user_email'));

        // Set headers for WP Remote post
        $headers = array(
            'Content-type: application/json',
            'Authorization' => 'Bearer ' . $zoom_jwt_token,
        );
var_dump($zoom_jwt_token);
        // Check values with dashboard
        if ($shift8_action == 'check') {
            // Use WP Remote Get to poll the zoom api 
            $response = wp_remote_get( S8ZOOM_API . '/v2/users/' . $zoom_user_email . '/webinars',
                array(
                    'method' => 'GET',
                    'headers' => $headers,
                    'httpversion' => '1.1',
                    'timeout' => '45',
                    //'blocking' => true,
                )
            );

            // Deal with the response
            if (is_object(json_decode($response['body']))) {
                // Populate options from response if its a check
                if ($shift8_action == 'check') {
                    echo json_encode(array(
                        'total_records' => esc_attr(json_decode($response['body'])->total_records),
                        'webinar_data' => json_encode(json_decode($response['body'])->webinars),
                    ));
                }

            } else {
                echo 'Error Detected : ';
                if (is_array($response['response'])) {
                    echo esc_attr(json_decode($response['body'])->error);

                } else {
                    echo 'unknown';
                }
            } 
        }
    } 
}



// Functions to produce debugging information
function shift8_zoom_debug_get_php_info() {
    //retrieve php info for current server
    if (!function_exists('ob_start') || !function_exists('phpinfo') || !function_exists('ob_get_contents') || !function_exists('ob_end_clean') || !function_exists('preg_replace')) {
        echo 'This information is not available.';
    } else {
        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();

        $pinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$pinfo);
        echo $pinfo;
    }
}

function shift8_zoom_debug_version_check() {
    //outputs basic information
    $notavailable = __('This information is not available.');
    if ( !function_exists( 'get_bloginfo' ) ) {
        $wp = $notavailable;
    } else {
        $wp = get_bloginfo( 'version' );
    }

    if ( !function_exists( 'wp_get_theme' ) ) {
        $theme = $notavailable;
    } else {
        $theme = wp_get_theme();
    }

    if ( !function_exists( 'get_plugins' ) ) {
        $plugins = $notavailable;
    } else {
        $plugins_list = get_plugins();
        if( is_array( $plugins_list ) ){
            $active_plugins = '';
            $plugins = '<ul>';
            foreach ( $plugins_list as $plugin ) {
                $version = '' != $plugin['Version'] ? $plugin['Version'] : __( 'Unversioned', 'debug-info' );
                if( !empty( $plugin['PluginURI'] ) ){
                    $plugins .= '<li><a href="' . $plugin['PluginURI'] . '">' . $plugin['Name'] . '</a> (' . $version . ')</li>';
                } else {
                    $plugins .= '<li>' . $plugin['Name'] . ' (' . $version . ')</li>';
                }
            }
            $plugins .= '</ul>';
        }
    }

    if ( !function_exists( 'phpversion' ) ) {
        $php = $notavailable;
    } else {
        $php = phpversion();
    }


    $themeversion   = $theme->get( 'Name' ) . __( ' version ', 'debug-info' ) . $theme->get( 'Version' ) . $theme->get( 'Template' );
    $themeauth      = $theme->get( 'Author' ) . ' - ' . $theme->get( 'AuthorURI' );
    $uri            = $theme->get( 'ThemeURI' );

    echo '<strong>' . __( 'WordPress Version: ' ) . '</strong>' . $wp . '<br />';
    echo '<strong>' . __( 'Current WordPress Theme: ' ) . '</strong>' . $themeversion . '<br />';
    echo '<strong>' . __( 'Theme Author: ' ) . '</strong>' . $themeauth . '<br />';
    echo '<strong>' . __( 'Theme URI: ' ) . '</strong>' . $uri . '<br />';
    echo '<strong>' . __( 'PHP Version: ' ) . '</strong>' . $php . '<br />';
    echo '<strong>' . __( 'Active Plugins: ' ) . '</strong>' . $plugins . '<br />';
}

// Function to schedule cron polling interval to check if account is paid

// Check user plan options
add_action( 'shift8_zoom_cron_hook', 'shift8_zoom_check' );
function shift8_zoom_check() {
    $zoom_url = esc_attr(get_option('shift8_zoom_url'));
    $zoom_api = esc_attr(get_option('shift8_zoom_api'));

    // Use WP Remote Get to poll the zoom api 
    $response = wp_remote_get( S8ZOOM_API . '/api/check',
        array(
            'method' => 'POST',
            'httpversion' => '1.1',
            'timeout' => '45',
            'blocking' => true,
            'body' => array(
                'url' => $zoom_url,
                'api' => $zoom_api
            ),
        )
    );
    // Set transient based on results
    $suffix_get = esc_attr(json_decode($response['body'])->user_plan->zoom_suffix);
    $suffix_set = (!empty($suffix_get) && $suffix_get !== "" ? $suffix_get : S8ZOOM_SUFFIX );
    set_transient(S8ZOOM_PAID_CHECK, $suffix_set, 0);
}

// Custom Cron schedules outside of default WP Cron
add_filter( 'cron_schedules', 'shift8_zoom_add_cron_interval' );
function shift8_zoom_add_cron_interval( $schedules ) { 
    $schedules['shift8_zoom_five'] = array(
        'interval' => 5,
        'display'  => esc_html__( 'Every Five Seconds' ), );
    $schedules['shift8_zoom_minute'] = array(
        'interval' => 60,
        'display'  => esc_html__( 'Every Sixty Seconds' ), );
    $schedules['shift8_zoom_halfhour'] = array(
        'interval' => 1800,
        'display'  => esc_html__( 'Every 30 minutes' ), );
    $schedules['shift8_zoom_twohour'] = array(
        'interval' => 7200,
        'display'  => esc_html__( 'Every two hours' ), );
    $schedules['shift8_zoom_fourhour'] = array(
        'interval' => 14400,
        'display'  => esc_html__( 'Every four hours' ), );
    return $schedules;
}

// Set the cron task on an hourly basis to check the zoom suffix
if ( ! wp_next_scheduled( 'shift8_zoom_cron_hook' ) ) {
    wp_schedule_event( time(), 'shift8_zoom_fourhour', 'shift8_zoom_cron_hook' );
}


// Generate JWT Token 
function shift8_zoom_generate_jwt() {
    $key = esc_attr(get_option('shift8_zoom_api_key'));
    $secret = esc_attr(get_option('shift8_zoom_api_secret'));
    $header = array(
        'typ' => 'JWT', 
        'alg' => 'HS256'
    );
    $payload = array(
        "iss" => $key,
        "exp" => 1496091964000,
    );
    //$header_encode = JWT::urlsafeB64Encode(JWT::jsonEncode($header));
    //$payload_encode = JWT::urlsafeB64Encode(JWT::jsonEncode($payload));
    //$decoded = JWT::decode($jwt, $key, array('HS256'));
    $jwt = JWT::encode($payload, $secret);
    return $jwt;
}
// Build 
function shift8_zoom_get_import_frequency_options() {
    $import_frequency = array(
        'hourly' => 'Hourly',
        'twicedaily' => 'Twice Daily',
        'daily' => 'Daily',
        'weekly' => 'Weekly'
    );
    return $import_frequency;
}