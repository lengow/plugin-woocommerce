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
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
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
	 * @var integer|null id of the record Lengow order table.
	 */
	private $_order_lengow_id = null;

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
	 * @var boolean re-import order
	 */
	private $_is_reimported = false;

	/**
	 * @var string marketplace order state.
	 */
	private $_order_state_marketplace;

	/**
	 * @var string lengow order state.
	 */
	private $_order_state_lengow;

	/**
	 * @var float order total paid.
	 */
	private $_total_paid;

	/**
	 * @var float order processing fee.
	 */
	private $_processing_fee;

	/**
	 * @var float order shipping cost.
	 */
	private $_shipping_cost;

	/**
	 * @var integer number of order items.
	 */
	private $_order_item;

	/**
	 * @var string|null carrier
	 */
	private $_carrier = null;

	/**
	 * @var string|null carrier method
	 */
	private $_carrier_method = null;

	/**
	 * @var string|null carrier tracking number
	 */
	private $_carrier_tracking = null;

	/**
	 * @var string|null carrier relay id
	 */
	private $_carrier_id_relay = null;

	/**
	 * @var boolean True if order is send by the marketplace.
	 */
	private $_sent_marketplace = false;

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
	 *
	 * @throws Lengow_Exception
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
	 * @return array|false
	 */
	public function import_order() {
		// if order error exist and not finished
		$order_error = Lengow_Order_Error::order_is_in_error( $this->_marketplace_sku, $this->_delivery_address_id );
		if ( $order_error ) {
			$decoded_message = Lengow_Main::decode_log_message( $order_error->message, 'en_GB' );
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.error_already_created',
					array(
						'decoded_message' => $decoded_message,
						'date_message'    => $order_error->created_at,
					)
				),
				$this->_log_output,
				$this->_marketplace_sku
			);

			return false;
		}
		// recovery id if the command has already been imported.
		$order_id = Lengow_Order::get_order_id_from_lengow_orders(
			$this->_marketplace_sku,
			$this->_marketplace->name,
			$this->_delivery_address_id,
			$this->_marketplace->legacy_code
		);
		// update order state if already imported.
		if ( $order_id ) {

			// TODO check and update order

			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.order_already_decremented' ),
				$this->_log_output,
				$this->_marketplace_sku
			);

			return false;
		}
		// checks if an external id already exists.
		$order_id_woocommerce = $this->_check_external_ids( $this->_order_data->merchant_order_id );
		if ( $order_id_woocommerce && ! $this->_preprod_mode && ! $this->_is_reimported ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.external_id_exist',
					array( 'order_id' => $order_id_woocommerce )
				),
				$this->_log_output,
				$this->_marketplace_sku
			);

			return false;
		}
		// get a record in the lengow order table.
		$this->_order_lengow_id = Lengow_Order::get_id_from_lengow_orders(
			$this->_marketplace_sku,
			$this->_delivery_address_id
		);
		// if order is cancelled or new -> skip.
		if ( ! Lengow_Import::check_state( $this->_order_state_marketplace, $this->_marketplace ) ) {

			// TODO check and complete an order not imported if it is canceled or refunded

			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.current_order_state_unavailable',
					array(
						'order_state_marketplace' => $this->_order_state_marketplace,
						'marketplace_name'        => $this->_marketplace->name,
					)
				),
				$this->_log_output,
				$this->_marketplace_sku
			);

			return false;
		}
		// create a new record in lengow order table if not exist.
		if ( ! $this->_order_lengow_id ) {
			// created a record in the lengow order table.
			if ( ! $this->_create_lengow_order() ) {
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message( 'log.import.lengow_order_not_saved' ),
					$this->_log_output,
					$this->_marketplace_sku
				);

				return false;
			} else {
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message( 'log.import.lengow_order_saved' ),
					$this->_log_output,
					$this->_marketplace_sku
				);
			}
		}
		// checks if the required order data is present.
		if ( ! $this->_check_order_data() ) {
			return $this->_return_result( 'error', $this->_order_lengow_id );
		}
		// get order amount and load processing fees and shipping cost.
		$this->_total_paid = $this->_get_order_amount();
		// load tracking data.
		$this->_load_tracking_data();
		// get customer name and email.
		$customer_name  = $this->_get_customer_name();
		$customer_email = null !== $this->_order_data->billing_address->email
			? (string) $this->_order_data->billing_address->email
			: (string) $this->_package_data->delivery->email;
		// get order comment from marketplace.
		$message = $this->_get_order_comment();
		// update Lengow order with new data
		Lengow_Order::update(
			$this->_order_lengow_id,
			array(
				'currency'             => (string) $this->_order_data->currency->iso_a3,
				'total_paid'           => $this->_total_paid,
				'order_item'           => $this->_order_item,
				'customer_name'        => $customer_name,
				'customer_email'       => $customer_email,
				'carrier'              => $this->_carrier,
				'carrier_method'       => $this->_carrier_method,
				'carrier_tracking'     => $this->_carrier_tracking,
				'carrier_id_relay'     => $this->_carrier_id_relay,
				'sent_marketplace'     => (int) $this->_sent_marketplace,
				'delivery_country_iso' => (string) $this->_package_data->delivery->common_country_iso_a2,
				'order_lengow_state'   => $this->_order_state_lengow,
				'message'              => $message,
				'extra'                => json_encode( $this->_order_data ),
			)
		);
		// try to synchronise order.
		try {
			// check if the order is shipped by marketplace.
			if ( $this->_sent_marketplace ) {
				// if decrease stocks from mp option is disabled.
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
			if ( empty( $products ) ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'lengow_log.exception.product_list_is_empty' )
				);
			} else {
				// decrement product stock.
				$this->_decrease_stock( $products );
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message( 'log.import.order_successfully_decremented' ),
					$this->_log_output,
					$this->_marketplace_sku
				);
			}
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			Lengow_Order_Error::create(
				array(
					'order_lengow_id' => $this->_order_lengow_id,
					'message'         => $error_message,
				)
			);
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

			return $this->_return_result( 'error', $this->_order_lengow_id );
		}

		return $this->_return_result( 'new', $this->_order_lengow_id );
	}

	/**
	 * Return an array of result for each order.
	 *
	 * @param string $type_result Type of result (new or error)
	 * @param integer $order_lengow_id Lengow order id
	 * @param integer|null $order_id WooCommerce order id
	 *
	 * @return array
	 */
	private function _return_result( $type_result, $order_lengow_id, $order_id = null ) {
		$result = array(
			'order_id'         => $order_id,
			'order_lengow_id'  => $order_lengow_id,
			'marketplace_sku'  => $this->_marketplace_sku,
			'marketplace_name' => $this->_marketplace->name,
			'lengow_state'     => $this->_order_state_lengow,
			'order_new'        => 'new' === $type_result ? true : false,
			'order_error'      => 'error' === $type_result ? true : false,
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
		if ( empty( $this->_package_data->cart ) ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_product' );
		}
		if ( ! isset( $this->_order_data->currency->iso_a3 ) ) {
			$errorMessages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_currency' );
		}
		if ( - 1 == $this->_order_data->total_order ) {
			$errorMessages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_change_rate' );
		}
		if ( null === $this->_order_data->billing_address ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_billing_address' );
		} elseif ( null === $this->_order_data->billing_address->common_country_iso_a2 ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_country_for_billing_address' );
		}
		if ( null === $this->_package_data->delivery->common_country_iso_a2 ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_country_for_delivery_address' );
		}
		if ( ! empty( $error_messages ) ) {
			foreach ( $error_messages as $error_message ) {
				Lengow_Order_Error::create(
					array(
						'order_lengow_id' => $this->_order_lengow_id,
						'message'         => $error_message,
					)
				);
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
	 * Checks if an external id already exists.
	 *
	 * @param array $external_ids external ids return by API
	 *
	 * @return integer|false
	 */
	private function _check_external_ids( $external_ids ) {
		$order_id_woocommerce = false;
		if ( null !== $external_ids && ! empty( $external_ids ) ) {
			foreach ( $external_ids as $external_id ) {
				$order_lengow_id = Lengow_Order::get_id_from_lengow_delivery_address(
					(int) $external_id,
					$this->_delivery_address_id
				);
				if ( $order_lengow_id ) {
					$order_id_woocommerce = $external_id;
					break;
				}
			}
		}

		return $order_id_woocommerce;
	}

	/**
	 * Get order amount
	 *
	 * @return float
	 */
	private function _get_order_amount() {
		$this->_processing_fee = (float) $this->_order_data->processing_fee;
		$this->_shipping_cost  = (float) $this->_order_data->shipping;
		// rewrite processing fees and shipping cost.
		if ( ! $this->_first_package ) {
			$this->_processing_fee = 0;
			$this->_shipping_cost  = 0;
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.rewrite_processing_fee' ),
				$this->_log_output,
				$this->_marketplace_sku
			);
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.rewrite_shipping_cost' ),
				$this->_log_output,
				$this->_marketplace_sku
			);
		}
		// get total amount and the number of items.
		$nb_items     = 0;
		$total_amount = 0;
		foreach ( $this->_package_data->cart as $product ) {
			// check whether the product is canceled for amount.
			if ( null !== $product->marketplace_status ) {
				$product_state = $this->_marketplace->get_state_lengow( (string) $product->marketplace_status );
				if ( 'canceled' === $product_state || 'refused' === $product_state ) {
					continue;
				}
			}
			$nb_items     += (int) $product->quantity;
			$total_amount += (float) $product->amount;
		}
		$this->_order_item = $nb_items;
		$order_amount      = $total_amount + $this->_processing_fee + $this->_shipping_cost;

		return $order_amount;
	}

	/**
	 * Get tracking data and update Lengow order record.
	 */
	private function _load_tracking_data() {
		$trackings = $this->_package_data->delivery->trackings;
		if ( ! empty( $trackings ) ) {
			$tracking                = $trackings[0];
			$this->_carrier          = null !== $tracking->carrier ? (string) $tracking->carrier : null;
			$this->_carrier_method   = null !== $tracking->method ? (string) $tracking->method : null;
			$this->_carrier_tracking = null !== $tracking->number ? (string) $tracking->number : null;
			$this->_carrier_id_relay = null !== $tracking->relay->id ? (string) $tracking->relay->id : null;
			if ( null !== $tracking->is_delivered_by_marketplace && $tracking->is_delivered_by_marketplace ) {
				$this->_sent_marketplace = true;
			}
		}
	}

	/**
	 * Get customer name
	 *
	 * @return string
	 */
	private function _get_customer_name() {
		$firstName = (string) $this->_order_data->billing_address->first_name;
		$lastName  = (string) $this->_order_data->billing_address->last_name;
		$firstName = ucfirst( strtolower( $firstName ) );
		$lastName  = ucfirst( strtolower( $lastName ) );
		if ( empty( $firstName ) && empty( $lastName ) ) {
			return (string) $this->_order_data->billing_address->full_name;
		} else {
			return $firstName . ' ' . $lastName;
		}
	}

	/**
	 * Get order comment from marketplace.
	 *
	 * @return string|null
	 */
	private function _get_order_comment() {
		if ( isset( $this->_order_data->comments ) && is_array( $this->_order_data->comments ) ) {
			$order_comment = ! empty( $this->_order_data->comments ) ? join( ',', $this->_order_data->comments ) : null;
		} else {
			$order_comment = $this->_order_data->comments;
		}

		return $order_comment;
	}

	/**
	 * Get products from the API and check that they exist in WooCommerce database.
	 *
	 * @return array
	 * @throws Lengow_Exception If product is not found
	 *
	 */
	private function _get_products() {
		$products = array();
		foreach ( $this->_package_data->cart as $product ) {
			$found          = false;
			$product_datas  = Lengow_Product::extract_product_data_from_api( $product );
			$api_product_id = null !== $product_datas['merchant_product_id']->id
				? (string) $product_datas['merchant_product_id']->id
				: (string) $product_datas['marketplace_product_id'];
			if ( null !== $product_datas['marketplace_status'] ) {
				$state_product = $this->_marketplace->get_state_lengow( (string) $product_datas['marketplace_status'] );
				if ( 'canceled' === $state_product || 'refused' === $state_product ) {
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'log.import.product_state_canceled',
							array(
								'product_id'    => $api_product_id,
								'state_product' => $state_product,
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
					$products[ $product_id ]['amount']   += (float) $product_datas['amount'];
				} else {
					$products[ $product_id ] = $product_datas;
				}
				$found = true;
			}
			if ( ! $found ) {
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
		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
			foreach ( $products as $product_id => $product ) {
				$wc_product = Lengow_Product::get_product( $product_id );
				// decrement stock product only if product managed in stock.
				if ( $wc_product->managing_stock() ) {
					$initial_stock = $wc_product->get_stock_quantity();
					$new_stock     = Lengow_Product::reduce_product_stock( $wc_product, $product['quantity'] );
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'log.import.stock_decreased',
							array(
								'product_id'    => $product_id,
								'initial_stock' => $initial_stock,
								'new_stock'     => $new_stock,
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
				unset( $wc_product );
			}
		}
	}

	/**
	 * Create a order in lengow orders table.
	 *
	 * @return boolean
	 */
	private function _create_lengow_order() {
		$order_date = null !== $this->_order_data->marketplace_order_date
			? (string) $this->_order_data->marketplace_order_date
			: (string) $this->_order_data->imported_at;

		$data   = array(
			'marketplace_sku'     => $this->_marketplace_sku,
			'marketplace_name'    => $this->_marketplace->name,
			'marketplace_label'   => $this->_marketplace->label_name,
			'delivery_address_id' => $this->_delivery_address_id,
			'order_date'          => date( 'Y-m-d H:i:s', strtotime( $order_date ) ),
			'order_lengow_state'  => $this->_order_state_lengow,
			'extra'               => json_encode( $this->_order_data ),
		);
		$result = Lengow_Order::create( $data );
		if ( $result ) {
			$this->_order_lengow_id = Lengow_Order::get_id_from_lengow_orders(
				$this->_marketplace_sku,
				$this->_delivery_address_id
			);

			return true;
		} else {
			return false;
		}
	}
}
