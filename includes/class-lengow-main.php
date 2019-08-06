<?php
/**
 * Utilities functions
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
 * @author      Team module <team-module@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Main Class.
 */
class Lengow_Main {

	/**
	 * @var array Lengow Authorized IPs.
	 */
	private static $_ips_lengow = array(
		'127.0.0.1',
		'10.0.4.150',
		'46.19.183.204',
		'46.19.183.217',
		'46.19.183.218',
		'46.19.183.219',
		'46.19.183.222',
		'52.50.58.130',
		'89.107.175.172',
		'89.107.175.185',
		'89.107.175.186',
		'89.107.175.187',
		'90.63.241.226',
		'109.190.189.175',
		'146.185.41.180',
		'146.185.41.177',
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
	);

	/**
	 * @var array Marketplaces collection.
	 */
	public static $registers = array();

	/**
	 * @var Lengow_Log Lengow log file instance.
	 */
	public static $log;

	/**
	 * @var integer life of log files in days.
	 */
	public static $log_life = 20;

	/**
	 * @var string Lengow configuration folder name.
	 */
	public static $lengow_config_folder = 'config';

	/**
	 * @var array WooCommerce product types.
	 */
	public static $product_types = array(
		'simple'   => 'Simple Product',
		'variable' => 'Variable Product',
		'external' => 'External Product',
		'grouped'  => 'Grouped Product',
	);

	/**
	 * Get export webservice links.
	 *
	 * @return string
	 */
	public static function get_export_url() {
		$sep = DIRECTORY_SEPARATOR;

		return LENGOW_PLUGIN_URL . $sep . 'webservice' . $sep . 'export.php?token=' . self::get_token();
	}

	/**
	 * Get cron webservice links.
	 *
	 * @return string
	 */
	public static function get_cron_url() {
		$sep = DIRECTORY_SEPARATOR;

		return LENGOW_PLUGIN_URL . $sep . 'webservice' . $sep . 'cron.php?token=' . self::get_token();
	}

	/**
	 * Check Webservice access (export and cron).
	 *
	 * @param string $token store token
	 *
	 * @return boolean
	 */
	public static function check_webservice_access( $token ) {
		if ( ! (bool) Lengow_Configuration::get( 'lengow_ip_enabled' ) && self::check_token( $token ) ) {
			return true;
		}
		if ( self::check_ip() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if token is correct.
	 *
	 * @param string $token store token
	 *
	 * @return boolean
	 */
	public static function check_token( $token ) {
		$storeToken = self::get_token();
		if ( $token === $storeToken ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if current IP is authorized.
	 *
	 * @param boolean $toolbox force check ip for toolbox
	 *
	 * @return boolean
	 */
	public static function check_ip( $toolbox = false ) {
		$ips = Lengow_Configuration::get( 'lengow_authorized_ip' );
		if ( strlen( $ips ) > 0 && ( (bool) Lengow_Configuration::get( 'lengow_ip_enabled' ) || $toolbox ) ) {
			$ips            = trim( str_replace( array( "\r\n", ',', '-', '|', ' ' ), ';', $ips ), ';' );
			$ips            = array_filter( explode( ';', $ips ) );
			$authorized_ips = count( $ips ) > 0 ? array_merge( $ips, self::$_ips_lengow ) : self::$_ips_lengow;
		} else {
			$authorized_ips = self::$_ips_lengow;
		}
		if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
			$authorized_ips[] = $_SERVER['SERVER_ADDR'];
		}
		$hostname_ip = $_SERVER['REMOTE_ADDR'];
		if ( in_array( $hostname_ip, $authorized_ips ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Generate token.
	 *
	 * @return string
	 */
	public static function get_token() {
		$token = Lengow_Configuration::get( 'lengow_token' );
		if ( $token && strlen( $token ) > 0 ) {
			return $token;
		} else {
			$token = bin2hex( openssl_random_pseudo_bytes( 16 ) );
			Lengow_Configuration::update_value( 'lengow_token', $token );
		}

		return $token;
	}

	/**
	 * Check if shop is already synchronised.
	 *
	 * @param $token mixed Token
	 *
	 * @return boolean
	 */
	public static function find_by_token( $token ) {
		$lengow_token = Lengow_Configuration::get( 'lengow_token' );
		if ( $lengow_token === $token ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Marketplace singleton.
	 *
	 * @param string $name marketplace name
	 *
	 * @return Lengow_Marketplace
	 *
	 * @throws Lengow_Exception
	 */
	public static function get_marketplace_singleton( $name ) {
		if ( ! isset( self::$registers[ $name ] ) ) {
			self::$registers[ $name ] = new Lengow_Marketplace( $name );
		}

		return self::$registers[ $name ];
	}

	/**
	 * Get version of woocommerce
	 *
	 * @return string
	 */
	public static function get_woocommerce_version() {
		global $woocommerce;

		return $woocommerce->version;
	}

	/**
	 * Writes log.
	 *
	 * @param string $category Category log
	 * @param string $txt log message
	 * @param boolean $force_output output on screen
	 * @param string|null $marketplace_sku lengow marketplace sku
	 */
	public static function log( $category, $txt, $force_output = false, $marketplace_sku = null ) {
		$log = self::get_log_instance();
		if ( $log ) {
			$log->write( $category, $txt, $force_output, $marketplace_sku );
		}
	}

	/**
	 * Get log Instance.
	 *
	 * @return Lengow_Log|false
	 */
	public static function get_log_instance() {
		if ( is_null( self::$log ) ) {
			try {
				self::$log = new Lengow_Log();
			} catch ( Lengow_Exception $e ) {
				return false;
			}
		}

		return self::$log;
	}

	/**
	 * Suppress log files when too old.
	 */
	public static function clean_log() {
		$days   = array();
		$days[] = 'logs-' . date( 'Y-m-d' ) . '.txt';
		for ( $i = 1; $i < self::$log_life; $i ++ ) {
			$days[] = 'logs-' . date( 'Y-m-d', strtotime( '-' . $i . 'day' ) ) . '.txt';
		}
		/** @var Lengow_File[] $log_files */
		$log_files = Lengow_Log::get_files();
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
	 * Set message with params for translation.
	 *
	 * @param string $key log key to translate
	 * @param array|null $params parameters to display in the translation message
	 *
	 * @return string
	 */
	public static function set_log_message( $key, $params = null ) {
		if ( is_null( $params ) || ( is_array( $params ) && empty( $params ) ) ) {
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
	 * Decode message with params for translation.
	 *
	 * @param string $message key to translate
	 * @param string|null $iso_code language translation iso code
	 * @param array|null $params parameters to display in the translation message
	 *
	 * @return string
	 */
	public static function decode_log_message( $message, $iso_code = null, $params = null ) {
		if ( preg_match( '/^(([a-z\_]*\.){1,3}[a-z\_]*)(\[(.*)\]|)$/', $message, $result ) ) {
			if ( isset( $result[1] ) ) {
				$key = $result[1];
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
		}

		return $message;
	}

	/**
	 * Record the date of the last import.
	 *
	 * @param string $type last import type (cron or manual)
	 */
	public static function update_date_import( $type ) {
		if ( $type === 'cron' ) {
			Lengow_Configuration::update_value( 'lengow_last_import_cron', time() );
		} else {
			Lengow_Configuration::update_value( 'lengow_last_import_manual', time() );
		}
	}

	/**
	 * Get last import (type and timestamp).
	 *
	 * @return array
	 */
	public static function get_last_import() {
		$timestamp_cron   = Lengow_Configuration::get( 'lengow_last_import_cron' );
		$timestamp_manual = Lengow_Configuration::get( 'lengow_last_import_manual' );
		if ( $timestamp_cron && $timestamp_manual ) {
			if ( (int) $timestamp_cron > (int) $timestamp_manual ) {
				return array( 'type' => 'cron', 'timestamp' => (int) $timestamp_cron );
			} else {
				return array( 'type' => 'manual', 'timestamp' => (int) $timestamp_manual );
			}
		} elseif ( $timestamp_cron && ! $timestamp_manual ) {
			return array( 'type' => 'cron', 'timestamp' => (int) $timestamp_cron );
		} elseif ( $timestamp_manual && ! $timestamp_cron ) {
			return array( 'type' => 'manual', 'timestamp' => (int) $timestamp_manual );
		}

		return array( 'type' => 'none', 'timestamp' => 'none' );
	}

	/**
	 * Clean data.
	 *
	 * @param string $str the content
	 *
	 * @return string
	 */
	public static function clean_data( $str ) {
		$str = preg_replace(
			'/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]
			|[\x00-\x7F][\x80-\xBF]+
			|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*
			|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})
			|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
			'',
			$str
		);
		$str = preg_replace(
			'/\xE0[\x80-\x9F][\x80-\xBF]|\xED[\xA0-\xBF][\x80-\xBF]/S',
			'',
			$str
		);
		$str = preg_replace( '/[\s]+/', ' ', $str );
		$str = trim( $str );
		$str = str_replace(
			array(
				'&nbsp;',
				'|',
				'"',
				'’',
				'&#39;',
				'&#150;',
				chr( 9 ),
				chr( 10 ),
				chr( 13 ),
				chr( 31 ),
				chr( 30 ),
				chr( 29 ),
				chr( 28 ),
				"\n",
				"\r",
			),
			array(
				' ',
				' ',
				'\'',
				'\'',
				' ',
				'-',
				' ',
				' ',
				' ',
				'',
				'',
				'',
				'',
				'',
				'',
			),
			$str
		);

		return $str;
	}

	/**
	 * Clean html.
	 *
	 * @param string $str the html content
	 *
	 * @return string
	 */
	public static function clean_html( $str ) {
		$str     = str_replace( '<br />', ' ', nl2br( $str ) );
		$str     = trim( strip_tags( htmlspecialchars_decode( $str ) ) );
		$str     = preg_replace( '`[\s]+`sim', ' ', $str );
		$str     = preg_replace( '`"`sim', '', $str );
		$str     = nl2br( $str );
		$pattern = '@<[\/\!]*?[^<>]*?>@si';
		$str     = preg_replace( $pattern, ' ', $str );
		$str     = preg_replace( '/[\s]+/', ' ', $str );
		$str     = trim( $str );
		$str     = str_replace( '&nbsp;', ' ', $str );
		$str     = str_replace( '|', ' ', $str );
		$str     = str_replace( '"', '\'', $str );
		$str     = str_replace( '’', '\'', $str );
		$str     = str_replace( '&#39;', '\' ', $str );
		$str     = str_replace( '&#150;', '-', $str );
		$str     = str_replace( chr( 9 ), ' ', $str );
		$str     = str_replace( chr( 10 ), ' ', $str );
		$str     = str_replace( chr( 13 ), ' ', $str );

		return $str;
	}

	/**
	 * Replace all accented chars by their equivalent non accented chars.
	 *
	 * @param string $str the content
	 *
	 * @return string
	 */
	public static function replace_accented_chars( $str ) {
		/* One source among others:
			http://www.tachyonsoft.com/uc0000.htm
			http://www.tachyonsoft.com/uc0001.htm
			http://www.tachyonsoft.com/uc0004.htm
		*/
		$patterns = array(
			/* Lowercase */
			/* a  */
			'/[\x{00E0}\x{00E1}\x{00E2}\x{00E3}\x{00E4}\x{00E5}\x{0101}\x{0103}\x{0105}\x{0430}\x{00C0}-\x{00C3}\x{1EA0}-\x{1EB7}]/u',
			/* b  */
			'/[\x{0431}]/u',
			/* c  */
			'/[\x{00E7}\x{0107}\x{0109}\x{010D}\x{0446}]/u',
			/* d  */
			'/[\x{010F}\x{0111}\x{0434}\x{0110}]/u',
			/* e  */
			'/[\x{00E8}\x{00E9}\x{00EA}\x{00EB}\x{0113}\x{0115}\x{0117}\x{0119}\x{011B}\x{0435}\x{044D}\x{00C8}-\x{00CA}\x{1EB8}-\x{1EC7}]/u',
			/* f  */
			'/[\x{0444}]/u',
			/* g  */
			'/[\x{011F}\x{0121}\x{0123}\x{0433}\x{0491}]/u',
			/* h  */
			'/[\x{0125}\x{0127}]/u',
			/* i  */
			'/[\x{00EC}\x{00ED}\x{00EE}\x{00EF}\x{0129}\x{012B}\x{012D}\x{012F}\x{0131}\x{0438}\x{0456}\x{00CC}\x{00CD}\x{1EC8}-\x{1ECB}\x{0128}]/u',
			/* j  */
			'/[\x{0135}\x{0439}]/u',
			/* k  */
			'/[\x{0137}\x{0138}\x{043A}]/u',
			/* l  */
			'/[\x{013A}\x{013C}\x{013E}\x{0140}\x{0142}\x{043B}]/u',
			/* m  */
			'/[\x{043C}]/u',
			/* n  */
			'/[\x{00F1}\x{0144}\x{0146}\x{0148}\x{0149}\x{014B}\x{043D}]/u',
			/* o  */
			'/[\x{00F2}\x{00F3}\x{00F4}\x{00F5}\x{00F6}\x{00F8}\x{014D}\x{014F}\x{0151}\x{043E}\x{00D2}-\x{00D5}\x{01A0}\x{01A1}\x{1ECC}-\x{1EE3}]/u',
			/* p  */
			'/[\x{043F}]/u',
			/* r  */
			'/[\x{0155}\x{0157}\x{0159}\x{0440}]/u',
			/* s  */
			'/[\x{015B}\x{015D}\x{015F}\x{0161}\x{0441}]/u',
			/* ss */
			'/[\x{00DF}]/u',
			/* t  */
			'/[\x{0163}\x{0165}\x{0167}\x{0442}]/u',
			/* u  */
			'/[\x{00F9}\x{00FA}\x{00FB}\x{00FC}\x{0169}\x{016B}\x{016D}\x{016F}\x{0171}\x{0173}\x{0443}\x{00D9}-\x{00DA}\x{0168}\x{01AF}\x{01B0}\x{1EE4}-\x{1EF1}]/u',
			/* v  */
			'/[\x{0432}]/u',
			/* w  */
			'/[\x{0175}]/u',
			/* y  */
			'/[\x{00FF}\x{0177}\x{00FD}\x{044B}\x{1EF2}-\x{1EF9}\x{00DD}]/u',
			/* z  */
			'/[\x{017A}\x{017C}\x{017E}\x{0437}]/u',
			/* ae */
			'/[\x{00E6}]/u',
			/* ch */
			'/[\x{0447}]/u',
			/* kh */
			'/[\x{0445}]/u',
			/* oe */
			'/[\x{0153}]/u',
			/* sh */
			'/[\x{0448}]/u',
			/* shh*/
			'/[\x{0449}]/u',
			/* ya */
			'/[\x{044F}]/u',
			/* ye */
			'/[\x{0454}]/u',
			/* yi */
			'/[\x{0457}]/u',
			/* yo */
			'/[\x{0451}]/u',
			/* yu */
			'/[\x{044E}]/u',
			/* zh */
			'/[\x{0436}]/u',

			/* Uppercase */
			/* A  */
			'/[\x{0100}\x{0102}\x{0104}\x{00C0}\x{00C1}\x{00C2}\x{00C3}\x{00C4}\x{00C5}\x{0410}]/u',
			/* B  */
			'/[\x{0411}]/u',
			/* C  */
			'/[\x{00C7}\x{0106}\x{0108}\x{010A}\x{010C}\x{0426}]/u',
			/* D  */
			'/[\x{010E}\x{0110}\x{0414}]/u',
			/* E  */
			'/[\x{00C8}\x{00C9}\x{00CA}\x{00CB}\x{0112}\x{0114}\x{0116}\x{0118}\x{011A}\x{0415}\x{042D}]/u',
			/* F  */
			'/[\x{0424}]/u',
			/* G  */
			'/[\x{011C}\x{011E}\x{0120}\x{0122}\x{0413}\x{0490}]/u',
			/* H  */
			'/[\x{0124}\x{0126}]/u',
			/* I  */
			'/[\x{0128}\x{012A}\x{012C}\x{012E}\x{0130}\x{0418}\x{0406}]/u',
			/* J  */
			'/[\x{0134}\x{0419}]/u',
			/* K  */
			'/[\x{0136}\x{041A}]/u',
			/* L  */
			'/[\x{0139}\x{013B}\x{013D}\x{0139}\x{0141}\x{041B}]/u',
			/* M  */
			'/[\x{041C}]/u',
			/* N  */
			'/[\x{00D1}\x{0143}\x{0145}\x{0147}\x{014A}\x{041D}]/u',
			/* O  */
			'/[\x{00D3}\x{014C}\x{014E}\x{0150}\x{041E}]/u',
			/* P  */
			'/[\x{041F}]/u',
			/* R  */
			'/[\x{0154}\x{0156}\x{0158}\x{0420}]/u',
			/* S  */
			'/[\x{015A}\x{015C}\x{015E}\x{0160}\x{0421}]/u',
			/* T  */
			'/[\x{0162}\x{0164}\x{0166}\x{0422}]/u',
			/* U  */
			'/[\x{00D9}\x{00DA}\x{00DB}\x{00DC}\x{0168}\x{016A}\x{016C}\x{016E}\x{0170}\x{0172}\x{0423}]/u',
			/* V  */
			'/[\x{0412}]/u',
			/* W  */
			'/[\x{0174}]/u',
			/* Y  */
			'/[\x{0176}\x{042B}]/u',
			/* Z  */
			'/[\x{0179}\x{017B}\x{017D}\x{0417}]/u',
			/* AE */
			'/[\x{00C6}]/u',
			/* CH */
			'/[\x{0427}]/u',
			/* KH */
			'/[\x{0425}]/u',
			/* OE */
			'/[\x{0152}]/u',
			/* SH */
			'/[\x{0428}]/u',
			/* SHH*/
			'/[\x{0429}]/u',
			/* YA */
			'/[\x{042F}]/u',
			/* YE */
			'/[\x{0404}]/u',
			/* YI */
			'/[\x{0407}]/u',
			/* YO */
			'/[\x{0401}]/u',
			/* YU */
			'/[\x{042E}]/u',
			/* ZH */
			'/[\x{0416}]/u',
		);

		// ö to oe.
		// å to aa.
		// ä to ae.
		$replacements = array(
			'a',
			'b',
			'c',
			'd',
			'e',
			'f',
			'g',
			'h',
			'i',
			'j',
			'k',
			'l',
			'm',
			'n',
			'o',
			'p',
			'r',
			's',
			'ss',
			't',
			'u',
			'v',
			'w',
			'y',
			'z',
			'ae',
			'ch',
			'kh',
			'oe',
			'sh',
			'shh',
			'ya',
			'ye',
			'yi',
			'yo',
			'yu',
			'zh',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'Y',
			'Z',
			'AE',
			'CH',
			'KH',
			'OE',
			'SH',
			'SHH',
			'YA',
			'YE',
			'YI',
			'YO',
			'YU',
			'ZH',
		);

		return preg_replace( $patterns, $replacements, $str );
	}
}
