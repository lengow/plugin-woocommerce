<?php
/**
 * All components to generate logs
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
 * Lengow_Log Class.
 */
class Lengow_Log extends Lengow_File {

	/**
	 * @var string name of logs folder
	 */
	public static $LENGOW_LOGS_FOLDER = 'logs';

	/**
	 * @var Lengow_File Lengow file instance
	 */
	protected $file;

	public function __construct( $file_name = null ) {
		if ( empty( $file_name ) ) {
			$this->file_name = 'logs-' . date( 'Y-m-d' ) . '.txt';
		} else {
			$this->file_name = $file_name;
		}
		$this->file = new Lengow_File( self::$LENGOW_LOGS_FOLDER, $this->file_name );
	}

	/**
	 * Write log
	 *
	 * @param string $category Category
	 * @param string $message log message
	 * @param boolean $display display on screen
	 * @param string $marketplace_sku lengow order id
	 */
	public function write( $category, $message = "", $display = false, $marketplace_sku = null ) {
		$decoded_message = Lengow_Main::decode_log_message( $message, 'en_GB' );
		$log             = date( 'Y-m-d H:i:s' );
		$log .= ' - ' . ( empty( $category ) ? '' : '[' . $category . '] ' );
		$log .= '' . ( empty( $marketplace_sku ) ? '' : 'order ' . $marketplace_sku . ' : ' );
		$log .= $decoded_message . "\r\n";
		if ( $display ) {
			echo $log . '<br />';
			flush();
		}
		$this->file->write( $log );
	}

	/**
	 * Get log files
	 *
	 * @return array
	 */
	public static function get_files() {
		return Lengow_File::get_files_from_folder( self::$LENGOW_LOGS_FOLDER );
	}

	/**
	 * Get log files path
	 *
	 * @return mixed
	 */
	public static function get_paths() {
		$files = self::get_files();
		if ( empty( $files ) ) {
			return false;
		}
		$logs = array();
		foreach ( $files as $file ) {
			preg_match( '/\/lengow-woocommerce\/logs\/logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt/', $file->get_path(), $match );
			$logs[] = array(
				'full_path'  => $file->get_path(),
				'short_path' => 'logs-' . $match[1] . '.txt',
				'name'       => $match[1] . '.txt'
			);
		}

		return $logs;
	}

	/**
	 * Download log file
	 */
	public static function download( $file = null ) {
		if ( $file && preg_match( '/^logs-([0-9]{4}-[0-9]{2}-[0-9]{2})\.txt$/', $file, $match ) ) {
			$filename = LENGOW_PLUGIN_PATH . '/' . self::$LENGOW_LOGS_FOLDER . '/' . $file;
			$handle   = fopen( $filename, "r" );
			$contents = fread( $handle, filesize( $filename ) );
			header( 'Content-type: text/plain' );
			header( 'Content-Disposition: attachment; filename="' . $match[1] . '.txt"' );
			echo $contents;
			exit();
		} else {
			$files = self::get_paths();
			header( 'Content-type: text/plain; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="logs.txt"' );
			foreach ( $files as $file ) {
				$handle   = fopen( $file['full_path'], "r" );
				$contents = fread( $handle, filesize( $file['full_path'] ) );
				echo $contents;
			}
			exit();
		}
	}
}

