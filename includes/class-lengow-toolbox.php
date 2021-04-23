<?php
/**
 * All components to manage the toolbox plugin
 *
 * Copyright 2021 Lengow SAS
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
 * @copyright   2021 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Toolbox Class.
 */
class Lengow_Toolbox {

	/* Toolbox GET params */
	const PARAM_TOKEN = 'token';
	const PARAM_TOOLBOX_ACTION = 'toolbox_action';
	const PARAM_DATE = 'date';
	const PARAM_TYPE = 'type';

	/* Toolbox Actions */
	const ACTION_DATA = 'data';
	const ACTION_LOG = 'log';

	/* Data type */
	const DATA_TYPE_ALL = 'all';
	const DATA_TYPE_CHECKLIST = 'checklist';
	const DATA_TYPE_CHECKSUM = 'checksum';
	const DATA_TYPE_CMS = 'cms';
	const DATA_TYPE_LOG = 'log';
	const DATA_TYPE_PLUGIN = 'plugin';
	const DATA_TYPE_OPTION = 'option';
	const DATA_TYPE_SHOP = 'shop';
	const DATA_TYPE_SYNCHRONIZATION = 'synchronization';

	/* Toolbox Data  */
	const CHECKLIST = 'checklist';
	const CHECKLIST_CURL_ACTIVATED = 'curl_activated';
	const CHECKLIST_SIMPLE_XML_ACTIVATED = 'simple_xml_activated';
	const CHECKLIST_JSON_ACTIVATED = 'json_activated';
	const CHECKLIST_MD5_SUCCESS = 'md5_success';
	const PLUGIN = 'plugin';
	const PLUGIN_CMS_VERSION = 'cms_version';
	const PLUGIN_VERSION = 'plugin_version';
	const PLUGIN_DEBUG_MODE_DISABLE = 'debug_mode_disable';
	const PLUGIN_WRITE_PERMISSION = 'write_permission';
	const PLUGIN_SERVER_IP = 'server_ip';
	const PLUGIN_AUTHORIZED_IP_ENABLE = 'authorized_ip_enable';
	const PLUGIN_AUTHORIZED_IPS = 'authorized_ips';
	const PLUGIN_TOOLBOX_URL = 'toolbox_url';
	const SYNCHRONIZATION = 'synchronization';
	const SYNCHRONIZATION_CMS_TOKEN = 'cms_token';
	const SYNCHRONIZATION_CRON_URL = 'cron_url';
	const SYNCHRONIZATION_NUMBER_ORDERS_IMPORTED = 'number_orders_imported';
	const SYNCHRONIZATION_NUMBER_ORDERS_WAITING_SHIPMENT = 'number_orders_waiting_shipment';
	const SYNCHRONIZATION_NUMBER_ORDERS_IN_ERROR = 'number_orders_in_error';
	const SYNCHRONIZATION_SYNCHRONIZATION_IN_PROGRESS = 'synchronization_in_progress';
	const SYNCHRONIZATION_LAST_SYNCHRONIZATION = 'last_synchronization';
	const SYNCHRONIZATION_LAST_SYNCHRONIZATION_TYPE = 'last_synchronization_type';
	const CMS_OPTIONS = 'cms_options';
	const SHOPS = 'shops';
	const SHOP_ID = 'shop_id';
	const SHOP_NAME = 'shop_name';
	const SHOP_DOMAIN_URL = 'domain_url';
	const SHOP_TOKEN = 'shop_token';
	const SHOP_FEED_URL = 'feed_url';
	const SHOP_ENABLED = 'enabled';
	const SHOP_CATALOG_IDS = 'catalog_ids';
	const SHOP_NUMBER_PRODUCTS_AVAILABLE = 'number_products_available';
	const SHOP_NUMBER_PRODUCTS_EXPORTED = 'number_products_exported';
	const SHOP_LAST_EXPORT = 'last_export';
	const SHOP_OPTIONS = 'shop_options';
	const CHECKSUM = 'checksum';
	const CHECKSUM_AVAILABLE = 'available';
	const CHECKSUM_SUCCESS = 'success';
	const CHECKSUM_NUMBER_FILES_CHECKED = 'number_files_checked';
	const CHECKSUM_NUMBER_FILES_MODIFIED = 'number_files_modified';
	const CHECKSUM_NUMBER_FILES_DELETED = 'number_files_deleted';
	const CHECKSUM_FILE_MODIFIED = 'file_modified';
	const CHECKSUM_FILE_DELETED = 'file_deleted';
	const LOGS = 'logs';

	/* Toolbox files */
	const FILE_CHECKMD5 = 'checkmd5.csv';
	const FILE_TEST = 'test.txt';

	/**
	 * @var array valid toolbox actions.
	 */
	public static $toolbox_actions = array(
		self::ACTION_DATA,
		self::ACTION_LOG,
	);

	/**
	 * Get all toolbox data.
	 *
	 * @param string $type Toolbox data type
	 *
	 * @return array
	 */
	public static function get_data( $type = self::DATA_TYPE_CMS ) {
		switch ( $type ) {
			case self::DATA_TYPE_ALL:
				return self::get_all_data();
			case self::DATA_TYPE_CHECKLIST:
				return self::get_checklist_data();
			case self::DATA_TYPE_CHECKSUM:
				return self::get_checksum_data();
			default:
			case self::DATA_TYPE_CMS:
				return self::get_cms_data();
			case self::DATA_TYPE_LOG:
				return self::get_log_data();
			case self::DATA_TYPE_OPTION:
				return self::get_option_data();
			case self::DATA_TYPE_PLUGIN:
				return self::get_plugin_data();
			case self::DATA_TYPE_SHOP:
				return self::get_shop_data();
			case self::DATA_TYPE_SYNCHRONIZATION:
				return self::get_synchronization_data();
		}
	}

	/**
	 * Download log file individually or globally.
	 *
	 * @param string|null $date name of file to download
	 */
	public static function download_log( $date = null ) {
		Lengow_Log::download( $date );
	}

	/**
	 * Check if PHP Curl is activated.
	 *
	 * @return boolean
	 */
	public static function is_curl_activated() {
		return function_exists( 'curl_version' );
	}

	/**
	 * Get all data.
	 *
	 * @return array
	 */
	private static function get_all_data() {
		return array(
			self::CHECKLIST       => self::get_checklist_data(),
			self::PLUGIN          => self::get_plugin_data(),
			self::SYNCHRONIZATION => self::get_synchronization_data(),
			self::CMS_OPTIONS     => Lengow_Configuration::get_all_values( false, false, true ),
			self::SHOPS           => self::get_shop_data(),
			self::CHECKSUM        => self::get_checksum_data(),
			self::LOGS            => self::get_log_data(),
		);
	}

	/**
	 * Get overview data.
	 *
	 * @return array
	 */
	private static function get_cms_data() {
		return array(
			self::CHECKLIST       => self::get_checklist_data(),
			self::PLUGIN          => self::get_plugin_data(),
			self::SYNCHRONIZATION => self::get_synchronization_data(),
			self::CMS_OPTIONS     => Lengow_Configuration::get_all_values( false, false, true ),
		);
	}

	/**
	 * Get array of requirements.
	 *
	 * @return array
	 */
	private static function get_checklist_data() {
		$checksum_data = self::get_checksum_data();

		return array(
			self::CHECKLIST_CURL_ACTIVATED       => self::is_curl_activated(),
			self::CHECKLIST_SIMPLE_XML_ACTIVATED => self::is_simple_XML_activated(),
			self::CHECKLIST_JSON_ACTIVATED       => self::is_json_activated(),
			self::CHECKLIST_MD5_SUCCESS          => $checksum_data[ self::CHECKSUM_SUCCESS ],
		);
	}

	/**
	 * Get array of plugin data.
	 *
	 * @return array
	 */
	private static function get_plugin_data() {
		global $wp_version;

		return array(
			self::PLUGIN_CMS_VERSION          => $wp_version,
			self::PLUGIN_VERSION              => LENGOW_VERSION,
			self::PLUGIN_DEBUG_MODE_DISABLE   => ! Lengow_Configuration::debug_mode_is_active(),
			self::PLUGIN_WRITE_PERMISSION     => self::test_write_permission(),
			self::PLUGIN_SERVER_IP            => $_SERVER['SERVER_ADDR'],
			self::PLUGIN_AUTHORIZED_IP_ENABLE => (bool) Lengow_Configuration::get(
				Lengow_Configuration::AUTHORIZED_IP_ENABLED
			),
			self::PLUGIN_AUTHORIZED_IPS       => Lengow_Configuration::get_authorized_ips(),
			self::PLUGIN_TOOLBOX_URL          => Lengow_Main::get_toolbox_url(),
		);
	}

	/**
	 * Get array of import data.
	 *
	 * @return array
	 */
	private static function get_synchronization_data() {
		$last_import          = Lengow_Main::get_last_import();
		$last_synchronization = $last_import['type'] === 'none' ? 0 : $last_import['timestamp'];

		return array(
			self::SYNCHRONIZATION_CMS_TOKEN                      => Lengow_Main::get_token(),
			self::SYNCHRONIZATION_CRON_URL                       => Lengow_Main::get_cron_url(),
			self::SYNCHRONIZATION_NUMBER_ORDERS_IMPORTED         => Lengow_Order::count_order_imported_by_lengow(),
			self::SYNCHRONIZATION_NUMBER_ORDERS_WAITING_SHIPMENT => Lengow_Order::count_order_to_be_sent(),
			self::SYNCHRONIZATION_NUMBER_ORDERS_IN_ERROR         => Lengow_Order::count_order_with_error(),
			self::SYNCHRONIZATION_SYNCHRONIZATION_IN_PROGRESS    => Lengow_Import::is_in_process(),
			self::SYNCHRONIZATION_LAST_SYNCHRONIZATION           => $last_synchronization,
			self::SYNCHRONIZATION_LAST_SYNCHRONIZATION_TYPE      => $last_import['type'],
		);
	}

	/**
	 * Get array of export data.
	 *
	 * @return array
	 */
	private static function get_shop_data() {
		$lengow_export = new Lengow_Export();
		$last_export   = Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_EXPORT );

		return array(
			array(
				self::SHOP_ID                        => 1,
				self::SHOP_NAME                      => Lengow_Configuration::get( 'blogname' ),
				self::SHOP_DOMAIN_URL                => $_SERVER['SERVER_NAME'],
				self::SHOP_TOKEN                     => Lengow_Main::get_token(),
				self::SHOP_FEED_URL                  => Lengow_Main::get_export_url(),
				self::SHOP_ENABLED                   => Lengow_Configuration::shop_is_active(),
				self::SHOP_CATALOG_IDS               => Lengow_Configuration::get_catalog_ids(),
				self::SHOP_NUMBER_PRODUCTS_AVAILABLE => $lengow_export->get_total_product(),
				self::SHOP_NUMBER_PRODUCTS_EXPORTED  => $lengow_export->get_total_export_product(),
				self::SHOP_LAST_EXPORT               => empty( $last_export ) ? 0 : (int) $last_export,
				self::SHOP_OPTIONS                   => Lengow_Configuration::get_all_values( false, true, true ),
			),
		);
	}

	/**
	 * Get all options available.
	 *
	 * @return array
	 */
	private static function get_option_data() {
		return array(
			self::CMS_OPTIONS  => Lengow_Configuration::get_all_values( false ),
			self::SHOP_OPTIONS => array(
				Lengow_Configuration::get_all_values( false, true ),
			),
		);
	}

	/**
	 * Get files checksum.
	 *
	 * @return array
	 */
	private static function get_checksum_data() {
		$file_counter  = 0;
		$file_modified = array();
		$file_deleted  = array();
		$sep           = DIRECTORY_SEPARATOR;
		$file_name     = LENGOW_PLUGIN_PATH . $sep . Lengow_Main::FOLDER_TOOLBOX . $sep . self::FILE_CHECKMD5;
		if ( file_exists( $file_name ) ) {
			$md5_available = true;
			if ( ( $file = fopen( $file_name, 'r' ) ) !== false ) {
				while ( ( $data = fgetcsv( $file, 1000, '|' ) ) !== false ) {
					$file_counter ++;
					$short_path = $data[0];
					$file_path = LENGOW_PLUGIN_PATH . $short_path;
					if ( file_exists( $file_path ) ) {
						$file_md = md5_file( $file_path );
						if ( $file_md !== $data[1] ) {
							$file_modified[] = $short_path;
						}
					} else {
						$file_deleted[] = $short_path;
					}
				}
				fclose( $file );
			}
		} else {
			$md5_available = false;
		}
		$file_modified_counter = count( $file_modified );
		$file_deleted_counter  = count( $file_deleted );
		$md5_success           = ! $md5_available
		                         || ! ( $file_modified_counter > 0 ) || ! ( $file_deleted_counter > 0 );

		return array(
			self::CHECKSUM_AVAILABLE             => $md5_available,
			self::CHECKSUM_SUCCESS               => $md5_success,
			self::CHECKSUM_NUMBER_FILES_CHECKED  => $file_counter,
			self::CHECKSUM_NUMBER_FILES_MODIFIED => $file_modified_counter,
			self::CHECKSUM_NUMBER_FILES_DELETED  => $file_deleted_counter,
			self::CHECKSUM_FILE_MODIFIED         => $file_modified,
			self::CHECKSUM_FILE_DELETED          => $file_deleted,
		);
	}

	/**
	 * Get all log files available.
	 *
	 * @return array
	 */
	private static function get_log_data() {
		$logs = Lengow_Log::get_paths();
		if ( ! empty( $logs ) ) {
			$logs[] = array(
				Lengow_Log::LOG_DATE => null,
				Lengow_Log::LOG_LINK => Lengow_Main::get_toolbox_url()
				                        . '&' . self::PARAM_TOOLBOX_ACTION . '=' . self::ACTION_LOG,
			);
		}

		return $logs;
	}

	/**
	 * Check if SimpleXML Extension is activated.
	 *
	 * @return boolean
	 */
	private static function is_simple_XML_activated() {
		return function_exists( 'simplexml_load_file' );
	}

	/**
	 * Check if SimpleXML Extension is activated.
	 *
	 * @return boolean
	 */
	private static function is_json_activated() {
		return function_exists( 'json_decode' );
	}

	/**
	 * Test write permission for log and export in file.
	 *
	 * @return boolean
	 */
	private static function test_write_permission() {
		$sep       = DIRECTORY_SEPARATOR;
		$file_path = LENGOW_PLUGIN_PATH . $sep . Lengow_Main::FOLDER_CONFIG . $sep . self::FILE_TEST;
		try {
			$file = fopen( $file_path, 'w+' );
			if ( ! $file ) {
				return false;
			}
			unlink( $file_path );

			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}
}
