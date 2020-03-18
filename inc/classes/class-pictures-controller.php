<?php
namespace BWCPP;

class Pictures_Controller {
	public static $post_type = 'attachment';
	public static $attachment_meta_key = '_bwcpp_user_id';

	public static function handle_upload( $files, $user_pictures ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$all_pictures   = $user_pictures->get_pictures();
		$pictures_count = count( $all_pictures );
		$pictures_limit = (int) self::get_pictures_limit();

		$overrides = array(
			'test_form' => false,
			'test_type' => true,
			'unique_filename_callback' => '\BWCPP\Helpers\get_unique_filename',
		);

		foreach ( $files['name'] as $key => $name ) {
			if ( empty( $name ) ) {
				continue;
			}

			if ( 0 !== $pictures_limit && $pictures_count >= $pictures_limit ) {
				return new \WP_Error( 'file_limit_reached', __( 'Sorry! You\'ve reached the maximum number of allowed profile pictures.', BWCPP_TEXT_DOMAIN ) );
			}

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

			$attachment = array(
				'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
				'post_mime_type' => $filetype['type'],
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			);

			$attachment_id = \wp_insert_attachment( $attachment, $filename, 0 );

			$user_pictures->add_picture( $attachment_id );

			if ( 0 === $pictures_count ) {
				$user_pictures->set_primary( $attachment_id );
			}

			$pictures_count++;
		}

		return true;
	}

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

	public static function assign_picture_to_user( $picture_id, $user_id ) {
		if ( 'attachment' !== \get_post_type( $picture_id ) ) {
			return false;
		}

		\update_post_meta( $picture_id, self::$attachment_meta_key, $user_id );
	}

	public static function get_pictures_limit() {
		return \get_option( Main::$limit_pictures_option_name );
	}

}
