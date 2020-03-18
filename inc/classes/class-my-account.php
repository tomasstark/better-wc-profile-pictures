<?php
/**
 * My_Account class file.
 *
 * @package bwcpp
 */

namespace BWCPP;

/**
 * My_Account class for registering hooks related to WooCommerce's My Account section.
 */
class My_Account {
	/**
	 * Last part of URL for profile pictures section.
	 *
	 * URL example: yoursite.com/my-account/profile-pictures
	 *
	 * @var string URL slug.
	 */
	public $my_account_endpoint = 'profile-pictures';

	/**
	 * Action input value to compare against when handling form submit.
	 *
	 * @var string Action name.
	 */
	public $save_pictures_action = 'save_profile_pictures';

	/**
	 * Profile Picture's form nonce.
	 *
	 * @var string Nonce.
	 */
	public $save_pictures_nonce = 'save-profile-pictures-nonce';

	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( "woocommerce_account_{$this->my_account_endpoint}_endpoint", array( $this, 'handle_endpoint' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_link' ) );
		add_action( 'template_redirect', array( $this, 'handle_form_submit' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Adds rewrite endpoint to WordPress.
	 *
	 * @hooked action `init`
	 *
	 * @return void
	 */
	public function add_endpoint() {
		\add_rewrite_endpoint( $this->my_account_endpoint, EP_PAGES );
	}

	/**
	 * Adds menu link to WooCommerce My Account menu.
	 *
	 * @hooked filter `woocommerce_account_menu_items`
	 *
	 * @return array Menu links.
	 */
	public function add_menu_link( $links ) {
		$profile_pictures_link = array(
			"{$this->my_account_endpoint}" => __( 'Profile Pictures', 'bwcpp' ),
		);

		/**
		 * Add 'Profile Pictures' link just before the last link in My Account section.
		 */
		$links = array_slice( $links, 0, -1, true ) + $profile_pictures_link + array_slice( $links, -1, null, true );

		return $links;
	}

	/**
	 * Includes front end template for My Account's section page.
	 *
	 * @hooked action `woocommerce_account_profile-pictures_endpoint`
	 *
	 * @return void
	 */
	public function handle_endpoint() {
		include( get_inc_dir() . '/templates/my-account.php' );
	}

	/**
	 * Handles My Account's page form submit.
	 *
	 * @hooked action `template_redirect`
	 *
	 * @return void
	 */
	public function handle_form_submit() {
		/**
		 * Return early if no nonce is present.
		 */
		if ( ! isset( $_POST[$this->save_pictures_nonce] ) ) {
			return;
		}

		/**
		 * Return early if nonce verification failed.
		 */
		if ( ! \wp_verify_nonce( $_POST[$this->save_pictures_nonce], $this->save_pictures_action ) ) {
			return;
		}

		$picture_files      = $_FILES['pictures'];
		$primary_picture_id = (int) $_POST['primary_picture'];
		$user_pictures      = new User_Pictures();

		/**
		 * If primary picture has been selected and submitted, set it as primary using
		 * `set_primary` method on `User_Pictures` class instance.
		 */
		if ( ! empty( $primary_picture_id ) ) {
			$user_pictures->set_primary( $primary_picture_id );
		}

		/**
		 * If there were pictures added to file input, process upload.
		 */
		if ( ! empty( $picture_files['name'] ) ) {
			$upload = Pictures_Controller::handle_upload( $picture_files, $user_pictures );
		}

		if ( \is_wp_error( $upload ) ) {
			/**
			 * If upload failed, propagate upload method's error and add WooCommerce error notice.
			 */
			\wc_add_notice( $upload->get_error_message(), 'error' );
		} else {
			/**
			 * On success, show success notice.
			 */
			\wc_add_notice( __( 'Pictures saved successfully.', BWCP_TEXT_DOMAIN ) );
		}

		/**
		 * Redirect back to our My Account section and exit.
		 */
		\wp_safe_redirect( \wc_get_page_permalink( 'myaccount' ) . '/' . $this->my_account_endpoint );
		exit();
	}

	/**
	 * Adds minimal CSS to My Account pages.
	 *
	 * @hooked action `wp_enqueue_scripts`
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		if ( ! \is_account_page() ) {
			return;
		}

		\wp_enqueue_style(
			'bwcpp-my-account',
			get_plugin_url() . '/assets/css/my-account.css',
			array(),
			BWCPP_VERSION,
		);
	}
}

new My_Account();
