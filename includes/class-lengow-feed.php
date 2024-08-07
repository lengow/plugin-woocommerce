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
 * Lengow_Feed Class.
 */
class Lengow_Feed {

	/* Feed formats */
	const FORMAT_CSV  = 'csv';
	const FORMAT_YAML = 'yaml';
	const FORMAT_XML  = 'xml';
	const FORMAT_JSON = 'json';

	/* Content types */
	const HEADER = 'header';
	const BODY   = 'body';
	const FOOTER = 'footer';

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
	 * @var array formats available for export.
	 */
	public static $available_formats = array(
		self::FORMAT_CSV,
		self::FORMAT_YAML,
		self::FORMAT_XML,
		self::FORMAT_JSON,
	);

	/**
	 * @var Lengow_File temporary export file.
	 */
	private $file;

	/**
	 * @var string feed format.
	 */
	private $format;

	/**
	 * @var boolean generate file or not.
	 */
	private $stream;

	/**
	 * @var boolean use legacy fields.
	 */
	private $legacy;

	/**
	 * @var string full export folder.
	 */
	private $export_folder;

	/**
	 * Construct a new Lengow feed.
	 *
	 * @param boolean $stream feed in file or not
	 * @param string  $format feed format
	 * @param boolean $legacy use legacy fields
	 *
	 * @throws Lengow_Exception Unable to create folder
	 */
	public function __construct( $stream, $format, $legacy ) {
		$this->stream = $stream;
		$this->format = $format;
		$this->legacy = $legacy;
		if ( ! $this->stream ) {
			$this->_init_export_file();
		}
	}

	/**
	 * Create export file.
	 *
	 * @throws Lengow_Exception Unable to create folder
	 */
	private function _init_export_file() {
		$sep                 = DIRECTORY_SEPARATOR;
		$this->export_folder = Lengow_Main::FOLDER_EXPORT;
		$folder_path         = LENGOW_PLUGIN_PATH . $sep . $this->export_folder;
		if ( ! file_exists( $folder_path ) && ! mkdir( $folder_path ) && ! is_dir( $folder_path ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message(
					'log.export.error_unable_to_create_folder',
					array( 'folder_path' => $folder_path )
				)
			);
		}
		$file_name  = 'flux-' . time() . '.' . $this->format;
		$this->file = new Lengow_File( $this->export_folder, $file_name );
	}

	/**
	 * Write feed.
	 *
	 * @param string       $type (header, body or footer)
	 * @param array        $data export data
	 * @param boolean|null $is_first is first product to export
	 * @param boolean|null $max_character Max characters for yaml format
	 *
	 * @return string
	 */
	public function write( $type, $data = array(), $is_first = null, $max_character = null ) {

				$header = '';
				$body   = '';
				$footer = '';
		switch ( $type ) {
			case self::HEADER:
				if ( $this->stream ) {
					header( $this->get_html_header() );
					if ( self::FORMAT_CSV === $this->format ) {
						header( 'Content-Disposition: attachment; filename=feed.csv' );
					}
				}
				$header = $this->get_header( $data );
				break;
			case self::BODY:
				$body = $this->get_body( $data, $is_first, $max_character );
				break;
			case self::FOOTER:
				$footer = $this->get_footer();
				break;
		}

		if ( ! $this->stream ) {
			$this->file->write( $header . $body . $footer );
			return '';
		}

				return $header . $body . $footer;
	}

	/**
	 * Return HTML header according to the given format.
	 *
	 * @return string
	 */
	private function get_html_header() {
		switch ( $this->format ) {
			case self::FORMAT_CSV:
			default:
				return 'Content-Type: text/csv; charset=UTF-8';
			case self::FORMAT_XML:
				return 'Content-Type: application/xml; charset=UTF-8';
			case self::FORMAT_JSON:
				return 'Content-Type: application/json; charset=UTF-8';
			case self::FORMAT_YAML:
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
	private function get_header( $data ) {
		switch ( $this->format ) {
			case self::FORMAT_CSV:
			default:
				$header = '';
				foreach ( $data as $field ) {
					$header .= self::PROTECTION . self::format_fields( $field, self::FORMAT_CSV, $this->legacy )
								. self::PROTECTION . self::CSV_SEPARATOR;
				}

				return rtrim( $header, self::CSV_SEPARATOR ) . self::EOL;
			case self::FORMAT_XML:
				return '<?xml version="1.0" encoding="UTF-8"?>' . self::EOL
						. '<catalog>' . self::EOL;
			case self::FORMAT_JSON:
				return '{"catalog":[';
			case self::FORMAT_YAML:
				return '"catalog":' . self::EOL;
		}
	}

	/**
	 * Get feed body.
	 *
	 * @param array   $data feed data
	 * @param boolean $is_first is first product to export
	 * @param integer $max_character max characters for yaml format
	 *
	 * @return string
	 */
	private function get_body( $data, $is_first, $max_character ) {
		switch ( $this->format ) {
			case self::FORMAT_CSV:
			default:
				$content = '';
				foreach ( $data as $value ) {
					$content .= self::PROTECTION . $value . self::PROTECTION . self::CSV_SEPARATOR;
				}

				return rtrim( $content, self::CSV_SEPARATOR ) . self::EOL;
			case self::FORMAT_XML:
				$content = '<product>';
				foreach ( $data as $field => $value ) {
					$field    = self::format_fields( $field, self::FORMAT_XML, $this->legacy );
					$content .= '<' . $field . '><![CDATA[' . $value . ']]></' . $field . '>' . self::EOL;
				}
				$content .= '</product>' . self::EOL;

				return $content;
			case self::FORMAT_JSON:
				$content    = $is_first ? '' : ',';
				$json_array = array();
				foreach ( $data as $field => $value ) {
					$field                = self::format_fields( $field, self::FORMAT_JSON, $this->legacy );
					$json_array[ $field ] = $value;
				}
				$content .= wp_json_encode( $json_array );

				return $content;
			case self::FORMAT_YAML:
				if ( 1 === $max_character % 2 ) {
					++$max_character;
				} else {
					$max_character += 2;
				}
				$content = '  ' . self::PROTECTION . 'product' . self::PROTECTION . ':' . self::EOL;
				foreach ( $data as $field => $value ) {
					$field    = self::format_fields( $field, self::FORMAT_YAML, $this->legacy );
					$content .= '    ' . self::PROTECTION . $field . self::PROTECTION . ':';
					$content .= $this->indent_yaml( $field, $max_character ) . $value . self::EOL;
				}

				return $content;
		}
	}

	/**
	 * Return feed footer.
	 *
	 * @return string
	 */
	private function get_footer() {
		switch ( $this->format ) {
			case self::FORMAT_XML:
				return '</catalog>';
			case self::FORMAT_JSON:
				return ']}';
			default:
				return '';
		}
	}



	/**
	 * Finalize export generation.
	 *
	 * @return boolean
	 *
	 * @throws Lengow_Exception
	 */
	public function end() {

		if ( ! $this->stream ) {
			$old_file_name = 'flux.' . $this->format;
			$old_file      = new Lengow_File( $this->export_folder, $old_file_name );
			if ( $old_file->exists() ) {
				$old_file_path = $old_file->get_path();
				$old_file->delete();
			}
			if ( isset( $old_file_path ) ) {
				$rename = $this->file->rename( $old_file_path );
			} else {
				$rename = $this->file->rename( $this->file->get_folder_path() . DIRECTORY_SEPARATOR . $old_file_name );
			}
			$this->file->file_name = $old_file_name;

			return $rename;
		}

		return true;
	}

	/**
	 * Format field names according to the given format.
	 *
	 * @param string  $str field name
	 * @param string  $format export format
	 * @param boolean $legacy export legacy field or not
	 *
	 * @return string
	 */
	public static function format_fields( $str, $format, $legacy = false ) {
		switch ( $format ) {
			case self::FORMAT_CSV:
				if ( $legacy ) {
					return strtoupper(
						substr(
							preg_replace(
								'/[^a-zA-Z0-9_]+/',
								'',
								str_replace( array( ' ', '\'' ), '_', Lengow_Main::replace_accented_chars( $str ) )
							),
							0,
							58
						)
					);
				}

				return strtolower(
					substr(
						preg_replace(
							'/[^a-zA-Z0-9_]+/',
							'',
							str_replace( array( ' ', '\'' ), '_', Lengow_Main::replace_accented_chars( $str ) )
						),
						0,
						58
					)
				);
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
	private function indent_yaml( $name, $max_size ) {
		$strlen = strlen( $name );
		$spaces = '';
		for ( $i = $strlen; $i < $max_size; $i++ ) {
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
		return $this->file->get_link();
	}
}
