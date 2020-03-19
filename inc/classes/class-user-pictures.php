<?php
/**
 * User_Pictures class file.
 *
 * @package bwcpp
 */

namespace BWCPP;

/**
 * User_Pictures class.
 *
 * By default, initiates with current user ID and handles all further operations
 * with that in mind. Accepts custom user ID allowing to create multiple instances of class.
 */
class User_Pictures {
	/**
	 * User ID.
	 *
	 * @var int User ID.
	 */
	public $user_id;

	/**
	 * User meta key for storing primary image ID information.
	 *
	 * @var string Meta key.
	 */
	public $primary_meta_key = '_bwcpp_primary_id';

	/**
	 * Constructor for `User_Pictures`. Sets user ID.
	 *
	 * @param int $user_id Optional parameter for user ID. Defaults to current user ID if not specified.
	 */
	public function __construct( $user_id = null ) {
		if ( is_null( $user_id ) ) {
			$user_id = \get_current_user_id();
		}

		$this->user_id = $user_id;
	}

	/**
	 * Set primary picture ID.
	 *
	 * @param int $post_id Attachment ID.
	 *
	 * @return int|bool True on success, False on failure. Meta ID if key didn't exist.
	 */
	public function set_primary( $post_id ) {
		return update_user_meta( $this->user_id, $this->primary_meta_key, $post_id );
	}

	/**
	 * Gets primary picture ID.
	 *
	 * @return int Attachment ID.
	 */
	public function get_primary() {
		return (int) get_user_meta( $this->user_id, $this->primary_meta_key, true );
	}

	/**
	 * Gets primary picture's URL.
	 *
	 * @return string URL.
	 */
	public function get_primary_src() {
		$primary_id = $this->get_primary();
		$src        = \wp_get_attachment_image_src( $primary_id );

		return $src[0];
	}

	/**
	 * Get all user pictures.
	 *
	 * Calls `get_pictures` of `Pictures_Controller` instance.
	 *
	 * @return array
	 */
	public function get_pictures() {
		return Pictures_Controller::get_pictures( $this->user_id );
	}

	/**
	 * Mark ownership of picture.
	 *
	 * @param int $picture_id Attachment ID.
	 *
	 * Calls `assign_picture_to_user` of `Pictures_Controller` instance.
	 */
	public function add_picture( $picture_id ) {
		return Pictures_Controller::assign_picture_to_user( $picture_id, $this->user_id );
	}

}
