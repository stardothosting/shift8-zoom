<?php
/**
 * Shift8 Zoom Webinar Custom Post Type
 *
 * Declaration of plugin settings used throughout
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
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
      'rewrite'            => array( 'slug' => 'shift8_zoom' ),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'supports'           => array( 'title', 'thumbnail' )
    );

    register_post_type( 'shift8_zoom', $args );
}
add_action( 'init', 'register_shift8_zoom_post_type' );

// Function to add custom meta boxes
function shift8_zoom_add_post_meta_boxes() {
    // see https://developer.wordpress.org/reference/functions/add_meta_box for a full explanation of each property
    add_meta_box(
        "post_metadata_shift8_zoom_fields", // div id containing rendered fields
        "Zoom Webinar Fields", // section heading displayed as text
        "shift8_zoom_post_meta_box", // callback function to render fields
        "shift8_zoom", // name of post type on which to render fields
        "normal", // location on the screen
        "high" // placement priority
    );
}
add_action( "admin_init", "shift8_zoom_add_post_meta_boxes" );

// Change "Enter title" text on custom post type
function shift8_zoom_custom_enter_title( $input ) {
	global $post_type;

	if( is_admin() && 'Add title' == $input && 'shift8_zoom' == $post_type )
		return 'Webinar Topic';

	return $input;
}
add_filter('gettext','shift8_zoom_custom_enter_title');

// Save the custom fields 
function shift8_zoom_save_post_meta_boxes(){
    global $post;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( get_post_status( $post->ID ) === 'auto-draft' ) {
        return;
    }
    update_post_meta( $post->ID, "_post_advertising_category", sanitize_text_field( $_POST[ "_post_advertising_category" ] ) );
    update_post_meta( $post->ID, "_post_advertising_html", sanitize_text_field( $_POST[ "_post_advertising_html" ] ) );
}
add_action( 'save_post', 'shift8_zoom_save_post_meta_boxes' );

// Display the custom fields
function shift8_zoom_post_meta_box(){
    global $post;
    $custom = get_post_custom( $post->ID );
    $advertisingCategory = $custom[ "_post_advertising_category" ][ 0 ];
    $advertisingHtml = $custom[ "_post_advertising_html" ][ 0 ];

    wp_editor(
        htmlspecialchars_decode( $advertisingHtml ),
        '_post_shift8_zoom_agenda_html',
        $settings = array(
            'textarea_name' => '_post_shift8_zoom_agenda_html',
        )
    );

    echo '<div class="shift8-zoom-admin-custom-fields">';
    echo '<label>UUID :</label><input type="text" name="meta_key" value="test" readonly/>';
    echo '<label>ID :</label><input type="text" name="meta_key" value="test" readonly/><br />';
	echo '<label>Type :</label><input type="text" name="meta_key" value="5, 6 , 9"/><br />';
	echo '<label>Start Time :</label><input type="text" name="meta_key" value="2020-07-08T16:00:00Z"/><br />';
	echo '<label>Duration :</label><input type="text" name="meta_key" value="30"/><br />';
	echo '<label>Timezone :</label><input type="text" name="meta_key" value="America/New_York"/><br />';
    echo '</div>';

}


// Function to delete custom post type 
function shift8_zoom_delete_post_type(){
    unregister_post_type( 'shfit8_zoom' );
}