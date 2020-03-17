<?php
namespace BWCPP;

class My_Account {
	public $my_account_endpoint  = 'profile-pictures';

	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoint' ) );

		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_link' ) );
		add_action( "woocommerce_account_{$this->my_account_endpoint}_endpoint", array( $this, 'handle_endpoint' ) );
	}

	public function add_endpoint() {
		add_rewrite_endpoint( $this->my_account_endpoint, EP_PAGES );
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
		?>Hey 👋<?php
	}
}


new My_Account();
