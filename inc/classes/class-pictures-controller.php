<?php
namespace BWCPP;

class Pictures_Controller {
	public static $post_type = 'attachment';
	public static $attachment_meta_key = '_bwcpp_user_id';

	public static function handle_upload( $files ) {
		// noop
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

}
