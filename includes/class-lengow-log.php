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
	 * @var LengowFile Lengow file instance
	 */
	protected $file;

	public function __construct($file_name = null) {
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
		$decoded_message = Lengow_Main::decodeLogMessage( $message, 'en_GB' );
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
	public static function getFiles() {
		return Lengow_File::getFilesFromFolder( self::$LENGOW_LOGS_FOLDER );
	}
}

