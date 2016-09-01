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
	 * @var LengowLog Lengow log file instance
	 */
	public static $log;

	/**
	 * @var integer life of log files in days
	 */
	public static $LOG_LIFE = 20;

	/**
	 * Writes log
	 *
	 * @param string $category Category log
	 * @param string $txt log message
	 * @param boolean $force_output output on screen
	 * @param string $marketplace_sku lengow marketplace sku
	 */
	public static function log( $category, $txt, $force_output = false, $marketplace_sku = null ) {
		$log = self::getLogInstance();
		$log->write( $category, $txt, $force_output, $marketplace_sku );
	}

	/**
	 * Get log Instance
	 *
	 * @return LengowLog Lengow log file instance
	 */
	public static function getLogInstance() {
		if ( is_null( self::$log ) ) {
			self::$log = new Lengow_Log();
		}
		return self::$log;
	}

	/**
	 * Suppress log files when too old
	 */
	public static function cleanLog() {
		$log_files = Lengow_Log::getFiles();
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
	public static function setLogMessage( $key, $params = null ) {
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
	public static function decodeLogMessage( $message, $iso_code = null, $params = null ) {
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

