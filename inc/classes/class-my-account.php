<?php
namespace BWCPP;

class My_Account {
	public $my_account_endpoint  = 'profile-pictures';
	public $save_pictures_action = 'save_profile_pictures';
	public $save_pictures_nonce  = 'save-profile-pictures-nonce';

	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_action( "woocommerce_account_{$this->my_account_endpoint}_endpoint", array( $this, 'handle_endpoint' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_link' ) );
		add_action( 'template_redirect', array( $this, 'handle_form_submit' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	public function add_endpoint() {
		\add_rewrite_endpoint( $this->my_account_endpoint, EP_PAGES );
	}

	public function add_menu_link( $links ) {
		$profile_pictures_link = array(
			"{$this->my_account_endpoint}" => __( 'Profile Pictures', BWCPP_TEXT_DOMAIN ),
		);

		/**
		 * Add 'Profile Pictures' link just before the last link in My Account section.
		 */
		$links = array_slice( $links, 0, -1, true ) + $profile_pictures_link + array_slice( $links, -1, null, true );

		return $links;
	}

	public function handle_endpoint() {
		include( get_inc_dir() . '/templates/my-account.php' );
	}

	public function handle_form_submit() {
		if ( ! isset( $_POST[$this->save_pictures_nonce] ) ) {
			return;
		}

		if ( ! \wp_verify_nonce( $_POST[$this->save_pictures_nonce], $this->save_pictures_action ) ) {
			return;
		}

		if ( ! isset( $_POST['save_account_details'] ) ) {
			return;
		}

		$picture_files      = $_FILES['pictures'];
		$primary_picture_id = (int) $_POST['primary_picture'];
		$user_pictures      = new User_Pictures();

		if ( ! empty( $primary_picture_id ) ) {
			$user_pictures->set_primary( $primary_picture_id );
		}

		if ( ! empty( $picture_files['name'] ) ) {
			$upload = Pictures_Controller::handle_upload( $picture_files, $user_pictures );
		}

		if ( \is_wp_error( $upload ) ) {
			\wc_add_notice( $upload->get_error_message(), 'error' );
		} else {
			\wc_add_notice( __( 'Pictures saved successfully.', BWCP_TEXT_DOMAIN ) );
		}

		\wp_safe_redirect( \wc_get_page_permalink( 'myaccount' ) . '/profile-pictures' );
		exit();
	}

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
