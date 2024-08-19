<?php
declare( strict_types=1 );

use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;
use Http\Mock\Client;
use Lengow\Sdk\ClientFactory;
use Lengow\Sdk\Resource\Api;
use Lengow\Sdk\Sdk;

trait Trait_Mock_Client
{
	public string $sdk_mock_dir = __DIR__ . '/../../vendor/lengow/api-php-sdk/tests/Resource/mock/';
	public string $mock_dir = __DIR__ . '/../mock/';

	protected Client $mock_client;

	public function init_with_mock_client( bool $without_token = true ): void {
		$this->mock_client = new Client();

		Lengow_Configuration::update_value( Lengow_Configuration::ACCESS_TOKEN, '***' );
		Lengow_Configuration::update_value( Lengow_Configuration::SECRET, '***' );
		Lengow_Configuration::update_value( Lengow_Configuration::ACCOUNT_ID, '123' );
		if ( $without_token ) {
			Lengow_Configuration::update_value( Lengow_Configuration::AUTHORIZATION_TOKEN, null );
			Lengow_Configuration::update_value( Lengow_Configuration::AUTHORIZATION_TOKEN_EXPIRE_AT, null );
		}

		$factory = Lengow_Container::instance();
		$factory->bind( Sdk::class, function () {
			$api_key    = Lengow_Configuration::get( Lengow_Configuration::ACCESS_TOKEN );
			$api_secret = Lengow_Configuration::get( Lengow_Configuration::SECRET );
			$auth_token = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN );
			$expire_at  = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN_EXPIRE_AT );
			$account_id = Lengow_Configuration::get( Lengow_Configuration::ACCOUNT_ID );
			$factory    = new ClientFactory($this->mock_client);
			if ( $api_key && $api_secret ) {
				$factory->withCredentials( $api_key, $api_secret );
				if ( $auth_token && $expire_at && $account_id ) {
					$factory->withAuthorization( $auth_token, $expire_at, (int) $account_id );
				}
			}

			if ( 'preprod' === Lengow_Configuration::get_plugin_environment() ) {
				$factory->withApiUrl( ClientFactory::API_URL_PREPROD );
			}

			$client   = $factory->getClient();
			$listener = new Lengow_Sdk_Listener();
			$client->addBeforeSendRequestListener( $listener )
			       ->addAfterSendRequestListener( $listener )
			       ->getAuthenticator()
			       ->addAfterRequestTokenListener( $listener );

			return new Lengow\Sdk\Sdk( $client );
		}, true);
	}

	public function mock_on_access_token() {
		$this->mock_client->on(
			new RequestMatcher( Api\Access::API ),
			new Response( 200, [], file_get_contents(
				$this->sdk_mock_dir . 'access-gettoken.json'
			) )
		);
	}
}
