<?php
namespace BWCPP;

class User_Pictures {
	public $user_id;
	public $primary_meta_key = '_bwcpp_primary_id';

	public function __construct( $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = \get_current_user_id();
		}

		$this->user_id = $user_id;
	}

	public function set_primary( $post_id ) {
		update_user_meta( $this->user_id, $this->primary_meta_key, $post_id );
	}

	public function get_primary() {
		return get_user_meta( $this->user_id, $this->primary_meta_key, true );
	}

	public function get_primary_src() {
		$primary_id = $this->get_primary();

		return \wp_get_attachment_image_src( $primary_id );
	}

	public function get_pictures() {
		// noop
	}

	public function add_picture( $picture_id ) {
		// noop
	}

}
