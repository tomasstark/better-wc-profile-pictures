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
	 * @return WP_Error|array On success, returns array of attachment IDs. On failure, returns `WP_Error`.
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
		$all_pictures = $user_pictures->get_pictures();

		/**
		 * Get total count of pictures.
		 */
		$pictures_count = count( $all_pictures );

		/**
		 * Get pictures limit current setting.
		 */
		$pictures_limit = (int) self::get_pictures_limit();

		/**
		 * Only allow image file types
		 */
		$allowed_file_types = array(
			'jpg'  => 'image/jpg',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
		);

		/**
		 * Overrides for `wp_handle_upload` function.
		 */
		$overrides = array(
			'test_form'                => false,
			'test_size'                => true,
			'test_type'                => true,
			'mimes'                    => $allowed_file_types,
			'unique_filename_callback' => '\BWCPP\Helpers\get_unique_filename',
		);

		/**
		 * Create an array to be returned by this method on success.
		 */
		$response = array();

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
				if ( ! isset( $response['limit_reached'] ) ) {
					$response['limit_reached'] = new \WP_Error(
						'file_limit_reached',
						__( 'Some files couldn\'t be uploaded, sorry! You\'ve reached the maximum number of allowed profile pictures.', 'bwcpp' )
					);
				}

				continue;
			}

			/**
			 * Form an array for each file.
			 */
			$file = array(
				'name'     => $files['name'][ $key ],
				'type'     => $files['type'][ $key ],
				'tmp_name' => $files['tmp_name'][ $key ],
				'error'    => $files['error'][ $key ],
				'size'     => $files['size'][ $key ],
			);

			if ( $file['size'] > 1 * MB_IN_BYTES ) {
				if ( ! isset( $response['too_big'] ) ) {
					$response['too_big'] = new \WP_Error(
						'file_too_big',
						__( 'Some files couldn\'t be uploaded, sorry! The maximum allowed file size is 1 MB.', 'bwcpp' )
					);
				}

				continue;
			}

			$upload = \wp_handle_upload( $file, $overrides );

			if ( isset( $upload['error'] ) && ! empty( $upload['error'] ) ) {
				if ( ! isset( $response['wrong_type'] ) ) {
					$response['wrong_type'] = new \WP_Error(
						'file_not_permitted',
						__( 'Some files couldn\'t be uploaded, sorry! The only supported file types are .jpg, .jpeg, and .png.', 'bwcpp' )
					);
				}

				continue;
			}

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
				'post_status'    => 'inherit',
			);

			/**
			 * Insert attachment with parent post set to 0 so it doesn't belong to any post.
			*/
			$attachment_id = \wp_insert_attachment( $attachment, $filename, 0 );

			if ( is_wp_error( $attachment_id ) ) {
				$response[] = new \WP_Error(
					'creating_attachment_failed',
					__( 'Some files couldn\'t be uploaded, sorry! An unexpected error occurred. Please try again.', 'bwcpp' )
				);

				continue;
			}

			/**
			 * Add to response array.
			 */
			$response[] = $attachment_id;

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

		return $response;
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
			'post_type'      => BWCPP_PICTURE_POST_TYPE,
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
				'compare' => '=',
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
		if ( BWCPP_PICTURE_POST_TYPE !== \get_post_type( $picture_id ) ) {
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
		return (int) \get_option( BWCPP_LIMIT_OPTION_NAME );
	}

	/**
	 * Remove attachment / picture.
	 *
	 * @param int $picture_id Attachment ID.
	 *
	 * @return boolean|void Returns false if ID does not match any attachment or if attachment is not linked to any user.
	 */
	public static function remove_picture( $picture_id ) {
		if ( BWCPP_PICTURE_POST_TYPE !== \get_post_type( $picture_id ) ) {
			return false;
		}

		if ( ! \metadata_exists( 'post', $picture_id, self::$attachment_meta_key ) ) {
			return false;
		}

		\wp_delete_attachment( $picture_id, true );
	}

}
