<?php
declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;
use Lengow\Sdk\Resource\Api;

/**
 * Class TestConnectCms
 *
 * @package Lengow_Woocommerce
 */

class Test_Connect_Cms extends WP_UnitTestCase
{
	use Trait_Mock_Client;
	use Trait_Fixtures;

	/**
	 * Set up the test environment.
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->load_products();
	}

	/**
	 * @throws Exception
	 */
	function test() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_client->on(
			new RequestMatcher( Api\Cms::API, null, [ 'GET' ] ),
			new Response( 200, [], file_get_contents(
				$this->sdk_mock_dir . 'cms-list.json'
			) )
		);

		$this->mock_client->on(
			new RequestMatcher( Api\Cms::API, null, [ 'PUT', 'POST' ] ),
			new Response( 200, [], file_get_contents(
				$this->sdk_mock_dir . 'cms.json'
			) )
		);

		$this->mock_client->on(
			new RequestMatcher( Api\Restriction::API ),
			new Response( 200, [], file_get_contents(
				$this->sdk_mock_dir . 'restriction-restrictions.json'
			) )
		);

		$post_data = [
			'do_action' => 'connect_cms',
			'access_token' => '***',
			'secret' => '***',
		];

		foreach ( $post_data as $k => $v ) {
			$_POST[ $k ] = $v;
		}

		ob_start();
		Lengow_Admin_Connection::post_process();
		$output = ob_get_clean();

		foreach ( $post_data as $k => $v ) {
			unset( $_POST[ $k ] );
		}

		$this->assertEquals(
			[ 123, '***', '***' ],
			Lengow_Configuration::get_access_id()
		);

		$expected = esc_html( ( new Lengow_Translation() )->t( 'connection.cms.success_title' ) );
		$this->assertMatchesRegularExpression(
			'/' . preg_quote( $expected ) . '/',
			$output,
			'Output should contain a success message'
		);
	}
}
