<?php
/**
 * All components for toolbox
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
 * Lengow_Check Class.
 */
class Lengow_Check {

	/**
	 * Check API Authentification
	 *
	 * @return boolean
	 */
	public static function is_valid_auth() {
		if ( ! self::is_curl_activated() ) {
			return false;
		}
		list( $account_id, $access_token, $secret ) = Lengow_Connector::get_access_id();
		if ( is_null( $account_id ) || is_null( $access_token ) || is_null( $secret ) ) {
			return false;
		}
		$connector = new Lengow_Connector( $access_token, $secret );
		$result    = $connector->connect();
		if ( isset( $result['token'] ) && $account_id != 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if PHP Curl is activated
	 *
	 * @return boolean
	 */
	public static function is_curl_activated() {
		return function_exists( 'curl_version' );
	}

	/**
	 * Check if SimpleXML Extension is activated
	 *
	 * @return boolean
	 */
	public static function is_simple_xml_activated() {
		return function_exists( 'simplexml_load_file' );
	}

	/**
	 * Check if json Extension is activated
	 *
	 * @return boolean
	 */
	public static function is_json_activated() {
		return function_exists( 'json_decode' );
	}
}

