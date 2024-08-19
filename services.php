<?php
/**
 * Lengow
 *
 * Copyright 2024 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category  Lengow
 * @package   lengow-woocommerce
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2024 Lengow SAS
 * @license   https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

declare(strict_types=1);

return [
	Lengow\Sdk\Sdk::class => function () {
		$api_key    = Lengow_Configuration::get( Lengow_Configuration::ACCESS_TOKEN );
		$api_secret = Lengow_Configuration::get( Lengow_Configuration::SECRET );
		$auth_token = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN );
		$expire_at  = (int) Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN_EXPIRE_AT );
		$account_id = (int) Lengow_Configuration::get( Lengow_Configuration::ACCOUNT_ID );
		$factory    = new Lengow\Sdk\ClientFactory();
		if ( $api_key && $api_secret ) {
			$factory->withCredentials( $api_key, $api_secret );
			if ( $auth_token && $expire_at && $account_id ) {
				$factory->withAuthorization( $auth_token, $expire_at, $account_id );
			}
		}

		if ( 'preprod' === Lengow_Configuration::get_plugin_environment() ) {
			$factory->withApiUrl( Lengow\Sdk\ClientFactory::API_URL_PREPROD );
		}

		$client   = $factory->getClient();
		$listener = new Lengow_Sdk_Listener();
		$client->addBeforeSendRequestListener( $listener )
		       ->addAfterSendRequestListener( $listener )
		       ->getAuthenticator()
		       ->addAfterRequestTokenListener( $listener );

		return new Lengow\Sdk\Sdk( $client );
	}
];
