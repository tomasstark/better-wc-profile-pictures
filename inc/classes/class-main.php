<?php
namespace BWCPP;

class Main {
	public $post_type = 'bwcpp';

	public function __construct() {
		$this->hook();
	}

	public function hook() {
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

}
