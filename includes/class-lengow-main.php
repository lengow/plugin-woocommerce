<?php
/**
 * Utilities functions
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
 * Lengow_Main Class.
 */
class Lengow_Main {

	/**
	 * Lengow Authorized IPs
	 */
	protected static $IPS_LENGOW = array(
		'46.19.183.204',
		'46.19.183.218',
		'46.19.183.222',
		'89.107.175.172',
		'89.107.175.186',
		'185.61.176.129',
		'185.61.176.130',
		'185.61.176.131',
		'185.61.176.132',
		'185.61.176.133',
		'185.61.176.134',
		'185.61.176.137',
		'185.61.176.138',
		'185.61.176.139',
		'185.61.176.140',
		'185.61.176.141',
		'185.61.176.142',
		'95.131.137.18',
		'95.131.137.19',
		'95.131.137.21',
		'95.131.137.26',
		'95.131.137.27',
		'88.164.17.227',
		'88.164.17.216',
		'109.190.78.5',
		'95.131.141.168',
		'95.131.141.169',
		'95.131.141.170',
		'95.131.141.171',
		'82.127.207.67',
		'80.14.226.127',
		'80.236.15.223',
		'92.135.36.234',
		'81.64.72.170',
		'80.11.36.123'
	);

	/**
	 * @var LengowLog Lengow log file instance
	 */
	public static $log;

	/**
	 * @var integer life of log files in days
	 */
	public static $LOG_LIFE = 20;

	/**
	 * @var array WooCommerce product types
	 */
	public static $PRODUCT_TYPES = array(
		'simple'   => 'Simple Product',
		'variable' => 'Variable Product',
		'external' => 'External Product',
		'grouped'  => 'Grouped Product',
	);

	/**
	 * Check if current IP is authorized
	 *
	 * @return boolean
	 */
	public static function check_ip() {
		$ips              = Lengow_Configuration::get( 'lengow_authorized_ip' );
		$ips              = trim( str_replace( array( "\r\n", ',', '-', '|', ' ' ), ';', $ips ), ';' );
		$ips              = array_filter( explode( ';', $ips ) );
		$authorized_ips   = count( $ips ) > 0 ? array_merge( $ips, self::$IPS_LENGOW ) : self::$IPS_LENGOW;
		$authorized_ips[] = $_SERVER['SERVER_ADDR'];
		$hostname_ip      = $_SERVER['REMOTE_ADDR'];
		if ( in_array( $hostname_ip, $authorized_ips ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Writes log
	 *
	 * @param string $category Category log
	 * @param string $txt log message
	 * @param boolean $force_output output on screen
	 * @param string $marketplace_sku lengow marketplace sku
	 */
	public static function log( $category, $txt, $force_output = false, $marketplace_sku = null ) {
		$log = self::get_log_instance();
		$log->write( $category, $txt, $force_output, $marketplace_sku );
	}

	/**
	 * Get log Instance
	 *
	 * @return LengowLog Lengow log file instance
	 */
	public static function get_log_instance() {
		if ( is_null( self::$log ) ) {
			self::$log = new Lengow_Log();
		}

		return self::$log;
	}

	/**
	 * Suppress log files when too old
	 */
	public static function clean_log() {
		$log_files = Lengow_Log::get_files();
		$days      = array();
		$days[]    = 'logs-' . date( 'Y-m-d' ) . '.txt';
		for ( $i = 1; $i < self::$LOG_LIFE; $i ++ ) {
			$days[] = 'logs-' . date( 'Y-m-d', strtotime( '-' . $i . 'day' ) ) . '.txt';
		}
		if ( empty( $log_files ) ) {
			return;
		}
		foreach ( $log_files as $log ) {
			if ( ! in_array( $log->file_name, $days ) ) {
				$log->delete();
			}
		}
	}

	/**
	 * Set message with params for translation
	 *
	 * @param string $key
	 * @param array $params
	 *
	 * @return string
	 */
	public static function set_log_message( $key, $params = null ) {
		if ( is_null( $params ) || ( is_array( $params ) && count( $params ) == 0 ) ) {
			return $key;
		}
		$all_params = array();
		foreach ( $params as $param => $value ) {
			$value        = str_replace( array( '|', '==' ), array( '', '' ), $value );
			$all_params[] = $param . '==' . $value;
		}
		$message = $key . '[' . join( '|', $all_params ) . ']';

		return $message;
	}

	/**
	 * Decode message with params for translation
	 *
	 * @param string $message Key to translate
	 * @param string $iso_code Language translation iso code
	 * @param mixed $params array Parameters to display in the translation message
	 *
	 * @return string
	 */
	public static function decode_log_message( $message, $iso_code = null, $params = null ) {
		if ( preg_match( '/^(([a-z\_]*\.){1,3}[a-z\_]*)(\[(.*)\]|)$/', $message, $result ) ) {
			if ( isset( $result[1] ) ) {
				$key = $result[1];
			}
			if ( isset( $result[4] ) && is_null( $params ) ) {
				$str_param  = $result[4];
				$all_params = explode( '|', $str_param );
				foreach ( $all_params as $param ) {
					$result               = explode( '==', $param );
					$params[ $result[0] ] = $result[1];
				}
			}
			$locale  = new Lengow_Translation();
			$message = $locale->t( $key, $params, $iso_code );
		}

		return $message;
	}
}

