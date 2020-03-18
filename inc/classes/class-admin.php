<?php
namespace BWCPP;

class Admin {
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
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

new Admin();
