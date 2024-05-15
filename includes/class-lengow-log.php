<?php
/**
 * All components to generate logs
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
 * Lengow_Log Class.
 */
class Lengow_Log {

	/* Log category codes */
	const CODE_INSTALL     = 'Install';
	const CODE_CONNECTION  = 'Connection';
	const CODE_SETTING     = 'Setting';
	const CODE_CONNECTOR   = 'Connector';
	const CODE_EXPORT      = 'Export';
	const CODE_IMPORT      = 'Import';
	const CODE_ACTION      = 'Action';
	const CODE_MAIL_REPORT = 'Mail Report';

	/* Log params for export */
	const LOG_DATE = 'date';
	const LOG_LINK = 'link';

	/**
	 * @var Lengow_File Lengow file instance.
	 */
	private $file;

		/**
		 *
		 * @var bool $display
		 */
		private $display;

		/**
		 * Construct a new Lengow log.
		 *
		 * @param string $file_name log file name
		 *
		 * @throws Lengow_Exception
		 */
	public function __construct( $file_name = null ) {
		if ( empty( $file_name ) ) {
			$file_name = 'logs-' . date( Lengow_Main::DATE_DAY ) . '.txt';
		}
		$this->file = new Lengow_File( Lengow_Main::FOLDER_LOG, $file_name );
	}

	/**
	 * Write log.
	 *
	 * @param string      $category Category
	 * @param string      $message log message
	 * @param boolean     $display display on screen
	 * @param string|null $marketplace_sku lengow order id
	 */
	public function write( $category, $message = '', $display = false, $marketplace_sku = null ) {
		$decoded_message = Lengow_Main::decode_log_message( $message, Lengow_Translation::DEFAULT_ISO_CODE );
		$log             = get_date_from_gmt( date( Lengow_Main::DATE_FULL ) );
		$log            .= ' - ' . ( empty( $category ) ? '' : '[' . $category . '] ' );
		$log            .= empty( $marketplace_sku ) ? '' : 'order ' . $marketplace_sku . ' : ';
		$log            .= $decoded_message . "\r\n";
		if ( $display ) {
					$this->display = true;
		}
		$this->file->write( $log );
	}

	/**
	 * Get log files.
	 *
	 * @return array
	 */
	public static function get_files() {
		return Lengow_File::get_files_from_folder( Lengow_Main::FOLDER_LOG );
	}

	/**
	 * Get log files path.
	 *
	 * @return array
	 */
	public static function get_paths() {
		$logs  = array();
		$files = self::get_files();
		if ( empty( $files ) ) {
			return $logs;
		}
		foreach ( $files as $file ) {
			preg_match( '/^logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt$/', $file->file_name, $match );
			$date   = $match[1];
			$logs[] = array(
				self::LOG_DATE => $date,
				self::LOG_LINK => Lengow_Main::get_toolbox_url()
									. '&' . Lengow_Toolbox::PARAM_TOOLBOX_ACTION . '=' . Lengow_Toolbox::ACTION_LOG
									. '&' . Lengow_Toolbox::PARAM_DATE . '=' . urlencode( $date ),
			);
		}

		return array_reverse( $logs );
	}

	/**
	 * Download log file.
	 *
	 * @param string|null $date date for a specific log file
	 */
	public static function download( $date = null ) {
		/** @var Lengow_File[] $log_files */
		if ( $date && preg_match( '/^(\d{4}-\d{2}-\d{2})$/', $date ) ) {
			$log_files = false;
			$file      = 'logs-' . $date . '.txt';
			$file_name = $date . '.txt';
			$sep       = DIRECTORY_SEPARATOR;
			$file_path = LENGOW_PLUGIN_PATH . $sep . Lengow_Main::FOLDER_LOG . $sep . $file;
			if ( file_exists( $file_path ) ) {
				try {
					$log_files = array( new Lengow_File( Lengow_Main::FOLDER_LOG, $file ) );
				} catch ( Lengow_Exception $e ) {
					$log_files = array();
				}
			}
		} else {
			$file_name = 'logs.txt';
			$log_files = self::get_files();
		}
		$contents = '';
		if ( $log_files ) {
			foreach ( $log_files as $log_file ) {
				$file_path = $log_file->get_path();
				$handle    = fopen( $file_path, 'rb' );
				$file_size = filesize( $file_path );
				if ( $file_size > 0 ) {
					$contents .= fread( $handle, $file_size );
				}
			}
		}
		header( 'Content-type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		echo wp_kses_post( $contents );
		exit();
	}

		/**
		 * Returns log content for display
		 *
		 * @return string
		 */
	public function getFileLogContent( $format = 'txt' ) {
		if ( ! $this->display ) {
			return '';
		}
		$contentLog = (string) file_get_contents( $this->file->get_path() );

		if ( $format === 'txt' ) {
			return $contentLog;
		}

		$contentLines = array();
		$explodeLines = explode( "\r\n", $contentLog );
		foreach ( $explodeLines as $index => $logLine ) {

			$contentLines[] = $logLine;
		}

		return wp_json_encode( $contentLines );
	}

	/**
	 * Log PHP errors
	 *
	 * @return void
	 */
	public static function register_shutdown_function(): void {
		ini_set( 'log_errors_max_len', 10240 );
		register_shutdown_function(
			function () {
				$error = error_get_last();
				if ( $error ) {
						Lengow_Main::log(
							'Error',
							$error['type'] . ': ' . $error['message']
							. PHP_EOL . 'in ' . $error['file'] . ' on line ' . $error['line']
						);
				}
			}
		);
	}
}
