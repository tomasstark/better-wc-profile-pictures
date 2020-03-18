<?php
/**
 * Set of helper functions used across code base.
 *
 * @package bwcpp
 */

namespace BWCPP\Helpers;

/**
 * Checks if WooCommerce is activated.
 *
 * @return boolean
 */
function is_woocommerce() {
	if ( class_exists( 'woocommerce' ) ){
		return true;
	}

	return false;
}

/**
 * Generates UUID for uploaded file.
 *
 * @hooked callback `wp_unique_filename`
 *
 * @return string
 */
function get_unique_filename( $path, $name, $ext ) {
	$user = \get_current_user();
	$name = \wp_generate_uuid4() . $ext;

	return $name;
}
