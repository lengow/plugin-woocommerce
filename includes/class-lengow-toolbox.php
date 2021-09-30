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
 * (at your option) any later version.
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
	const PARAM_CREATED_FROM = 'created_from';
	const PARAM_CREATED_TO = 'created_to';
	const PARAM_DATE = 'date';
	const PARAM_DAYS = 'days';
	const PARAM_FORCE = 'force';
	const PARAM_MARKETPLACE_NAME = 'marketplace_name';
	const PARAM_MARKETPLACE_SKU = 'marketplace_sku';
	const PARAM_PROCESS = 'process';
	const PARAM_TOKEN = 'token';
	const PARAM_TOOLBOX_ACTION = 'toolbox_action';
	const PARAM_TYPE = 'type';

	/* Toolbox Actions */
	const ACTION_DATA = 'data';
	const ACTION_LOG = 'log';
	const ACTION_ORDER = 'order';

	/* Data type */
	const DATA_TYPE_ACTION = 'action';
	const DATA_TYPE_ALL = 'all';
	const DATA_TYPE_CHECKLIST = 'checklist';
	const DATA_TYPE_CHECKSUM = 'checksum';
	const DATA_TYPE_CMS = 'cms';
	const DATA_TYPE_ERROR = 'error';
	const DATA_TYPE_EXTRA = 'extra';
	const DATA_TYPE_LOG = 'log';
	const DATA_TYPE_PLUGIN = 'plugin';
	const DATA_TYPE_OPTION = 'option';
	const DATA_TYPE_ORDER = 'order';
	const DATA_TYPE_ORDER_STATUS = 'order_status';
	const DATA_TYPE_SHOP = 'shop';
	const DATA_TYPE_SYNCHRONIZATION = 'synchronization';

	/* Toolbox process type */
	const PROCESS_TYPE_GET_DATA = 'get_data';
	const PROCESS_TYPE_SYNC = 'sync';

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

	/* Toolbox order data  */
	const ID = 'id';
	const ORDERS = 'orders';
	const ORDER_MARKETPLACE_SKU = 'marketplace_sku';
	const ORDER_MARKETPLACE_NAME = 'marketplace_name';
	const ORDER_MARKETPLACE_LABEL = 'marketplace_label';
	const ORDER_MERCHANT_ORDER_ID = 'merchant_order_id';
	const ORDER_MERCHANT_ORDER_REFERENCE = 'merchant_order_reference';
	const ORDER_DELIVERY_ADDRESS_ID = 'delivery_address_id';
	const ORDER_DELIVERY_COUNTRY_ISO = 'delivery_country_iso';
	const ORDER_PROCESS_STATE = 'order_process_state';
	const ORDER_STATUSES = 'order_statuses';
	const ORDER_STATUS = 'order_status';
	const ORDER_MERCHANT_ORDER_STATUS = 'merchant_order_status';
	const ORDER_TOTAL_PAID = 'total_paid';
	const ORDER_MERCHANT_TOTAL_PAID = 'merchant_total_paid';
	const ORDER_COMMISSION = 'commission';
	const ORDER_CURRENCY = 'currency';
	const ORDER_DATE = 'order_date';
	const ORDER_ITEMS = 'order_items';
	const ORDER_IS_REIMPORTED = 'is_reimported';
	const ORDER_IS_IN_ERROR = 'is_in_error';
	const ORDER_ACTION_IN_PROGRESS = 'action_in_progress';
	const CUSTOMER = 'customer';
	const CUSTOMER_NAME = 'name';
	const CUSTOMER_EMAIL = 'email';
	const CUSTOMER_VAT_NUMBER = 'vat_number';
	const ORDER_TYPES = 'order_types';
	const ORDER_TYPE_EXPRESS = 'is_express';
	const ORDER_TYPE_PRIME = 'is_prime';
	const ORDER_TYPE_BUSINESS = 'is_business';
	const ORDER_TYPE_DELIVERED_BY_MARKETPLACE = 'is_delivered_by_marketplace';
	const TRACKING = 'tracking';
	const TRACKING_CARRIER = 'carrier';
	const TRACKING_METHOD = 'method';
	const TRACKING_NUMBER = 'tracking_number';
	const TRACKING_RELAY_ID = 'relay_id';
	const TRACKING_DELIVERED_BY_MARKETPLACE = 'is_delivered_by_marketplace';
	const TRACKING_MERCHANT_CARRIER = 'merchant_carrier';
	const TRACKING_MERCHANT_TRACKING_NUMBER = 'merchant_tracking_number';
	const TRACKING_MERCHANT_TRACKING_URL = 'merchant_tracking_url';
	const CREATED_AT = 'created_at';
	const UPDATED_AT = 'updated_at';
	const IMPORTED_AT = 'imported_at';
	const ERRORS = 'errors';
	const ERROR_TYPE = 'type';
	const ERROR_MESSAGE = 'message';
	const ERROR_CODE = 'code';
	const ERROR_FINISHED = 'is_finished';
	const ERROR_REPORTED = 'is_reported';
	const ACTIONS = 'actions';
	const ACTION_ID = 'action_id';
	const ACTION_PARAMETERS = 'parameters';
	const ACTION_RETRY = 'retry';
	const ACTION_FINISH = 'is_finished';

	/* Process state labels */
	const PROCESS_STATE_NEW = 'new';
	const PROCESS_STATE_IMPORT = 'import';
	const PROCESS_STATE_FINISH = 'finish';

	/* Error type labels */
	const TYPE_ERROR_IMPORT = 'import';
	const TYPE_ERROR_SEND = 'send';

	/* PHP extensions */
	const PHP_EXTENSION_CURL = 'curl_version';
	const PHP_EXTENSION_SIMPLEXML = 'simplexml_load_file';
	const PHP_EXTENSION_JSON = 'json_decode';

	/* Toolbox files */
	const FILE_CHECKMD5 = 'checkmd5.csv';
	const FILE_TEST = 'test.txt';

	/**
	 * @var array valid toolbox actions.
	 */
	public static $toolbox_actions = array(
		self::ACTION_DATA,
		self::ACTION_LOG,
		self::ACTION_ORDER,
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
			default:
			case self::DATA_TYPE_CMS:
				return self::get_cms_data();
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
	 * Start order synchronization based on specific parameters.
	 *
	 * @param array $params synchronization parameters
	 *
	 * @return array
	 */
	public static function sync_orders( $params = array() ) {
		// get all params for order synchronization.
		$params = self::filter_params_for_sync( $params );
		$import = new Lengow_Import( $params );
		$result = $import->exec();
		// if global error return error message and request http code.
		if ( isset( $result[ Lengow_Import::ERRORS ][0] ) ) {
			return self::generate_error_return( Lengow_Connector::CODE_403, $result[ Lengow_Import::ERRORS ][0] );
		}
		unset( $result[ Lengow_Import::ERRORS ] );

		return $result;
	}

	/**
	 * Get all order data from a marketplace reference.
	 *
	 * @param string|null $marketplace_sku marketplace order reference
	 * @param string|null $marketplace_name marketplace code
	 * @param string $type Toolbox order data type
	 *
	 * @return array
	 */
	public static function get_order_data(
		$marketplace_sku = null,
		$marketplace_name = null,
		$type = self::DATA_TYPE_ORDER
	) {
		$lengow_orders = $marketplace_sku && $marketplace_name
			? Lengow_Order::get_all_lengow_orders( $marketplace_sku, $marketplace_name )
			: array();
		// if no reference is found, process is blocked.
		if ( empty( $lengow_orders ) ) {
			return self::generate_error_return(
				Lengow_Connector::CODE_404,
				Lengow_Main::set_log_message( 'log.import.unable_find_order' )
			);
		}
		$orders = array();
		foreach ( $lengow_orders as $lengow_order ) {
			if ( $type === self::DATA_TYPE_EXTRA ) {
				return self::get_order_extra_data( $lengow_order );
			}
			$marketplace_label = $lengow_order->marketplace_label;
			$orders[]          = self::get_order_data_by_type( $lengow_order, $type );
		}

		return array(
			self::ORDER_MARKETPLACE_SKU   => $marketplace_sku,
			self::ORDER_MARKETPLACE_NAME  => $marketplace_name,
			self::ORDER_MARKETPLACE_LABEL => isset( $marketplace_label ) ? $marketplace_label : null,
			self::ORDERS                  => $orders,
		);
	}

	/**
	 * Check if PHP Curl is activated.
	 *
	 * @return boolean
	 */
	public static function is_curl_activated() {
		return function_exists( self::PHP_EXTENSION_CURL );
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
					$file_path  = LENGOW_PLUGIN_PATH . $short_path;
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
		$md5_success           = $md5_available && ! ( $file_modified_counter > 0 ) && ! ( $file_deleted_counter > 0 );

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
		return function_exists( self::PHP_EXTENSION_SIMPLEXML );
	}

	/**
	 * Check if SimpleXML Extension is activated.
	 *
	 * @return boolean
	 */
	private static function is_json_activated() {
		return function_exists( self::PHP_EXTENSION_JSON );
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

	/**
	 * Filter parameters for order synchronization.
	 *
	 * @param array $params synchronization params
	 *
	 * @return array
	 */
	private static function filter_params_for_sync( $params = array() ) {
		$params_filtered = array( Lengow_Import::PARAM_TYPE => Lengow_Import::TYPE_TOOLBOX );
		if ( isset( $params[ self::PARAM_MARKETPLACE_SKU ], $params[ self::PARAM_MARKETPLACE_NAME ] ) ) {
			// get all parameters to synchronize a specific order.
			$params_filtered[ Lengow_Import::PARAM_MARKETPLACE_SKU ]  = $params[ self::PARAM_MARKETPLACE_SKU ];
			$params_filtered[ Lengow_Import::PARAM_MARKETPLACE_NAME ] = $params[ self::PARAM_MARKETPLACE_NAME ];
		} elseif ( isset( $params[ self::PARAM_CREATED_FROM ], $params[ self::PARAM_CREATED_TO ] ) ) {
			// get all parameters to synchronize over a fixed period.
			$params_filtered[ Lengow_Import::PARAM_CREATED_FROM ] = $params[ self::PARAM_CREATED_FROM ];
			$params_filtered[ Lengow_Import::PARAM_CREATED_TO ]   = $params[ self::PARAM_CREATED_TO ];
		} elseif ( isset( $params[ self::PARAM_DAYS ] ) ) {
			// get all parameters to synchronize over a time interval.
			$params_filtered[ Lengow_Import::PARAM_DAYS ] = (int) $params[ self::PARAM_DAYS ];
		}
		// force order synchronization by removing pending errors.
		if ( isset( $params[ self::PARAM_FORCE ] ) ) {
			$params_filtered[ Lengow_Import::PARAM_FORCE_SYNC ] = (bool) $params[ self::PARAM_FORCE ];
		}

		return $params_filtered;
	}

	/**
	 * Get array of all the data of the order.
	 *
	 * @param Lengow_Order $lengow_order Lengow order instance
	 * @param string $type Toolbox order data type
	 *
	 * @return array
	 */
	private static function get_order_data_by_type( $lengow_order, $type ) {
		$order            = $lengow_order->order_id ? new WC_Order( $lengow_order->order_id ) : null;
		$order_references = array(
			self::ID                             => $lengow_order->id,
			self::ORDER_MERCHANT_ORDER_ID        => $order ? Lengow_Order::get_order_id( $order ) : null,
			self::ORDER_MERCHANT_ORDER_REFERENCE => $order ? (string) Lengow_Order::get_order_id( $order ) : null,
			self::ORDER_DELIVERY_ADDRESS_ID      => $lengow_order->delivery_address_id,
		);
		switch ( $type ) {
			case self::DATA_TYPE_ACTION:
				$order_data = array(
					self::ACTIONS => $order
						? self::get_order_action_data( Lengow_Order::get_order_id( $order ) )
						: array(),
				);
				break;
			case self::DATA_TYPE_ERROR:
				$order_data = array(
					self::ERRORS => self::get_order_errors_data( $lengow_order->id ),
				);
				break;
			case self::DATA_TYPE_ORDER_STATUS:
				$order_data = array(
					self::ORDER_STATUSES => $order ? self::get_order_statuses_data( $order ) : array(),
				);
				break;
			case self::DATA_TYPE_ORDER:
			default:
				$order_data = self::get_all_order_data( $lengow_order, $order );
		}

		return array_merge( $order_references, $order_data );
	}

	/**
	 * Get array of all the data of the order.
	 *
	 * @param Lengow_Order $lengow_order Lengow order instance
	 * @param WC_Order|null $order WooCommerce order instance
	 *
	 * @return array
	 */
	private static function get_all_order_data( $lengow_order, $order = null ) {
		if ( $order ) {
			$order_id                 = Lengow_Order::get_order_id( $order );
			$carrier                  = get_post_meta( $order_id, '_lengow_carrier', true );
			$custom_carrier           = get_post_meta( $order_id, '_lengow_custom_carrier', true );
			$merchant_carrier         = $carrier ?: $custom_carrier;
			$merchant_tracking_number = get_post_meta( $order_id, '_lengow_tracking_number', true );
			$merchant_tracking_url    = get_post_meta( $order_id, '_lengow_tracking_url', true );
		}

		return array(
			self::ORDER_DELIVERY_COUNTRY_ISO  => $lengow_order->delivery_country_iso,
			self::ORDER_PROCESS_STATE         => self::get_order_process_label( $lengow_order->order_process_state ),
			self::ORDER_STATUS                => $lengow_order->order_lengow_state,
			self::ORDER_MERCHANT_ORDER_STATUS => $order ? Lengow_Order::get_order_status( $order ) : null,
			self::ORDER_STATUSES              => $order ? self::get_order_statuses_data( $order ) : array(),
			self::ORDER_TOTAL_PAID            => $lengow_order->total_paid,
			self::ORDER_MERCHANT_TOTAL_PAID   => $order ? (float) $order->get_total() : null,
			self::ORDER_COMMISSION            => $lengow_order->commission,
			self::ORDER_CURRENCY              => $lengow_order->currency,
			self::CUSTOMER                    => array(
				self::CUSTOMER_NAME       => ! empty( $lengow_order->customer_name )
					? $lengow_order->customer_name
					: null,
				self::CUSTOMER_EMAIL      => ! empty( $lengow_order->customer_email )
					? $lengow_order->customer_email
					: null,
				self::CUSTOMER_VAT_NUMBER => ! empty( $lengow_order->customer_vat_number )
					? $lengow_order->customer_vat_number
					: null,
			),
			self::ORDER_DATE                  => strtotime( $lengow_order->order_date ),
			self::ORDER_TYPES                 => array(
				self::ORDER_TYPE_EXPRESS                  => isset(
					$lengow_order->order_types[ Lengow_Order::TYPE_EXPRESS ]
				),
				self::ORDER_TYPE_PRIME                    => isset(
					$lengow_order->order_types[ Lengow_Order::TYPE_PRIME ]
				),
				self::ORDER_TYPE_BUSINESS                 => isset(
					$lengow_order->order_types[ Lengow_Order::TYPE_BUSINESS ]
				),
				self::ORDER_TYPE_DELIVERED_BY_MARKETPLACE => isset(
					$lengow_order->order_types[ Lengow_Order::TYPE_DELIVERED_BY_MARKETPLACE ]
				),
			),
			self::ORDER_ITEMS                 => $lengow_order->order_item,
			self::TRACKING                    => array(
				self::TRACKING_CARRIER                  => ! empty( $lengow_order->carrier )
					? $lengow_order->carrier
					: null,
				self::TRACKING_METHOD                   => ! empty( $lengow_order->carrier_method )
					? $lengow_order->carrier_method
					: null,
				self::TRACKING_NUMBER                   => ! empty( $lengow_order->carrier_tracking )
					? $lengow_order->carrier_tracking
					: null,
				self::TRACKING_RELAY_ID                 => ! empty( $lengow_order->carrier_id_relay )
					? $lengow_order->carrier_id_relay
					: null,
				self::TRACKING_MERCHANT_CARRIER         => $order && ! empty( $merchant_carrier )
					? $merchant_carrier
					: null,
				self::TRACKING_MERCHANT_TRACKING_NUMBER => $order && ! empty( $merchant_tracking_number )
					? $merchant_tracking_number
					: null,
				self::TRACKING_MERCHANT_TRACKING_URL    => $order && ! empty( $merchant_tracking_url )
					? $merchant_tracking_url
					: null,
			),
			self::ORDER_IS_REIMPORTED         => $lengow_order->is_reimported,
			self::ORDER_IS_IN_ERROR           => $lengow_order->is_in_error,
			self::ERRORS                      => self::get_order_errors_data( $lengow_order->id ),
			self::ORDER_ACTION_IN_PROGRESS    => $order && $lengow_order->has_an_action_in_progress(),
			self::ACTIONS                     => $order
				? self::get_order_action_data( Lengow_Order::get_order_id( $order ) )
				: array(),
			self::CREATED_AT                  => strtotime( $lengow_order->created_at ),
			self::UPDATED_AT                  => strtotime( $lengow_order->updated_at ),
			self::IMPORTED_AT                 => $order
				? strtotime( Lengow_Order::get_date_imported( Lengow_Order::get_order_id( $order ) ) )
				: 0,
		);
	}

	/**
	 * Get array of all the errors of a Lengow order.
	 *
	 * @param integer $lengow_order_id Lengow order id
	 *
	 * @return array
	 */
	private static function get_order_errors_data( $lengow_order_id ) {
		$order_errors = array();
		$errors       = Lengow_Order_Error::get_order_errors( $lengow_order_id );
		if ( $errors ) {
			foreach ( $errors as $error ) {
				$type           = (int) $error->{Lengow_Order_Error::FIELD_TYPE};
				$order_errors[] = array(
					self::ID             => (int) $error->{Lengow_Order_Error::FIELD_ID},
					self::ERROR_TYPE     => $type === Lengow_Order_Error::ERROR_TYPE_IMPORT
						? self::TYPE_ERROR_IMPORT
						: self::TYPE_ERROR_SEND,
					self::ERROR_MESSAGE  => Lengow_Main::decode_log_message(
						$error->{Lengow_Order_Error::FIELD_MESSAGE},
						Lengow_Translation::DEFAULT_ISO_CODE
					),
					self::ERROR_FINISHED => (bool) $error->{Lengow_Order_Error::FIELD_IS_FINISHED},
					self::ERROR_REPORTED => (bool) $error->{Lengow_Order_Error::FIELD_MAIL},
					self::CREATED_AT     => strtotime( $error->{Lengow_Order_Error::FIELD_CREATED_AT} ),
					self::UPDATED_AT     => strtotime( $error->{Lengow_Order_Error::FIELD_UPDATED_AT} ),
				);
			}
		}

		return $order_errors;
	}

	/**
	 * Get array of all the actions of a Lengow order.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return array
	 */
	private static function get_order_action_data( $order_id ) {
		$order_actions = array();
		$actions       = Lengow_Action::get_action_by_order_id( $order_id );
		if ( $actions ) {
			foreach ( $actions as $action ) {
				$order_actions[] = array(
					self::ID                => (int) $action->{Lengow_Action::FIELD_ID},
					self::ACTION_ID         => (int) $action->{Lengow_Action::FIELD_ACTION_ID},
					self::ACTION_PARAMETERS => json_decode( $action->{Lengow_Action::FIELD_PARAMETERS}, true ),
					self::ACTION_RETRY      => (int) $action->{Lengow_Action::FIELD_RETRY},
					self::ACTION_FINISH     => $action->{Lengow_Action::FIELD_STATE} === Lengow_Action::STATE_FINISH,
					self::CREATED_AT        => strtotime( $action->{Lengow_Action::FIELD_CREATED_AT} ),
					self::UPDATED_AT        => $action->{Lengow_Action::FIELD_UPDATED_AT}
						? strtotime( $action->{Lengow_Action::FIELD_UPDATED_AT} )
						: 0,
				);
			}
		}

		return $order_actions;
	}

	/**
	 * Get array of all the statuses of an order.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 *
	 * @return array
	 */
	private static function get_order_statuses_data( $order ) {
		$order_statuses        = array();
		$imported_date         = Lengow_Order::get_date_imported( Lengow_Order::get_order_id( $order ) );
		$created_date          = $order->get_date_created()
			? get_gmt_from_date( $order->get_date_created()->date( Lengow_Main::DATE_FULL ) )
			: null;
		$imported_date         = $imported_date ?: $created_date;
		$completed_date        = $order->get_date_completed()
			? get_gmt_from_date( $order->get_date_completed()->date( Lengow_Main::DATE_FULL ) )
			: null;
		$current_order_status  = Lengow_Order::get_order_status( $order );
		$current_modified_date = $order->get_date_modified()
			? get_gmt_from_date( $order->get_date_modified()->date( Lengow_Main::DATE_FULL ) )
			: null;
		if ( $imported_date ) {
			$order_statuses[] = [
				self::ORDER_MERCHANT_ORDER_STATUS => Lengow_Order::get_order_state(
					Lengow_Order::STATE_WAITING_SHIPMENT
				),
				self::ORDER_STATUS                => Lengow_Order::STATE_WAITING_SHIPMENT,
				self::CREATED_AT                  => strtotime( $imported_date ),
			];
		}
		if ( $completed_date ) {
			$order_statuses[] = [
				self::ORDER_MERCHANT_ORDER_STATUS => Lengow_Order::get_order_state( Lengow_Order::STATE_SHIPPED ),
				self::ORDER_STATUS                => Lengow_Order::STATE_SHIPPED,
				self::CREATED_AT                  => strtotime( $completed_date ),
			];
		}
		if ( $current_modified_date
		     && $current_order_status === Lengow_Order::get_order_state( Lengow_Order::STATE_CANCELED )
		) {
			$order_statuses[] = [
				self::ORDER_MERCHANT_ORDER_STATUS => Lengow_Order::get_order_state( Lengow_Order::STATE_CANCELED ),
				self::ORDER_STATUS                => Lengow_Order::STATE_CANCELED,
				self::CREATED_AT                  => strtotime( $current_modified_date ),
			];
		}

		return $order_statuses;
	}

	/**
	 * Get all the data of the order at the time of import.
	 *
	 * @param Lengow_Order $lengow_order Lengow order instance
	 *
	 * @return array
	 */
	private static function get_order_extra_data( $lengow_order ) {
		return json_decode( $lengow_order->extra, true );
	}

	/**
	 * Get order process label.
	 *
	 * @param integer $order_process Lengow order process (new, import or finish)
	 *
	 * @return string
	 */
	private static function get_order_process_label( $order_process ) {
		switch ( $order_process ) {
			case Lengow_Order::PROCESS_STATE_NEW:
				return self::PROCESS_STATE_NEW;
			case Lengow_Order::PROCESS_STATE_IMPORT:
				return self::PROCESS_STATE_IMPORT;
			case Lengow_Order::PROCESS_STATE_FINISH:
			default:
				return self::PROCESS_STATE_FINISH;
		}
	}

	/**
	 * Generates an error return for the Toolbox webservice.
	 *
	 * @param integer $http_code request http code
	 * @param string $error error message
	 *
	 * @return array
	 */
	private static function generate_error_return( $http_code, $error ) {
		return array(
			self::ERRORS => array(
				self::ERROR_MESSAGE => Lengow_Main::decode_log_message( $error, Lengow_Translation::DEFAULT_ISO_CODE ),
				self::ERROR_CODE    => $http_code,
			),
		);
	}
}
