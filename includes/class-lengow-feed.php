<?php
/**
 * All options to create a specific format
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
 * @category   	Lengow
 * @package    	lengow-woocommerce
 * @subpackage 	includes
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
 * @license    	https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Feed Class.
 */
class Lengow_Feed {

	/**
	 * @var string  CSV protection.
	 */
	const PROTECTION = '"';

	/**
	 * @var string CSV separator.
	 */
	const CSV_SEPARATOR = '|';

	/**
	 * @var string end of line.
	 */
	const EOL = "\r\n";

	/**
	 * @var Lengow_File temporary export file.
	 */
	private $_file;

	/**
	 * @var string feed format.
	 */
	private $_format;

	/**
	 * @var boolean generate file or not.
	 */
	private $_stream;

	/**
	 * @var boolean use legacy fields.
	 */
	private $_legacy;


	/**
	 * @var string full export folder.
	 */
	private $_export_folder;

	/**
	 * @var array formats available for export.
	 */
	public static $available_formats = array(
		'csv',
		'yaml',
		'xml',
		'json',
	);

	/**
	 * @var string Lengow export folder.
	 */
	public static $lengow_export_folder = 'export';

	/**
	 * Construct a new Lengow feed.
	 *
	 * @param boolean $stream feed in file or not
	 * @param string $format feed format
	 * @param boolean $legacy use legacy fields
	 */
	public function __construct( $stream, $format, $legacy ) {
		$this->_stream = $stream;
		$this->_format = $format;
		$this->_legacy = $legacy;
		if ( ! $this->_stream ) {
			$this->_init_export_file();
		}
	}

	/**
	 * Create export file.
	 *
	 * @throws Lengow_Exception Unable to create folder
	 */
	private function _init_export_file() {
		$sep                  = DIRECTORY_SEPARATOR;
		$this->_export_folder = self::$lengow_export_folder;
		$folder_path          = LENGOW_PLUGIN_PATH . $sep . $this->_export_folder;
		if ( ! file_exists( $folder_path ) ) {
			if ( ! mkdir( $folder_path ) ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message(
						'log.export.error_unable_to_create_folder',
						array( 'folder_path' => $folder_path )
					)
				);
			}
		}
		$file_name   = 'flux-' . time() . '.' . $this->_format;
		$this->_file = new Lengow_File( $this->_export_folder, $file_name );
	}

	/**
	 * Write feed.
	 *
	 * @param string $type (header, body or footer)
	 * @param array $data export data
	 * @param boolean $is_first is first product to export
	 * @param boolean $max_character Max characters for yaml format
	 */
	public function write( $type, $data = array(), $is_first = null, $max_character = null ) {
		switch ( $type ) {
			case 'header':
				if ( $this->_stream ) {
					header( $this->_get_html_header() );
					if ( $this->_format == 'csv' ) {
						header( 'Content-Disposition: attachment; filename=feed.csv' );
					}
				}
				$header = $this->_get_header( $data );
				$this->_flush( $header );
				break;
			case 'body':
				$body = $this->_get_body( $data, $is_first, $max_character );
				$this->_flush( $body );
				break;
			case 'footer':
				$footer = $this->_get_footer();
				$this->_flush( $footer );
				break;
		}
	}

	/**
	 * Return HTML header according to the given format.
	 *
	 * @return string
	 */
	private function _get_html_header() {
		switch ( $this->_format ) {
			case 'csv':
				return 'Content-Type: text/csv; charset=UTF-8';
			case 'xml':
				return 'Content-Type: application/xml; charset=UTF-8';
			case 'json':
				return 'Content-Type: application/json; charset=UTF-8';
			case 'yaml':
				return 'Content-Type: text/x-yaml; charset=UTF-8';
		}
	}

	/**
	 * Return feed header.
	 *
	 * @param array $data feed data
	 *
	 * @return string
	 */
	private function _get_header( $data ) {
		switch ( $this->_format ) {
			case 'csv':
				$header = '';
				foreach ( $data as $field ) {
					$header .= self::PROTECTION . $this->_format_fields( $field )
						. self::PROTECTION . self::CSV_SEPARATOR;
				}

				return rtrim( $header, self::CSV_SEPARATOR ) . self::EOL;
			case 'xml':
				return '<?xml version="1.0" encoding="UTF-8"?>' . self::EOL
					. '<catalog>' . self::EOL;
			case 'json':
				return '{"catalog":[';
			case 'yaml':
				return '"catalog":' . self::EOL;
		}
	}

	/**
	 * Get feed body.
	 *
	 * @param array $data feed data
	 * @param boolean $is_first is first product to export
	 * @param integer $max_character max characters for yaml format
	 *
	 * @return string
	 */
	private function _get_body( $data, $is_first, $max_character ) {
		switch ( $this->_format ) {
			case 'csv':
				$content = '';
				foreach ( $data as $value ) {
					$content .= self::PROTECTION . $value . self::PROTECTION . self::CSV_SEPARATOR;
				}

				return rtrim( $content, self::CSV_SEPARATOR ) . self::EOL;
			case 'xml':
				$content = '<product>';
				foreach ( $data as $field => $value ) {
					$field = $this->_format_fields( $field );
					$content .= '<' . $field . '><![CDATA[' . $value . ']]></' . $field . '>' . self::EOL;
				}
				$content .= '</product>' . self::EOL;

				return $content;
			case 'json':
				$content    = $is_first ? '' : ',';
				$json_array = array();
				foreach ( $data as $field => $value ) {
					$field                = $this->_format_fields( $field );
					$json_array[ $field ] = $value;
				}
				$content .= json_encode( $json_array );

				return $content;
			case 'yaml':
				if ( $max_character % 2 == 1 ) {
					$max_character = $max_character + 1;
				} else {
					$max_character = $max_character + 2;
				}
				$content = '  ' . self::PROTECTION . 'product' . self::PROTECTION . ':' . self::EOL;
				foreach ( $data as $field => $value ) {
					$field = $this->_format_fields( $field );
					$content .= '    ' . self::PROTECTION . $field . self::PROTECTION . ':';
					$content .= $this->_indent_yaml( $field, $max_character ) . (string) $value . self::EOL;
				}

				return $content;
		}
	}

	/**
	 * Return feed footer.
	 *
	 * @return string
	 */
	private function _get_footer() {
		switch ( $this->_format ) {
			case 'xml':
				return '</catalog>';
			case 'json':
				return ']}';
			default:
				return '';
		}
	}

	/**
	 * Flush feed content.
	 *
	 * @param string $content feed content to be flushed
	 *
	 */
	private function _flush( $content ) {
		if ( $this->_stream ) {
			echo $content;
			flush();
		} else {
			$this->_file->write( $content );
		}
	}

	/**
	 * Finalize export generation.
	 *
	 * @return string|boolean
	 */
	public function end() {
		$this->write( 'footer' );
		if ( ! $this->_stream ) {
			$old_file_name = 'flux.' . $this->_format;
			$old_file      = new Lengow_File( $this->_export_folder, $old_file_name );

			if ( $old_file->exists() ) {
				$old_file_path = $old_file->get_path();
				$old_file->delete();
			}

			if ( isset( $old_file_path ) ) {
				$rename                 = $this->_file->rename( $old_file_path );
				$this->_file->file_name = $old_file_name;
			} else {
				$sep                    = DIRECTORY_SEPARATOR;
				$rename                 = $this->_file->rename(
					$this->_file->get_folder_path() . $sep . $old_file_name
				);
				$this->_file->file_name = $old_file_name;
			}

			return $rename;
		}

		return true;
	}

	/**
	 * Format field names according to the given format.
	 *
	 * @param string $str field name
	 *
	 * @return string
	 */
	private function _format_fields( $str ) {
		switch ( $this->_format ) {
			case 'csv':
				if ( $this->_legacy ) {
					return substr(
						strtoupper(
							preg_replace(
								'/[^a-zA-Z0-9_]+/',
								'',
								str_replace( array( ' ', '\'' ), '_', Lengow_Main::replace_accented_chars( $str ) )
							)
						),
						0,
						58
					);
				} else {
					return substr(
						strtolower(
							preg_replace(
								'/[^a-zA-Z0-9_]+/',
								'',
								str_replace( array( ' ', '\'' ), '_', Lengow_Main::replace_accented_chars( $str ) )
							)
						),
						0,
						58
					);
				}
				break;
			default:
				return strtolower(
					preg_replace(
						'/[^a-zA-Z0-9_]+/',
						'',
						str_replace( array( ' ', '\'' ), '_', Lengow_Main::replace_accented_chars( $str ) )
					)
				);
		}
	}

	/**
	 * For YAML, add spaces to have good indentation.
	 *
	 * @param string $name the field name
	 * @param string $max_size space limit
	 *
	 * @return string
	 */
	private function _indent_yaml( $name, $max_size ) {
		$strlen = strlen( $name );
		$spaces = '';
		for ( $i = $strlen; $i < $max_size; $i ++ ) {
			$spaces .= ' ';
		}

		return $spaces;
	}

	/**
	 * Get feed URL.
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->_file->get_link();
	}
}
