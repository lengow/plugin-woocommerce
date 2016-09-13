<?php
/**
 * All components to manage the translation module
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
 * Lengow_Translation Class.
 */
class Lengow_Translation {

	/**
	 * Version
	 */
	protected static $translation = null;

	/**
	 * Fallback iso code
	 */
	public $fallback_iso_code = 'en_GB';

	/**
	 * Iso code
	 */
	protected $iso_code = null;

	/**
	 * Force iso code for log and toolbox
	 */
	public static $force_iso_code = null;

	/**
	 * Construct a new Lengow translation
	 */
	public function __construct() {
		$this->iso_code = get_locale();
	}

	/**
	 * Translate message
	 *
	 * @param string $message localization key
	 * @param array $args replace word in string
	 * @param array $iso_code iso code
	 *
	 * @return mixed
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
	 * Translate string
	 *
	 * @param string $text
	 * @param array $args
	 *
	 * @return string Final Translate string
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
	 * Load csv file
	 *
	 * @param string $iso_code
	 * @param string $filename file location
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

