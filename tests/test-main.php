<?php
/**
 * Main class test file.
 *
 * @package bwcpp
 */

class Main_Test extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		global $wp_rest_server;

		$this->server   = new \WP_REST_Server;
		$this->instance = new \BWCPP\Main();
	}

	public function test_rest_endpoint_is_working_and_callable() {
		$routes = $this->server->get_routes();

		foreach ( $routes as $route => $route_config ) {
			if ( 0 === strpos( $this->instance->rest_route_base . '/pictures', $route ) ) {
				$this->assertTrue( is_array( $route_config ) );

				foreach ( $route_config as $i => $endpoint ) {
					$this->assertArrayHasKey( 'callback', $endpoint );
					$this->assertArrayHasKey( 0, $endpoint['callback'], get_class( $this->instance ) );
					$this->assertArrayHasKey( 1, $endpoint['callback'], get_class( $this->instance ) );
					$this->assertTrue( is_callable( array( $endpoint['callback'][0], $endpoint['callback'][1] ) ) );
				}
			}
		}
	}
}
