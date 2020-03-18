<?php
/**
 * Admin class file.
 *
 * @package bwcpp
 */

namespace BWCPP;

/**
 * Admin class for all hooks related to admin screen.
 */
class Admin {
	/**
	 * Class constructor. Hooks to WordPress.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );
		add_action( 'admin_init', array( $this, 'add_sections' ) );
		add_action( 'admin_menu', array( $this, 'add_plugin_settings_page' ) );
		add_action( 'edit_user_profile', array( $this, 'user_profile_pictures' ) );
	}

	/**
	 * Displays new section with all user's profile pictures
	 * on Edit Profile screen in admin.
	 *
	 * @param int $profileuser User ID.
	 *
	 * @hooked action `edit_user_profile`
	 *
	 * @return void
	 */
	public function user_profile_pictures( $profileuser ) {
		$user               = new User_Pictures( $profileuser->data->ID );
		$pictures           = $user->get_pictures();
		$primary_picture_id = $user->get_primary();
		?>

		<h2><?php _e( 'Profile Pictures', 'bwcpp' ); ?></h2>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th><?php _e( 'Uploaded Profile Pictures', 'bwcpp' ); ?></th>
					<td>
						<?php foreach ( $pictures as $picture ) : ?>
							<img src="<?php echo $picture['url']; ?>" width="96" style="margin-right: 5px;<?php echo ( $picture['id'] === $primary_picture_id ) ? ' border: 3px solid red;' : ''; ?>">
						<?php endforeach; ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Displays admin notices.
	 *
	 * @hooked action `admin_notices`
	 *
	 * @return void
	 */
	public function admin_notices() {
		/**
		 * Display admin notice if WooCommerce is not activated.
		 */
		if ( ! \BWCPP\Helpers\is_woocommerce() ) {
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					printf(
						/* translators: %s is replaced with URL to Plugins admin screen */
						__( 'Better WooCommerce Profile Pictures requires WooCommerce plugin to be activated. Please <a href="%s">activate WooCommerce</a>.', 'bwcpp' ),
						\plugins_url(),
					);
					?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Adds admin page for plugin settings.
	 *
	 * @hooked action `admin_menu`
	 *
	 * @return void
	 */
	public function add_plugin_settings_page() {
		add_options_page(
			__( 'Better WC Profile Pictures', 'bwcpp' ),
			__( 'Better WC Profile Pictures', 'bwcpp' ),
			'manage_options',
			'bwcpp_settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Callback for `add_options_page` function when adding settings page.
	 * Includes markup for this settings screen.
	 *
	 * @return void
	 */
	public function render_settings_page() {
		include( get_inc_dir() . '/templates/admin-settings.php' );
	}

	/**
	 * Adds settings section and settings field on settings screen using Settings API.
	 *
	 * @hooked action `admin_init`
	 *
	 * @return void
	 */
	public function add_sections() {
		/**
		 * Add generic section without label as it's requirement for `add_settings_field`.
		 */
		\add_settings_section(
			'bwcpp_settings_general',
			'',
			'',
			'bwcpp_settings'
		);

		/**
		 * Add limit pictures setting.
		 *
		 * Pass through `intval` for sanitization.
		 * Default value set to `20`.
		 */
		\register_setting(
			'bwcpp_settings',
			Main::$limit_pictures_option_name,
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'intval',
				'default'           => 20,
			),
		);

		/**
		 * Adds settings field for limit pictures setting.
		 */
		\add_settings_field(
			Main::$limit_pictures_option_name,
			__( 'Max pictures per user', 'bwcpp' ),
			array( $this, 'render_max_control' ),
			'bwcpp_settings',
			'bwcpp_settings_general',
			array(
				'label_for'   => Main::$limit_pictures_option_name,
				'id'          => Main::$limit_pictures_option_name,
				'description' => __( 'Enter 0 for unlimited.', 'bwcpp' ),
			)
		);
	}

	/**
	 * Callback for `add_settings_field` to render markup for added setting.
	 *
	 * @param array $options Array of additional options passed in `add_settings_field`.
	 *
	 * @return void
	 */
	public function render_max_control( $options ) {
		?>
		<input type="number" name="<?php echo $options['id']; ?>" value="<?php echo get_option( $options['id'] ); ?>" min="0" max="500" required>
		<p class="description"><?php echo $options['description']; ?></p>
		<?php
	}

	/**
	 * Adds meta box to WooCommerce order admin screen.
	 *
	 * @hooked action `add_meta_boxes`
	 *
	 * @return void
	 */
	public function add_order_meta_box() {
		\add_meta_box(
			'bwcpp_profile_picture_box',
			__( 'Profile Picture', 'bwcpp' ),
			array( $this, 'render_order_meta_box' ),
			'shop_order',
			'side',
			'default'
		);
	}

	/**
	 * Callback for `add_meta_box` used to render order meta box we're adding
	 * in `add_order_meta_box` method.
	 *
	 * @return void
	 */
	public function render_order_meta_box() {
		global $post_id;

		$order           = \wc_get_order( $post_id );
		$profile_picture = \get_post_meta( $post_id, '_bwcpp_picture_id', true );
		?>

		<?php if ( ! empty( $profile_picture ) ) : ?>

			<?php $picture = wp_get_attachment_image_src( $profile_picture ); ?>
			<img src="<?php echo $picture[0]; ?>" alt="" width="100%">

		<?php else : ?>

			<?php _e( 'User didn\'t upload any image.', 'bwcpp' ); ?>

		<?php endif; ?>

		<?php if ( $order->get_user_id() ) : ?>
			<a href="<?php echo get_edit_user_link( $order->get_user_id() ); ?>"><?php _e( 'View customer profile', 'bwcpp' ); ?> &rarr;</a>
		<?php endif; ?>

		<?php
	}

}

new Admin();
