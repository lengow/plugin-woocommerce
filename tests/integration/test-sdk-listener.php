<?php
declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;
use Lengow\Sdk\Resource\Api;

class Test_Sdk_Listener extends WP_UnitTestCase
{
	use Trait_Mock_Client;

	/**
	 * @throws Exception
	 */
	public function test_will_save_token() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();

		$this->mock_client->on(
			new RequestMatcher( Api\Cms::API ),
			new Response( 200, [], file_get_contents(
				$this->sdk_mock_dir . 'cms-list.json'
			) )
		);

		$this->mock_client->on(
			new RequestMatcher( Api\Restriction::API ),
			new Response( 200, [], file_get_contents(
				$this->sdk_mock_dir . 'restriction-restrictions.json'
			) )
		);

		$token_before = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN );
		$sdk = Lengow_Container::instance()->get( Lengow\Sdk\Sdk::class );

		$sdk->restriction()->restrictions();
		$sdk->cms()->list();

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
				$this->sdk_mock_dir . 'restriction-restrictions.json'
			) )
		);
		$sdk->restriction()->restrictions();
	}
}
