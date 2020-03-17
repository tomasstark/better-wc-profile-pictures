<?php
namespace BWCPP;

class Main {
	public $post_type = 'bwcpp';

	public function __construct() {
		$this->hook();
	}

	public function hook() {
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		if ( \BWCPP\Helpers\is_woocommerce() ) {
			return;
		}

		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	public function register_post_type() {
		$args = array(
			'label'                 => __( 'Better WooCommerce Profile Picture', BWCPP_TEXT_DOMAIN ),
			'supports'              => array( 'title', 'thumbnail' ),
			'taxonomies'            => array(),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'capability_type'       => 'page',
		);

		register_post_type( $this->post_type, $args );
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
