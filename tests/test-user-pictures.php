<?php
/**
 * User_Pictures class test file.
 *
 * @package bwcpp
 */

class User_Pictures_Test extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		$this->instance = new \BWCPP\User_Pictures( 1 );
	}

	public function test_get_primary_returns_123() {
		$this->instance->set_primary( 123 );

		$this->assertEquals( 123, $this->instance->get_primary() );
	}

	public function test_set_primary_sets_new_post_id() {
		$this->instance->set_primary( 456 );

		$this->assertEquals( 456, $this->instance->get_primary() );
	}

	public function test_get_primary_src_returns_image_src() {
		$wp_upload_dir = wp_upload_dir();
		$filename      = date( 'Y' ) . '/' . date( 'm' ) . '/test-file.jpg';

		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => 'image/jpeg',
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = \wp_insert_attachment( $attachment, $filename, 0 );

		$this->instance->set_primary( $attachment_id );

		$src = $this->instance->get_primary_src();

		$this->assertEquals( $src, $attachment['guid'] );
	}

}
