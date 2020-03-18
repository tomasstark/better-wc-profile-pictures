<?php
/**
 * Plugin Name: Better WooCommerce Profile Pictures
 * Plugin URI: https://github.com/tomasstark/better-wc-profile-pictures
 * Description: Allows user to upload multiple profile pictures and choose primary one from already uploaded pictures.
 * Version: 0.1
 * Author: Tomas Stark
 * Author URI: https://github.com/tomasstark
 * License: GPL2
 *
 * @package bwcpp
 */

namespace BWCPP;

define( 'BWCPP_VERSION', '0.1' );

/**
 * Returns plugin URL. Useful for enqueuing assets on front end.
 *
 * @return string Plugin root URL.
 */
function get_plugin_url() {
	return plugin_dir_url( __FILE__ );
}

/**
 * Returns path to plugin's `inc/` directory for requiring classes and functions.
 *
 * @return string Path to plugin's `inc/` directory.
 */
function get_inc_dir() {
	return dirname( __FILE__ ) . '/inc';
}

/**
 * Activation hook. Flushes rewrite rules since we're adding rewrite endpoint.
 *
 * Hook `bwcpp_activate` is available to hook custom actions on activation.
 *
 * @return void
 */
function activate() {
	flush_rewrite_rules();

	do_action( 'bwcpp_activate' );
}

register_activation_hook( __FILE__, '\BWCPP\activate' );

/**
 * Deactivation hook. Flushing rewrite rules to clean up our rewrites.
 *
 * Hook `bwcpp_deactivate` allowing to hook custom code on deactivation.
 *
 * @return void
 */
function deactivate() {
	flush_rewrite_rules();

	do_action( 'bwcpp_deactivate' );
}

register_deactivation_hook( __FILE__, '\BWCPP\deactivate' );

/**
 * Initiating main class.
 *
 * @hooked action `plugins_loaded`
 *
 * @return void
 */
function initialize() {
	require_once( get_inc_dir() . '/helpers.php' );
	require_once( get_inc_dir() . '/classes/class-main.php' );

	$bwcpp = new Main();
}

add_action( 'plugins_loaded', '\BWCPP\initialize' );
