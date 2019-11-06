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
	 * @var string lengow order date.
	 */
	private $_order_date;

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
	 * @var string marketplace comment.
	 */
	private $_message;

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
		// if order error exists and not finished.
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
			$order_updated = $this->_check_and_update_order( $order_id );
			if ( $order_updated && isset( $order_updated['update'] ) ) {
				return $this->_return_result( 'update', $order_updated['order_lengow_id'], $order_id );
			}
			if ( ! $this->_is_reimported ) {
				return false;
			}
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
			$order_process_state = Lengow_Order::get_order_process_state( $this->_order_state_lengow );
			// check and complete an order not imported if it is canceled or refunded.
			if ( $this->_order_lengow_id && Lengow_Order::PROCESS_STATE_FINISH === $order_process_state ) {
				Lengow_Order_Error::finish_order_errors( $this->_order_lengow_id );
				Lengow_Order::update(
					$this->_order_lengow_id,
					array(
						'order_lengow_state'  => $this->_order_state_lengow,
						'order_process_state' => $order_process_state,
					)
				);
			}
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
		// load order date.
		$this->_load_order_date();
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
		$this->_message = $this->_get_order_comment();
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
				'message'              => $this->_message,
				'extra'                => json_encode( $this->_order_data ),
			)
		);
		// try to synchronise order.
		try {
			// check if the order is shipped by marketplace.
			if ( $this->_sent_marketplace ) {
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
					Lengow_Order::update(
						$this->_order_lengow_id,
						array(
							'order_process_state' => Lengow_Order::PROCESS_STATE_FINISH,
							'is_in_error'         => 0,
							'is_reimported'       => 0,
						)
					);

					return false;
				}
			}
			// get a product list.
			$products = $this->_get_products();
			if ( empty( $products ) ) {
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'lengow_log.exception.product_list_is_empty' )
				);
			}
			// get billing and shipping addresses for the user and the order.
			$billing_address  = new Lengow_Address(
				$this->_order_data->billing_address,
				Lengow_Address::TYPE_BILLING
			);
			$shipping_address = new Lengow_Address(
				$this->_package_data->delivery,
				Lengow_Address::TYPE_SHIPPING,
				$this->_carrier_id_relay
			);
			$billing_email    = $billing_address->get_data( 'email' );
			if ( empty( $billing_email ) ) {
				$shipping_email = $shipping_address->get_data( 'email' );
				$billing_address->set_data( 'email', $shipping_email );
			}
			$billing_phone = $billing_address->get_data( 'phone' );
			if ( empty( $billing_phone ) ) {
				$shipping_phone = $shipping_address->get_data( 'phone' );
				$billing_address->set_data( 'phone', $shipping_phone );
			}
			// get fictitious email for user creation.
			$user_email = $this->_get_user_email();
			// get or create a Wordpress user.
			$user = get_user_by( 'email', $user_email );
			if ( ! $user ) {
				$user = $this->_create_user( $user_email, $billing_address, $shipping_address );
			}
			// create a WooCommerce order.
			$order = $this->_create_order( $user, $products, $billing_address, $shipping_address );
			// save order line id in lengow_order_line table.
			$order_line_saved = $this->_save_lengow_order_lines( $order, $products );
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.lengow_order_line_saved',
					array( 'order_line_saved' => $order_line_saved )
				),
				$this->_log_output,
				$this->_marketplace_sku
			);
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.order_successfully_imported',
					array( 'order_id' => Lengow_Order::get_order_id( $order ) )
				),
				$this->_log_output,
				$this->_marketplace_sku
			);
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			Lengow_Order::add_order_error( $this->_order_lengow_id, $error_message );
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
			Lengow_Order::update(
				$this->_order_lengow_id,
				array(
					'order_lengow_state' => $this->_order_state_lengow,
					'is_reimported'      => 0,
				)
			);

			return $this->_return_result( 'error', $this->_order_lengow_id );
		}

		return $this->_return_result( 'new', $this->_order_lengow_id, Lengow_Order::get_order_id( $order ) );
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
			'order_update'     => 'update' === $type_result ? true : false,
			'order_error'      => 'error' === $type_result ? true : false,
		);

		return $result;
	}

	/**
	 * Check the order and updates data if necessary.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return array|false
	 */
	protected function _check_and_update_order( $order_id ) {
		Lengow_Main::log(
			'Import',
			Lengow_Main::set_log_message( 'log.import.order_already_imported', array( 'order_id' => $order_id ) ),
			$this->_log_output,
			$this->_marketplace_sku
		);
		$order           = new WC_Order( $order_id );
		$order_lengow_id = Lengow_Order::get_id_from_order_id( $order_id );
		$order_lengow    = new Lengow_Order( $order_lengow_id );
		$result          = array( 'order_lengow_id' => $order_lengow->id );
		// Lengow -> cancel and reimport order
		if ( $order_lengow->is_reimported ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.order_ready_to_reimport', array( 'order_id' => $order_id ) ),
				$this->_log_output,
				$this->_marketplace_sku
			);
			$this->_is_reimported = true;

			return false;
		} else {
			$order_updated = Lengow_Order::update_state(
				$order,
				$order_lengow,
				$this->_order_state_lengow,
				$this->_package_data
			);
			if ( $order_updated ) {
				$result['update'] = true;
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message(
						'log.import.order_state_updated',
						array( 'state_name' => $order_updated )
					),
					$this->_log_output,
					$this->_marketplace_sku
				);
			}
			unset( $order, $order_lengow );

			return $result;
		}


	}

	/**
	 * Load order date for order creation.
	 */
	private function _load_order_date() {
		$order_date        = null !== $this->_order_data->marketplace_order_date
			? (string) $this->_order_data->marketplace_order_date
			: (string) $this->_order_data->imported_at;
		$this->_order_date = date( 'Y-m-d H:i:s', strtotime( $order_date ) );
	}

	/**
	 * Create a order in lengow orders table.
	 *
	 * @return boolean
	 */
	private function _create_lengow_order() {
		$data   = array(
			'marketplace_sku'     => $this->_marketplace_sku,
			'marketplace_name'    => $this->_marketplace->name,
			'marketplace_label'   => $this->_marketplace->label_name,
			'delivery_address_id' => $this->_delivery_address_id,
			'order_date'          => $this->_order_date,
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
			Lengow_Order::update( $this->_order_lengow_id, array( 'is_in_error' => 1 ) );

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
				if ( in_array( $product_state, array( Lengow_Order::STATE_CANCELED, Lengow_Order::STATE_REFUSED ) ) ) {
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
	 * Load tracking data for order creation.
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
			$order_comment = ! empty( $this->_order_data->comments )
				? join( ', ', $this->_order_data->comments )
				: null;
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
		foreach ( $this->_package_data->cart as $api_product ) {
			$found          = false;
			$order_line_id  = (string) $api_product->marketplace_order_line_id;
			$product_datas  = Lengow_Product::extract_product_data_from_api( $api_product );
			$api_product_id = null !== $product_datas['merchant_product_id']->id
				? (string) $product_datas['merchant_product_id']->id
				: (string) $product_datas['marketplace_product_id'];
			if ( null !== $product_datas['marketplace_status'] ) {
				$product_state = $this->_marketplace->get_state_lengow( (string) $product_datas['marketplace_status'] );
				if ( in_array( $product_state, array( Lengow_Order::STATE_CANCELED, Lengow_Order::STATE_REFUSED ) ) ) {
					Lengow_Main::log(
						'Import',
						Lengow_Main::set_log_message(
							'log.import.product_state_canceled',
							array(
								'product_id'    => $api_product_id,
								'product_state' => $product_state,
							)
						),
						$this->_log_output,
						$this->_marketplace_sku
					);
					continue;
				}
			}
			$product = Lengow_Product::match_product( $product_datas, $this->_marketplace_sku, $this->_log_output );
			if ( $product ) {
				if ( Lengow_Main::compare_version( '3.0' ) ) {
					$product_id   = $product->get_id();
					$product_name = $product->get_name();
				} else {
					$product_id   = $product->id;
					$product_name = $product->get_title();
				}
				if ( array_key_exists( $product_id, $products ) ) {
					$products[ $product_id ]['quantity']         += (integer) $product_datas['quantity'];
					$products[ $product_id ]['amount']           += (float) $product_datas['amount'];
					$products[ $product_id ]['order_line_ids'][] = $order_line_id;
				} else {
					$products[ $product_id ] = array(
						'woocommerce_product' => $product,
						'name'                => $product_name,
						'amount'              => (float) $product_datas['amount'],
						'price_unit'          => $product_datas['price_unit'],
						'quantity'            => (int) $product_datas['quantity'],
						'order_line_ids'      => array( $order_line_id ),
					);
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
	 * Get fictitious email for user creation.
	 *
	 * @return string
	 */
	private function _get_user_email() {
		$domain = implode( '.', array_slice( explode( '.', parse_url( get_site_url(), PHP_URL_HOST ) ), - 2 ) );
		$domain = preg_match( '`^([\w]+)\.([a-z]+)$`', $domain ) ? $domain : 'lengow.com';
		$email  = $this->_marketplace_sku . '-' . $this->_marketplace->name . '@' . $domain;
		Lengow_Main::log(
			'Import',
			Lengow_Main::set_log_message( 'log.import.generate_unique_email', array( 'email' => $email ) ),
			$this->_log_output,
			$this->_marketplace_sku
		);

		return $email;
	}

	/**
	 * Create Wordpress user with billing and shipping addresses.
	 *
	 * @param string $user_email fictitious email for user
	 * @param Lengow_Address $billing_address Lengow billing address
	 * @param Lengow_Address $shipping_address Lengow shipping address
	 *
	 * @return WP_User|false
	 * @throws Lengow_Exception Woocommerce customer not saved
	 *
	 */
	private function _create_user( $user_email, $billing_address, $shipping_address ) {
		// create Wordpress user.
		$new_customer_data = array(
			'user_login' => strlen( $user_email ) > 60 ? substr( $user_email, - 60 ) : $user_email,
			'user_pass'  => wp_generate_password( 32, false ),
			'user_email' => $user_email,
			'role'       => 'customer',
			'first_name' => $billing_address->get_data( 'first_name' ),
			'last_name'  => $billing_address->get_data( 'last_name' ),
		);
		$user_id           = wp_insert_user( apply_filters( 'woocommerce_new_customer_data', $new_customer_data ) );
		if ( is_wp_error( $user_id ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'lengow_log.exception.woocommerce_customer_not_saved' )
			);
		}
		$user = get_user_by( 'id', $user_id );
		do_action( 'woocommerce_created_customer', $user_id );
		// get billing data formatted for WooCommerce address.
		$billing_data  = $billing_address->get_formatted_data();
		$shipping_data = $shipping_address->get_formatted_data();
		// adds shipping and billing addresses to a user.
		foreach ( $billing_data as $key => $field ) {
			update_user_meta( $user->ID, $key, $field );
		}
		foreach ( $shipping_data as $key => $field ) {
			update_user_meta( $user->ID, $key, $field );
		}
		do_action( 'woocommerce_customer_save_address', $user->ID );

		return $user;
	}

	/**
	 * Create WooCommerce order.
	 *
	 * @param WP_User $user current user
	 * @param array $products product list
	 * @param Lengow_Address $billing_address Lengow billing address
	 * @param Lengow_Address $shipping_address Lengow shipping address
	 *
	 * @return WC_Order
	 * @throws Exception|Lengow_Exception
	 *
	 */
	private function _create_order( $user, $products, $billing_address, $shipping_address ) {
		// create a generic order.
		$new_order_data = array(
			'post_type'     => 'shop_order',
			'post_title'    => sprintf(
				__( 'Order &ndash; %s', 'woocommerce' ),
				strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) )
			),
			'post_status'   => 'publish',
			'ping_status'   => 'closed',
			'post_excerpt'  => (string) $this->_message,
			'post_author'   => 1,
			'post_password' => uniqid( 'wc_order_' ),
			'post_date'     => get_date_from_gmt( $this->_order_date ),
			'post_date_gmt' => $this->_order_date,
		);
		$order_data     = apply_filters( 'woocommerce_new_order_data', $new_order_data );
		$order_id       = wp_insert_post( $order_data, true );
		if ( is_wp_error( $order_id ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'lengow_log.exception.woocommerce_order_not_saved' )
			);
		}
		do_action( 'woocommerce_new_order', $order_id );
		// update lengow_orders table directly after creating the WooCommerce order.
		$success = Lengow_Order::update(
			$this->_order_lengow_id,
			array(
				'order_id'            => $order_id,
				'order_process_state' => Lengow_Order::get_order_process_state( $this->_order_state_lengow ),
				'order_lengow_state'  => $this->_order_state_lengow,
				'is_in_error'         => 0,
				'is_reimported'       => 0,
			)
		);
		if ( ! $success ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.lengow_order_not_updated' ),
				$this->_log_output,
				$this->_marketplace_sku
			);
		} else {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.lengow_order_updated' ),
				$this->_log_output,
				$this->_marketplace_sku
			);
		}
		// get billing data formatted for WooCommerce address.
		$billing_data  = $billing_address->get_formatted_data();
		$shipping_data = $shipping_address->get_formatted_data();
		// adds shipping and billing addresses to the order.
		foreach ( $billing_data as $key => $field ) {
			update_post_meta( $order_id, '_' . $key, $field );
		}
		foreach ( $shipping_data as $key => $field ) {
			update_post_meta( $order_id, '_' . $key, $field );
		}
		update_post_meta( $order_id, '_customer_user', absint( $user->ID ) );
		// load WooCommerce customer.
		$customer = new WC_Customer( $user->ID );
		// add products, shipping cost, tax and processing fees to the order.
		$tax_amount = 0;
		foreach ( $products as $product_data ) {
			$tax_amount += $this->_add_product( $order_id, $customer, $product_data );
		}
		$shipping_cost = $this->_add_shipping_cost( $order_id, $customer, $products );
		$this->_add_tax( $order_id, $customer, $tax_amount, $shipping_cost['tax_amount'] );
		if ( $this->_processing_fee > 0 ) {
			$this->_add_processing_fee( $order_id );
		}
		// add post meta.
		$order_shipping      = $this->_format_total( $shipping_cost['amount'] );
		$order_tax           = $this->_format_total( $tax_amount );
		$order_shipping_tax  = $this->_format_total( $shipping_cost['tax_amount'] );
		$order_total         = $this->_format_total( $this->_total_paid );
		$order_key           = apply_filters( 'woocommerce_generate_order_key', uniqid( 'wc_order_' ) );
		$order_currency      = (string) $this->_order_data->currency->iso_a3;
		$customer_ip_address = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] )
			? $_SERVER['HTTP_X_FORWARDED_FOR']
			: $_SERVER['REMOTE_ADDR'];
		$customer_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$prices_include_tax  = get_option( 'woocommerce_prices_include_tax' );
		update_post_meta( $order_id, '_cart_discount', 0 );
		update_post_meta( $order_id, '_order_discount', 0 );
		update_post_meta( $order_id, '_order_total', $order_total );
		update_post_meta( $order_id, '_order_tax', $order_tax );
		update_post_meta( $order_id, '_order_shipping', $order_shipping );
		update_post_meta( $order_id, '_order_shipping_tax', $order_shipping_tax );
		update_post_meta( $order_id, '_order_key', $order_key );
		update_post_meta( $order_id, '_order_currency', $order_currency );
		update_post_meta( $order_id, '_payment_method', WC_Lengow_Payment_Gateway::PAYMENT_LENGOW_ID );
		update_post_meta( $order_id, '_payment_method_title', $this->_marketplace->label_name );
		update_post_meta( $order_id, '_date_paid', strtotime( $this->_order_date ) );
		update_post_meta( $order_id, '_paid_date', $this->_order_date );
		update_post_meta( $order_id, '_shipping_method', $shipping_cost['method'] );
		update_post_meta( $order_id, '_shipping_method_title', $shipping_cost['method_title'] );
		update_post_meta( $order_id, '_prices_include_tax', $prices_include_tax );
		update_post_meta( $order_id, '_customer_ip_address', $customer_ip_address );
		update_post_meta( $order_id, '_customer_user_agent', $customer_user_agent );
		// load WooCommerce order.
		$order = new WC_Order( $order_id );
		// change order state.
		$order_state = Lengow_Order::get_woocommerce_state(
			$this->_order_state_marketplace,
			$this->_marketplace,
			$this->_sent_marketplace
		);
		$order->update_status( $order_state );
		// don't reduce stock for re-import order and order shipped by marketplace.
		if ( $this->_is_reimported
		     || ( $this->_sent_marketplace && ! (bool) Lengow_Configuration::get( 'lengow_import_stock_ship_mp' ) )
		) {
			if ( $this->_is_reimported ) {
				$logMessage = Lengow_Main::set_log_message( 'log.import.quantity_back_reimported_order' );
			} else {
				$logMessage = Lengow_Main::set_log_message( 'log.import.quantity_back_shipped_by_marketplace' );
			}
			Lengow_Main::log( 'Import', $logMessage, $this->_log_output, $this->_marketplace_sku );
			if ( Lengow_Main::compare_version( '3.0' ) ) {
				wc_increase_stock_levels( Lengow_Order::get_order_id( $order ) );
			}
		} else {
			// reduce stock levels for old versions.
			if ( ! Lengow_Main::compare_version( '3.0' ) ) {
				$order->reduce_order_stock();
			}
		}

		return $order;
	}

	/**
	 * Add item to the order.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param WC_Customer $customer WooCommerce customer instance
	 * @param array $product_data product data
	 *
	 * @return float
	 */
	private function _add_product( $order_id, $customer, $product_data ) {
		$line_tax = 0;
		$wc_tax   = new WC_Tax();
		// get product and product data.
		$product    = $product_data['woocommerce_product'];
		$price_unit = $product_data['price_unit'];
		$quantity   = $product_data['quantity'];
		try {
			// add line item.
			$new_product_data = array( 'order_item_name' => $product_data['name'], 'order_item_type' => 'line_item' );
			$item_id          = $this->_add_order_item( $order_id, $new_product_data );
			// calculated tax per line.
			$tax_rates         = Lengow_Main::compare_version( '3.2' )
				? $wc_tax->get_rates( $product->get_tax_class(), $customer )
				: $wc_tax->get_rates( $product->get_tax_class() );
			$taxes             = $wc_tax->calc_tax( $price_unit, $tax_rates, true );
			$tax_id            = ! empty( $taxes ) ? (int) key( $taxes ) : false;
			$product_tax       = $tax_id ? $taxes[ $tax_id ] : 0;
			$line_subtotal     = $this->_format_decimal( $price_unit - $product_tax, 8 );
			$line_total        = $this->_format_decimal( ( $price_unit - $product_tax ) * $quantity, 8 );
			$line_subtotal_tax = $this->_format_decimal( $product_tax, 8 );
			$line_tax          = $this->_format_decimal( $product_tax * $quantity, 8 );
			$line_tax_data     = array(
				'total'    => array( $tax_id => $line_tax ),
				'subtotal' => array( $tax_id => $line_subtotal_tax ),
			);
			// add line item meta.
			$this->_add_order_item_meta( $item_id, '_product_id', Lengow_Product::get_product_id( $product ) );
			$this->_add_order_item_meta( $item_id, '_variation_id', Lengow_Product::get_variation_id( $product ) );
			$this->_add_order_item_meta( $item_id, '_qty', apply_filters( 'woocommerce_stock_amount', $quantity ) );
			$this->_add_order_item_meta( $item_id, '_tax_class', $product->get_tax_class() );
			$this->_add_order_item_meta( $item_id, '_line_subtotal', $line_subtotal );
			$this->_add_order_item_meta( $item_id, '_line_subtotal_tax', $line_subtotal_tax );
			$this->_add_order_item_meta( $item_id, '_line_total', $line_total );
			$this->_add_order_item_meta( $item_id, '_line_tax', $line_tax );
			$this->_add_order_item_meta( $item_id, '_line_tax_data', $line_tax_data );
		} catch ( Exception $e ) {
			$product_id = Lengow_Main::compare_version( '3.0' ) ? $product->get_id() : $product->id;
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.product_not_saved',
					array( 'product_id' => $product_id, 'error_message' => $e->getMessage() )
				),
				$this->_log_output,
				$this->_marketplace_sku
			);
		}

		return (float) $line_tax;
	}


	/**
	 * Add shipping cost to the order.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param WC_Customer $customer WooCommerce customer instance
	 * @param array $products products list
	 *
	 * @return array
	 */
	private function _add_shipping_cost( $order_id, $customer, $products ) {
		$wc_tax = new WC_Tax();
		// set shipping cost tax.
		$shipping   = $this->_shipping_cost;
		$tax_rates  = Lengow_Main::compare_version( '3.2' )
			? $wc_tax->get_shipping_tax_rates( '', $customer )
			: $wc_tax->get_shipping_tax_rates();
		$taxes      = $wc_tax->calc_tax( $shipping, $tax_rates, true, false );
		$tax_id     = ! empty( $taxes ) ? (int) key( $taxes ) : false;
		$tax_amount = $tax_id ? $taxes[ $tax_id ] : 0;
		$amount     = $shipping - $tax_amount;
		// get default shipping method.
		$wc_shipping             = new WC_Shipping();
		$shipping_methods        = $wc_shipping->load_shipping_methods();
		$default_shipping_method = Lengow_Configuration::get( 'lengow_import_default_shipping_method' );
		$shipping_method         = array_key_exists( $default_shipping_method, $shipping_methods )
			? $shipping_methods[ $default_shipping_method ]
			: $shipping_method = current( $shipping_methods );
		$shipping_method_title   = Lengow_Main::compare_version( '3.0' )
			? $shipping_method->get_method_title()
			: $shipping_method->method_title;
		try {
			$new_shipping_data = array( 'order_item_name' => $shipping_method_title, 'order_item_type' => 'shipping' );
			$item_id           = $this->_add_order_item( $order_id, $new_shipping_data );
			// add line item meta for shipping.
			$articles = array();
			foreach ( $products as $product ) {
				$articles[] = $product['name'] . ' &times; ' . $product['quantity'];
			}
			$instance_id = Lengow_Main::compare_version( '3.0' ) ? $shipping_method->instance_id : 0;
			$this->_add_order_item_meta( $item_id, 'method_id', $shipping_method->id );
			$this->_add_order_item_meta( $item_id, 'instance_id', $instance_id );
			$this->_add_order_item_meta( $item_id, 'cost', $amount );
			$this->_add_order_item_meta( $item_id, 'total_tax', $tax_amount );
			$this->_add_order_item_meta( $item_id, 'taxes', array( 'total' => array( $tax_id => $tax_amount ) ) );
			$this->_add_order_item_meta( $item_id, 'Articles', implode( ', ', $articles ) );
		} catch ( Exception $e ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.shipping_not_saved',
					array( 'error_message' => $e->getMessage() )
				),
				$this->_log_output,
				$this->_marketplace_sku
			);
		}

		return array(
			'method'       => $shipping_method->id,
			'method_title' => $shipping_method_title,
			'amount'       => $amount,
			'tax_amount'   => $tax_amount,
		);
	}

	/**
	 * Add tax to the order.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param WC_Customer $customer WooCommerce customer instance
	 * @param float $tax_amount order tax amount without shipping
	 * @param float $shipping_tax_amount shipping tax amount
	 */
	private function _add_tax( $order_id, $customer, $tax_amount, $shipping_tax_amount ) {
		$wc_tax    = new WC_Tax();
		$tax_rates = Lengow_Main::compare_version( '3.2' )
			? $wc_tax->get_rates( '', $customer )
			: $wc_tax->get_rates();
		if ( ! empty( $tax_rates ) ) {
			$tax_id = key( $tax_rates );
			$tax    = $tax_rates[ $tax_id ];
			try {
				$new_tax_data = array(
					'order_item_name' => $wc_tax->get_rate_code( $tax_id ),
					'order_item_type' => 'tax',
				);
				$item_id      = $this->_add_order_item( $order_id, $new_tax_data );
				// add line item meta for tax.
				$this->_add_order_item_meta( $item_id, 'rate_id', $tax_id );
				$this->_add_order_item_meta( $item_id, 'label', $tax['label'] );
				$this->_add_order_item_meta( $item_id, 'compound', $tax['compound'] === 'yes' ? 1 : 0 );
				$this->_add_order_item_meta( $item_id, 'tax_amount', $tax_amount );
				$this->_add_order_item_meta( $item_id, 'shipping_tax_amount', $shipping_tax_amount );
				$this->_add_order_item_meta( $item_id, 'rate_percent', $tax['rate'] );
			} catch ( Exception $e ) {
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message(
						'log.import.tax_not_saved',
						array( 'error_message' => $e->getMessage() )
					),
					$this->_log_output,
					$this->_marketplace_sku
				);
			}
		}
	}

	/**
	 * Add processing fee to the order.
	 *
	 * @param integer $order_id WooCommerce order id
	 */
	private function _add_processing_fee( $order_id ) {
		try {
			$locale                  = new Lengow_Translation();
			$new_processing_fee_data = array(
				'order_item_name' => $locale->t( 'module.processing_fee' ),
				'order_item_type' => 'fee',
			);
			$item_id                 = $this->_add_order_item( $order_id, $new_processing_fee_data );
			// add line item meta for processing fee.
			$this->_add_order_item_meta( $item_id, '_tax_class', '0' );
			$this->_add_order_item_meta( $item_id, '_line_total', $this->_format_total( $this->_processing_fee ) );
			$this->_add_order_item_meta( $item_id, '_line_tax', '0' );
		} catch ( Exception $e ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message(
					'log.import.processing_fee_not_saved',
					array( 'error_message' => $e->getMessage() )
				),
				$this->_log_output,
				$this->_marketplace_sku
			);
		}
	}

	/**
	 * Save order line in lengow orders line table.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 * @param array $products order products
	 *
	 * @return string|false
	 */
	private function _save_lengow_order_lines( $order, $products ) {
		$order_line_saved = false;
		foreach ( $products as $product_id => $product_data ) {
			foreach ( $product_data['order_line_ids'] as $order_line_id ) {
				$result = Lengow_Order_Line::create(
					array(
						'order_id'      => Lengow_Order::get_order_id( $order ),
						'order_line_id' => $order_line_id,
						'product_id'    => $product_id,
					)
				);
				if ( $result ) {
					$order_line_saved .= ! $order_line_saved ? $order_line_id : ' / ' . $order_line_id;
				}
			}
		}

		return $order_line_saved;
	}

	/**
	 * Get compatibility for woocommerce_format_total function.
	 *
	 * @param mixed $number number
	 *
	 * @return string
	 */
	private function _format_total( $number ) {
		if ( Lengow_Main::compare_version( '2.1' ) ) {
			return wc_format_decimal( $number );
		} else {
			return woocommerce_format_total( $number );
		}
	}

	/**
	 * Get compatibility for woocommerce_format_decimal function.
	 *
	 * @param mixed $number number
	 * @param mixed $dp number of decimal points to use
	 *
	 * @return string
	 */
	private function _format_decimal( $number, $dp ) {
		if ( Lengow_Main::compare_version( '2.1' ) ) {
			return wc_format_decimal( $number, $dp );
		} else {
			return woocommerce_format_decimal( $number, $dp );
		}
	}

	/**
	 * Get compatibility for woocommerce_add_order_item function.
	 *
	 * @param integer $order_id item id
	 * @param mixed $item meta key
	 *
	 * @return mixed
	 * @throws Exception
	 *
	 */
	private function _add_order_item( $order_id, $item ) {
		if ( Lengow_Main::compare_version( '2.1' ) ) {
			return wc_add_order_item( $order_id, $item );
		} else {
			return woocommerce_add_order_item( $order_id, $item );
		}
	}

	/**
	 * Get compatibility for woocommerce_add_order_item_meta function.
	 *
	 * @param mixed $item_id item id
	 * @param mixed $meta_key meta key
	 * @param mixed $meta_value meta value
	 *
	 * @return integer
	 * @throws Exception
	 *
	 */
	private function _add_order_item_meta( $item_id, $meta_key, $meta_value ) {
		if ( Lengow_Main::compare_version( '2.1' ) ) {
			return wc_add_order_item_meta( $item_id, $meta_key, $meta_value );
		} else {
			return woocommerce_add_order_item_meta( $item_id, $meta_key, $meta_value );
		}
	}
}
