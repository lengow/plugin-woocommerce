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
 * at your option) any later version.
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
	// const LENGOW_URL = 'lengow.io';
	const LENGOW_URL = 'lengow.net';

	/**
	 * @var string url of the Lengow API.
	 */
	// const LENGOW_API_URL = 'https://api.lengow.io';
	const LENGOW_API_URL = 'https://api.lengow.net';

	/**
	 * @var string url of access token API.
	 */
	const API_ACCESS_TOKEN = '/access/get_token';

	/**
	 * @var string url of order API.
	 */
	const API_ORDER = '/v3.0/orders';

	/**
	 * @var string url of order merchant order id API.
	 */
	const API_ORDER_MOI = '/v3.0/orders/moi/';

	/**
	 * @var string url of order action API.
	 */
	const API_ORDER_ACTION = '/v3.0/orders/actions/';

	/**
	 * @var string url of marketplace API.
	 */
	const API_MARKETPLACE = '/v3.0/marketplaces';

	/**
	 * @var string url of plan API.
	 */
	const API_PLAN = '/v3.0/plans';

	/**
	 * @var string url of cms API.
	 */
	const API_CMS = '/v3.1/cms';

	/**
	 * @var string url of cms catalog API.
	 */
	const API_CMS_CATALOG = '/v3.1/cms/catalogs/';

	/**
	 * @var string url of cms mapping API.
	 */
	const API_CMS_MAPPING = '/v3.1/cms/mapping/';

	/**
	 * @var string url of plugin API.
	 */
	const API_PLUGIN = '/v3.0/plugins';

	/**
	 * @var string request GET.
	 */
	const GET = 'GET';

	/**
	 * @var string request POST.
	 */
	const POST = 'POST';

	/**
	 * @var string request PUT.
	 */
	const PUT = 'PUT';

	/**
	 * @var string request PATCH.
	 */
	const PATCH = 'PATCH';

	/**
	 * @var string json format return.
	 */
	const FORMAT_JSON = 'json';

	/**
	 * @var string stream format return.
	 */
	const FORMAT_STREAM = 'stream';

	/**
	 * @var string success code.
	 */
	const CODE_200 = 200;

	/**
	 * @var string success create code.
	 */
	const CODE_201 = 201;

	/**
	 * @var string unauthorized access code.
	 */
	const CODE_401 = 401;

	/**
	 * @var string forbidden access code.
	 */
	const CODE_403 = 403;

	/**
	 * @var string error server code.
	 */
	const CODE_500 = 500;

	/**
	 * @var string timeout server code.
	 */
	const CODE_504 = 504;

	/**
	 * @var array success HTTP codes for request.
	 */
	private $_success_codes = array(
		self::CODE_200,
		self::CODE_201,
	);

	/**
	 * @var array authorization HTTP codes for request.
	 */
	protected $_authorization_codes = array(
		self::CODE_401,
		self::CODE_403,
	);

	/**
	 * @var integer Authorization token lifetime.
	 */
	private $_token_lifetime = 3000;

	/**
	 * @var array default options for curl.
	 */
	private $_curl_opts = array(
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
	protected $api_without_url_args = array(
		self::API_ACCESS_TOKEN,
		self::API_ORDER_ACTION,
		self::API_ORDER_MOI,
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
		if ( null === $account_id || 0 === $account_id || ! is_numeric( $account_id ) ) {
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
	 * @param string $type request type (GET / POST / PUT / PATCH)
	 * @param string $api request url
	 * @param array $args request params
	 * @param string $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 */
	public static function query_api( $type, $api, $args = array(), $body = '', $log_output = false ) {
		if ( ! in_array( $type, array( self::GET, self::POST, self::PUT, self::PATCH ) ) ) {
			return false;
		}
		try {
			list( $account_id, $access_token, $secret ) = Lengow_Configuration::get_access_id();
			if ( null === $account_id ) {
				return false;
			}
			$connector = new Lengow_Connector( $access_token, $secret );
			$type      = strtolower( $type );
			$results   = $connector->$type(
				$api,
				array_merge( array( 'account_id' => $account_id ), $args ),
				self::FORMAT_STREAM,
				$body,
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

			return false;
		}

		return json_decode( $results );
	}

	/**
	 * Get account id by credentials from Middleware.
	 *
	 * @param string $access_token access token for api
	 * @param string $secret secret for api
	 * @param boolean $log_output see log or not
	 *
	 * @return int|null
	 */
	public static function get_account_id_by_credentials( $access_token, $secret, $log_output = false ) {
		$connector = new Lengow_Connector( $access_token, $secret );
		try {
			$data = $connector->_call_action(
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
	 *
	 */
	public function connect( $force = false, $log_output = false ) {
		$token      = Lengow_Configuration::get( Lengow_Configuration::AUTHORIZATION_TOKEN );
		$updated_at = Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_AUTHORIZATION_TOKEN );
		if ( ! $force
		     && null !== $token
		     && null !== $updated_at
		     && '' !== $token
		     && ( time() - $updated_at ) < $this->_token_lifetime
		) {
			$authorization_token = $token;
		} else {
			$authorization_token = $this->_get_authorization_token( $log_output );
			Lengow_Configuration::update_value( Lengow_Configuration::AUTHORIZATION_TOKEN, $authorization_token );
			Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_AUTHORIZATION_TOKEN, time() );
		}
		$this->_token = $authorization_token;
	}

	/**
	 * Get API call.
	 *
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 *
	 */
	public function get( $api, $args = array(), $format = self::FORMAT_JSON, $body = '', $log_output = false ) {
		return $this->_call( $api, $args, self::GET, $format, $body, $log_output );
	}

	/**
	 * Post API call.
	 *
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 *
	 */
	public function post( $api, $args = array(), $format = self::FORMAT_JSON, $body = '', $log_output = false ) {
		return $this->_call( $api, $args, self::POST, $format, $body, $log_output );
	}

	/**
	 * Put API call.
	 *
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 *
	 */
	public function put( $api, $args = array(), $format = self::FORMAT_JSON, $body = '', $log_output = false ) {
		return $this->_call( $api, $args, self::PUT, $format, $body, $log_output );
	}

	/**
	 * Patch API call.
	 *
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 *
	 */
	public function patch( $api, $args = array(), $format = self::FORMAT_JSON, $body = '', $log_output = false ) {
		return $this->_call( $api, $args, self::PATCH, $format, $body, $log_output );
	}

	/**
	 * The API method.
	 *
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $type type of request GET|POST|PUT|PATCH
	 * @param string $format return format of API
	 * @param string $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 *
	 */
	private function _call( $api, $args, $type, $format, $body, $log_output ) {
		try {
			$this->connect( false, $log_output );
			$data = $this->_call_action( $api, $args, $type, $format, $body, $log_output );
		} catch ( Lengow_Exception $e ) {
			if ( in_array( $e->getCode(), $this->_authorization_codes, true ) ) {
				Lengow_Main::log(
					Lengow_Log::CODE_CONNECTOR,
					Lengow_Main::set_log_message( 'log.connector.retry_get_token' ),
					$log_output
				);
				$this->connect( true, $log_output );
				$data = $this->_call_action( $api, $args, $type, $format, $body, $log_output );
			} else {
				throw new Lengow_Exception( $e->getMessage(), $e->getCode() );
			}
		}

		return $data;
	}

	/**
	 * Call API action.
	 *
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $type type of request GET|POST|PUT|PATCH
	 * @param string $format return format of API
	 * @param string $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 *
	 */
	private function _call_action( $api, $args, $type, $format, $body, $log_output ) {
		$result = $this->_make_request( $type, $api, $args, $this->_token, $body, $log_output );

		return $this->_format( $result, $format );
	}

	/**
	 * Get authorization token from Middleware.
	 *
	 * @param boolean $log_output see log or not
	 *
	 * @return string
	 * @throws Lengow_Exception
	 */
	private function _get_authorization_token( $log_output ) {
		// reset temporary token for the new authorization
		$this->_token = null;
		$data         = $this->_call_action(
			self::API_ACCESS_TOKEN,
			array(
				'access_token' => $this->_access_token,
				'secret'       => $this->_secret,
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
		if ( 0 === strlen( $data['token'] ) ) {
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
	 * @param string $type type of request GET|POST|PUT|PATCH
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $token temporary authorization token
	 * @param string $body body data for request
	 * @param boolean $log_output see log or not
	 *
	 * @return mixed
	 * @throws Lengow_Exception
	 *
	 */
	private function _make_request( $type, $api, $args, $token, $body, $log_output ) {
		// define CURLE_OPERATION_TIMEDOUT for old php versions.
		defined( 'CURLE_OPERATION_TIMEDOUT' ) || define( 'CURLE_OPERATION_TIMEDOUT', CURLE_OPERATION_TIMEOUTED );
		$ch = curl_init();
		// get default curl options.
		$opts = $this->_curl_opts;
		// get special timeout for specific Lengow API.
		if ( array_key_exists( $api, $this->_lengow_urls ) ) {
			$opts[ CURLOPT_TIMEOUT ] = $this->_lengow_urls[ $api ];
		}
		// get base url for a specific environment.
		$url                           = self::LENGOW_API_URL . $api;
		$opts[ CURLOPT_CUSTOMREQUEST ] = strtoupper( $type );
		$url                           = parse_url( $url );
		if ( isset( $url['port'] ) ) {
			$opts[ CURLOPT_PORT ] = $url['port'];
		}
		$opts[ CURLOPT_HEADER ]  = false;
		$opts[ CURLOPT_VERBOSE ] = false;
		if ( ! empty( $token ) ) {
			$opts[ CURLOPT_HTTPHEADER ] = array( 'Authorization: ' . $token );
		}
		// get call url with the mandatory parameters.
		$opts[ CURLOPT_URL ] = $url['scheme'] . '://' . $url['host'] . $url['path'];
		if ( ! empty( $args ) && ( $type === self::GET || ! in_array( $api, $this->api_without_url_args, true ) ) ) {
			$opts[ CURLOPT_URL ] .= '?' . http_build_query( $args );
		}
		if ( $type !== self::GET ) {
			if ( ! empty( $body ) ) {
				// sending data in json format for new APIs.
				$opts[ CURLOPT_HTTPHEADER ] = array_merge(
					$opts[ CURLOPT_HTTPHEADER ],
					array(
						'Content-Type: application/json',
						'Content-Length: ' . strlen( $body ),
					)
				);
				$opts[ CURLOPT_POSTFIELDS ] = $body;
			} else {
				// sending data in string format for legacy APIs.
				$opts[ CURLOPT_POST ]       = count( $args );
				$opts[ CURLOPT_POSTFIELDS ] = http_build_query( $args );
			}
		}
		Lengow_Main::log(
			Lengow_Log::CODE_CONNECTOR,
			Lengow_Main::set_log_message(
				'log.connector.call_api',
				array(
					'call_type' => $type,
					'curl_url'  => $opts[ CURLOPT_URL ],
				)
			),
			$log_output
		);
		curl_setopt_array( $ch, $opts );
		$result            = curl_exec( $ch );
		$http_code         = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$curl_error        = curl_error( $ch );
		$curl_error_number = curl_errno( $ch );
		curl_close( $ch );
		$this->_check_return_request( $result, $http_code, $curl_error, $curl_error_number );

		return $result;
	}

	/**
	 * Check return request and generate exception if needed.
	 *
	 * @param string $result Curl return call
	 * @param integer $http_code request http code
	 * @param string $curl_error Curl error
	 * @param string $curl_error_number Curl error number
	 *
	 * @throws Lengow_Exception
	 *
	 */
	private function _check_return_request( $result, $http_code, $curl_error, $curl_error_number ) {
		if ( false === $result ) {
			// recovery of Curl errors.
			if ( in_array( $curl_error_number, array( CURLE_OPERATION_TIMEDOUT, CURLE_OPERATION_TIMEOUTED ), true ) ) {
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
		if ( ! in_array( $http_code, $this->_success_codes, true ) ) {
			$result = $this->_format( $result );
			// recovery of Lengow Api errors.
			if ( isset( $result['error'], $result['error']['message'] ) ) {
				throw new Lengow_Exception( $result['error']['message'], $http_code );
			}
			throw new Lengow_Exception( Lengow_Main::set_log_message( 'log.connector.api_not_available' ), $http_code );
		}
	}

	/**
	 * Get data in specific format.
	 *
	 * @param mixed $data Curl response data
	 * @param string $format return format of API
	 *
	 * @return mixed
	 */
	private function _format( $data, $format = self::FORMAT_JSON ) {
		switch ( $format ) {
			case self::FORMAT_STREAM:
				return $data;
			default:
			case self::FORMAT_JSON:
				return json_decode( $data, true );
		}
	}
}
