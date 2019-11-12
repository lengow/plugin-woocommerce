<?php
/**
 * Connector to use Lengow API
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Connector Class.
 */
class Lengow_Connector {

	/**
	 * @var string url of the API Lengow.
	 */
	const LENGOW_API_URL = 'https://api.lengow.io';
	// const LENGOW_API_URL = 'https://api.lengow.net';
	// const LENGOW_API_URL = 'http://api.lengow.rec';
	// const LENGOW_API_URL = 'http://10.100.1.82:8081';

	/**
	 * @var array default options for curl.
	 */
	public static $curl_opts = array(
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 10,
		CURLOPT_USERAGENT      => 'lengow-cms-woocommerce',
	);

	/**
	 * @var string the access token to connect.
	 */
	private $_access_token;

	/**
	 * @var string the secret to connect.
	 */
	private $_secret;

	/**
	 * @var string temporary token for the authorization.
	 */
	private $_token;

	/**
	 * @var array lengow url for curl timeout.
	 */
	private $_lengow_urls = array(
		'/v3.0/orders'       => 20,
		'/v3.0/marketplaces' => 15,
		'/v3.0/plans'        => 5,
		'/v3.0/stats'        => 5,
		'/v3.1/cms'          => 5,
	);

	/**
	 * Make a new Lengow API Connector.
	 *
	 * @param string $access_token Your access token
	 * @param string $secret Your secret
	 */
	public function __construct( $access_token, $secret ) {
		$this->_access_token = $access_token;
		$this->_secret       = $secret;
	}

	/**
	 * Connection to the API.
	 *
	 * @return array|false
	 * @throws Lengow_Exception Get Curl error
	 *
	 */
	public function connect() {
		$data = $this->call_action(
			'/access/get_token',
			array(
				'access_token' => $this->_access_token,
				'secret'       => $this->_secret,
			),
			'POST'
		);
		if ( isset( $data['token'] ) ) {
			$this->_token = $data['token'];

			return $data;
		} else {
			return false;
		}
	}

	/**
	 * The API method.
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $type type of request GET|POST|PUT|PATCH
	 * @param string $format return format of API
	 * @param string $body body datas for request
	 *
	 * @return mixed
	 * @throws Lengow_Exception Get Curl error
	 *
	 */
	public function call( $method, $array = array(), $type = 'GET', $format = 'json', $body = '' ) {
		$this->connect();
		try {
			$data = $this->call_action( $method, $array, $type, $format, $body );
		} catch ( Lengow_Exception $e ) {
			return $e->getMessage();
		}

		return $data;
	}

	/**
	 * Get API call.
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body body datas for request
	 *
	 * @return mixed
	 * @throws Lengow_Exception Get Curl error
	 *
	 */
	public function get( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'GET', $format, $body );
	}

	/**
	 * Post API call.
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body body datas for request
	 *
	 * @return mixed
	 * @throws Lengow_Exception Get Curl error
	 *
	 */
	public function post( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'POST', $format, $body );
	}

	/**
	 * Put API call.
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body body datas for request
	 *
	 * @return mixed
	 * @throws Lengow_Exception Get Curl error
	 *
	 */
	public function put( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'PUT', $format, $body );
	}

	/**
	 * Patch API call.
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body body datas for request
	 *
	 * @return mixed
	 * @throws Lengow_Exception Get Curl error
	 *
	 */
	public function patch( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'PATCH', $format, $body );
	}

	/**
	 * Call API action.
	 *
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $type type of request GET|POST|PUT|HEAD|DELETE|PATCH
	 * @param string $format return format of API
	 * @param string $body body datas for request
	 *
	 * @return mixed
	 * @throws Lengow_Exception Get Curl error
	 *
	 */
	private function call_action( $api, $args, $type, $format = 'json', $body = '' ) {
		$result = $this->make_request( $type, $api, $args, $this->_token, $body );

		return $this->format( $result, $format );
	}

	/**
	 * Get data in specific format.
	 *
	 * @param mixed $data Curl response data
	 * @param string $format return format of API
	 *
	 * @return mixed
	 */
	private function format( $data, $format ) {
		switch ( $format ) {
			case 'json':
				return json_decode( $data, true );
			case 'xml':
				return simplexml_load_string( $data );
			case 'csv':
			case 'stream':
				return $data;
		}
	}

	/**
	 * Make Curl request.
	 *
	 * @param string $type Lengow method API call
	 * @param string $url Lengow API url
	 * @param array $args Lengow method API parameters
	 * @param string $token temporary access token
	 * @param string $body body datas for request
	 *
	 * @return mixed
	 * @throws Lengow_Exception Get curl error
	 *
	 */
	protected function make_request( $type, $url, $args, $token, $body = '' ) {
		// define CURLE_OPERATION_TIMEDOUT for old php versions.
		defined( 'CURLE_OPERATION_TIMEDOUT' ) || define( 'CURLE_OPERATION_TIMEDOUT', CURLE_OPERATION_TIMEOUTED );
		$ch = curl_init();
		// options.
		$opts = self::$curl_opts;
		// get special timeout for specific Lengow API.
		if ( array_key_exists( $url, $this->_lengow_urls ) ) {
			$opts[ CURLOPT_TIMEOUT ] = $this->_lengow_urls[ $url ];
		}
		// get url for a specific environment.
		$url                           = self::LENGOW_API_URL . $url;
		$opts[ CURLOPT_CUSTOMREQUEST ] = strtoupper( $type );
		$url                           = parse_url( $url );
		if ( isset( $url['port'] ) ) {
			$opts[ CURLOPT_PORT ] = $url['port'];
		}
		$opts[ CURLOPT_HEADER ]         = false;
		$opts[ CURLOPT_RETURNTRANSFER ] = true;
		$opts[ CURLOPT_VERBOSE ]        = false;
		if ( isset( $token ) ) {
			$opts[ CURLOPT_HTTPHEADER ] = array(
				'Authorization: ' . $token,
			);
		}
		$url = $url['scheme'] . '://' . $url['host'] . $url['path'];
		switch ( $type ) {
			case 'GET':
				$opts[ CURLOPT_URL ] = $url . ( ! empty( $args ) ? '?' . http_build_query( $args ) : '' );
				Lengow_Main::log(
					Lengow_Log::CODE_CONNECTOR,
					Lengow_Main::set_log_message(
						'log.connector.call_api',
						array( 'curl_url' => $opts[ CURLOPT_URL ] )
					)
				);
				break;
			case 'PUT':
				if ( isset( $token ) ) {
					$opts[ CURLOPT_HTTPHEADER ] = array_merge(
						$opts[ CURLOPT_HTTPHEADER ],
						array(
							'Content-Type: application/json',
							'Content-Length: ' . strlen( $body ),
						)
					);
				}
				$opts[ CURLOPT_URL ]        = $url . '?' . http_build_query( $args );
				$opts[ CURLOPT_POSTFIELDS ] = $body;
				break;
			case 'PATCH':
				if ( isset( $token ) ) {
					$opts[ CURLOPT_HTTPHEADER ] = array_merge(
						$opts[ CURLOPT_HTTPHEADER ],
						array( 'Content-Type: application/json' )
					);
				}
				$opts[ CURLOPT_URL ]        = $url;
				$opts[ CURLOPT_POST ]       = count( $args );
				$opts[ CURLOPT_POSTFIELDS ] = json_encode( $args );
				break;
			default:
				$opts[ CURLOPT_URL ]        = $url;
				$opts[ CURLOPT_POST ]       = count( $args );
				$opts[ CURLOPT_POSTFIELDS ] = http_build_query( $args );
				break;
		}
		curl_setopt_array( $ch, $opts );
		$result       = curl_exec( $ch );
		$error_number = curl_errno( $ch );
		$error_text   = curl_error( $ch );
		if ( in_array( $error_number, array( CURLE_OPERATION_TIMEDOUT, CURLE_OPERATION_TIMEOUTED ) ) ) {
			$timeout       = Lengow_Main::set_log_message( 'log.connector.timeout_api' );
			$error_message = Lengow_Main::set_log_message(
				'log.connector.error_api',
				array(
					'error_code' => Lengow_Main::decode_log_message( $timeout, Lengow_Translation::DEFAULT_ISO_CODE ),
				)
			);
			Lengow_Main::log( Lengow_Log::CODE_CONNECTOR, $error_message );
			throw new Lengow_Exception( $timeout );
		}
		curl_close( $ch );
		if ( false === $result ) {
			$error_curl    = Lengow_Main::set_log_message(
				'log.connector.error_curl',
				array(
					'error_code'    => $error_number,
					'error_message' => $error_text,
				)
			);
			$error_message = Lengow_Main::set_log_message(
				'log.connector.error_api',
				array(
					'error_code' => Lengow_Main::decode_log_message(
						$error_curl,
						Lengow_Translation::DEFAULT_ISO_CODE
					),
				)
			);
			Lengow_Main::log( Lengow_Log::CODE_CONNECTOR, $error_message );
			throw new Lengow_Exception( $error_curl );
		}

		return $result;
	}

	/**
	 * Check if is a new merchant.
	 *
	 * @return boolean
	 */
	public static function is_new_merchant() {
		list( $account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
		if ( null !== $account_id && null !== $access_token && null !== $secret_token ) {
			return false;
		}

		return true;
	}

	/**
	 * Check API Authentication.
	 *
	 * @return boolean
	 */
	public static function is_valid_auth() {
		if ( ! Lengow_Check::is_curl_activated() ) {
			return false;
		}
		list( $account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
		if ( null === $account_id || 0 === $account_id || ! is_numeric( $account_id ) ) {
			return false;
		}
		$connector = new Lengow_Connector( $access_token, $secret_token );
		try {
			$result = $connector->connect();
		} catch ( Lengow_Exception $e ) {
			return false;
		}
		if ( isset( $result['token'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get result for a query Api.
	 *
	 * @param string $type request type (GET / POST / PUT / PATCH)
	 * @param string $url request url
	 * @param array $params request params
	 * @param string $body body datas for request
	 *
	 * @return mixed
	 */
	public static function query_api( $type, $url, $params = array(), $body = '' ) {
		if ( ! in_array( $type, array( 'get', 'post', 'put', 'patch' ) ) ) {
			return false;
		}
		try {
			list( $account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
			if ( null === $account_id ) {
				return false;
			}
			$connector = new Lengow_Connector( $access_token, $secret_token );
			$results   = $connector->$type(
				$url,
				array_merge( array( 'account_id' => $account_id ), $params ),
				'stream',
				$body
			);
		} catch ( Lengow_Exception $e ) {
			return false;
		}

		return json_decode( $results );
	}
}
