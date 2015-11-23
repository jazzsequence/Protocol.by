<?php
/*
Plugin Name: Protocol.by
Plugin URI: https://github.com/ArcanePalette/Protocol.by
Description: <a href="http://protocol.by" target="_blank">Protocol.by</a> is a service developed by the MIT Media Lab to inform people what the best ways of getting in touch with you are.  This plugin allows you to use the embed code for your Protocol.by profile without having to modify any of your theme's php files.  Once activated, add <code>&lt;div id="protocol"&gt;&lt;/div&gt;</code> in any post or page to display the Protocol.by embed.
Version: 1.0.2
Author: Chris Reynolds
Author URI: https://jazzsequence.com
License: GPL3
*/

/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.


    http://www.opensource.org/licenses/gpl-3.0.html
*/

/**
 *
 * Protocol.by Options page
 *
 * @package protocol_by
 * @since 1.0
 *
 * Adds an admin menu to the options page to define custom settings
 * This is (hopefully) pretty standard Settings API stuff, much of which was derived from Otto's post here:
 * http://ottodestruct.com/blog/2009/wordpress-settings-api-tutorial/ and the Codex here:
 * http://codex.wordpress.org/Creating_Options_Pages
 *
 */
function protocol_by_menu() {
	add_options_page( 'Protocol.by Settings', 'Protocol.by', 'manage_options', 'protocol-by', 'protocol_by_options' );
}
add_action( 'admin_menu', 'protocol_by_menu' );

function protocol_by_options() {
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	} ?>

		<div class="wrap">
		<h2>Protocol.by Options</h2>
		<form action="options.php" method="post">
		<?php settings_fields( 'protocol_options' ); ?>
		<?php do_settings_sections( 'protocol_by' ); ?>
		<p class="submit"><input name="submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
		</form>
		</div>

<?php }

function protocol_by_admin_init() {
	register_setting ( 'protocol_options', 'protocol_options', 'protocol_by_options_validate' );
	add_settings_section( 'protocol_by_username', 'Protocol.by Username', 'protocol_by_username_text', 'protocol_by' ); // adds a section for Protocol.by username
	add_settings_field( 'protocol_by_username', 'Username', 'protocol_by_username', 'protocol_by', 'protocol_by_username' ); // adds the actual username setting
	add_settings_section( 'protocol_by_appearance', 'Protocol.by Appearance Settings', 'protocol_by_appearance_text', 'protocol_by' );  // adds a section for Protocol.by appearance stuff
	add_settings_field( 'protocol_by_width', 'Width', 'protocol_by_width', 'protocol_by', 'protocol_by_appearance' ); // adds a setting for width
	add_settings_field( 'protocol_by_theme', 'Theme', 'protocol_by_theme', 'protocol_by', 'protocol_by_appearance' ); // adds a setting for theme - options are dark, light, default
	add_settings_field( 'protocol_by_size', 'Size', 'protocol_by_size', 'protocol_by', 'protocol_by_appearance' ); // adds a setting for size - options are dense, ultradense, default
	add_settings_field( 'protocol_by_background', 'Background (optional)', 'protocol_by_background', 'protocol_by', 'protocol_by_appearance' ); // adds an (optional) setting for background - default is commented out
}
add_action( 'admin_init', 'protocol_by_admin_init' );

function protocol_by_username_text() {
	echo '<p>Your Protocol.by username</p>';
}

function protocol_by_username() {
	$options = get_option( 'protocol_options' );
	echo "<input id='protocol_by_username' name='protocol_options[username]' size='40' type='text' value='{$options['username']}' />";
}

function protocol_by_appearance_text() {
	echo '<p>Protocol.by embed appearance settings</p>';
}

function protocol_by_width() {
	$options = get_option( 'protocol_options' );
	echo "<input id='protocol_by_width' name='protocol_options[width]' size='10' type='text' value='{$options['width']}' />";
}

function protocol_by_theme() {
	$options = get_option( 'protocol_options' );
	$items = array( 'dark', 'light', 'default' );
	foreach($items as $item) {
		$checked = ( $options['theme'] == $item ) ? ' checked="checked" ' : '';
		echo "<label><input " . $checked . " value='$item' name='protocol_options[theme]' type='radio' /> $item</label><br />";
	}
}

function protocol_by_size() {
	$options = get_option( 'protocol_options' );
	$items = array( 'dense', 'ultradense', 'default' );
	foreach($items as $item) {
		$checked = ( $options['size'] == $item ) ? ' checked="checked" ' : '';
		echo "<label><input " . $checked . " value='$item' name='protocol_options[size]' type='radio' /> $item</label><br />";
	}
}

function protocol_by_background() {
	$options = get_option( 'protocol_options' );
	echo "<input id='protocol_by_background' name='protocol_options[background] size='10' type='text' value='{$options['background']}' /><br />";
	echo "<em>HTML color hex value. Leave blank for default</em>";
}

function protocol_by_options_validate($input) {
	// Check our textbox option field contains no HTML tags - if so strip them out
	$input['username'] =  esc_html($input['username']); // validates the username field
	$input['width'] = esc_html($input['width']); // validates the width
	return $input; // return validated input
}

/**
 *
 * Protocol.by Embed
 *
 * @package protocol_by
 * @since 1.0
 *
 * outputs the Protocol.by embed code into the header with the options that were defined on the settings page
 *
 */

function protocol_by_head_embed() {
	$protocol_options = get_option( 'protocol_options' );
	$protocol_by_username = $protocol_options['username'];
	$protocol_by_width = $protocol_options['width'];
	$protocol_by_theme = $protocol_options['theme'];
	$protocol_by_size = $protocol_options['size'];
	$protocol_by_background = $protocol_options['background'];
	echo '<script src="http://protocol.media.mit.edu/static/js/protocol-embed.js"></script>';
	echo '<script>';
	echo 'Protocol.username = "' . $protocol_by_username . '";';
	echo 'Protocol.width = "' . $protocol_by_width . '";';
	echo 'Protocol.theme = "' . $protocol_by_theme . '";';
	echo 'Protocol.size = "'. $protocol_by_size . '";';
	if ( $protocol_by_background != '' ) {
		echo 'Protocol.background = "' . $protocol_by_background . '";';
	}
	echo '</script>';
}
add_action( 'wp_footer', 'protocol_by_head_embed');
?>