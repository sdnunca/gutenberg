<?php
/**
 * Unit tests covering WP_REST_Pattern_Directory_Controller functionality.
 *
 * @package WordPress
 * @subpackage REST API
 */

/**
 * @group restapi
 * @group pattern-directory
 */
class WP_REST_Pattern_Directory_Controller_Test extends WP_Test_REST_Controller_Testcase {

	/**
	 * Contributor user id.
	 *
	 * @since 5.8.0
	 *
	 * @var int
	 */
	protected static $contributor_id;

	/**
	 * An instance of WP_REST_Pattern_Directory_Controller class.
	 *
	 * @since 6.0.0
	 *
	 * @var WP_REST_Pattern_Directory_Controller
	 */
	private static $controller;

	/**
	 * List of URLs captured.
	 *
	 * @since 6.2.0
	 *
	 * @var string[]
	 */
	protected static $http_request_urls;

	/**
	 * Set up class test fixtures.
	 *
	 * @param WP_UnitTest_Factory $factory WordPress unit test factory.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$contributor_id = $factory->user->create(
			array(
				'role' => 'contributor',
			)
		);

		self::$http_request_urls = array();

		static::$controller = new WP_REST_Pattern_Directory_Controller();
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$contributor_id );
	}

	/**
	 * Clear the captured request URLs after each test.
	 */
	public function tear_down() {
		self::$http_request_urls = array();
		parent::tear_down();
	}

	/**
	 * Tests if the provided query args are passed through to the wp.org API.
	 *
	 * @dataProvider data_get_items_query_args
	 *
	 * @covers WP_REST_Pattern_Directory_Controller::get_items
	 *
	 * @since 6.2.0
	 *
	 * @param string $param    Query parameter name (ex, page).
	 * @param mixed  $value    Query value to test.
	 * @param bool   $is_error Whether this value should error or not.
	 * @param mixed  $expected Expected value (or expected error code).
	 */
	public function test_get_items_query_args( $param, $value, $is_error, $expected ) {
		wp_set_current_user( self::$contributor_id );
		self::capture_http_urls();

		$request = new WP_REST_Request( 'GET', '/wp/v2/pattern-directory/patterns' );
		if ( $value ) {
			$request->set_query_params( array( $param => $value ) );
		}

		$response = rest_do_request( $request );
		$data     = $response->get_data();
		if ( $is_error ) {
			$this->assertSame( $expected, $data['code'] );
			$this->assertStringContainsString( $param, $data['message'] );
		} else {
			$this->assertCount( 1, self::$http_request_urls );
			$this->assertStringContainsString( $param . '=' . $expected, self::$http_request_urls[0] );
		}
	}

	/**
	 * @since 6.2.0
	 */
	public function data_get_items_query_args() {
		return array(
			'per_page default'   => array( 'per_page', false, false, 100 ),
			'per_page custom-1'  => array( 'per_page', 5, false, 5 ),
			'per_page custom-2'  => array( 'per_page', 50, false, 50 ),
			'per_page invalid-1' => array( 'per_page', 200, true, 'rest_invalid_param' ),
			'per_page invalid-2' => array( 'per_page', 'abc', true, 'rest_invalid_param' ),

			'page default'       => array( 'page', false, false, 1 ),
			'page custom'        => array( 'page', 5, false, 5 ),
			'page invalid'       => array( 'page', 'abc', true, 'rest_invalid_param' ),

			'offset custom'      => array( 'offset', 5, false, 5 ),
			'offset invalid-1'   => array( 'offset', 'abc', true, 'rest_invalid_param' ),

			'order default'      => array( 'order', false, false, 'desc' ),
			'order custom'       => array( 'order', 'asc', false, 'asc' ),
			'order invalid-1'    => array( 'order', 10, true, 'rest_invalid_param' ),
			'order invalid-2'    => array( 'order', 'fake', true, 'rest_invalid_param' ),

			'orderby default'    => array( 'orderby', false, false, 'date' ),
			'orderby custom-1'   => array( 'orderby', 'title', false, 'title' ),
			'orderby custom-2'   => array( 'orderby', 'date', false, 'date' ),
			'orderby custom-3'   => array( 'orderby', 'favorite_count', false, 'favorite_count' ),
			'orderby invalid-1'  => array( 'orderby', 10, true, 'rest_invalid_param' ),
			'orderby invalid-2'  => array( 'orderby', 'fake', true, 'rest_invalid_param' ),
		);
	}

	/**
	 * Attach a filter to capture requested wp.org URL.
	 *
	 * @since 6.2.0
	 */
	private static function capture_http_urls() {
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				if ( 'api.wordpress.org' !== wp_parse_url( $url, PHP_URL_HOST ) ) {
					return $preempt;
				}

				self::$http_request_urls[] = $url;

				// Return a response to prevent external API request.
				$response = array(
					'headers'  => array(),
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
					'body'     => '[]',
					'cookies'  => array(),
					'filename' => null,
				);

				return $response;
			},
			10,
			3
		);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_register_routes() {
		// Covered by the core test.
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_context_param() {
		// Covered by the core test.
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_get_items() {
		// Covered by the core test.
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_prepare_item() {
		// Covered by the core test.
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_get_item() {
		// Controller does not implement get_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_create_item() {
		// Controller does not implement create_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_update_item() {
		// Controller does not implement update_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_delete_item() {
		// Controller does not implement delete_item().
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function test_get_item_schema() {
		// The controller's schema is hardcoded, so tests would not be meaningful.
	}

}
