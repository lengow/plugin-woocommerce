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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Connector Class.
 */
class Lengow_Connector {

	/**
	 * @var string url of Lengow solution.
	 */
	const LENGOW_URL = 'lengow.io';


	/**
	 * @var string url of the Lengow API.
	 */
	const LENGOW_API_URL = 'https://api.lengow.io';

	/**
	 * @var string suffix for prod
	 */
	const LIVE_SUFFIX = '.io';

	/**
	 * @var string suffix for pre-prod
	 */
	const TEST_SUFFIX = '.net';



	/* Lengow API routes */
	const API_ACCESS_TOKEN = '/access/get_token';
	const API_ORDER        = '/v3.0/orders';
	const API_ORDER_MOI    = '/v3.0/orders/moi/';
	const API_ORDER_ACTION = '/v3.0/orders/actions/';
	const API_MARKETPLACE  = '/v3.0/marketplaces';
	const API_PLAN         = '/v3.0/plans';
	const API_CMS          = '/v3.1/cms';
	const API_CMS_CATALOG  = '/v3.1/cms/catalogs/';
	const API_CMS_MAPPING  = '/v3.1/cms/mapping/';
	const API_PLUGIN       = '/v3.0/plugins';

	/* Request actions */
	const GET   = 'GET';
	const POST  = 'POST';
	const PUT   = 'PUT';
	const PATCH = 'PATCH';

	/* Return formats */
	const FORMAT_JSON   = 'json';
	const FORMAT_STREAM = 'stream';

	/* Http codes */
	const CODE_200 = 200;
	const CODE_201 = 201;
	const CODE_401 = 401;
	const CODE_403 = 403;
	const CODE_404 = 404;
	const CODE_500 = 500;
	const CODE_504 = 504;

	/**
	 * @var array success HTTP codes for request.
	 */
	private $success_codes = array(
		self::CODE_200,
		self::CODE_201,
	);

	/**
	 * @var array authorization HTTP codes for request.
	 */
	private $authorization_codes = array(
		self::CODE_401,
		self::CODE_403,
	);

	/**
	 * @var integer Authorization token lifetime.
	 */
	private $token_lifetime = 3000;

	/**
	 * @var array default options for curl.
	 */
	private $curl_opts = array(
		'connecttiemout'  => 10,
		'returntransfert' => true,
		'timeout'         => 10,
		'useragrent'      => 'lengow-cms-woocommerce',
	);

	/**
	 * @var string the access token to connect.
	 */
	private $access_token;

	/**
	 * @var string the secret to connect.
	 */
	private $secret;

	/**
	 * @var string temporary token for the authorization.
	 */
	private $token;

	/**
	 * @var array lengow url for curl timeout.
	 */
	private $lengow_urls = array(
		self::API_ORDER        => 20,
		self::API_ORDER_MOI    => 10,
		self::API_ORDER_ACTION => 15,
		self::API_MARKETPLACE  => 15,
		self::API_PLAN         => 5,
		self::API_CMS          => 5,
		self::API_CMS_CATALOG  => 10,
		self::API_CMS_MAPPING  => 10,
		self::API_PLUGIN       => 5,
	);

	/**
	 * @var array API requiring no arguments in the call url.
	 */
	private $api_without_url_args = array(
		self::API_ACCESS_TOKEN,
		self::API_ORDER_ACTION,
		self::API_ORDER_MOI,
	);

	/**
	 * @var array API requiring no authorization for the call url
	 */
	private static $api_without_authorizations = array(
		self::API_PLUGIN,
	);

	/**
	 * Make a new Lengow API Connector.
	 *
	 * @param string $access_token Your access token
	 * @param string $secret Your secret
	 */
	public function __construct( $access_token, $secret ) {
		$this->access_token = $access_token;
		$this->secret       = $secret;
	}

	/**
	 * Check API Authentication.
	 *
	 * @param boolean $log_output see log or not
	 *
	 * @return boolean
	 */
	public static function is_valid_auth( $log_output = false ) {
		if ( ! Lengow_Toolbox::is_curl_activated() ) {
			return false;
		}
		list( $account_id, $access_token, $secret ) = Lengow_Configuration::get_access_id();
		if ( null === $account_id ) {
			return false;
		}
		$connector = new Lengow_Connector( $access_token, $secret );
		try {
			$connector->connect( false, $log_output );
		} catch ( Lengow_Exception $e ) {
			$message = Lengow_Main::decode_log_message( $e->getMessage(), Lengow_Translation::DEFAULT_ISO_CODE );
			$error   = Lengow_Main::set_log_message(
				'log.connector.error_api',
				array(
					'error_code'    => $e->getCode(),
					'error_message' => $message,
				)
			);
			Lengow_Main::log( Lengow_Log::CODE_CONNECTOR, $error, $log_output );

			return false;
		}

		return true;
	}

	/**
	 * Get result for a query Api.
	 *
	 * @param string  $type request type (GET / POST / PUT / PATCH)
	 * @param string  $api request url
	 * @param array   $args request params
	 * @param string  $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 */
	public static function query_api( $type, $api, $args = array(), $body = '', $log_output = false ) {
		if ( ! in_array( $type, array( self::GET, self::POST, self::PUT, self::PATCH ) ) ) {
			return false;
		}
		try {
			$authorization_required                     = ! in_array( $api, self::$api_without_authorizations, true );
			list( $account_id, $access_token, $secret ) = Lengow_Configuration::get_access_id();
			if ( null === $account_id && $authorization_required ) {
				return false;
			}
			$connector = new Lengow_Connector( $access_token, $secret );
			$type      = strtolower( $type );
			$args      = $authorization_required ? array_merge( array( Lengow_Import::ARG_ACCOUNT_ID => $account_id ), $args ) : $args;
			$results   = $connector->$type( $api, $args, self::FORMAT_STREAM, $body, $log_output );
		} catch ( Lengow_Exception $e ) {
			$message = Lengow_Main::decode_log_message( $e->getMessage(), Lengow_Translation::DEFAULT_ISO_CODE );
			$error   = Lengow_Main::set_log_message(
				'log.connector.error_api',
				array(
					'error_code'    => $e->getCode(),
					'error_message' => $message,
				)
			);
			Lengow_Main::log( Lengow_Log::CODE_CONNECTOR, $error, $log_output );

			return false;
		}

		// don't decode into array as we use the result as an object.
		return json_decode( $results );
	}

	/**
	 * Get account id by credentials from Middleware.
	 *
	 * @param string  $access_token access token for api
	 * @param string  $secret secret for api
	 * @param boolean $log_output see log or not
	 *
	 * @return int|null
	 */
	public static function get_account_id_by_credentials( $access_token, $secret, $log_output = false ) {
		$connector = new Lengow_Connector( $access_token, $secret );
		try {
			$data = $connector->call_action(
				self::API_ACCESS_TOKEN,
				array(
					'access_token' => $access_token,
					'secret'       => $secret,
				),
				self::POST,
				self::FORMAT_JSON,
				'',
				$log_output
			);
		} catch ( Lengow_Exception $e ) {
			$message = Lengow_Main::decode_log_message( $e->getMessage(), Lengow_Translation::DEFAULT_ISO_CODE );
			$error   = Lengow_Main::set_log_message(
				'log.connector.error_api',
				array(
					'error_code'    => $e->getCode(),
					'error_message' => $message,
				)
			);
			Lengow_Main::log( Lengow_Log::CODE_CONNECTOR, $error, $log_output );

			return null;
		}

		return $data['account_id'] ? (int) $data['account_id'] : null;
	}

	/**
	 * Connection to the API.
	 *
	 * @param boolean $force Force cache Update
	 * @param boolean $log_output see log or not
	 *
	 * @throws Lengow_Exception
	 */
	public function connect( $force = false, $log_output = false ) {
		$token      = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN );
		$updated_at = Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_AUTHORIZATION_TOKEN );
		if ( ! $force && null !== $token && null !== $updated_at && '' !== $token && ( time() - $updated_at ) < $this->token_lifetime
		) {
			$authorization_token = $token;
		} else {
			$authorization_token = $this->get_authorization_token( $log_output );
			Lengow_Configuration::update_value( Lengow_Configuration::AUTHORIZATION_TOKEN, $authorization_token );
			Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_AUTHORIZATION_TOKEN, time() );
		}
		$this->token = $authorization_token;
	}

	/**
	 * Get API call.
	 *
	 * @param string  $api Lengow method API call
	 * @param array   $args Lengow method API parameters
	 * @param string  $format return format of API
	 * @param string  $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 */
	public function get( $api, $args = array(), $format = self::FORMAT_JSON, $body = '', $log_output = false ) {
		return $this->call( $api, $args, self::GET, $format, $body, $log_output );
	}

	/**
	 * Post API call.
	 *
	 * @param string  $api Lengow method API call
	 * @param array   $args Lengow method API parameters
	 * @param string  $format return format of API
	 * @param string  $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 */
	public function post( $api, $args = array(), $format = self::FORMAT_JSON, $body = '', $log_output = false ) {
		return $this->call( $api, $args, self::POST, $format, $body, $log_output );
	}

	/**
	 * Put API call.
	 *
	 * @param string  $api Lengow method API call
	 * @param array   $args Lengow method API parameters
	 * @param string  $format return format of API
	 * @param string  $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 */
	public function put( $api, $args = array(), $format = self::FORMAT_JSON, $body = '', $log_output = false ) {
		return $this->call( $api, $args, self::PUT, $format, $body, $log_output );
	}

	/**
	 * Patch API call.
	 *
	 * @param string  $api Lengow method API call
	 * @param array   $args Lengow method API parameters
	 * @param string  $format return format of API
	 * @param string  $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 */
	public function patch( $api, $args = array(), $format = self::FORMAT_JSON, $body = '', $log_output = false ) {
		return $this->call( $api, $args, self::PATCH, $format, $body, $log_output );
	}

	/**
	 * The API method.
	 *
	 * @param string  $api Lengow method API call
	 * @param array   $args Lengow method API parameters
	 * @param string  $type type of request GET|POST|PUT|PATCH
	 * @param string  $format return format of API
	 * @param string  $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 */
	private function call( $api, $args, $type, $format, $body, $log_output ) {
		try {
			if ( ! in_array( $api, self::$api_without_authorizations, true ) ) {
				$this->connect( false, $log_output );
			}
			$data = $this->call_action( $api, $args, $type, $format, $body, $log_output );
		} catch ( Lengow_Exception $e ) {
			if ( in_array( $e->getCode(), $this->authorization_codes, true ) ) {
				Lengow_Main::log(
					Lengow_Log::CODE_CONNECTOR,
					Lengow_Main::set_log_message( 'log.connector.retry_get_token' ),
					$log_output
				);
				if ( ! in_array( $api, self::$api_without_authorizations, true ) ) {
					$this->connect( true, $log_output );
				}
				$data = $this->call_action( $api, $args, $type, $format, $body, $log_output );
			} else {
				throw new Lengow_Exception( $e->getMessage(), $e->getCode() );
			}
		}

		return $data;
	}

	/**
	 * Call API action.
	 *
	 * @param string  $api Lengow method API call
	 * @param array   $args Lengow method API parameters
	 * @param string  $type type of request GET|POST|PUT|PATCH
	 * @param string  $format return format of API
	 * @param string  $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 */
	private function call_action( $api, $args, $type, $format, $body, $log_output ) {
		$result = $this->make_request( $type, $api, $args, $this->token, $body, $log_output );

		return $this->format( $result, $format );
	}

	/**
	 * Get authorization token from Middleware.
	 *
	 * @param boolean $log_output see log or not
	 *
	 * @return string
	 * @throws Lengow_Exception
	 */
	private function get_authorization_token( $log_output ) {
		// reset temporary token for the new authorization
		$this->token = null;
		$data        = $this->call_action(
			self::API_ACCESS_TOKEN,
			array(
				'access_token' => $this->access_token,
				'secret'       => $this->secret,
			),
			self::POST,
			self::FORMAT_JSON,
			'',
			$log_output
		);
		// return a specific error for get_token.
		if ( ! isset( $data['token'] ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'log.connector.token_not_return' ),
				self::CODE_500
			);
		}
		if ( '' === $data['token'] ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'log.connector.token_is_empty' ),
				self::CODE_500
			);
		}

		return $data['token'];
	}

	/**
	 * Make Curl request.
	 *
	 * @param string  $type type of request GET|POST|PUT|PATCH
	 * @param string  $api Lengow method API call
	 * @param array   $args Lengow method API parameters
	 * @param string  $token temporary authorization token
	 * @param string  $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return bool|string
	 * @throws Lengow_Exception
	 */
	private function make_request( $type, $api, $args, $token, $body, $log_output ) {

		// get default curl options.
		$opts                      = $this->curl_opts;
				$curl_error        = '';
				$curl_error_number = '';

		// get special timeout for specific Lengow API.
		if ( array_key_exists( $api, $this->lengow_urls ) ) {

						$opts['timeout'] = $this->lengow_urls[ $api ];
		}
		// get base url for a specific environment.
		$url = Lengow_Configuration::get_lengow_api_url() . $api;

				// exit;
		$opts['customrequest'] = strtoupper( $type );

		$url = parse_url( $url );
		if ( isset( $url['port'] ) ) {
			$opts['port'] = $url['port'];
		}
		$opts['header']          = false;
		$opts['verbose']         = false;
				$opts['headers'] = array();
		if ( ! empty( $token ) ) {
						$opts['headers']['Authorization'] = $token;
		}

		// get call url with the mandatory parameters.
		$opts['url'] = $url['scheme'] . '://' . $url['host'] . $url['path'];
		if ( ! empty( $args ) && ( $type === self::GET || ! in_array( $api, $this->api_without_url_args, true ) ) ) {
			$opts['url'] .= '?' . http_build_query( $args );
		}
		if ( $type !== self::GET ) {
			if ( ! empty( $body ) ) {
				// sending data in json format for new APIs.

								$opts['headers']['Content-Type']   = 'application/json';
								$opts['headers']['Content-Length'] = strlen( $body );
				$opts['body']                                      = $body;
			} else {
				// sending data in string format for legacy APIs.
				$opts['post'] = count( $args );
				$opts['body'] = http_build_query( $args );

			}
		}
		Lengow_Main::log(
			Lengow_Log::CODE_CONNECTOR,
			Lengow_Main::set_log_message(
				'log.connector.call_api',
				array(
					'call_type' => $type,
					'curl_url'  => $opts['url'],
				)
			),
			$log_output
		);

		if ( $type === self::GET ) {
			$result = wp_remote_get( $opts['url'], $opts );
		} else {

			$result = wp_remote_post( $opts['url'], $opts );
		}

		$http_code         = wp_remote_retrieve_response_code( $result );
				$http_body = wp_remote_retrieve_body( $result );
		if ( $result instanceof WP_Error ) {
			$curl_error        = $result->get_error_message();
			$curl_error_number = $result->get_error_code();

		}

		$this->check_return_request(
			$result,
			(int) $http_code,
			(string) $curl_error,
			(string) $curl_error_number
		);

		return $http_body;
	}

	/**
	 * Check return request and generate exception if needed.
	 *
	 * @param string|WP_Error $result Curl return call
	 * @param integer         $http_code request http code
	 * @param string          $curl_error Curl error
	 * @param string          $curl_error_number Curl error number
	 *
	 * @throws Lengow_Exception
	 */
	private function check_return_request( $result, $http_code, $curl_error, $curl_error_number ) {
		if ( false === $result ) {
			// recovery of Curl errors.
			if ( in_array( $curl_error_number, array( CURLE_OPERATION_TIMEDOUT ), true ) ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'log.connector.timeout_api' ),
					self::CODE_504
				);
			}
			$error = Lengow_Main::set_log_message(
				'log.connector.error_curl',
				array(
					'error_code'    => $curl_error_number,
					'error_message' => $curl_error,
				)
			);
			throw new Lengow_Exception( $error, self::CODE_500 );
		}

		if ( ! in_array( $http_code, $this->success_codes ) ) {

			$result = $this->format( wp_remote_retrieve_body( $result ) );
			// recovery of Lengow Api errors.

			if ( $result instanceof WP_Error ) {
				throw new Lengow_Exception(
					(string) $result->get_error_message(),
					(int) $http_code
				);
			}

			throw new Lengow_Exception(
				(string) Lengow_Main::set_log_message( 'log.connector.api_not_available' ),
				(int) $http_code
			);
		}
	}

	/**
	 * Get data in specific format.
	 *
	 * @param mixed  $data Curl response data
	 * @param string $format return format of API
	 *
	 * @return mixed
	 */
	private function format( $data, $format = self::FORMAT_JSON ) {
		switch ( $format ) {
			case self::FORMAT_STREAM:
				return $data;
			default:
			case self::FORMAT_JSON:
				return json_decode( $data, true );
		}
	}
}
