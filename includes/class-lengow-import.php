<?php
/**
 * Import process to synchronize orders
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
 * Lengow_Import Class.
 */
class Lengow_Import {

	/* Import GET params */
	const PARAM_TOKEN = 'token';
	const PARAM_TYPE = 'type';
	const PARAM_MARKETPLACE_SKU = 'marketplace_sku';
	const PARAM_MARKETPLACE_NAME = 'marketplace_name';
	const PARAM_DELIVERY_ADDRESS_ID = 'delivery_address_id';
	const PARAM_DAYS = 'days';
	const PARAM_CREATED_FROM = 'created_from';
	const PARAM_CREATED_TO = 'created_to';
	const PARAM_ORDER_LENGOW_ID = 'order_lengow_id';
	const PARAM_LIMIT = 'limit';
	const PARAM_LOG_OUTPUT = 'log_output';
	const PARAM_DEBUG_MODE = 'debug_mode';
	const PARAM_FORCE = 'force';
	const PARAM_FORCE_SYNC = 'force_sync';
	const PARAM_SYNC = 'sync';
	const PARAM_GET_SYNC = 'get_sync';

	/* Import API arguments */
	const ARG_ACCOUNT_ID = 'account_id';
	const ARG_CATALOG_IDS = 'catalog_ids';
	const ARG_MARKETPLACE = 'marketplace';
	const ARG_MARKETPLACE_ORDER_DATE_FROM = 'marketplace_order_date_from';
	const ARG_MARKETPLACE_ORDER_DATE_TO = 'marketplace_order_date_to';
	const ARG_MARKETPLACE_ORDER_ID = 'marketplace_order_id';
	const ARG_MERCHANT_ORDER_ID = 'merchant_order_id';
	const ARG_NO_CURRENCY_CONVERSION = 'no_currency_conversion';
	const ARG_PAGE = 'page';
	const ARG_UPDATED_FROM = 'updated_from';
	const ARG_UPDATED_TO = 'updated_to';

	/* Import types */
	const TYPE_MANUAL = 'manual';
	const TYPE_CRON = 'cron';
	const TYPE_TOOLBOX = 'toolbox';

	/* Import Data */
	const NUMBER_ORDERS_PROCESSED = 'number_orders_processed';
	const NUMBER_ORDERS_CREATED = 'number_orders_created';
	const NUMBER_ORDERS_UPDATED = 'number_orders_updated';
	const NUMBER_ORDERS_FAILED = 'number_orders_failed';
	const NUMBER_ORDERS_IGNORED = 'number_orders_ignored';
	const NUMBER_ORDERS_NOT_FORMATTED = 'number_orders_not_formatted';
	const ORDERS_CREATED = 'orders_created';
	const ORDERS_UPDATED = 'orders_updated';
	const ORDERS_FAILED = 'orders_failed';
	const ORDERS_IGNORED = 'orders_ignored';
	const ORDERS_NOT_FORMATTED = 'orders_not_formatted';
	const ERRORS = 'errors';

	/**
	 * @var integer max interval time for order synchronisation old versions (1 day).
	 */
	const MIN_INTERVAL_TIME = 86400;

	/**
	 * @var integer max import days for old versions (10 days).
	 */
	const MAX_INTERVAL_TIME = 864000;

	/**
	 * @var integer security interval time for cron synchronisation (2 hours).
	 */
	const SECURITY_INTERVAL_TIME = 7200;

	/**
	 * @var integer interval of months for cron synchronisation.
	 */
	const MONTH_INTERVAL_TIME = 3;

	/**
	 * @var integer interval of minutes for cron synchronisation
	 */
	const MINUTE_INTERVAL_TIME = 1;

	/**
	 * @var array valid states lengow to create a Lengow order.
	 */
	public static $lengow_states = array(
		Lengow_Order::STATE_WAITING_SHIPMENT,
		Lengow_Order::STATE_SHIPPED,
		Lengow_Order::STATE_CLOSED,
	);

	/**
	 * @var boolean import is processing.
	 */
	public static $processing;

	/**
	 * @var string|null marketplace order sku.
	 */
	private $marketplace_sku;

	/**
	 * @var string|null marketplace name.
	 */
	private $marketplace_name;

	/**
	 * @var integer|null delivery address id.
	 */
	private $delivery_address_id;

	/**
	 * @var integer maximum number of new orders created.
	 */
	private $limit;

	/**
	 * @var boolean force import order even if there are errors
	 */
	private $force_sync;

	/**
	 * @var integer|false imports orders updated since (timestamp).
	 */
	private $updated_from = false;

	/**
	 * @var integer|false imports orders updated until (timestamp).
	 */
	private $updated_to = false;

	/**
	 * @var integer|false imports orders created since (timestamp).
	 */
	private $created_from = false;

	/**
	 * @var integer|false imports orders created until (timestamp).
	 */
	private $created_to = false;

	/**
	 * @var boolean import one order.
	 */
	private $import_one_order = false;

	/**
	 * @var boolean use debug mode.
	 */
	private $debug_mode;

	/**
	 * @var boolean display log messages.
	 */
	private $log_output;

	/**
	 * @var string type import (manual or cron).
	 */
	private $type_import;

	/**
	 * @var string account ID.
	 */
	private $account_id;

	/**
	 * @var Lengow_Connector Lengow connector instance.
	 */
	private $connector;

	/**
	 * @var array shop catalog ids for import.
	 */
	private $shop_catalog_ids = array();

	/**
	 * @var integer Lengow order id.
	 */
	private $order_lengow_id;

	/**
	 * @var array all orders created during the process
	 */
	private $orders_created = array();

	/**
	 * @var array all orders updated during the process
	 */
	private $orders_updated = array();

	/**
	 * @var array all orders failed during the process
	 */
	private $orders_failed = array();

	/**
	 * @var array all orders ignored during the process
	 */
	private $orders_ignored = array();

	/**
	 * @var array all incorrectly formatted orders that cannot be processed
	 */
	private $orders_not_formatted = array();

	/**
	 * @var array all synchronization error (global or by shop)
	 */
	private $errors = array();

	/**
	 * Construct the import manager.
	 *
	 * @param $params array Optional options
	 * string  marketplace_sku     Lengow marketplace order id to synchronize
	 * string  marketplace_name    Lengow marketplace name to synchronize
	 * string  type                Type of current synchronization
	 * string  created_from        Synchronization of orders since
	 * string  created_to          Synchronization of orders until
	 * integer delivery_address_id Lengow delivery address id to synchronize
	 * integer order_lengow_id     Lengow order id in Woocommerce
	 * integer days                Synchronization interval time
	 * integer limit               Maximum number of new orders created
	 * boolean log_output          Display log messages
	 * boolean debug_mode          Debug mode
	 * boolean force_sync          Force synchronization order even if there are errors
	 */
	public function __construct( $params = array() ) {
		// get generic params for synchronisation.
		$this->debug_mode  = isset( $params[ self::PARAM_DEBUG_MODE ] )
			? $params[ self::PARAM_DEBUG_MODE ]
			: Lengow_Configuration::debug_mode_is_active();
		$this->type_import = isset( $params[ self::PARAM_TYPE ] ) ? $params[ self::PARAM_TYPE ] : self::TYPE_MANUAL;
		$this->force_sync  = isset( $params[ self::PARAM_FORCE_SYNC ] ) && $params[ self::PARAM_FORCE_SYNC ];
		$this->log_output  = isset( $params[ self::PARAM_LOG_OUTPUT ] ) && $params[ self::PARAM_LOG_OUTPUT ];
		// params for re-import order.
		if ( isset( $params[ self::PARAM_MARKETPLACE_SKU ], $params[ self::PARAM_MARKETPLACE_NAME ] ) ) {
			$this->marketplace_sku  = $params[ self::PARAM_MARKETPLACE_SKU ];
			$this->marketplace_name = $params[ self::PARAM_MARKETPLACE_NAME ];
			$this->limit            = 1;
			$this->import_one_order = true;
			if ( isset( $params[ self::PARAM_DELIVERY_ADDRESS_ID ] )
			     && '' !== $params[ self::PARAM_DELIVERY_ADDRESS_ID ]
			) {
				$this->delivery_address_id = (int) $params[ self::PARAM_DELIVERY_ADDRESS_ID ];
			}
			if ( isset( $params[ self::PARAM_ORDER_LENGOW_ID ] ) ) {
				$this->order_lengow_id = (int) $params[ self::PARAM_ORDER_LENGOW_ID ];
				$this->force_sync      = true;
			}
		} else {
			// set the time interval.
			$this->set_interval_time(
				isset( $params[ self::PARAM_DAYS ] ) ? (int) $params[ self::PARAM_DAYS ] : null,
				isset( $params[ self::PARAM_CREATED_FROM ] ) ? $params[ self::PARAM_CREATED_FROM ] : null,
				isset( $params[ self::PARAM_CREATED_TO ] ) ? $params[ self::PARAM_CREATED_TO ] : null
			);
			$this->limit = isset( $params[ self::PARAM_LIMIT ] ) ? $params[ self::PARAM_LIMIT ] : 0;
		}
                Lengow_Main::log(
			Lengow_Log::CODE_IMPORT,
			Lengow_Main::set_log_message(
				'log.import.init_params',
                                ['init_params' => json_encode($params)]
			),
			$this->log_output
		);
	}

	/**
	 * Execute import: fetch orders and import them.
	 *
	 * @return array
	 */
	public function exec() {
		$sync_ok = true;
		// checks if a synchronization is not already in progress.
		if ( ! $this->can_execute_synchronization() ) {
			return $this->get_result();
		}
		// starts some processes necessary for synchronization.
		$this->setup_synchronization();
		// synchronize all orders for a specific shop
		if ( Lengow_Configuration::get( Lengow_Configuration::SHOP_ACTIVE ) && ! $this->synchronize_orders_by_shop() ) {
			$sync_ok = false;
		}
		// get order synchronization result
		$result = $this->get_result();
		Lengow_Main::log(
			Lengow_Log::CODE_IMPORT,
			Lengow_Main::set_log_message(
				'log.import.sync_result',
				array(
					'number_orders_processed'     => $result[ self::NUMBER_ORDERS_PROCESSED ],
					'number_orders_created'       => $result[ self::NUMBER_ORDERS_CREATED ],
					'number_orders_updated'       => $result[ self::NUMBER_ORDERS_UPDATED ],
					'number_orders_failed'        => $result[ self::NUMBER_ORDERS_FAILED ],
					'number_orders_ignored'       => $result[ self::NUMBER_ORDERS_IGNORED ],
					'number_orders_not_formatted' => $result[ self::NUMBER_ORDERS_NOT_FORMATTED ],
				)
			),
			$this->log_output
		);
		// update last synchronization date only if importation succeeded.
		if ( ! $this->import_one_order && $sync_ok ) {
			Lengow_Main::update_date_import( $this->type_import );
		}
		// complete synchronization and start all necessary processes.
		$this->finish_synchronization();

		return $result;
	}

	/**
	 * Check if order status is valid for import.
	 *
	 * @param string $order_state_marketplace order state
	 * @param Lengow_Marketplace $marketplace Lengow marketplace instance
	 *
	 * @return boolean
	 */
	public static function check_state( $order_state_marketplace, $marketplace ) {
		if ( empty( $order_state_marketplace ) ) {
			return false;
		}

		return in_array( $marketplace->get_state_lengow( $order_state_marketplace ), self::$lengow_states, true );
	}

	/**
	 * Check if order synchronization is already in process.
	 *
	 * @return boolean
	 */
	public static function is_in_process() {
		$timestamp = (int) Lengow_Configuration::get( Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS );
		if ( $timestamp > 0 ) {
			// security check: if last import is more than 60 seconds old => authorize new import to be launched.
			if ( ( $timestamp + ( 60 * self::MINUTE_INTERVAL_TIME ) ) < time() ) {
				self::set_end();

				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Get Rest time to make a new order synchronization.
	 *
	 * @return integer
	 */
	public static function rest_time_to_import() {
		$timestamp = (int) Lengow_Configuration::get( Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS );
		if ( $timestamp > 0 ) {
			return $timestamp + ( 60 * self::MINUTE_INTERVAL_TIME ) - time();
		}

		return 0;
	}

	/**
	 * Set interval time for order synchronisation.
	 *
	 * @param integer|null $days Import period
	 * @param string|null $created_from Import of orders since
	 * @param string|null $created_to Import of orders until
	 */
	private function set_interval_time( $days = null, $created_from = null, $created_to = null ) {
		if ( $created_from && $created_to ) {
			// retrieval of orders created from ... until ...
			$created_from_timestamp = strtotime( get_gmt_from_date( $created_from ) );
			$created_to_timestamp   = strtotime( get_gmt_from_date( $created_to ) ) + 86399;
			$interval_time          = $created_to_timestamp - $created_from_timestamp;
			$this->created_from     = $created_from_timestamp;
			$this->created_to       = $interval_time > self::MAX_INTERVAL_TIME
				? $created_from_timestamp + self::MAX_INTERVAL_TIME
				: $created_to_timestamp;

			return;
		}
		if ( $days ) {
			$interval_time = $days * 86400;
			$interval_time = $interval_time > self::MAX_INTERVAL_TIME ? self::MAX_INTERVAL_TIME : $interval_time;
		} else {
			// order recovery updated since ... days.
			$import_days   = (int) Lengow_Configuration::get( Lengow_Configuration::SYNCHRONIZATION_DAY_INTERVAL );
			$interval_time = $import_days * 86400;
			// add security for older versions of the plugin.
			$interval_time = $interval_time < self::MIN_INTERVAL_TIME ? self::MIN_INTERVAL_TIME : $interval_time;
			$interval_time = $interval_time > self::MAX_INTERVAL_TIME ? self::MAX_INTERVAL_TIME : $interval_time;
			// get dynamic interval time for cron synchronisation.
			$last_import         = Lengow_Main::get_last_import();
			$last_setting_update = (int) Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_SETTING );
			if ( self::TYPE_CRON === $this->type_import
			     && 'none' !== $last_import['timestamp']
			     && $last_import['timestamp'] > $last_setting_update
			) {
				$last_interval_time = ( time() - $last_import['timestamp'] ) + self::SECURITY_INTERVAL_TIME;
				$interval_time      = $last_interval_time > $interval_time ? $interval_time : $last_interval_time;
			}
		}
		$this->updated_from = time() - $interval_time;
		$this->updated_to   = time();
	}

	/**
	 * Checks if a synchronization is not already in progress.
	 *
	 * @return boolean
	 */
	private function can_execute_synchronization() {
		$global_error = false;
		if ( ! $this->debug_mode && ! $this->import_one_order && self::is_in_process() ) {
			$global_error = Lengow_Main::set_log_message(
				'lengow_log.error.rest_time_to_import',
				array( 'rest_time' => self::rest_time_to_import() )
			);
			Lengow_Main::log( Lengow_Log::CODE_IMPORT, $global_error, $this->log_output );
		} elseif ( ! $this->check_credentials() ) {
			$global_error = Lengow_Main::set_log_message( 'lengow_log.error.credentials_not_valid' );
			Lengow_Main::log( Lengow_Log::CODE_IMPORT, $global_error, $this->log_output );
		}
		// if we have a global error, we stop the process directly.
		if ( $global_error ) {
			$this->errors[0] = $global_error;
			if ( isset( $this->order_lengow_id ) && $this->order_lengow_id ) {
				Lengow_Order_Error::finish_order_errors( $this->order_lengow_id );
				Lengow_Order::add_order_error( $this->order_lengow_id, $global_error );
			}

			return false;
		}

		return true;
	}

	/**
	 * Starts some processes necessary for synchronization.
	 */
	private function setup_synchronization() {
		// suppress log files when too old.
		Lengow_Main::clean_log();
		if ( ! $this->import_one_order ) {
			self::set_in_process();
		}
		// check Lengow catalogs for order synchronisation.
		if ( ! $this->import_one_order && self::TYPE_MANUAL === $this->type_import ) {
			Lengow_Sync::sync_catalog();
		}
		Lengow_Main::log(
			Lengow_Log::CODE_IMPORT,
			Lengow_Main::set_log_message( 'log.import.start', array( 'type' => $this->type_import ) ),
			$this->log_output
		);
		if ( $this->debug_mode ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.debug_mode_active' ),
				$this->log_output
			);
		}
	}

	/**
	 * Check credentials and get Lengow connector.
	 *
	 * @return boolean
	 */
	private function check_credentials() {
		if ( Lengow_Connector::is_valid_auth( $this->log_output ) ) {
			list( $this->account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
			$this->connector = new Lengow_Connector( $access_token, $secret_token );

			return true;
		}

		return false;
	}

	/**
	 * Return the synchronization result.
	 *
	 * @return array
	 */
	private function get_result() {
		$nb_orders_created       = count( $this->orders_created );
		$nb_orders_updated       = count( $this->orders_updated );
		$nb_orders_failed        = count( $this->orders_failed );
		$nb_orders_ignored       = count( $this->orders_ignored );
		$nb_orders_not_formatted = count( $this->orders_not_formatted );
		$nb_orders_processed     = $nb_orders_created
		                           + $nb_orders_updated
		                           + $nb_orders_failed
		                           + $nb_orders_ignored
		                           + $nb_orders_not_formatted;

		return array(
			self::NUMBER_ORDERS_PROCESSED     => $nb_orders_processed,
			self::NUMBER_ORDERS_CREATED       => $nb_orders_created,
			self::NUMBER_ORDERS_UPDATED       => $nb_orders_updated,
			self::NUMBER_ORDERS_FAILED        => $nb_orders_failed,
			self::NUMBER_ORDERS_IGNORED       => $nb_orders_ignored,
			self::NUMBER_ORDERS_NOT_FORMATTED => $nb_orders_not_formatted,
			self::ORDERS_CREATED              => $this->orders_created,
			self::ORDERS_UPDATED              => $this->orders_updated,
			self::ORDERS_FAILED               => $this->orders_failed,
			self::ORDERS_IGNORED              => $this->orders_ignored,
			self::ORDERS_NOT_FORMATTED        => $this->orders_not_formatted,
			self::ERRORS                      => $this->errors,
		);
	}

	/**
	 * Synchronize all orders for a specific shop.
	 *
	 * @return boolean
	 */
	private function synchronize_orders_by_shop() {
		// check shop catalog ids.
		if ( ! $this->check_catalog_ids() ) {
			return true;
		}
		try {
			// get orders from Lengow API.
			$orders              = $this->get_orders_from_api();
			$number_orders_found = count( $orders );
			if ( $this->import_one_order ) {
				Lengow_Main::log(
					Lengow_Log::CODE_IMPORT,
					Lengow_Main::set_log_message(
						'log.import.find_one_order',
						array(
							'nb_order'         => $number_orders_found,
							'marketplace_sku'  => $this->marketplace_sku,
							'marketplace_name' => $this->marketplace_name,
							'account_id'       => $this->account_id,
						)
					),
					$this->log_output
				);
			} else {
				Lengow_Main::log(
					Lengow_Log::CODE_IMPORT,
					Lengow_Main::set_log_message(
						'log.import.find_all_orders',
						array(
							'nb_order'   => $number_orders_found,
							'account_id' => $this->account_id,
						)
					),
					$this->log_output
				);
			}
			if ( $number_orders_found <= 0 && $this->import_one_order ) {
				throw new Lengow_Exception( 'lengow_log.exception.order_not_found' );
			}
			if ( $number_orders_found > 0 ) {
				// import orders in WooCommerce.
				$this->import_orders( $orders );
			}

		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce error]: "' . $e->getMessage()
			                 . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			if ( isset( $this->order_lengow_id ) && $this->order_lengow_id ) {
				Lengow_Order_Error::finish_order_errors( $this->order_lengow_id );
				Lengow_Order::add_order_error( $this->order_lengow_id, $error_message );
			}
			$decoded_message = Lengow_Main::decode_log_message(
				$error_message,
				Lengow_Translation::DEFAULT_ISO_CODE
			);
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.import_failed',
					array( 'decoded_message' => $decoded_message )
				),
				$this->log_output
			);
			$this->errors[1] = $error_message;
			unset( $error_message );

			return false;
		}

		return true;
	}

	/**
	 * Check catalog ids.
	 *
	 * @return boolean
	 */
	private function check_catalog_ids() {
		if ( $this->import_one_order ) {
			return true;
		}
		$catalog_ids = Lengow_Configuration::get_catalog_ids();
		if ( ! empty( $catalog_ids ) ) {
			$this->shop_catalog_ids = $catalog_ids;

			return true;
		}
		$message = Lengow_Main::set_log_message( 'lengow_log.error.no_catalog_for_shop' );
		Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output );
		$this->errors[1] = $message;

		return false;
	}

	/**
	 * Call Lengow order API.
	 *
	 * @return array
	 * @throws Lengow_Exception no connection with Lengow webservice / credentials not valid
	 *
	 */
	private function get_orders_from_api() {
		$page                = 1;
		$orders              = array();
		$currency_conversion = ! (bool) Lengow_Configuration::get( Lengow_Configuration::CURRENCY_CONVERSION_ENABLED );
		if ( $this->import_one_order ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.connector_get_order',
					array(
						'marketplace_sku'  => $this->marketplace_sku,
						'marketplace_name' => $this->marketplace_name,
					)
				),
				$this->log_output
			);
		} else {
			$date_from = $this->created_from ?: $this->updated_from;
			$date_to   = $this->created_to ?: $this->updated_to;
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.connector_get_all_order',
					array(
						'date_from'  => get_date_from_gmt( date( Lengow_Main::DATE_FULL, $date_from ) ),
						'date_to'    => get_date_from_gmt( date( Lengow_Main::DATE_FULL, $date_to ) ),
						'catalog_id' => implode( ', ', $this->shop_catalog_ids ),
					)
				),
				$this->log_output
			);
		}
		do {
			try {
				if ( $this->import_one_order ) {
					$results = $this->connector->get(
						Lengow_Connector::API_ORDER,
						array(
							self::ARG_MARKETPLACE_ORDER_ID   => $this->marketplace_sku,
							self::ARG_MARKETPLACE            => $this->marketplace_name,
							self::ARG_ACCOUNT_ID             => $this->account_id,
							self::ARG_PAGE                   => $page,
							self::ARG_NO_CURRENCY_CONVERSION => $currency_conversion,
						),
						Lengow_Connector::FORMAT_STREAM,
						'',
						$this->log_output
					);
				} else {
					if ( $this->created_from && $this->created_to ) {
						$time_params = array(
							self::ARG_MARKETPLACE_ORDER_DATE_FROM => get_date_from_gmt(
								date( Lengow_Main::DATE_FULL, $this->created_from ),
								Lengow_Main::DATE_ISO_8601
							),
							self::ARG_MARKETPLACE_ORDER_DATE_TO   => get_date_from_gmt(
								date( Lengow_Main::DATE_FULL, $this->created_to ),
								Lengow_Main::DATE_ISO_8601
							),
						);
					} else {
						$time_params = array(
							self::ARG_UPDATED_FROM => get_date_from_gmt(
								date( Lengow_Main::DATE_FULL, $this->updated_from ),
								Lengow_Main::DATE_ISO_8601
							),
							self::ARG_UPDATED_TO   => get_date_from_gmt(
								date( Lengow_Main::DATE_FULL, $this->updated_to ),
								Lengow_Main::DATE_ISO_8601
							),
						);
					}
					$results = $this->connector->get(
						Lengow_Connector::API_ORDER,
						array_merge(
							$time_params,
							array(
								self::ARG_CATALOG_IDS            => implode( ',', $this->shop_catalog_ids ),
								self::ARG_ACCOUNT_ID             => $this->account_id,
								self::ARG_PAGE                   => $page,
								self::ARG_NO_CURRENCY_CONVERSION => $currency_conversion,
							)
						),
						Lengow_Connector::FORMAT_STREAM,
						'',
						$this->log_output
					);
				}
			} catch ( Exception $e ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message(
						'lengow_log.exception.error_lengow_webservice',
						array(
							'error_code'    => $e->getCode(),
							'error_message' => Lengow_Main::decode_log_message(
								$e->getMessage(),
								Lengow_Translation::DEFAULT_ISO_CODE
							),
						)
					)
				);
			}
			if ( null === $results ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'lengow_log.exception.no_connection_webservice' )
				);
			}
			// don't decode into array as we use the result as an object.
			$results = json_decode( $results );
			if ( ! is_object( $results ) ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'lengow_log.exception.no_connection_webservice' )
				);
			}
			// construct array orders.
			foreach ( $results->results as $order ) {
				$orders[] = $order;
			}
			$page ++;
			$finish = null === $results->next || $this->import_one_order;
		} while ( true !== $finish );

		return $orders;
	}

	/**
	 * Create or update order in WooCommerce.
	 *
	 * @param mixed $orders API orders
	 */
	private function import_orders( $orders ) {
		$import_finished = false;
		foreach ( $orders as $order_data ) {
			if ( ! $this->import_one_order ) {
				self::set_in_process();
			}
			$nb_package      = 0;
			$marketplace_sku = (string) $order_data->marketplace_order_id;
			if ( $this->debug_mode ) {
				$marketplace_sku .= '--' . time();
			}
			// if order contains no package.
			if ( empty( $order_data->packages ) ) {
				$message = Lengow_Main::set_log_message( 'log.import.error_no_package' );
				Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $marketplace_sku );
				$this->add_order_not_formatted( $marketplace_sku, $message, $order_data );
				continue;
			}
			// start import.
			foreach ( $order_data->packages as $package_data ) {
				$nb_package ++;
				// check whether the package contains a shipping address.
				if ( ! isset( $package_data->delivery->id ) ) {
					$message = Lengow_Main::set_log_message( 'log.import.error_no_delivery_address' );
					Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $marketplace_sku );
					$this->add_order_not_formatted( $marketplace_sku, $message, $order_data );
					continue;
				}
				$package_delivery_address_id = (int) $package_data->delivery->id;
				$first_package               = ! ( $nb_package > 1 );
				// check the package for re-import order.
				if ( $this->import_one_order
				     && null !== $this->delivery_address_id
				     && $this->delivery_address_id !== $package_delivery_address_id
				) {
				     $message = Lengow_Main::set_log_message( 'log.import.error_wrong_package_number' );
				     Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $marketplace_sku );
				     $this->add_order_not_formatted( $marketplace_sku, $message, $order_data );
				     continue;
				}
				try {
					// try to import or update order.
					$import_order = new Lengow_Import_Order(
						array(
							Lengow_Import_Order::PARAM_FORCE_SYNC          => $this->force_sync,
							Lengow_Import_Order::PARAM_DEBUG_MODE          => $this->debug_mode,
							Lengow_Import_Order::PARAM_LOG_OUTPUT          => $this->log_output,
							Lengow_Import_Order::PARAM_MARKETPLACE_SKU     => $marketplace_sku,
							Lengow_Import_Order::PARAM_DELIVERY_ADDRESS_ID => $package_delivery_address_id,
							Lengow_Import_Order::PARAM_ORDER_DATA          => $order_data,
							Lengow_Import_Order::PARAM_PACKAGE_DATA        => $package_data,
							Lengow_Import_Order::PARAM_FIRST_PACKAGE       => $first_package,
							Lengow_Import_Order::PARAM_IMPORT_ONE_ORDER    => $this->import_one_order,
						)
					);
					$result       = $import_order->import_order();
					// synchronize the merchant order id with Lengow
					$this->synchronize_merchant_order_id( $result );
					// save the result of the order synchronization by type
					$this->save_synchronization_result( $result );
					// clean import order process
					unset( $import_order, $result );
				} catch ( Exception $e ) {
					$error_message = '[WooCommerce error]: "' . $e->getMessage()
					                 . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
					Lengow_Main::log(
						Lengow_Log::CODE_IMPORT,
						Lengow_Main::set_log_message(
							'log.import.order_import_failed',
							array( 'decoded_message' => $error_message )
						),
						$this->log_output,
						$marketplace_sku
					);
					unset( $error_message );
					continue;
				}
				// if limit is set.
				if ( $this->limit > 0 && count( $this->orders_created ) === $this->limit ) {
					$import_finished = true;
					break;
				}
			}
			if ( $import_finished ) {
				break;
			}
		}
	}

	/**
	 * Return an array of result for order not formatted.
	 *
	 * @param string $marketplace_sku id lengow of current order
	 * @param string $error_message Error message
	 * @param mixed $order_data API order data
	 */
	private function add_order_not_formatted( $marketplace_sku, $error_message, $order_data ) {
		$message_decoded              = Lengow_Main::decode_log_message(
			$error_message,
			Lengow_Translation::DEFAULT_ISO_CODE
		);
		$this->orders_not_formatted[] = array(
			Lengow_Import_Order::MERCHANT_ORDER_ID        => null,
			Lengow_Import_Order::MERCHANT_ORDER_REFERENCE => null,
			Lengow_Import_Order::LENGOW_ORDER_ID          => $this->order_lengow_id,
			Lengow_Import_Order::MARKETPLACE_SKU          => $marketplace_sku,
			Lengow_Import_Order::MARKETPLACE_NAME         => (string) $order_data->marketplace,
			Lengow_Import_Order::DELIVERY_ADDRESS_ID      => null,
			Lengow_Import_Order::SHOP_ID                  => 1,
			Lengow_Import_Order::CURRENT_ORDER_STATUS     => (string) $order_data->lengow_status,
			Lengow_Import_Order::PREVIOUS_ORDER_STATUS    => (string) $order_data->lengow_status,
			Lengow_Import_Order::ERRORS                   => array( $message_decoded ),
		);
	}

	/**
	 * Synchronize the merchant order id with Lengow.
	 *
	 * @param array $result synchronization order result
	 */
	private function synchronize_merchant_order_id( $result ) {
		if ( ! $this->debug_mode
		     && $result[ Lengow_Import_Order::RESULT_TYPE ] === Lengow_Import_Order::RESULT_CREATED
		) {
			$lengow_order = new Lengow_Order( (int) $result[ Lengow_Import_Order::LENGOW_ORDER_ID ] );
			$success      = $lengow_order->synchronize_order( $this->connector, $this->log_output );
			$message_key  = $success
				? 'log.import.order_synchronized_with_lengow'
				: 'log.import.order_not_synchronized_with_lengow';
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					$message_key,
					array( 'order_id' => $result[ Lengow_Import_Order::MERCHANT_ORDER_ID ] )
				),
				$this->log_output,
				$result[ Lengow_Import_Order::MARKETPLACE_SKU ]
			);
		}
	}

	/**
	 * Save the result of the order synchronization by type.
	 *
	 * @param array $result synchronization order result
	 */
	private function save_synchronization_result( $result ) {
		$result_type = $result[ Lengow_Import_Order::RESULT_TYPE ];
		unset( $result[ Lengow_Import_Order::RESULT_TYPE ] );
		switch ( $result_type ) {
			case Lengow_Import_Order::RESULT_CREATED:
				$this->orders_created[] = $result;
				break;
			case Lengow_Import_Order::RESULT_UPDATED:
				$this->orders_updated[] = $result;
				break;
			case Lengow_Import_Order::RESULT_FAILED:
				$this->orders_failed[] = $result;
				break;
			case Lengow_Import_Order::RESULT_IGNORED:
				$this->orders_ignored[] = $result;
				break;
		}
	}

	/**
	 * Complete synchronization and start all necessary processes.
	 */
	private function finish_synchronization() {
		// finish synchronization process.
		self::set_end();
		Lengow_Main::log(
			Lengow_Log::CODE_IMPORT,
			Lengow_Main::set_log_message( 'log.import.end', array( 'type' => $this->type_import ) ),
			$this->log_output
		);
		// check if order action is finish (ship or cancel).
		if ( ! $this->debug_mode && ! $this->import_one_order && self::TYPE_MANUAL === $this->type_import ) {
			Lengow_Action::check_finish_action( $this->log_output );
			Lengow_Action::check_old_action( $this->log_output );
			Lengow_Action::check_action_not_sent( $this->log_output );
		}
		// sending email in error for orders and actions.
		if ( ! $this->debug_mode
		     && ! $this->import_one_order
		     && Lengow_Configuration::get( Lengow_Configuration::REPORT_MAIL_ENABLED )
		) {
			Lengow_Main::send_mail_alert( $this->log_output );
		}
	}

	/**
	 * Set import to "in process" state.
	 */
	private static function set_in_process() {
		self::$processing = true;
		Lengow_Configuration::update_value( Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS, time() );
	}

	/**
	 * Set import to finished.
	 */
	private static function set_end() {
		self::$processing = false;
		Lengow_Configuration::update_value( Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS, - 1 );
	}


}
