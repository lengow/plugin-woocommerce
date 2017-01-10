<?php
/**
 * Import order process to synchronise stock
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 * 
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category   	Lengow
 * @package    	lengow-woocommerce
 * @subpackage 	includes
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
 * @license    	https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Import_Order Class.
 */
class Lengow_Import_Order {

	/**
	 * @var boolean use preprod mode.
	 */
	private $_preprod_mode = false;

	/**
	 * @var boolean display log messages.
	 */
	private $_log_output = false;

	/**
	 * @var Lengow_Marketplace Lengow marketplace instance.
	 */
	private $_marketplace;

	/**
	 * @var string id lengow of current order.
	 */
	private $_marketplace_sku;

	/**
	 * @var integer id of delivery address for current order.
	 */
	private $_delivery_address_id;

	/**
	 * @var mixed all order data.
	 */
	private $_order_data;

	/**
	 * @var mixed all package data.
	 */
	private $_package_data;

	/**
	 * @var boolean if order is first package.
	 */
	private $_first_package;

	/**
	 * @var string marketplace order state.
	 */
	private $_order_state_marketplace;

	/**
	 * @var string lengow order state.
	 */
	private $_order_state_lengow;

	/**
	 * @var integer id of the record Lengow order table.
	 */
	private $_id_order_lengow = null;

	/**
	 * @var boolean True if order is send by the marketplace.
	 */
	private $_shipped_by_mp = false;

	/**
	 * Construct the import manager.
	 *
	 * @param $params array Optional options
	 * boolean preprod_mode        preprod mode
	 * boolean log_output          display log messages
	 * string  marketplace_sku     order marketplace sku
	 * integer delivery_address_id order delivery address id
	 * mixed   order_data          order data
	 * mixed   package_data        package data
	 * boolean first_package       it is the first package
	 */
	public function __construct( $params = array() ) {
		$this->_preprod_mode        = $params['preprod_mode'];
		$this->_log_output          = $params['log_output'];
		$this->_marketplace_sku     = $params['marketplace_sku'];
		$this->_delivery_address_id = $params['delivery_address_id'];
		$this->_order_data          = $params['order_data'];
		$this->_package_data        = $params['package_data'];
		$this->_first_package       = $params['first_package'];
		// get marketplace and Lengow order state.
		$this->_marketplace             = Lengow_Main::get_marketplace_singleton(
			(string) $this->_order_data->marketplace
		);
		$this->_order_state_marketplace = (string) $this->_order_data->marketplace_status;
		$this->_order_state_lengow      = $this->_marketplace->get_state_lengow( $this->_order_state_marketplace );
	}

	/**
	 * Create or update order.
	 *
	 * @throws Lengow_Exception product list is empty
	 *
	 * @return array|false
	 */
	public function import_order() {
		// get a record in the lengow order table.
		$already_decremented = Lengow_Order::get_id_order_from_lengow_orders(
			$this->_marketplace_sku,
			$this->_marketplace->name,
			$this->_delivery_address_id,
			$this->_marketplace->legacy_code
		);
		// If order does not already exist.
		if ( $already_decremented ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.order_already_decremented' ),
				$this->_log_output,
				$this->_marketplace_sku
			);

			return false;
		}
		// if order is cancelled or new -> skip.
		if ( ! Lengow_Import::check_state( $this->_order_state_marketplace, $this->_marketplace ) ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.current_order_state_unavailable',
					array(
						'order_state_marketplace' => $this->_order_state_marketplace,
						'marketplace_name'        => $this->_marketplace->name
					)
				),
				$this->_log_output,
				$this->_marketplace_sku
			);

			return false;
		}
		// checks if the required order data is present.
		if ( ! $this->_check_order_data() ) {
			return $this->_return_result( 'error', $this->_id_order_lengow );
		}
		// load tracking data.
		$this->_load_tracking_data();
		// try to synchronise order.
		try {
			// check if the order is shipped by marketplace.
			if ( $this->_shipped_by_mp ) {
				// If decrease stocks from mp option is disabled.
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message(
						'log.import.order_shipped_by_marketplace',
						array( 'marketplace_name' => $this->_marketplace->name )
					),
					$this->_log_output,
					$this->_marketplace_sku
				);
				if ( ! Lengow_Configuration::get( 'lengow_import_ship_mp_enabled' ) ) {
					return false;
				}
			}
			// get products.
			$products = $this->_get_products();
			if ( count( $products ) == 0 ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'lengow_log.exception.product_list_is_empty' )
				);
			} else {
				// decrement product stock.
				$this->_decrease_stock( $products );
				// created a record in the lengow order table.
				if ( ! $this->_create_lengow_order() ) {
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message( 'log.import.lengow_order_not_saved' ),
						$this->_log_output,
						$this->_marketplace_sku
					);

					return $this->_return_result( 'error', $this->_id_order_lengow );
				} else {
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message( 'log.import.lengow_order_saved' ),
						$this->_log_output,
						$this->_marketplace_sku
					);
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message( 'log.import.order_successfully_decremented' ),
						$this->_log_output,
						$this->_marketplace_sku
					);
				}
			}
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			$decoded_message = Lengow_Main::decode_log_message( $error_message, 'en_GB' );
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.order_import_failed',
					array( 'decoded_message' => $decoded_message )
				),
				$this->_log_output,
				$this->_marketplace_sku
			);

			return $this->_return_result( 'error', $this->_id_order_lengow );
		}

		return $this->_return_result( 'new', $this->_id_order_lengow );
	}

	/**
	 * Return an array of result for each order.
	 *
	 * @param string $type_result Type of result (new or error)
	 * @param integer $id_order_lengow Lengow order id
	 * @param integer $id_order WooCommerce order id
	 *
	 * @return array
	 */
	private function _return_result( $type_result, $id_order_lengow, $id_order = null ) {
		$result = array(
			'id_order'         => $id_order,
			'id_order_lengow'  => $id_order_lengow,
			'marketplace_sku'  => $this->_marketplace_sku,
			'marketplace_name' => $this->_marketplace->name,
			'lengow_state'     => $this->_order_state_lengow,
			'order_new'        => ( $type_result == 'new' ? true : false ),
			'order_error'      => ( $type_result == 'error' ? true : false )
		);

		return $result;
	}

	/**
	 * Checks if order data are present.
	 *
	 * @return boolean
	 */
	private function _check_order_data() {
		$error_messages = array();
		if ( count( $this->_package_data->cart ) == 0 ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_product' );
		}
		if ( is_null( $this->_order_data->billing_address ) ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_billing_address' );
		} elseif ( is_null( $this->_order_data->billing_address->common_country_iso_a2 ) ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_country_for_billing_address' );
		}
		if ( is_null( $this->_package_data->delivery->common_country_iso_a2 ) ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_country_for_delivery_address' );
		}
		if ( count( $error_messages ) > 0 ) {
			foreach ( $error_messages as $error_message ) {
				$decoded_message = Lengow_Main::decode_log_message( $error_message, 'en_GB' );
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message(
						'log.import.order_import_failed',
						array( 'decoded_message' => $decoded_message )
					),
					$this->_log_output,
					$this->_marketplace_sku
				);
			};

			return false;
		}

		return true;
	}

	/**
	 * Get tracking data and update Lengow order record.
	 */
	private function _load_tracking_data() {
		$tracking = $this->_package_data->delivery->trackings;
		if ( count( $tracking ) > 0 ) {
			if ( ! is_null( $tracking[0]->is_delivered_by_marketplace ) && $tracking[0]->is_delivered_by_marketplace ) {
				$this->_shipped_by_mp = true;
			}
		}
	}

	/**
	 * Get products from the API and check that they exist in WooCommerce database.
	 *
	 * @throws Lengow_Exception If product is not found
	 * 
	 * @return array
	 */
	private function _get_products() {
		$products = array();
		foreach ( $this->_package_data->cart as $product ) {
			$found         = false;
			$product_datas = Lengow_Product::extract_product_data_from_api( $product );
			if ( ! is_null( $product_datas['marketplace_status'] ) ) {
				$state_product = $this->_marketplace->get_state_lengow( (string) $product_datas['marketplace_status'] );
				if ( $state_product == 'canceled' || $state_product == 'refused' ) {
					$api_product_id = ( ! is_null( $product_datas['merchant_product_id']->id )
						? (string) $product_datas['merchant_product_id']->id
						: (string) $product_datas['marketplace_product_id']
					);
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'log.import.product_state_canceled',
							array(
								'product_id'    => $api_product_id,
								'state_product' => $state_product
							)
						),
						$this->_log_output,
						$this->_marketplace_sku
					);
					continue;
				}
			}
			$product_id = Lengow_Product::match_product( $product_datas, $this->_marketplace_sku, $this->_log_output );
			if ( $product_id ) {
				if ( array_key_exists( $product_id, $products ) ) {
					$products[ $product_id ]['quantity'] += (integer) $product_datas['quantity'];
					$products[ $product_id ]['amount'] += (float) $product_datas['amount'];
				} else {
					$products[ $product_id ] = $product_datas;
				}
				$found = true;
			}
			if ( ! $found ) {
				$api_product_id = ( ! is_null( $product_datas['merchant_product_id']->id )
					? (string) $product_datas['merchant_product_id']->id
					: (string) $product_datas['marketplace_product_id']
				);
				throw new Lengow_Exception(
					Lengow_Main::set_log_message(
						'lengow_log.exception.product_not_be_found',
						array( 'product_id' => $api_product_id )
					)
				);
			}
		}

		return $products;
	}

	/**
	 * Decrease stocks for a giving product.
	 *
	 * @param array $products product which needs stocks to be decreased
	 */
	private function _decrease_stock( $products ) {
		if ( get_option( 'woocommerce_manage_stock' ) === 'yes' ) {
			foreach ( $products as $product_id => $product ) {
				$lengow_product = get_product( $product_id );
				// Decrement stock product only if product managed in stock.
				if ( $lengow_product->managing_stock() ) {
					$initial_stock = $lengow_product->get_stock_quantity();
					$new_stock     = $lengow_product->reduce_stock( $product['quantity'] );
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'log.import.stock_decreased',
							array(
								'product_id'    => $product_id,
								'initial_stock' => $initial_stock,
								'new_stock'     => $new_stock
							)
						),
						$this->_log_output,
						$this->_marketplace_sku
					);
				} else {
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'log.import.stock_no_managed',
							array( 'product_id' => $product_id )
						),
						$this->_log_output,
						$this->_marketplace_sku
					);
				}
				unset( $lengow_product );
			}
		}
	}

	/**
	 * Create a order in lengow orders table.
	 *
	 * @return boolean
	 */
	private function _create_lengow_order() {
		global $wpdb;

		if ( ! is_null( $this->_order_data->marketplace_order_date ) ) {
			$order_date = (string) $this->_order_data->marketplace_order_date;
		} else {
			$order_date = (string) $this->_order_data->imported_at;
		}

		$result = $wpdb->insert(
			$wpdb->prefix . 'lengow_orders',
			array(
				'marketplace_sku'     => $this->_marketplace_sku,
				'marketplace_name'    => $this->_marketplace->name,
				'delivery_address_id' => (int) $this->_delivery_address_id,
				'order_date'          => date( 'Y-m-d H:i:s', strtotime( $order_date ) ),
				'extra'               => json_encode( $this->_order_data ),
				'created_at'          => date( 'Y-m-d H:i:s' )
			),
			array(
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s'
			)
		);

		if ( $result ) {
			$this->_id_order_lengow = Lengow_Order::get_id_from_lengow_orders(
				$this->_marketplace_sku,
				$this->_delivery_address_id
			);

			return true;
		} else {
			return false;
		}
	}
}

