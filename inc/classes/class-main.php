<?php
namespace BWCPP;

class Main {
	public static $limit_pictures_option_name = 'bwcpp_max_pictures_per_user';

	public function __construct() {
		$this->hook();
	}

	public function hook() {
		require_once( get_inc_dir() . '/class/class-admin.php' );

		if ( ! \BWCPP\Helpers\is_woocommerce() ) {
			return;
		}

		require_once( get_inc_dir() . '/classes/class-user-pictures.php' );
		require_once( get_inc_dir() . '/classes/class-pictures-controller.php' );
		require_once( get_inc_dir() . '/classes/class-my-account.php' );
	}

}
