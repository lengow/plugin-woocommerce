<?php
/**
 * Connector to use Lengow API
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Connector Class.
 */
class Lengow_Connector {
	/**
	 * @var string connector version
	 */
	const VERSION = '1.0';

	/**
	 * @var mixed error returned by the API
	 */
	public $error;

	/**
	 * @var string the access token to connect
	 */
	protected $_access_token;

	/**
	 * @var string the secret to connect
	 */
	protected $_secret;

	/**
	 * @var string temporary token for the authorization
	 */
	protected $_token;

	/**
	 * @var integer ID account
	 */
	protected $_account_id;

	/**
	 * @var integer the user Id
	 */
	protected $_user_id;

	/**
	 * @var string URL of the API Lengow
	 */
	// const LENGOW_API_URL = 'http://api.lengow.io:80';
	// const LENGOW_API_URL = 'http://api.lengow.net:80';
	const LENGOW_API_URL = 'http://api.lengow.rec:80';
	// const LENGOW_API_URL = 'http://10.100.1.82:8081';

	/**
	 * @var string URL of the SANDBOX Lengow
	 */
	const LENGOW_API_SANDBOX_URL = 'http://api.lengow.net:80';

	/**
	 * @var array default options for curl
	 */
	public static $curl_opts = array(
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 20,
		CURLOPT_USERAGENT      => 'lengow-php-sdk',
	);

	/**
	 * @var array lengow url for curl timeout
	 */
	protected $lengow_urls = array(
		'/v3.0/orders'        => 15,
		'/v3.0/marketplaces'  => 10,
		'/v3.0/subscriptions' => 5,
		'/v3.0/stats'         => 5,
		'/v3.0/cms'           => 5,
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
	 * Connectection to the API
	 *
	 * @param string $user_token The user token if is connected
	 *
	 * @return mixed array [authorized token + account_id + user_id] or false
	 */
	public function connect( $user_token = '' ) {
		$data = $this->call_action(
			'/access/get_token',
			array(
				'access_token' => $this->_access_token,
				'secret'       => $this->_secret,
				'user_token'   => $user_token
			),
			'POST'
		);
		if ( isset( $data['token'] ) ) {
			$this->_token      = $data['token'];
			$this->_account_id = $data['account_id'];
			$this->_user_id    = $data['user_id'];

			return $data;
		} else {
			return false;
		}
	}

	/**
	 * The API method
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $type type of request GET|POST|PUT|HEAD|DELETE|PATCH
	 * @param string $format return format of API
	 * @param string $body
	 *
	 * @return array The formated data response
	 */
	public function call( $method, $array = array(), $type = 'GET', $format = 'json', $body = '' ) {
		$this->connect();
		try {
			if ( ! array_key_exists( 'account_id', $array ) ) {
				$array['account_id'] = $this->_account_id;
			}
			$data = $this->call_action( $method, $array, $type, $format, $body );
		} catch ( Lengow_Exception $e ) {
			return $e->getMessage();
		}

		return $data;
	}

	/**
	 * Get API call
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body
	 *
	 * @return array The formated data response
	 */
	public function get( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'GET', $format, $body );
	}

	/**
	 * Post API call
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body
	 *
	 * @return array The formated data response
	 */
	public function post( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'POST', $format, $body );
	}

	/**
	 * Head API call
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body
	 *
	 * @return array The formated data response
	 */
	public function head( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'HEAD', $format, $body );
	}

	/**
	 * Put API call
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body
	 *
	 * @return array The formated data response
	 */
	public function put( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'PUT', $format, $body );
	}

	/**
	 * Delete API call
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body
	 *
	 * @return array The formated data response
	 */
	public function delete( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'DELETE', $format, $body );
	}

	/**
	 * Patch API call
	 *
	 * @param string $method Lengow method API call
	 * @param array $array Lengow method API parameters
	 * @param string $format return format of API
	 * @param string $body
	 *
	 * @return array The formated data response
	 */
	public function patch( $method, $array = array(), $format = 'json', $body = '' ) {
		return $this->call( $method, $array, 'PATCH', $format, $body );
	}

	/**
	 * Call API action
	 *
	 * @param string $api Lengow method API call
	 * @param array $args Lengow method API parameters
	 * @param string $type type of request GET|POST|PUT|HEAD|DELETE|PATCH
	 * @param string $format return format of API
	 * @param string $body
	 *
	 * @return array The formated data response
	 */
	private function call_action( $api, $args, $type, $format = 'json', $body = '' ) {
		$result = $this->make_request( $type, $api, $args, $this->_token, $body );

		return $this->format( $result, $format );
	}

	/**
	 * Get data in specific format
	 *
	 * @param mixed $data
	 * @param string $format
	 *
	 * @return mixed
	 */
	private function format( $data, $format ) {
		switch ( $format ) {
			case 'json':
				return json_decode( $data, true );
			case 'csv':
				return $data;
			case 'xml':
				return simplexml_load_string( $data );
			case 'stream':
				return $data;
		}
	}

	/**
	 * Make Curl request
	 *
	 * @param string $type Lengow method API call
	 * @param string $url Lengow API url
	 * @param array $args Lengow method API parameters
	 * @param string $token temporary access token
	 * @param string $body
	 *
	 * @throws Lengow_Exception Get curl error
	 *
	 * @return array The formated data response
	 */
	protected function make_request( $type, $url, $args, $token, $body = '' ) {
		// Define CURLE_OPERATION_TIMEDOUT for old php versions
		defined( "CURLE_OPERATION_TIMEDOUT" ) || define( "CURLE_OPERATION_TIMEDOUT", CURLE_OPERATION_TIMEOUTED );
		$ch = curl_init();
		// Options
		$opts = self::$curl_opts;
		// get special timeout for specific Lengow API
		if ( array_key_exists( $url, $this->lengow_urls ) ) {
			$opts[ CURLOPT_TIMEOUT ] = $this->lengow_urls[ $url ];
		}
		// get url for a specific environment
		$url                            = self::LENGOW_API_URL . $url;
		$opts[ CURLOPT_CUSTOMREQUEST ]  = strtoupper( $type );
		$url                            = parse_url( $url );
		$opts[ CURLOPT_PORT ]           = $url['port'];
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
			case "GET":
				$opts[ CURLOPT_URL ] = $url . '?' . http_build_query( $args );
				Lengow_Main::log(
					'Connector',
					Lengow_Main::set_log_message(
						'log.connector.call_api',
						array( 'curl_url' => $opts[ CURLOPT_URL ] )
					)
				);
				break;
			case "PUT":
				$opts[ CURLOPT_HTTPHEADER ] = array_merge(
					$opts[ CURLOPT_HTTPHEADER ],
					array(
						'Content-Type: application/json',
						'Content-Length: ' . strlen( $body )
					)
				);
				$opts[ CURLOPT_URL ]        = $url . '?' . http_build_query( $args );
				$opts[ CURLOPT_POSTFIELDS ] = $body;
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
				array( 'error_code' => Lengow_Main::decode_log_message( $timeout, 'en_GB' ) )
			);
			Lengow_Main::log( 'Connector', $error_message );
			throw new Lengow_Exception( $timeout );
		}
		curl_close( $ch );
		if ( $result === false ) {
			$error_curl    = Lengow_Main::set_log_message(
				'log.connector.error_curl',
				array(
					'error_code'    => $error_number,
					'error_message' => $error_text
				)
			);
			$error_message = Lengow_Main::set_log_message(
				'log.connector.error_api',
				array( 'error_code' => Lengow_Main::decode_log_message( $error_curl, 'en_GB' ) )
			);
			Lengow_Main::log( 'Connector', $error_message );
			throw new Lengow_Exception( $error_curl );
		}

		return $result;
	}

	/**
	 * Get Valid Account / Access / Secret
	 *
	 * @return array
	 */
	public static function get_access_id() {
		if ( strlen( Lengow_Configuration::get( 'lengow_account_id' ) ) > 0
		     && strlen( Lengow_Configuration::get( 'lengow_access_token' ) ) > 0
		     && strlen( Lengow_Configuration::get( 'lengow_secret_token' ) ) > 0
		) {
			return array(
				Lengow_Configuration::get( 'lengow_account_id' ),
				Lengow_Configuration::get( 'lengow_access_token' ),
				Lengow_Configuration::get( 'lengow_secret_token' )
			);
		} else {
			return array( null, null, null );
		}
	}

	/**
	 * Get result for a query Api
	 *
	 * @param string $type (GET / POST / PUT / PATCH)
	 * @param string $url
	 * @param array $params
	 * @param string $body
	 *
	 * @return mixed
	 */
	public static function query_api( $type, $url, $params = array(), $body = '' ) {
		if ( ! in_array( $type, array( 'get', 'post', 'put', 'patch' ) ) ) {
			return false;
		}
		try {
			list( $account_id, $access_token, $secret_token ) = self::get_access_id();
			if ( is_null( $account_id ) ) {
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

