<?php
/**
 * All components to generate logs
 *
 * Copyright 2017 Lengow SAS
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
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

use Lengow\Sdk\Client\Listener\AfterRequestTokenInterface;
use Lengow\Sdk\Client\Listener\AfterSendRequestInterface;
use Lengow\Sdk\Client\Listener\BeforeSendRequestInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Sdk_Listener Class.
 */
class Lengow_Sdk_Listener implements AfterRequestTokenInterface, BeforeSendRequestInterface, AfterSendRequestInterface
{

	/**
	 * @param string $token
	 * @param int $expireAt
	 * @param int $accountId
	 *
	 * @return void
	 */
	public function afterRequestToken( string $token, int $expireAt, int $accountId): void
	{
		Lengow_Configuration::update_value( Lengow_Configuration::AUTHORIZATION_TOKEN, $token );
		Lengow_Configuration::update_value( Lengow_Configuration::AUTHORIZATION_TOKEN_EXPIRE_AT, $expireAt );
	}

	/**
	 * @param ResponseInterface $response
	 *
	 * @return ResponseInterface
	 */
	public function afterSendRequest( ResponseInterface $response ): ResponseInterface
	{
		$logger = Lengow_Main::get_log_instance();
		$logger->write(
			'sdk.response',
			$response->getStatusCode() . ' ' . $this->anonymize((string)$response->getBody())
		);

		return $response;
	}

	/**
	 * @param RequestInterface $request
	 *
	 * @return RequestInterface
	 */
	public function beforeSendRequest( RequestInterface $request ): RequestInterface
	{
		$logger = Lengow_Main::get_log_instance();
		$logger->write(
			'sdk.request',
			$request->getMethod() . ' ' . $request->getUri() . ' ' . $this->anonymize((string)$request->getBody())
		);

		return $request;
	}

	/**
	 * Anonymize sensible data such as access keys
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	protected function anonymize(string $str): string
	{
		$str = preg_replace( '/access_token=([a-z0-9]{64})/', 'access_token=***', $str );
		$str = preg_replace( '/secret=([a-z0-9]{64})/', 'secret=***', $str );
		$str = preg_replace( '/"token": "[a-z0-9-]{36}"/', '"token": "***"', $str );

		return $str;
	}
}
