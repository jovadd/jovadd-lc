<?php
/**
 * Check and setup theme's default settings
 *
 * @package jovadd-lc
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'jovadd-lc_setup_theme_default_settings' ) ) {
	/**
	 * Store default theme settings in database.
	 */
	function jovadd-lc_setup_theme_default_settings() {
		$defaults = jovadd-lc_get_theme_default_settings();
		$settings = get_theme_mods();
		foreach ( $defaults as $setting_id => $default_value ) {
			// Check if setting is set, if not set it to its default value.
			if ( ! isset( $settings[ $setting_id ] ) ) {
				set_theme_mod( $setting_id, $default_value );
			}
		}
	}
}

if ( ! function_exists( 'jovadd-lc_get_theme_default_settings' ) ) {
	/**
	 * Retrieve default theme settings.
	 *
	 * @return array
	 */
	function jovadd-lc_get_theme_default_settings() {
		$defaults = array(
			'jovadd-lc_posts_index_style' => 'default',   // Latest blog posts style.
			'jovadd-lc_sidebar_position'  => 'right',     // Sidebar position.
			'jovadd-lc_container_type'    => 'container', // Container width.
		);

		/**
		 * Filters the default theme settings.
		 *
		 * @param array $defaults Array of default theme settings.
		 */
		return apply_filters( 'jovadd-lc_theme_default_settings', $defaults );
	}
}


// as per https://livecanvas.com/faq/which-themes-with-livecanvas/
function lc_theme_is_livecanvas_friendly(){}


// expose to the livecanvas plugin the active Bootstrap version
function lc_theme_bootstrap_version(){return 5.3;}


//submenu for adminBar in customizer
add_action('admin_bar_menu', 'add_link_admin_bar', 999);
function add_link_admin_bar($adminBar) {
	$args = [
        'parent' => 'customize',
		'id' => 'jovadd-lc-theme',
        'title' => 'Jovadd LC Theme Options', 
        'href' => admin_url('themes.php?page=jovadd-lc-theme-options'),
	];
    $adminBar->add_node($args);
}




//Add Twitter handle/username to User Contact Information
 
function user_contact_add_twitter( $user_contact ) {
	$user_contact['twitter'] = __( 'Twitter Username' );

	return $user_contact;
}
add_filter( 'user_contactmethods', 'user_contact_add_twitter' );
