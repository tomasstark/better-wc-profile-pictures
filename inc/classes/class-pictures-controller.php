<?php
/**
 * Pictures_Controller class file.
 *
 * @package bwcpp
 */

namespace BWCPP;

/**
 * Controller for image operations like upload, gets, removals etc.
 *
 * All methods and vars are static.
 */
class Pictures_Controller {
	/**
	 * Post type used for pictures.
	 *
	 * @var string Post type.
	 */
	public static $post_type = 'attachment';

	/**
	 * Meta key used for making attachments link to user.
	 *
	 * @var string Meta key.
	 */
	public static $attachment_meta_key = '_bwcpp_user_id';

	/**
	 * Requires WordPress core files and handles upload.
	 *
	 * @param array         $files         Array of submitted files. Index of $_FILES global var.
	 * @param User_Pictures $user_pictures Instance of User_Pictures already initiated with appropriate user ID.
	 *
	 * @return WP_Error|boolean On success, returns TRUE. On failure, returns `WP_Error`.
	 */
	public static function handle_upload( $files, $user_pictures ) {
		/**
		 * Require core files for handling uploads on front end.
		 */
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		/**
		 * Get all pictures.
		 */
		$all_pictures   = $user_pictures->get_pictures();

		/**
		 * Get total count of pictures.
		 */
		$pictures_count = count( $all_pictures );

		/**
		 * Get pictures limit current setting.
		 */
		$pictures_limit = (int) self::get_pictures_limit();

		/**
		 * Overrides for `wp_handle_upload` function.
		 */
		$overrides = array(
			'test_form'                => false,
			'test_type'                => true,
			'unique_filename_callback' => '\BWCPP\Helpers\get_unique_filename',
		);

		foreach ( $files['name'] as $key => $name ) {
			/**
			 * If name is empty, don't do anything and move to next iteration.
			 */
			if ( empty( $name ) ) {
				continue;
			}

			/**
			 * In case pictures are not unlimited (limit = 0), compare pictures count
			 * with limit value and return error if reached.
			 */
			if ( 0 !== $pictures_limit && $pictures_count >= $pictures_limit ) {
				return new \WP_Error(
					'file_limit_reached',
					__( 'Sorry! You\'ve reached the maximum number of allowed profile pictures.', BWCPP_TEXT_DOMAIN )
				);
			}

			/**
			 * Form an array for each file.
			 */
			$file = array(
				'name'     => $files['name'][$key],
				'type'     => $files['type'][$key],
				'tmp_name' => $files['tmp_name'][$key],
				'error'    => $files['error'][$key],
				'size'     => $files['size'][$key],
			);

			$upload        = \wp_handle_upload( $file, $overrides );
			$filename      = $upload['file'];
			$filetype      = \wp_check_filetype( basename( $filename ), null );
			$wp_upload_dir = \wp_upload_dir();

			/**
			 * Form attachment's data for `wp_insert_attachment`.
			 */
			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			/**
			 * Insert attachment with parent post set to 0 so it doesn't belong to any post.
			 */
			$attachment_id = \wp_insert_attachment( $attachment, $filename, 0 );

			if ( is_wp_error( $attachment_id ) ) {
				return new \WP_Error(
					'creating_attachment_failed',
					__( 'Sorry! There was something wrong while uploading your pictures. Please try again.', BWCPP_TEXT_DOMAIN )
				);
			}

			/**
			 * Adds meta data to attachment so it's linked to user ID.
			 */
			$user_pictures->add_picture( $attachment_id );

			/**
			 * If this is first picture ever uploaded, mark it as primary by default.
			 */
			if ( 0 === $pictures_count ) {
				$user_pictures->set_primary( $attachment_id );
			}

			$pictures_count++;
		}

		return true;
	}

	/**
	 * Gets all pictures.
	 *
	 * @param int $user_id If not present, returns all pictures. If present, returns pictures for specific user.
	 *
	 * @return array Array of pictures data.
	 */
	public static function get_pictures( $user_id = null ) {
		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'meta_query'     => array(),
		);

		if ( null === $user_id ) {
			$args['meta_query'][] = array(
				'key'     => self::$attachment_meta_key,
				'compare' => 'EXISTS',
			);
		} else {
			$args['meta_query'][] = array(
				'key'     => self::$attachment_meta_key,
				'value'   => $user_id,
				'compare' => '='
			);
		}

		$attachments = \get_posts( $args );
		$pictures    = array();

		foreach ( $attachments as $attachment ) {
			$pictures[] = array(
				'id'   => $attachment->ID,
				'url'  => $attachment->guid,
				'type' => $attachment->post_mime_type,
			);
		}

		return $pictures;
	}

	/**
	 * Assign attachment ID to user ID by updating attachment's post meta.
	 *
	 * @param int $picture_id Attachment ID.
	 * @param int $user_id    User ID.
	 *
	 * @return boolean|void FALSE on failure.
	 */
	public static function assign_picture_to_user( $picture_id, $user_id ) {
		if ( 'attachment' !== \get_post_type( $picture_id ) ) {
			return false;
		}

		\update_post_meta( $picture_id, self::$attachment_meta_key, $user_id );
	}

	/**
	 * Gets current pictures limit using `get_option`.
	 *
	 * @return int Maximum allowed number of profile pictures per user.
	 */
	public static function get_pictures_limit() {
		return (int) \get_option( Main::$limit_pictures_option_name );
	}

	/**
	 * Remove attachment / picture.
	 *
	 * @param int $picture_id Attachment ID.
	 *
	 * @return void
	 */
	public static function remove_picture( $picture_id ) {
		if ( 'attachment' !== \get_post_type( $picture_id ) ) {
			return false;
		}

		if ( ! \metadata_exists( self::$post_type, $picture_id, self::$attachment_meta_key ) ) {
			return false;
		}

		\wp_delete_post( $picture_id, true );
	}

}
