<?php

use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;
use Lengow\Sdk\Resource\Api;

/**
 * Class TestSdkListener
 *
 * @package Lengow_Woocommerce
 */

class TestSdkListener extends WP_UnitTestCase
{
	use MockClientTrait;

	/**
	 * @throws Exception
	 */
	function test_will_save_token() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();

		$this->mock_client->on(
			new RequestMatcher( Api\Cms::API ),
			new Response( 200, [], file_get_contents(
				$this->mock_dir . 'cms-list.json'
			) )
		);

		$this->mock_client->on(
			new RequestMatcher( Api\Restriction::API ),
			new Response( 200, [], file_get_contents(
				$this->mock_dir . 'restriction-restrictions.json'
			) )
		);

		$token_before = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN );

		Lengow::sdk()->restriction()->restrictions();
		Lengow::sdk()->cms()->list();

		$token_after = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN );
		$token_expire_at = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN_EXPIRE_AT );
		$this->assertEmpty( $token_before );
		$this->assertEquals( '********-****-****-****-************', $token_after );
		$this->assertGreaterThan( 0, $token_expire_at );

		// expect the client to reuse the token
		$this->init_with_mock_client( false );
		$this->mock_client->addException( new Exception('Unexpected request') );

		$this->mock_client->on(
			new RequestMatcher( Api\Restriction::API ),
			new Response( 200, [], file_get_contents(
				$this->mock_dir . 'restriction-restrictions.json'
			) )
		);
		Lengow::sdk()->restriction()->restrictions();
	}
}
