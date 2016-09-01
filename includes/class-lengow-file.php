<?php
/**
 * All components to manage file
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
 * Lengow_File Class.
 */
class Lengow_File {

	/**
	 * @var string file name
	 */
	public $file_name;

	/**
	 * @var string folder name that contains the file
	 */
	public $folder_name;

	/**
	 * @var string file link
	 */
	public $link;

	/**
	 * @var ressource file hande
	 */
	public $instance;

	/**
	 * Construct
	 */
	public function __construct( $folder_name, $file_name = null, $mode = 'a+' ) {
		$this->file_name   = $file_name;
		$this->folder_name = $folder_name;
		$this->instance    = self::getRessource( $this->getPath(), $mode );
		if ( ! is_resource( $this->instance ) ) {
			throw new Lengow_Exception(
				Lengow_Main::setLogMessage(
					'log/export/error_unable_to_create_file',
					array(
						'file_name'   => $file_name,
						'folder_name' => $folder_name
					)
				)
			);
		}
	}

	/**
	 * Destruct
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * Write content in file
	 *
	 * @param string $txt text to be written
	 */
	public function write( $txt ) {
		if ( ! $this->instance ) {
			$this->instance = fopen( $this->getPath(), 'a+' );
		}
		fwrite( $this->instance, $txt );
	}

	/**
	 * Delete file
	 */
	public function delete() {
		if ( $this->exists() ) {
			if ( $this->instance ) {
				$this->close();
			}
			unlink( $this->getPath() );
		}
	}

	/**
	 * Rename file
	 *
	 * @return boolean
	 */
	public function rename( $new_name ) {
		return rename( $this->getPath(), $new_name );
	}

	/**
	 * Close file handle
	 */
	public function close() {
		if ( is_resource( $this->instance ) ) {
			fclose( $this->instance );
		}
	}

	/**
	 * Get resource of a given stream
	 *
	 * @param string $path path to the file
	 * @param string $mode type of access
	 *
	 * @return resource
	 */
	public static function getRessource( $path, $mode = 'a+' ) {
		return fopen( $path, $mode );
	}

	/**
	 * Get file link
	 *
	 * @return string
	 */
	public function getLink() {
		if ( empty( $this->link ) ) {
			if ( ! $this->exists() ) {
				$this->link = null;
			}
			$sep = DIRECTORY_SEPARATOR;
			$this->link = LENGOW_PLUGIN_URL . $sep . $this->folder_name . $sep . $this->file_name;
		}
		return $this->link;
	}

	/**
	 * Get file path
	 *
	 * @return string
	 */
	public function getPath() {
		$sep = DIRECTORY_SEPARATOR;
		return LENGOW_PLUGIN_PATH . $sep . $this->folder_name . $sep . $this->file_name;
	}

	/**
	 * Get folder path of current file
	 *
	 * @return string
	 */
	public function getFolderPath() {
		$sep = DIRECTORY_SEPARATOR;
		return LENGOW_PLUGIN_PATH . $sep . $this->folder_name;
	}

	/**
	 * Check if current file exists
	 *
	 * @return bool
	 */
	public function exists() {
		return file_exists( $this->getPath() );
	}

	/**
	 * Get a file list for a given folder
	 *
	 * @param string $folder folder name
	 *
	 * @return array List of files
	 */
	public static function getFilesFromFolder($folder)
	{
		$sep = DIRECTORY_SEPARATOR;
		$folder_path = LENGOW_PLUGIN_PATH . $sep . $folder;
		if (!file_exists($folder_path)) {
			return false;
		}
		$folder_content = scandir($folder_path);
		$files = array();
		foreach ($folder_content as $file) {
			if (!preg_match('/^\.[a-zA-Z\.]+$|^\.$|index\.php/', $file)) {
				$files[] = new Lengow_File($folder, $file);
			}
		}
		return $files;
	}
}

