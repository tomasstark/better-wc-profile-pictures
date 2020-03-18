<?php
namespace BWCPP;

class Admin {
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_settings_page' ) );
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

	public function add_plugin_settings_page() {
		add_options_page(
			__( 'Better WC Profile Pictures', BWCPP_TEXT_DOMAIN ),
			__( 'Better WC Profile Pictures', BWCPP_TEXT_DOMAIN ),
			'manage_options',
			'bwcpp_settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page() {
		// noop
	}

	public function add_order_meta_box() {
		\add_meta_box(
			'bwcpp_profile_picture_box',
			__( 'Profile Picture', BWCPP_TEXT_DOMAIN ),
			array( $this, 'render_order_meta_box' ),
			'shop_order',
			'side',
			'default'
		);
	}

	public function render_order_meta_box() {
		global $post_id;

		$order           = \wc_get_order( $post_id );
		$profile_picture = \get_post_meta( $post_id, '_bwcpp_picture_id', true );

		if ( ! empty( $profile_picture ) ) {
			$picture = wp_get_attachment_image_src( $profile_picture );

			?>
			<img src="<?php echo $picture[0]; ?>" alt="" width="100%">

			<a href="<?php echo get_edit_user_link( $order->get_user_id() ); ?>">View user profile</a>
			<?php
		} else {
			?>
			No image
			<?php
		}
	}

}

new Admin();
