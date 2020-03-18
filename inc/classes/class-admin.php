<?php
namespace BWCPP;

class Admin {
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );
		add_action( 'admin_init', array( $this, 'add_sections' ) );
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
		include( get_inc_dir() . '/templates/admin-settings.php' );
	}

	public function add_sections() {
		\add_settings_section(
			'bwcpp_settings_general',
			'',
			'',
			'bwcpp_settings'
		);

		\register_setting(
			'bwcpp_settings',
			Main::$limit_pictures_option_name,
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'intval',
				'default'           => 20,
			),
		);

		\add_settings_field(
			Main::$limit_pictures_option_name,
			__( 'Max pictures per user', BWCPP_TEXT_DOMAIN ),
			array( $this, 'render_max_control' ),
			'bwcpp_settings',
			'bwcpp_settings_general',
			array(
				'label_for' => Main::$limit_pictures_option_name,
				'id' => Main::$limit_pictures_option_name,
				'description' => __( 'Enter 0 for unlimited.', BWCPP_TEXT_DOMAIN ),
			)
		);
	}

	public function render_max_control( $options ) {
		?>
		<input type="number" name="<?php echo $options['id']; ?>" value="<?php echo get_option( $options['id'] ); ?>" min="0" max="500" required>
		<p class="description"><?php echo $options['description']; ?></p>
		<?php
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
