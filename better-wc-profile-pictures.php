<?php
/*
Plugin Name: Better WooCommerce Profile Pictures
Plugin URI: https://github.com/tomasstark/better-wc-profile-pictures
Description: Allows user to upload multiple profile pictures and choose primary one from already uploaded pictures.
Version: 0.1
Author: Tomas Stark
Author URI: https://github.com/tomasstark
License: GPL2
*/

namespace BWCPP;

define( 'BWCPP_TEXT_DOMAIN', 'bwcpp' );
define( 'BWCPP_VERSION', '0.1' );

function get_inc_dir() {
	return dirname( __FILE__ ) . '/inc';
}

function activate() {
	do_action( 'bwcpp_activate' );
}

register_activation_hook( __FILE__, '\BWCPP\activate' );

function initialize() {
	require_once( get_inc_dir() . '/helpers.php' );
	require_once( get_inc_dir() . '/classes/class-main.php' );

	$bwcpp = new Main();
}

add_action( 'plugins_loaded', '\BWCPP\initialize' );
?>
