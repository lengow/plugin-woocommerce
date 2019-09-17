<?php
/**
 * All components to manage the translation module
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
 * Lengow_Translation Class.
 */
class Lengow_Translation {

	/**
	 * @var array|null all translations.
	 */
	protected static $translation = null;

	/**
	 * @var string fallback iso code.
	 */
	public $fallback_iso_code = 'en_GB';

	/**
	 * @var string|null iso code.
	 */
	protected $iso_code = null;

	/**
	 * @var string|null force iso code for log and toolbox.
	 */
	public static $force_iso_code = null;

	/**
	 * Construct a new Lengow translation.
	 */
	public function __construct() {
		$this->iso_code = get_locale();
	}

	/**
	 * Translate message.
	 *
	 * @param string $message localization key
	 * @param array $args replace word in string
	 * @param array|null $iso_code iso code
	 *
	 * @return string
	 */
	public function t( $message, $args = array(), $iso_code = null ) {
		if ( ! is_null( self::$force_iso_code ) ) {
			$iso_code = self::$force_iso_code;
		}
		if ( is_null( $iso_code ) ) {
			$iso_code = $this->iso_code;
		}
		if ( ! isset( self::$translation[ $iso_code ] ) ) {
			$this->load_file( $iso_code );
		}
		if ( isset( self::$translation[ $iso_code ][ $message ] ) ) {
			return $this->translate_final( self::$translation[ $iso_code ][ $message ], $args );
		} else {
			if ( ! isset( self::$translation[ $this->fallback_iso_code ] ) ) {
				$this->load_file( $this->fallback_iso_code );
			}
			if ( isset( self::$translation[ $this->fallback_iso_code ][ $message ] ) ) {
				return $this->translate_final( self::$translation[ $this->fallback_iso_code ][ $message ], $args );
			} else {
				return 'Missing Translation [' . $message . ']';
			}
		}
	}

	/**
	 * Translate string.
	 *
	 * @param string $text localization key
	 * @param array $args replace word in string
	 *
	 * @return string
	 */
	protected function translate_final( $text, $args ) {
		if ( $args ) {
			$params = array();
			$values = array();
			foreach ( $args as $key => $value ) {
				$params[] = '%{' . $key . '}';
				$values[] = $value;
			}

			return str_replace( $params, $values, $text );
		} else {
			return $text;
		}
	}

	/**
	 * Load csv file.
	 *
	 * @param string $iso_code translation iso code
	 * @param string|null $filename file location
	 *
	 * @return boolean
	 */
	public function load_file( $iso_code, $filename = null ) {
		if ( ! $filename ) {
			$filename = LENGOW_PLUGIN_PATH . '/translations/' . $iso_code . '.csv';
		}
		$translation = array();
		if ( file_exists( $filename ) ) {
			if ( ( $handle = fopen( $filename, "r" ) ) !== false ) {
				while ( ( $data = fgetcsv( $handle, 1000, "|" ) ) !== false ) {
					$translation[ $data[0] ] = $data[1];
				}
				fclose( $handle );
			}
		}
		self::$translation[ $iso_code ] = $translation;

		return count( $translation ) > 0;
	}
}
