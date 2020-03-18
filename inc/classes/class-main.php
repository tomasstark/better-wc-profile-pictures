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

		add_filter( 'get_avatar', array( $this, 'get_avatar' ), 10, 5 );

		require_once( get_inc_dir() . '/classes/class-user-pictures.php' );
		require_once( get_inc_dir() . '/classes/class-pictures-controller.php' );
		require_once( get_inc_dir() . '/classes/class-my-account.php' );
	}

	public function get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
		$user = null;

		if ( is_numeric( $id_or_email ) ) {
			$user = \get_user_by( 'id', (int) $id_or_email );
		} else {
			$user = \get_user_by( 'email', (string) $id_or_email );
		}

		if ( ! $user ) {
			return $avatar;
		}

		$user_pictures      = new User_Pictures( $user->ID );
		$primary_picture_id = $user_pictures->get_primary();

		if ( empty( $primary_picture_id ) ) {
			return $avatar;
		}

		$primary_url = $user_pictures->get_primary_src();

		if ( empty( $primary_url ) ) {
			return $avatar;
		}

		$avatar = sprintf(
			'<img src="%s" alt="%s" class="%s" height="%s" width="%s">',
			$primary_url,
			$alt,
			"avatar avatar-{$size} photo",
			$size,
			$size
		);

		return $avatar;
	}

}
