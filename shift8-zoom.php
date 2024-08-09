<?php
/**
 * Plugin Name: Shift8 Zoom
 * Plugin URI: https://github.com/stardothosting/shift8-zoom
 * Description: Plugin that imports Zoom webinars into custom content or existing content
 * Version: 1.1.2
 * Author: Shift8 Web 
 * Author URI: https://www.shift8web.ca
 * License: GPLv3
 */

// Composer dependencies
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

require_once(plugin_dir_path(__FILE__).'shift8-zoom-rules.php' );
require_once(plugin_dir_path(__FILE__).'components/enqueuing.php' );
require_once(plugin_dir_path(__FILE__).'components/settings.php' );
require_once(plugin_dir_path(__FILE__).'components/custom_post.php' );
require_once(plugin_dir_path(__FILE__).'components/functions.php' );

// Admin welcome page
if (!function_exists('shift8_main_page')) {
	function shift8_main_page() {
	?>
	<div class="wrap">
	<h2>Shift8 Plugins</h2>
	Shift8 is a Toronto based web development and design company. We specialize in Wordpress development and love to contribute back to the Wordpress community whenever we can! You can see more about us by visiting <a href="https://www.shift8web.ca" target="_new">our website</a>.
	</div>
	<?php
	}
}

// Admin settings page
function shift8_zoom_settings_page() {
?>
<div class="wrap">
<h2>Shift8 Zoom Settings</h2>
<?php if (is_admin()) { 
$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'core_settings';
$plugin_data = get_plugin_data( __FILE__ );
$plugin_name = $plugin_data['TextDomain'];
    ?>
<h2 class="nav-tab-wrapper">
    <a href="?page=<?php echo $plugin_name; ?>%2Fcomponents%2Fsettings.php%2Fcustom&tab=core_settings" class="nav-tab <?php echo $active_tab == 'core_settings' ? 'nav-tab-active' : ''; ?>">Core Settings</a>
    <a href="?page=<?php echo $plugin_name; ?>%2Fcomponents%2Fsettings.php%2Fcustom&tab=shortcode_examples" class="nav-tab <?php echo $active_tab == 'shortcode_examples' ? 'nav-tab-active' : ''; ?>">Shortcode Examples</a>
    <a href="?page=<?php echo $plugin_name; ?>%2Fcomponents%2Fsettings.php%2Fcustom&tab=support_options" class="nav-tab <?php echo $active_tab == 'support_options' ? 'nav-tab-active' : ''; ?>">Support</a>
</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'shift8-zoom-settings-group' ); ?>
    <?php do_settings_sections( 'shift8-zoom-settings-group' ); ?>
    <?php
	$locations = get_theme_mod( 'nav_menu_locations' );
	if (!empty($locations)) {
		foreach ($locations as $locationId => $menuValue) {
			if (has_nav_menu($locationId)) {
				$shift8_zoom_menu = $locationId;
			}
		}
	}

	?>
    <table class="form-table shift8-zoom-table">
    <tbody class="<?php echo $active_tab == 'core_settings' ? 'shift8-zoom-admin-tab-active' : 'shift8-zoom-admin-tab-inactive'; ?>">
	<tr valign="top">
    <th scope="row">Core Settings</th>
    <td><span id="shift8-zoom-notice">
    <?php 
    settings_errors('shift8_zoom_url');
    settings_errors('shift8_zoom_user_email');
    settings_errors('shift8_zoom_client_id');
    settings_errors('shift8_zoom_client_secret');
    settings_errors('shift8_zoom_account_id');
    settings_errors('shift8_zoom_import_frequency');
    settings_errors('shift8_zoom_filter_title');
    ?>
    </span></td>
	</tr>
    <tr valign="top">
    <th scope="row">Enable Shift8 Zoom : </th>
    <td>
    <?php
    if (esc_attr( get_option('shift8_zoom_enabled') ) == 'on') {
        $enabled_checked = "checked";
    } else {
        $enabled_checked = "";
    }
    ?>
    <label class="switch">
    <input type="checkbox" name="shift8_zoom_enabled" <?php echo $enabled_checked; ?>>
    <div class="slider round"></div>
    </label>
    </td>
    </tr>
    <tr valign="top">
    <th scope="row">Shift8 Zoom User Email : </th>
    <td><input type="text" id="shift8_zoom_user_email_field" name="shift8_zoom_user_email" size="34" value="<?php echo (empty(sanitize_email(get_option('shift8_zoom_user_email'))) ? '' : sanitize_email(get_option('shift8_zoom_user_email'))); ?>">
    <div class="shift8-zoom-tooltip"><span class="dashicons dashicons-editor-help"></span>
        <span class="shift8-zoom-tooltiptext">This is the email address of your Zoom account holder</span>
    </div>
    </td>
    </tr>
    <tr valign="top">
        <th scope="row">Client ID</th>
        <td>
            <input type="text" name="shift8_zoom_client_id" value="<?php echo esc_attr(get_option('shift8_zoom_client_id')); ?>" />
        </td>
    </tr>

    <tr valign="top">
        <th scope="row">Client Secret</th>
        <td>
            <input type="text" name="shift8_zoom_client_secret" value="<?php echo esc_attr(get_option('shift8_zoom_client_secret')); ?>" />
        </td>
    </tr>

    <tr valign="top">
        <th scope="row">Account ID</th>
        <td>
            <input type="text" name="shift8_zoom_account_id" value="<?php echo esc_attr(get_option('shift8_zoom_account_id')); ?>" />
        </td>
    </tr>
    <tr valign="top">
    <th scope="row">Select Zoom Webinar Import Frequency : </th>
    <td>
    <div class="shift8-zoom-select">
            <select name="shift8_zoom_import_frequency">
                    <?php
                        $import_options = shift8_zoom_get_import_frequency_options();
                        foreach ( $import_options as $import_option => $description ) {
                            $selected = ($import_option == get_option('shift8_zoom_import_frequency') ? 'selected' : '');
                            echo "<option value='" . $import_option . "' " . $selected . ">" . esc_attr( $description ) . "</option>";
                        }
                    ?>
            </select>
    </div>
    <div class="shift8-zoom-tooltip"><span class="dashicons dashicons-editor-help"></span>
        <span class="shift8-zoom-tooltiptext">This determines the frequency where we will pull new webinars from Zoom</span>
    </div>
    </td>
    </tr>
    <tr valign="top">
    <th scope="row">Shift8 Zoom Filter Title : </th>
    <td><input type="text" id="shift8_zoom_filter_title" name="shift8_zoom_filter_title" size="34" value="<?php echo (empty(esc_attr(get_option('shift8_zoom_filter_title'))) ? '' : esc_attr(get_option('shift8_zoom_filter_title'))); ?>">
    <div class="shift8-zoom-tooltip"><span class="dashicons dashicons-editor-help"></span>
        <span class="shift8-zoom-tooltiptext">Enter a text phrase for the webinar import process to ignore or skip webinars based on text matches of the webinar title</span>
    </div>
    </td>
    </tr>
    <tr valign="top">
    <td width="226px"><div class="shift8-zoom-spinner"></div></td>
    <td>
    <ul class="shift8-zoom-controls">
    <?php if (!empty(esc_attr(get_option('shift8_zoom_client_id')) && esc_attr(get_option('shift8_zoom_client_secret')))) { ?>
    <li>
    <div class="shift8-zoom-button-container">
    <a id="shift8-zoom-check" href="<?php echo wp_nonce_url( admin_url('admin-ajax.php?action=shift8_zoom_push'), 'process'); ?>"><button class="shift8-zoom-button shift8-zoom-button-check">Test</button></a>
    </div>
    </li>
    <li>
    <div class="shift8-zoom-button-container">
    <a id="shift8-zoom-import" href="<?php echo wp_nonce_url( admin_url('admin-ajax.php?action=shift8_zoom_push'), 'process'); ?>"><button class="shift8-zoom-button shift8-zoom-button-import">Manual Import</button></a>
    </div>
    </li>
    <?php } ?>
    </ul>
    <div class="shift8-zoom-response">
    </div>
    </td>
    </tr>
    </tbody>
    <!-- SHORTCODE EXAMPLES TAB -->
    <tbody class="<?php echo $active_tab == 'shortcode_examples' ? 'shift8-zoom-admin-tab-active' : 'shift8-zoom-admin-tab-inactive'; ?>">
    <tr valign="top">
    <th scope="row">Shortcode Examples</th>
    </tr>
    <tr valign="top">
    <td style="width:500px;">Below find some shortcode examples that can be used in any post grid. The idea is to feed the Zoom events into a post grid and use the shortcode below in the grid template to pull the custom fields.<br /><br />
    <!--<tr valign="top">
    <th scope="row">Shortcode</th>
    <td>Description</td>
    </tr>
    <tr>
    <td><b>[shift8_zoom_title]</b></td>
    <td>Display the event title</td>
    </tr>-->
    </td>
    </tr>
    </tbody>
    <!-- SUPPORT TAB -->
    <tbody class="<?php echo $active_tab == 'support_options' ? 'shift8-zoom-admin-tab-active' : 'shift8-zoom-admin-tab-inactive'; ?>">
    <tr valign="top">
    <th scope="row">Support</th>
    </tr>
    <tr valign="top">
    <td style="width:500px;">If you are experiencing difficulties, you can receive support if you Visit the <a href="https://wordpress.org/support/plugin/shift8-zoom/" target="_new">Shift8 zoom Wordpress support page</a> and post your question there.<Br /><Br />
    <strong>Debug Info</strong><br /><br />
    Providing the debug information below to the Shift8 zoom support team may be helpful in them assisting in diagnosing any issues you may be having. <br /><br />
    <div class="shift8-zoom-button-container">
    </div><button class="shift8-zoom-button shift8-zoom-button-copyclipboard" id="button1" onclick="Shift8ZoomCopyToClipboard('shift8zoom-debug')">Copy info below to clipboard</button>
    <br /><br />
    <script type="text/javascript">
        function showDetails(id) {
            document.getElementById(id).style.display = 'block';
        }
        function hideDetails(id) {
            document.getElementById(id).style.display = 'none';
        }
    </script>
    <div class="wrap">
        <div class="postbox" id="shift8zoom-debug">
            <h2><?php _e('Shift8 Zoom Debug Info'); ?></h2>
            <p><?php echo shift8_zoom_debug_version_check(); ?></p>
        </div>
        <!--<div class="postbox" id="shift8zoom-debugphp">
            <h2><?php _e('Shift8 zoom PHP Debug Info'); ?></h2>
            <p><?php _e('For more detailed PHP server related information, click the Show Details link below.'); ?></p>
            <a href="#" onclick="showDetails('details'); return false;"><?php _e('Show Details'); ?></a>
            <a href="#" onclick="hideDetails('details'); return false;"><?php _e('Hide Details'); ?></a>
            <span id="details" style="display: none;"><?php echo shift8_zoom_debug_get_php_info(); ?></span>
        </div>-->
    </div>
    </td>
    </tr>
    </tbody>
    </table>
    <?php 
    if ($active_tab !== 'support_options' && $active_tab !== 'shortcode_examples') {
        submit_button(); 
    }
    ?>
    </form>
</div>
<?php 
	} // is_admin
}


