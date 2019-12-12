<?php
/**
 * All components to manage file
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
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
 * Lengow_File Class.
 */
class Lengow_File {

	/**
	 * @var string file name.
	 */
	public $file_name;

	/**
	 * @var string folder name that contains the file.
	 */
	public $folder_name;

	/**
	 * @var string file link.
	 */
	public $link;

	/**
	 * @var Lengow_File resource file handel.
	 */
	public $instance;

	/**
	 * Construct a new Lengow File.
	 *
	 * @param string $folder_name Folder name
	 * @param string|null $file_name File name
	 * @param string $mode Mode to manage file
	 *
	 * @throws Lengow_Exception Unable to create file
	 */
	public function __construct( $folder_name, $file_name = null, $mode = 'a+' ) {
		$this->file_name   = $file_name;
		$this->folder_name = $folder_name;
		$this->instance    = self::get_resource( $this->get_path(), $mode );
		if ( ! is_resource( $this->instance ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message(
					'log.export.error_unable_to_create_file',
					array(
						'file_name'   => $file_name,
						'folder_name' => $folder_name,
					)
				)
			);
		}
	}

	/**
	 * Destruct.
	 */
	public function __destruct() {
		$this->close();
	}

	/**
	 * Write content in file.
	 *
	 * @param string $txt text to be written
	 */
	public function write( $txt ) {
		if ( ! $this->instance ) {
			$this->instance = fopen( $this->get_path(), 'a+' );
		}
		fwrite( $this->instance, $txt );
	}

	/**
	 * Delete file.
	 */
	public function delete() {
		if ( $this->exists() ) {
			if ( $this->instance ) {
				$this->close();
			}
			unlink( $this->get_path() );
		}
	}

	/**
	 * Rename file.
	 *
	 * @param string $new_name new name for file
	 *
	 * @return boolean
	 */
	public function rename( $new_name ) {
		return rename( $this->get_path(), $new_name );
	}

	/**
	 * Close file handle.
	 */
	public function close() {
		if ( is_resource( $this->instance ) ) {
			fclose( $this->instance );
		}
	}

	/**
	 * Get resource of a given stream.
	 *
	 * @param string $path path to the file
	 * @param string $mode type of access
	 *
	 * @return resource
	 */
	public static function get_resource( $path, $mode = 'a+' ) {
		return fopen( $path, $mode );
	}

	/**
	 * Get file link.
	 *
	 * @return string
	 */
	public function get_link() {
		if ( empty( $this->link ) ) {
			if ( ! $this->exists() ) {
				$this->link = null;
			}
			$sep        = DIRECTORY_SEPARATOR;
			$this->link = LENGOW_PLUGIN_URL . $sep . $this->folder_name . $sep . $this->file_name;
		}

		return $this->link;
	}

	/**
	 * Get file path.
	 *
	 * @return string
	 */
	public function get_path() {
		$sep = DIRECTORY_SEPARATOR;

		return LENGOW_PLUGIN_PATH . $sep . $this->folder_name . $sep . $this->file_name;
	}

	/**
	 * Get folder path of current file.
	 *
	 * @return string
	 */
	public function get_folder_path() {
		$sep = DIRECTORY_SEPARATOR;

		return LENGOW_PLUGIN_PATH . $sep . $this->folder_name;
	}

	/**
	 * Check if current file exists.
	 *
	 * @return boolean
	 */
	public function exists() {
		return file_exists( $this->get_path() );
	}

	/**
	 * Get a file list for a given folder.
	 *
	 * @param string $folder folder name
	 *
	 * @return array|false
	 */
	public static function get_files_from_folder( $folder ) {
		$sep         = DIRECTORY_SEPARATOR;
		$folder_path = LENGOW_PLUGIN_PATH . $sep . $folder;
		if ( ! file_exists( $folder_path ) ) {
			return false;
		}
		$folder_content = scandir( $folder_path );
		$files          = array();
		foreach ( $folder_content as $file ) {
			try {
				if ( ! preg_match( '/^\.[a-zA-Z\.]+$|^\.$|index\.php/', $file ) ) {
					$files[] = new Lengow_File( $folder, $file );
				}
			} catch ( Lengow_Exception $e ) {
				continue;
			}
		}

		return $files;
	}
}
