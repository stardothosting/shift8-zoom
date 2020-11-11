<?php
/**
 * Shift8 Zoom Define rules
 *
 * Defined rules used throughout the plugin operations
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
}

define( 'S8ZOOM_FILE', 'shift8-zoom/shift8-zoom.php' );

if ( !defined( 'S8ZOOM_DIR' ) )
    define( 'S8ZOOM_DIR', realpath( dirname( __FILE__ ) ) );

define( 'S8ZOOM_API' , 'https://api.zoom.us');
