<?php
/**
 * Main class file.
 *
 * @package bwcpp
 */

namespace BWCPP;

/**
 * Main class.
 *
 * Handles requiring other classes and handling general hooks that don't fit elsewhere.
 */
class Main {
	/**
	 * WordPress option name for number of maximum pictures allowed per user.
	 *
	 * @var string Option name.
	 */
	public static $limit_pictures_option_name = 'bwcpp_max_pictures_per_user';

	/**
	 * Base part of REST route for all routes registered in this plugin.
	 *
	 * @var string Base route.
	 */
	public $rest_route_base = '/bwcpp/v1';

	/**
	 * Class constructor. Hooks to WordPress.
	 */
	public function __construct() {
		/**
		 * Only require admin class if we're in admin.
		 *
		 * We're requiring this earlier because admin class handles notice display in case
		 * WooCommerce is not activated.
		 */
		if ( is_admin() ) {
			require_once( get_inc_dir() . '/classes/class-admin.php' );
		}

		/**
		 * If WooCommerce is not activated, do not proceed including other classes.
		 */
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

	/**
	 * Adds user's primary picture ID at the time of order to order meta.
	 *
	 * @param int $order_id ID of WooCommerce order.
	 *
	 * @hooked action `woocommerce_thankyou`
	 *
	 * @return void
	 */
	public function add_image_info_to_order( $order_id ) {
		$user_pictures = new User_Pictures();
		$picture_id    = $user_pictures->get_primary();

		add_post_meta( $order_id, '_bwcpp_picture_id', $picture_id );
	}

	/**
	 * Modifies `get_avatar` to return user's primary picture instead of default if available.
	 *
	 * @param string     $avatar      Current avatar we're about to modify.
	 * @param int|string $id_or_email Either user ID or email address.
	 * @param int        $size        Avatar's desired size.
	 * @param string     $default     Default avatar.
	 * @param string     $alt         Alt text.
	 *
	 * @hooked filter `get_avatar`
	 *
	 * @return string Avatar's <img> tag.
	 */
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

	/**
	 * Adds REST API route for listing all pictures.
	 *
	 * @hooked action `rest_api_init`
	 *
	 * @return void
	 */
	public function add_rest_route() {
		\register_rest_route(
			$this->rest_route_base,
			'/pictures',
			array(
				'methods'  => \WP_REST_Server::READABLE,
				'callback' => array( $this, 'rest_get_pictures' ),
			)
		);
	}

	/**
	 * Callback function for REST API endpoint.
	 *
	 * @return string
	 */
	public function rest_get_pictures() {
		/**
		 * Calls `Pictures_Controller` to get all pictures by all users.
		 */
		$pictures = Pictures_Controller::get_pictures();

		/**
		 * Returns through `rest_ensure_response` for properly formatted response.
		 */
		return rest_ensure_response( $pictures );
	}

	/**
	 * Restricts `pictures` REST route to logged in users only. Throws error if not logged in.
	 *
	 * @param WP_Error|null|boolean $result State of the restriction.
	 *
	 * @hooked filter `rest_authentication_errors`
	 *
	 * @return WP_Error|null|boolean Returns null if authentication not used, WP_Error if not logged in, true on success.
	 */
	public function rest_restrict_route( $result ) {
		if ( strpos( $_SERVER['REQUEST_URI'], "{$this->rest_route_base}/pictures" ) ) {
			if ( ! is_user_logged_in() ) {
				$result = new \WP_Error( 'not_logged_in', __( 'You need to be logged in to access this endpoint.', 'bwcpp' ) );
			}
		}

		return $result;
	}

}
