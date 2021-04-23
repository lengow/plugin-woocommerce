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
	const PARAM_SYNC = 'sync';
	const PARAM_GET_SYNC = 'get_sync';

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
	 * @var string manual import type.
	 */
	const TYPE_MANUAL = 'manual';

	/**
	 * @var integer cron import type.
	 */
	const TYPE_CRON = 'cron';

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
	private $_marketplace_sku;

	/**
	 * @var string|null marketplace name.
	 */
	private $_marketplace_name;

	/**
	 * @var integer|null delivery address id.
	 */
	private $_delivery_address_id;

	/**
	 * @var integer number of orders to import.
	 */
	private $_limit;

	/**
	 * @var integer|false imports orders updated since (timestamp).
	 */
	protected $_updated_from = false;

	/**
	 * @var integer|false imports orders updated until (timestamp).
	 */
	protected $_updated_to = false;

	/**
	 * @var integer|false imports orders created since (timestamp).
	 */
	protected $_created_from = false;

	/**
	 * @var integer|false imports orders created until (timestamp).
	 */
	protected $_created_to = false;

	/**
	 * @var boolean import one order.
	 */
	private $_import_one_order = false;

	/**
	 * @var boolean use debug mode.
	 */
	private $_debug_mode;

	/**
	 * @var boolean display log messages.
	 */
	private $_log_output;

	/**
	 * @var string type import (manual or cron).
	 */
	private $_type_import;

	/**
	 * @var string account ID.
	 */
	private $_account_id;

	/**
	 * @var Lengow_Connector Lengow connector instance.
	 */
	private $_connector;

	/**
	 * @var array shop catalog ids for import.
	 */
	private $_shop_catalog_ids = array();

	/**
	 * @var integer Lengow order id.
	 */
	private $_order_lengow_id;

	/**
	 * Construct the import manager.
	 *
	 * @param $params array Optional options
	 * string  marketplace_sku     lengow marketplace order id to import
	 * string  marketplace_name    lengow marketplace name to import
	 * string  type                type of current import
	 * string  created_from        import of orders since
	 * string  created_to          import of orders until
	 * integer delivery_address_id Lengow delivery address id to import
	 * integer order_lengow_id     Lengow order id in Woocommerce
	 * integer days                import period
	 * integer limit               number of orders to import
	 * boolean log_output          display log messages
	 * boolean debug_mode          debug mode
	 */
	public function __construct( $params = array() ) {
		// get generic params for synchronisation.
		$this->_debug_mode  = isset( $params[ self::PARAM_DEBUG_MODE ] )
			? $params[ self::PARAM_DEBUG_MODE ]
			: Lengow_Configuration::debug_mode_is_active();
		$this->_type_import = isset( $params[ self::PARAM_TYPE ] ) ? $params[ self::PARAM_TYPE ] : self::TYPE_MANUAL;
		$this->_log_output  = isset( $params[ self::PARAM_LOG_OUTPUT ] ) && $params[ self::PARAM_LOG_OUTPUT ];
		// params for re-import order.
		if ( isset( $params[ self::PARAM_MARKETPLACE_SKU ], $params[ self::PARAM_MARKETPLACE_NAME ] ) ) {
			$this->_marketplace_sku  = $params[ self::PARAM_MARKETPLACE_SKU ];
			$this->_marketplace_name = $params[ self::PARAM_MARKETPLACE_NAME ];
			$this->_limit            = 1;
			$this->_import_one_order = true;
			if ( isset( $params[ self::PARAM_DELIVERY_ADDRESS_ID ] )
			     && '' !== $params[ self::PARAM_DELIVERY_ADDRESS_ID ]
			) {
				$this->_delivery_address_id = (int) $params[ self::PARAM_DELIVERY_ADDRESS_ID ];
			}
			if ( isset( $params[ self::PARAM_ORDER_LENGOW_ID ] ) ) {
				$this->_order_lengow_id = (int) $params[ self::PARAM_ORDER_LENGOW_ID ];
			}
		} else {
			// set the time interval.
			$this->_set_interval_time(
				isset( $params[ self::PARAM_DAYS ] ) ? (int) $params[ self::PARAM_DAYS ] : false,
				isset( $params[ self::PARAM_CREATED_FROM ] ) ? $params[ self::PARAM_CREATED_FROM ] : false,
				isset( $params[ self::PARAM_CREATED_TO ] ) ? $params[ self::PARAM_CREATED_TO ] : false
			);
			$this->_limit = isset( $params[ self::PARAM_LIMIT ] ) ? $params[ self::PARAM_LIMIT ] : 0;
		}
	}

	/**
	 * Execute import: fetch orders and import them.
	 *
	 * @return array|false
	 */
	public function exec() {
		$order_new    = 0;
		$order_update = 0;
		$order_error  = 0;
		$error        = false;
		$sync_ok      = true;
		// clean logs.
		Lengow_Main::clean_log();
		if ( ! $this->_debug_mode && ! $this->_import_one_order && self::is_in_process() ) {
			$error = Lengow_Main::set_log_message(
				'lengow_log.error.rest_time_to_import',
				array( 'rest_time' => self::rest_time_to_import() )
			);
			Lengow_Main::log( Lengow_Log::CODE_IMPORT, $error, $this->_log_output );
		} elseif ( ! $this->_check_credentials() ) {
			$error = Lengow_Main::set_log_message( 'lengow_log.error.credentials_not_valid' );
			Lengow_Main::log( Lengow_Log::CODE_IMPORT, $error, $this->_log_output );
		} else {
			if ( ! $this->_import_one_order ) {
				self::set_in_process();
			}
			// check Lengow catalogs for order synchronisation.
			if ( ! $this->_import_one_order && self::TYPE_MANUAL === $this->_type_import ) {
				Lengow_Sync::sync_catalog();
			}
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.start', array( 'type' => $this->_type_import ) ),
				$this->_log_output
			);
			if ( $this->_debug_mode ) {
				Lengow_Main::log(
					Lengow_Log::CODE_IMPORT,
					Lengow_Main::set_log_message( 'log.import.debug_mode_active' ),
					$this->_log_output
				);
			}
			if ( Lengow_Configuration::get( Lengow_Configuration::SHOP_ACTIVE ) ) {
				try {
					// check shop catalog ids.
					if ( ! $this->_check_catalog_ids() ) {
						$error_catalog_ids = Lengow_Main::set_log_message( 'lengow_log.error.no_catalog_for_shop' );
						Lengow_Main::log( Lengow_Log::CODE_IMPORT, $error_catalog_ids, $this->_log_output );
						$error = $error_catalog_ids;
					} else {
						// get orders from Lengow API.
						$orders       = $this->_get_orders_from_api();
						$total_orders = count( $orders );
						if ( $this->_import_one_order ) {
							Lengow_Main::log(
								Lengow_Log::CODE_IMPORT,
								Lengow_Main::set_log_message(
									'log.import.find_one_order',
									array(
										'nb_order'         => $total_orders,
										'marketplace_sku'  => $this->_marketplace_sku,
										'marketplace_name' => $this->_marketplace_name,
										'account_id'       => $this->_account_id,
									)
								),
								$this->_log_output
							);
						} else {
							Lengow_Main::log(
								Lengow_Log::CODE_IMPORT,
								Lengow_Main::set_log_message(
									'log.import.find_all_orders',
									array(
										'nb_order'   => $total_orders,
										'account_id' => $this->_account_id,
									)
								),
								$this->_log_output
							);
						}
						if ( $total_orders <= 0 && $this->_import_one_order ) {
							throw new Lengow_Exception( 'lengow_log.exception.order_not_found' );
						} elseif ( $total_orders > 0 ) {
							if ( null !== $this->_order_lengow_id ) {
								Lengow_Order_Error::finish_order_errors( $this->_order_lengow_id );
							}
							$result = $this->_import_orders( $orders );
							if ( ! $this->_import_one_order ) {
								$order_new    += $result['order_new'];
								$order_update += $result['order_update'];
								$order_error  += $result['order_error'];
							}
						}
					}
				} catch ( Lengow_Exception $e ) {
					$error_message = $e->getMessage();
				} catch ( Exception $e ) {
					$error_message = '[WooCommerce error] "' . $e->getMessage()
					                 . '" ' . $e->getFile() . ' | ' . $e->getLine();
				}
				if ( isset( $error_message ) ) {
					$sync_ok = false;
					if ( null !== $this->_order_lengow_id ) {
						Lengow_Order_Error::finish_order_errors( $this->_order_lengow_id );
						Lengow_Order::add_order_error( $this->_order_lengow_id, $error_message );
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
						$this->_log_output
					);
					$error = $error_message;
					unset( $error_message );
				}
				if ( ! $this->_import_one_order ) {
					Lengow_Main::log(
						Lengow_Log::CODE_IMPORT,
						Lengow_Main::set_log_message(
							'lengow_log.error.nb_order_imported',
							array( 'nb_order' => $order_new )
						),
						$this->_log_output
					);
					Lengow_Main::log(
						Lengow_Log::CODE_IMPORT,
						Lengow_Main::set_log_message(
							'lengow_log.error.nb_order_updated',
							array( 'nb_order' => $order_update )
						),
						$this->_log_output
					);
					Lengow_Main::log(
						Lengow_Log::CODE_IMPORT,
						Lengow_Main::set_log_message(
							'lengow_log.error.nb_order_with_error',
							array( 'nb_order' => $order_error )
						),
						$this->_log_output
					);
				}
			}
			// update last import date.
			if ( ! $this->_import_one_order && $sync_ok ) {
				Lengow_Main::update_date_import( $this->_type_import );
			}
			// finish import process.
			self::set_end();
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.end', array( 'type' => $this->_type_import ) ),
				$this->_log_output
			);
			// check if order action is finish (ship or cancel).
			if ( ! $this->_debug_mode && ! $this->_import_one_order && self::TYPE_MANUAL === $this->_type_import ) {
				Lengow_Action::check_finish_action( $this->_log_output );
				Lengow_Action::check_old_action( $this->_log_output );
				Lengow_Action::check_action_not_sent( $this->_log_output );
			}
			// sending email in error for orders and actions.
			if ( ! $this->_debug_mode
			     && ! $this->_import_one_order
			     && (bool) Lengow_Configuration::get( Lengow_Configuration::REPORT_MAIL_ENABLED )
			) {
				Lengow_Main::send_mail_alert( $this->_log_output );
			}
		}
		// save global error.
		if ( $error ) {
			if ( isset( $this->_order_lengow_id ) && $this->_order_lengow_id ) {
				Lengow_Order_Error::finish_order_errors( $this->_order_lengow_id );
				Lengow_Order::add_order_error( $this->_order_lengow_id, $error );
			}
		}
		if ( $this->_import_one_order ) {
			$result['error'] = $error;

			return $result;
		}

		return array(
			'order_new'    => $order_new,
			'order_update' => $order_update,
			'order_error'  => $order_error,
			'error'        => $error,
		);
	}

	/**
	 * Check credentials.
	 *
	 * @return boolean
	 */
	private function _check_credentials() {
		if ( Lengow_Connector::is_valid_auth( $this->_log_output ) ) {
			list( $this->_account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
			$this->_connector = new Lengow_Connector( $access_token, $secret_token );

			return true;
		}

		return false;
	}

	/**
	 * Check catalog ids.
	 *
	 * @return boolean
	 */
	private function _check_catalog_ids() {
		if ( $this->_import_one_order ) {
			return true;
		}
		$catalog_ids = Lengow_Configuration::get_catalog_ids();
		if ( ! empty( $catalog_ids ) ) {
			$this->_shop_catalog_ids = $catalog_ids;

			return true;
		}

		return false;
	}

	/**
	 * Call Lengow order API.
	 *
	 * @return array
	 * @throws Lengow_Exception no connection with Lengow webservice / credentials not valid
	 *
	 */
	private function _get_orders_from_api() {
		$page   = 1;
		$orders = array();

		if ( $this->_import_one_order ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.connector_get_order',
					array(
						'marketplace_sku'  => $this->_marketplace_sku,
						'marketplace_name' => $this->_marketplace_name,
					)
				),
				$this->_log_output
			);
		} else {
			$date_from = $this->_created_from ? $this->_created_from : $this->_updated_from;
			$date_to   = $this->_created_to ? $this->_created_to : $this->_updated_to;
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.connector_get_all_order',
					array(
						'date_from'  => get_date_from_gmt( date( 'Y-m-d H:i:s', $date_from ) ),
						'date_to'    => get_date_from_gmt( date( 'Y-m-d H:i:s', $date_to ) ),
						'catalog_id' => implode( ', ', $this->_shop_catalog_ids ),
					)
				),
				$this->_log_output
			);
		}
		do {
			try {
				$currency_conversion = ! (bool) Lengow_Configuration::get(
					Lengow_Configuration::CURRENCY_CONVERSION_ENABLED
				);
				if ( $this->_import_one_order ) {
					$results = $this->_connector->get(
						Lengow_Connector::API_ORDER,
						array(
							'marketplace_order_id'   => $this->_marketplace_sku,
							'marketplace'            => $this->_marketplace_name,
							'account_id'             => $this->_account_id,
							'page'                   => $page,
							'no_currency_conversion' => $currency_conversion,
						),
						Lengow_Connector::FORMAT_STREAM,
						'',
						$this->_log_output
					);
				} else {
					if ( $this->_created_from && $this->_created_to ) {
						$time_params = array(
							'marketplace_order_date_from' => get_date_from_gmt(
								date( 'Y-m-d H:i:s', $this->_created_from ),
								'c'
							),
							'marketplace_order_date_to'   => get_date_from_gmt(
								date( 'Y-m-d H:i:s', $this->_created_to ),
								'c'
							),
						);
					} else {
						$time_params = array(
							'updated_from' => get_date_from_gmt( date( 'Y-m-d H:i:s', $this->_updated_from ), 'c' ),
							'updated_to'   => get_date_from_gmt( date( 'Y-m-d H:i:s', $this->_updated_to ), 'c' ),
						);
					}
					$results = $this->_connector->get(
						Lengow_Connector::API_ORDER,
						array_merge(
							$time_params,
							array(
								'catalog_ids'            => implode( ',', $this->_shop_catalog_ids ),
								'account_id'             => $this->_account_id,
								'page'                   => $page,
								'no_currency_conversion' => $currency_conversion,
							)
						),
						Lengow_Connector::FORMAT_STREAM,
						'',
						$this->_log_output
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
			$finish = null === $results->next || $this->_import_one_order;
		} while ( true !== $finish );

		return $orders;
	}

	/**
	 * Create or update order in WooCommerce.
	 *
	 * @param mixed $orders API orders
	 *
	 * @return array|false
	 */
	protected function _import_orders( $orders ) {
		$order_new       = 0;
		$order_update    = 0;
		$order_error     = 0;
		$import_finished = false;

		foreach ( $orders as $order_data ) {
			if ( ! $this->_import_one_order ) {
				self::set_in_process();
			}
			$nb_package      = 0;
			$marketplace_sku = (string) $order_data->marketplace_order_id;
			if ( $this->_debug_mode ) {
				$marketplace_sku .= '--' . time();
			}
			// if order contains no package.
			if ( empty( $order_data->packages ) ) {
				Lengow_Main::log(
					Lengow_Log::CODE_IMPORT,
					Lengow_Main::set_log_message( 'log.import.error_no_package' ),
					$this->_log_output,
					$marketplace_sku
				);
				continue;
			}
			// start import.
			foreach ( $order_data->packages as $package_data ) {
				$nb_package ++;
				// check whether the package contains a shipping address.
				if ( ! isset( $package_data->delivery->id ) ) {
					Lengow_Main::log(
						Lengow_Log::CODE_IMPORT,
						Lengow_Main::set_log_message( 'log.import.error_no_delivery_address' ),
						$this->_log_output,
						$marketplace_sku
					);
					continue;
				}
				$package_delivery_address_id = (int) $package_data->delivery->id;
				$first_package               = ! ( $nb_package > 1 );
				// check the package for re-import order.
				if ( $this->_import_one_order ) {
					if ( null !== $this->_delivery_address_id
					     && $this->_delivery_address_id !== $package_delivery_address_id
					) {
						Lengow_Main::log(
							Lengow_Log::CODE_IMPORT,
							Lengow_Main::set_log_message( 'log.import.error_wrong_package_number' ),
							$this->_log_output,
							$marketplace_sku
						);
						continue;
					}
				}
				try {
					// try to import or update order.
					$import_order = new Lengow_Import_Order(
						array(
							'debug_mode'          => $this->_debug_mode,
							'log_output'          => $this->_log_output,
							'marketplace_sku'     => $marketplace_sku,
							'delivery_address_id' => $package_delivery_address_id,
							'order_data'          => $order_data,
							'package_data'        => $package_data,
							'first_package'       => $first_package,
							'import_one_order'    => $this->_import_one_order,
						)
					);
					$order        = $import_order->import_order();
				} catch ( Lengow_Exception $e ) {
					$error_message = $e->getMessage();
				} catch ( Exception $e ) {
					$error_message = '[WooCommerce error]: "' . $e->getMessage()
					                 . '" ' . $e->getFile() . ' | ' . $e->getLine();
				}
				if ( isset( $error_message ) ) {
					$decoded_message = Lengow_Main::decode_log_message(
						$error_message,
						Lengow_Translation::DEFAULT_ISO_CODE
					);
					Lengow_Main::log(
						Lengow_Log::CODE_IMPORT,
						Lengow_Main::set_log_message(
							'log.import.order_import_failed',
							array( 'decoded_message' => $decoded_message )
						),
						$this->_log_output,
						$marketplace_sku
					);
					unset( $error_message );
					continue;
				}
				if ( isset( $order ) ) {
					// sync to lengow if no debug_mode.
					if ( ! $this->_debug_mode && isset( $order['order_new'] ) && $order['order_new'] ) {
						$order_lengow = new Lengow_Order( $order['order_lengow_id'] );
						$synchro      = $order_lengow->synchronize_order( $this->_connector, $this->_log_output );
						if ( $synchro ) {
							$synchroMessage = Lengow_Main::set_log_message(
								'log.import.order_synchronized_with_lengow',
								array( 'order_id' => $order['order_id'] )
							);
						} else {
							$synchroMessage = Lengow_Main::set_log_message(
								'log.import.order_not_synchronized_with_lengow',
								array( 'order_id' => $order['order_id'] )
							);
						}
						Lengow_Main::log(
							Lengow_Log::CODE_IMPORT,
							$synchroMessage,
							$this->_log_output,
							$marketplace_sku
						);
						unset( $order_lengow );
					}
					// if re-import order -> return order information.
					if ( $this->_import_one_order ) {
						return $order;
					}
					if ( isset( $order['order_new'] ) && $order['order_new'] ) {
						$order_new ++;
					} elseif ( isset( $order['order_update'] ) && $order['order_update'] ) {
						$order_update ++;
					} elseif ( isset( $order['order_error'] ) && $order['order_error'] ) {
						$order_error ++;
					}
				}
				// clean process.
				unset( $import_order, $order );
				// if limit is set.
				if ( $this->_limit > 0 && $order_new === $this->_limit ) {
					$import_finished = true;
					break;
				}
			}
			if ( $import_finished ) {
				break;
			}
		}

		return array(
			'order_new'    => $order_new,
			'order_update' => $order_update,
			'order_error'  => $order_error,
		);
	}

	/**
	 * Set interval time for order synchronisation.
	 *
	 * @param integer|false $days Import period
	 * @param string|false $created_from Import of orders since
	 * @param string|false $created_to Import of orders until
	 */
	protected function _set_interval_time( $days, $created_from, $created_to ) {
		if ( $created_from && $created_to ) {
			// retrieval of orders created from ... until ...
			$created_from_timestamp = strtotime( get_gmt_from_date( $created_from ) );
			$created_to_timestamp   = strtotime( get_gmt_from_date( $created_to ) ) + 86399;
			$interval_time          = $created_to_timestamp - $created_from_timestamp;
			$this->_created_from    = $created_from_timestamp;
			$this->_created_to      = $interval_time > self::MAX_INTERVAL_TIME
				? $created_from_timestamp + self::MAX_INTERVAL_TIME
				: $created_to_timestamp;
		} else {
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
				if ( 'manual' !== $this->_type_import
				     && 'none' !== $last_import['timestamp']
				     && $last_import['timestamp'] > $last_setting_update
				) {
					$last_interval_time = ( time() - $last_import['timestamp'] ) + self::SECURITY_INTERVAL_TIME;
					$interval_time      = $last_interval_time > $interval_time ? $interval_time : $last_interval_time;
				}
			}
			$this->_updated_from = time() - $interval_time;
			$this->_updated_to   = time();
		}
	}

	/**
	 * Check if import is already in process.
	 *
	 * @return boolean
	 */
	public static function is_in_process() {
		$timestamp = (int) Lengow_Configuration::get( Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS );
		if ( $timestamp > 0 ) {
			// security check: if last import is more than 60 seconds old => authorize new import to be launched.
			if ( ( $timestamp + ( 60 * 1 ) ) < time() ) {
				self::set_end();

				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Get Rest time to make re import order.
	 *
	 * @return boolean
	 */
	public static function rest_time_to_import() {
		$timestamp = (int) Lengow_Configuration::get( Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS );
		if ( $timestamp > 0 ) {
			return $timestamp + ( 60 * 1 ) - time();
		}

		return false;
	}

	/**
	 * Set import to "in process" state.
	 */
	public static function set_in_process() {
		self::$processing = true;
		Lengow_Configuration::update_value( Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS, time() );
	}

	/**
	 * Set import to finished.
	 */
	public static function set_end() {
		self::$processing = false;
		Lengow_Configuration::update_value( Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS, - 1 );
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
		if ( ! in_array( $marketplace->get_state_lengow( $order_state_marketplace ), self::$lengow_states, true ) ) {
			return false;
		}

		return true;
	}
}
