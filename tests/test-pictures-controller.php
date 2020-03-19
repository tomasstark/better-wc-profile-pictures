<?php
/**
 * Pictures_Controller class test file.
 *
 * @package bwcpp
 */

class Pictures_Controller_Test extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		$this->instance = new \BWCPP\Pictures_Controller();

		add_option( BWCPP_LIMIT_OPTION_NAME, 20 );

		$wp_upload_dir = wp_upload_dir();
		$filename = date( 'Y' ) . '/' . date( 'm' ) . '/test-file.jpg';

		$this->attachment_args = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => 'image/jpeg',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$this->attachment_id = \wp_insert_attachment( $this->attachment_args, $filename, 0 );
	}

	public function test_get_pictures_limit_returns_option_value() {
		$this->assertEquals( 20, $this->instance::get_pictures_limit() );
	}

	public function test_assign_picture_to_user_returns_false_if_not_attachment() {
		$this->assertFalse( $this->instance->assign_picture_to_user( 1, null ) );
	}

	public function test_remove_picture_returns_false_if_not_attachment() {
		$this->assertFalse( $this->instance->remove_picture( 1 ) );
	}

	public function test_remove_picture_returns_false_if_attachment_meta_is_missing() {
		$this->assertFalse( $this->instance->remove_picture( $this->attachment_id ) );
	}

	public function test_remove_picture_removes_attachment_if_valid() {
		update_post_meta( $this->attachment_id, $this->instance::$attachment_meta_key, 1 );

		$attachments = get_posts(
			array(
				'posts_per_page' => 1,
				'post_type'      => 'attachment',
			)
		);

		$this->instance::remove_picture( $this->attachment_id );

		$attachments = get_posts(
			array(
				'posts_per_page' => 1,
				'post_type'      => 'attachment',
			)
		);

		$this->assertEquals( 0, count( $attachments ) );
	}
}
