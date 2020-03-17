<?php
namespace BWCPP\Helpers;

function is_woocommerce() {
	if ( class_exists( 'woocommerce' ) ){
		return true;
	}

	return false;
}
