<?php
namespace BWCPP;

class Main {
	public static $limit_pictures_option_name = 'bwcpp_max_pictures_per_user';

	public $rest_route_base = '/bwcpp/v1';

	public function __construct() {
		$this->hook();
	}

	public function hook() {
		if ( is_admin() ) {
			require_once( get_inc_dir() . '/classes/class-admin.php' );
		}

		if ( ! \BWCPP\Helpers\is_woocommerce() ) {
			return;
		}

		add_filter( 'get_avatar', array( $this, 'get_avatar' ), 10, 5 );
		add_action( 'woocommerce_thankyou', array( $this, 'add_image_info_to_order' ) );
		add_action( 'rest_api_init', array( $this, 'add_rest_route' ) );
		add_filter( 'rest_authentication_errors', array( $this, 'rest_restrict_route' ) );

		require_once( get_inc_dir() . '/classes/class-user-pictures.php' );
		require_once( get_inc_dir() . '/classes/class-pictures-controller.php' );
		require_once( get_inc_dir() . '/classes/class-my-account.php' );
	}

	public function add_image_info_to_order( $order_id ) {
		$user_pictures = new User_Pictures();
		$picture_id = $user_pictures->get_primary();

		add_post_meta( $order_id, '_bwcpp_picture_id', $picture_id );
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

	public function add_rest_route() {
		\register_rest_route(
			$this->rest_route_base,
			'/pictures',
			array(
				'methods' => \WP_REST_Server::READABLE,
				'callback' => array( $this, 'rest_get_pictures' ),
			)
		);
	}

	public function rest_get_pictures() {
		$pictures = Pictures_Controller::get_pictures();

		return rest_ensure_response( $pictures );
	}

	public function rest_restrict_route( $result ) {
		if ( strpos( $_SERVER['REQUEST_URI'], "{$this->rest_route_base}/pictures" ) ) {
			if ( ! is_user_logged_in() ) {
				$result = new \WP_Error( 'not_logged_in', __( 'You need to be logged in to access this endpoint.', BWCPP_TEXT_DOMAIN ) );
			}
		}

		return $result;
	}

}
