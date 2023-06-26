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
      'not_found'          => 'No webinars found',
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
      'rewrite'            => array( 'slug' => 'events' ),
      'capability_type'    => 'post',
      'has_archive'        => true,
      'hierarchical'       => false,
      'menu_position'      => null,
      'supports'           => array( 'title', 'thumbnail' ),
      'taxonomies'          => array( 'zoom_categories' ),
    );

    register_post_type( 'shift8_zoom', $args );

    // Flush rewrite rules
    if( !get_option('shift8_zoom_plugin_permalinks_flushed') ) {
        flush_rewrite_rules(false);
        update_option('shift8_zoom_plugin_permalinks_flushed', 1);
    }
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

function shift8_zoom_taxonomies_webinar() {
  register_taxonomy(
    'zoom_categories',  // The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
    'zoom',             // post type name
    array(
      'hierarchical' => true,
      'label' => 'Categories', // display name
      'show_admin_column' => true,
      'query_var' => true,
      'rewrite' => array(
        'slug' => 'zoom',    // This controls the base slug that will display before each term
        'with_front' => false  // Don't display the category base before
      )
    )
  );
}
add_action( 'init', 'shift8_zoom_taxonomies_webinar', 0 );

// Save the custom fields 
function shift8_zoom_save_post_meta_boxes(){
    global $post;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( get_post_status( $post->ID ) === 'auto-draft' ) {
        return;
    }
    update_post_meta( $post->ID, "_post_shift8_zoom_type", sanitize_text_field( $_POST[ "_post_shift8_zoom_type" ] ) );
    update_post_meta( $post->ID, "_post_shift8_zoom_start", sanitize_text_field( $_POST[ "_post_shift8_zoom_start" ] ) );
    update_post_meta( $post->ID, "_post_shift8_zoom_duration", sanitize_text_field( $_POST[ "_post_shift8_zoom_duration" ] ) );
    update_post_meta( $post->ID, "_post_shift8_zoom_timezone", sanitize_text_field( $_POST[ "_post_shift8_zoom_timezone" ] ) );
    update_post_meta( $post->ID, "_post_shift8_zoom_joinurl", sanitize_url( $_POST[ "_post_shift8_zoom_joinurl" ] ) );
    update_post_meta( $post->ID, "_post_shift8_zoom_agenda_html", shift8_zoom_wp_kses( $_POST[ "_post_shift8_zoom_agenda_html" ] ) );
}
add_action( 'save_post', 'shift8_zoom_save_post_meta_boxes' );

// Display the custom fields
function shift8_zoom_post_meta_box(){
    global $post;
    $custom = get_post_custom( $post->ID );
    $zoom_uuid = $custom[ "_post_shift8_zoom_uuid" ][ 0 ];
    $zoom_id = $custom[ "_post_shift8_zoom_id" ][ 0 ];
    $zoom_type = $custom[ "_post_shift8_zoom_type" ][ 0 ];
    switch ( $zoom_type ) {
        case '5':
            $webinarSelected = "selected";
            break;
        case '6':
            $recurringNoFixedSelected = "selected";
            break;
        case '9':
            $recurringFixedSelected = "selected";
            break;
        // Custom types
        case 'shift8_inperson': 
            $inpersonSelected = "selected";
            break;
        case 'shift8_virtual': 
            $virtualSelected = "selected";
            break;
        case 'shift8_tradeshow': 
            $tradeshowSelected = "selected";
            break;
    }
    $zoom_start = $custom[ "_post_shift8_zoom_start" ][ 0 ];
    $zoom_duration = $custom[ "_post_shift8_zoom_duration" ][ 0 ];
    $zoom_timezone = $custom[ "_post_shift8_zoom_timezone" ][ 0 ];
    $zoom_joinurl = $custom[ "_post_shift8_zoom_joinurl" ][ 0 ];
    $zoom_agenda = $custom[ "_post_shift8_zoom_agenda_html" ][ 0 ];

    echo '<div class="shift8-zoom-admin-custom-fields">';
    echo '<label>UUID :</label><input type="text" name="_post_shift8_zoom_uuid" value="'. $zoom_uuid . '" readonly/>';
    echo '<label>ID :</label><input type="text" name="_post_shift8_zoom_id" value="' . $zoom_id . '" readonly/><br />';
	  echo '<label>Type :</label><select name="_post_shift8_zoom_type"/>
    <option value="shift8_inperson" ' . $inpersonSelected . '>In-person Event</option>
		<option value="5" ' . $webinarSelected . '>Webinar</option>
    <option value="shift8_virtual" ' . $virtualSelected . '>Virtual Event</option>
    <option value="shift8_tradeshow" ' . $tradeshowSelected . '>Tradeshow</option>
		<option value="6" ' . $recurringNoFixedSelected . '>Recurring webinar with no fixed time</option>
		<option value="9" ' . $recurringFixedSelected . '>Recurring webinar with a fixed time</option>
		</select>
	  <br />';
  	echo '<label>Start Time :</label><input type="text" name="_post_shift8_zoom_start" value="' . $zoom_start . '"/><br />';
  	echo '<label>Duration :</label><input type="text" name="_post_shift8_zoom_duration" value="' . $zoom_duration . '"/><br />';
  	echo '<label>Timezone :</label><input type="text" name="_post_shift8_zoom_timezone" value="' . $zoom_timezone . '"/><br />';
  	echo '<label>Register URL :</label><input type="text" name="_post_shift8_zoom_joinurl" value="' . $zoom_joinurl . '"/><br />';
  	echo '<label><b>Agenda Details :</b></label><br /><br />';
    wp_editor(
        htmlspecialchars_decode( $zoom_agenda ),
        '_post_shift8_zoom_agenda_html',
        $settings = array(
            'textarea_name' => '_post_shift8_zoom_agenda_html',
        )
    );
    echo '</div>';

}


// Function to delete custom post type 
function shift8_zoom_delete_post_type(){
    unregister_post_type( 'shfit8_zoom' );
}
