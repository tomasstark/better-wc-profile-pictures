<?php
namespace BWCPP;

class Main {
	public function __construct() {
		$this->hook();
	}

	public function hook() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		if ( ! \BWCPP\Helpers\is_woocommerce() ) {
			return;
		}

		require_once( get_inc_dir() . '/classes/class-my-account.php' );
	}

	public function admin_notices() {
		if ( ! \BWCPP\Helpers\is_woocommerce() ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					printf(
						__( 'Better WooCommerce Profile Pictures requires WooCommerce plugin to be activated. Please <a href="%s">activate WooCommerce</a>.', BWCPP_TEXT_DOMAIN ),
						\plugins_url(),
					);
					?>
				</p>
			</div>
			<?php
		}
	}

}
