<?php
namespace BWCPP\Helpers;

function is_woocommerce() {
	if ( class_exists( 'woocommerce' ) ){
		return true;
	}

	return false;
}

function get_unique_filename( $path, $name ) {
	$user = \get_current_user();
	$pathinfo = pathinfo( $name );

	$name = \wp_generate_uuid4() . '.' . $pathinfo['extension'];

	return $name;
}
