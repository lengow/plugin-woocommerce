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
 * Lengow_Import_Order Class.
 */
class Lengow_Import_Order {

	/* Import Order construct params */
	const PARAM_FORCE_SYNC = 'force_sync';
	const PARAM_DEBUG_MODE = 'debug_mode';
	const PARAM_LOG_OUTPUT = 'log_output';
	const PARAM_MARKETPLACE_SKU = 'marketplace_sku';
	const PARAM_DELIVERY_ADDRESS_ID = 'delivery_address_id';
	const PARAM_ORDER_DATA = 'order_data';
	const PARAM_PACKAGE_DATA = 'package_data';
	const PARAM_FIRST_PACKAGE = 'first_package';
	const PARAM_IMPORT_ONE_ORDER = 'import_one_order';

	/* Import Order data */
	const MERCHANT_ORDER_ID = 'merchant_order_id';
	const MERCHANT_ORDER_REFERENCE = 'merchant_order_reference';
	const LENGOW_ORDER_ID = 'lengow_order_id';
	const MARKETPLACE_SKU = 'marketplace_sku';
	const MARKETPLACE_NAME = 'marketplace_name';
	const DELIVERY_ADDRESS_ID = 'delivery_address_id';
	const SHOP_ID = 'shop_id';
	const CURRENT_ORDER_STATUS = 'current_order_status';
	const PREVIOUS_ORDER_STATUS = 'previous_order_status';
	const ERRORS = 'errors';
	const RESULT_TYPE = 'result_type';

	/* Synchronisation results */
	const RESULT_CREATED = 'created';
	const RESULT_UPDATED = 'updated';
	const RESULT_FAILED = 'failed';
	const RESULT_IGNORED = 'ignored';

	/**
	 * @var boolean force import order even if there are errors.
	 */
	private $force_sync;

	/**
	 * @var boolean use debug mode.
	 */
	private $debug_mode;

	/**
	 * @var boolean display log messages.
	 */
	private $log_output;

	/**
	 * @var integer|null id of the record Lengow order table.
	 */
	private $order_lengow_id;

	/**
	 * @var integer id of the record WooCommerce order table
	 */
	private $order_id;

	/**
	 * @var integer WooCommerce order reference
	 */
	private $order_reference;

	/**
	 * @var Lengow_Marketplace Lengow marketplace instance.
	 */
	private $marketplace;

	/**
	 * @var string id lengow of current order.
	 */
	private $marketplace_sku;

	/**
	 * @var string marketplace label.
	 */
	private $marketplace_label;

	/**
	 * @var integer id of delivery address for current order.
	 */
	private $delivery_address_id;

	/**
	 * @var mixed all order data.
	 */
	private $order_data;

	/**
	 * @var mixed all package data.
	 */
	private $package_data;

	/**
	 * @var boolean if order is first package.
	 */
	private $first_package;

	/**
	 * @var boolean import one order var from lengow import.
	 */
	private $import_one_order;

	/**
	 * @var boolean re-import order.
	 */
	private $is_reimported = false;

	/**
	 * @var string marketplace order state.
	 */
	private $order_state_marketplace;

	/**
	 * @var string lengow order state.
	 */
	private $order_state_lengow;

	/**
	 * @var string Previous Lengow order state.
	 */
	private $previous_order_state_lengow;

	/**
	 * @var string lengow order date.
	 */
	private $order_date;

	/**
	 * @var float order total paid.
	 */
	private $total_paid;

	/**
	 * @var float order processing fee.
	 */
	private $processing_fee;

	/**
	 * @var float order shipping cost.
	 */
	private $shipping_cost;

	/**
	 * @var integer number of order items.
	 */
	private $order_item;

	/**
	 * @var array order types (is_express, is_prime...).
	 */
	private $order_types;

	/**
	 * @var string|null carrier.
	 */
	private $carrier;

	/**
	 * @var string|null carrier method.
	 */
	private $carrier_method;

	/**
	 * @var string|null carrier tracking number.
	 */
	private $carrier_tracking;

	/**
	 * @var string|null carrier relay id.
	 */
	private $carrier_id_relay;

	/**
	 * @var boolean True if order is send by the marketplace.
	 */
	private $sent_marketplace = false;

	/**
	 * @var string marketplace comment.
	 */
	private $message;

	/**
	 * @var array order errors.
	 */
	private $errors = array();

	/**
	 * Construct the import manager.
	 *
	 * @param $params array Optional options
	 * boolean force_sync          Force import order even if there are errors
	 * boolean debug_mode          Debug mode
	 * boolean log_output          Display log messages
	 * string  marketplace_sku     Order marketplace sku
	 * integer delivery_address_id Order delivery address id
	 * mixed   order_data          Order data
	 * mixed   package_data        Package data
	 * boolean first_package       It is the first package
	 * boolean import_one_order    Synchronization for one order
	 */
	public function __construct( $params = array() ) {
		$this->force_sync          = $params[ self::PARAM_FORCE_SYNC ];
		$this->debug_mode          = $params[ self::PARAM_DEBUG_MODE ];
		$this->log_output          = $params[ self::PARAM_LOG_OUTPUT ];
		$this->marketplace_sku     = $params[ self::PARAM_MARKETPLACE_SKU ];
		$this->delivery_address_id = $params[ self::PARAM_DELIVERY_ADDRESS_ID ];
		$this->order_data          = $params[ self::PARAM_ORDER_DATA ];
		$this->package_data        = $params[ self::PARAM_PACKAGE_DATA ];
		$this->first_package       = $params[ self::PARAM_FIRST_PACKAGE ];
		$this->import_one_order    = $params[ self::PARAM_IMPORT_ONE_ORDER ];
	}

	/**
	 * Create or update order.
	 *
	 * @return array
	 *
	 */
	public function import_order() {
		// load marketplace singleton and marketplace data.
		if ( ! $this->load_marketplace_data() ) {
			return $this->return_result( self::RESULT_IGNORED );
		}
		// get a record in the lengow order table.
		$this->order_lengow_id = Lengow_Order::get_id_from_lengow_orders(
			$this->marketplace_sku,
			$this->marketplace->name

		);
		// checks if an order already has an error in progress.
		if ( $this->order_lengow_id && $this->order_error_already_exist() ) {
			return $this->return_result( self::RESULT_IGNORED );
		}
		// recovery id if the command has already been imported.
		$order_id = Lengow_Order::get_order_id_from_lengow_orders(
			$this->marketplace_sku,
			$this->marketplace->name,
			$this->marketplace->legacy_code
		);
		// update order state if already imported.
		if ( $order_id ) {
			$order_updated = $this->check_and_update_order( $order_id );
			if ( $order_updated ) {
				return $this->return_result( self::RESULT_UPDATED );
			}
			if ( ! $this->is_reimported ) {
				return $this->return_result( self::RESULT_IGNORED );
			}
		}
		// checks if the order is not anonymized or too old.
		if ( ! $this->order_lengow_id && ! $this->can_create_order() ) {
			return $this->return_result( self::RESULT_IGNORED );
		}
		// checks if an external id already exists.
		if ( ! $this->order_lengow_id && $this->external_id_already_exist() ) {
			return $this->return_result( self::RESULT_IGNORED );
		}
		// Checks if the order status is valid for order creation.
		if ( ! $this->order_status_is_valid() ) {
			return $this->return_result( self::RESULT_IGNORED );
		}
		// load data and create a new record in lengow order table if not exist.
		if ( ! $this->create_lengow_order() ) {
			return $this->return_result( self::RESULT_IGNORED );
		}
		// checks if the required order data is present and update Lengow order record.
		if ( ! $this->check_and_update_lengow_order_data() ) {
			return $this->return_result( self::RESULT_FAILED );
		}
		// checks if an order sent by the marketplace must be created or not.
		if ( ! $this->can_create_order_shipped_by_marketplace() ) {
			return $this->return_result( self::RESULT_IGNORED );
		}
		// create WooCommerce order.
		if ( ! $this->create_order() ) {
			return $this->return_result( self::RESULT_FAILED );
		}

		return $this->return_result( self::RESULT_CREATED );
	}

	/**
	 * Load marketplace singleton and marketplace data.
	 *
	 * @return boolean
	 */
	private function load_marketplace_data() {
		try {
			// get marketplace and Lengow order state.
			$this->marketplace                 = Lengow_Main::get_marketplace_singleton(
				(string) $this->order_data->marketplace
			);
			$this->marketplace_label           = $this->marketplace->label_name;
			$this->order_state_marketplace     = (string) $this->order_data->marketplace_status;
			$this->order_state_lengow          = $this->marketplace->get_state_lengow(
				$this->order_state_marketplace
			);
			$this->previous_order_state_lengow = $this->order_state_lengow;

			return true;
		} catch ( Lengow_Exception $e ) {
			$this->errors[] = Lengow_Main::decode_log_message( $e->getMessage(), Lengow_Translation::DEFAULT_ISO_CODE );
			Lengow_Main::log( Lengow_Log::CODE_IMPORT, $e->getMessage(), $this->log_output, $this->marketplace_sku );
		}

		return false;
	}

	/**
	 * Return an array of result for each order.
	 *
	 * @param string $result_type Type of result (created, updated, failed or ignored)
	 *
	 * @return array
	 */
	private function return_result( $result_type ) {
		return array(
			self::MERCHANT_ORDER_ID        => $this->order_id,
			self::MERCHANT_ORDER_REFERENCE => $this->order_reference,
			self::LENGOW_ORDER_ID          => $this->order_lengow_id,
			self::MARKETPLACE_SKU          => $this->marketplace_sku,
			self::MARKETPLACE_NAME         => $this->marketplace ? $this->marketplace->name : null,
			self::DELIVERY_ADDRESS_ID      => $this->delivery_address_id,
			self::SHOP_ID                  => null,
			self::CURRENT_ORDER_STATUS     => $this->order_state_lengow,
			self::PREVIOUS_ORDER_STATUS    => $this->previous_order_state_lengow,
			self::ERRORS                   => $this->errors,
			self::RESULT_TYPE              => $result_type,
		);
	}

	/**
	 * Checks if an order already has an error in progress.
	 *
	 * @return boolean
	 */
	private function order_error_already_exist() {
		// if order error exists and not finished.
		$order_error = Lengow_Order_Error::order_is_in_error(
                    $this->marketplace_sku,
                    $this->marketplace->name
                );
		if ( ! $order_error ) {
			return false;
		}
		// force order synchronization by removing pending errors.
		if ( $this->force_sync ) {
			Lengow_Order_Error::finish_order_errors( $this->order_lengow_id );

			return false;
		}
		$decoded_message = Lengow_Main::decode_log_message(
			$order_error->{Lengow_Order_Error::FIELD_MESSAGE},
			Lengow_Translation::DEFAULT_ISO_CODE
		);
		$message         = Lengow_Main::set_log_message(
			'log.import.error_already_created',
			array(
				'decoded_message' => $decoded_message,
				'date_message'    => get_date_from_gmt( $order_error->{Lengow_Order_Error::FIELD_CREATED_AT} ),
			)
		);
		$this->errors[]  = Lengow_Main::decode_log_message( $message, Lengow_Translation::DEFAULT_ISO_CODE );
		Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $this->marketplace_sku );

		return true;
	}

	/**
	 * Check the order and updates data if necessary.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return boolean
	 */
	private function check_and_update_order( $order_id ) {
		Lengow_Main::log(
			Lengow_Log::CODE_IMPORT,
			Lengow_Main::set_log_message( 'log.import.order_already_imported', array( 'order_id' => $order_id ) ),
			$this->log_output,
			$this->marketplace_sku
		);
                try {
                    $order           = new WC_Order( $order_id );

                } catch (\Exception $e) {
                    return false;
                }

		$order_lengow_id = Lengow_Order::get_id_from_order_id( $order_id );
		$order_lengow    = new Lengow_Order( $order_lengow_id );
		// Lengow -> cancel and reimport order.
		if ( $order_lengow->is_reimported ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.order_ready_to_reimport', array( 'order_id' => $order_id ) ),
				$this->log_output,
				$this->marketplace_sku
			);
			$this->is_reimported = true;

			return false;
		}
		// load data for return.
		$this->order_id                    = (int) $order_id;
		$this->order_reference             = (string) $order_id;
		$this->previous_order_state_lengow = $order_lengow->order_lengow_state;
		$order_updated                     = Lengow_Order::update_state(
			$order,
			$order_lengow,
			$this->order_state_lengow,
			$this->package_data
		);

		if ( $order_updated ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.order_state_updated',
					array( 'state_name' => $order_updated )
				),
				$this->log_output,
				$this->marketplace_sku
			);
			$order_updated = true;
		}
                $vat_number_data = $this->get_vat_number_from_order_data();
                if ($order_lengow->customer_vat_number !== $vat_number_data) {
                    $this->check_and_update_lengow_order_data();
                    $order_updated = true;
                    Lengow_Main::log(
			Lengow_Log::CODE_IMPORT,
			Lengow_Main::set_log_message( 'log.import.lengow_order_updated' ),
			$this->log_output,
			$this->marketplace_sku
                    );
                }
		unset( $order, $order_lengow );
		return $order_updated;
	}

	/**
	 * Checks if the order is not anonymized or too old.
	 *
	 * @return boolean
	 */
	private function can_create_order() {
		if ( $this->import_one_order ) {
			return true;
		}
		// skip import if the order is anonymized.
		if ( $this->order_data->anonymized ) {
			$message        = Lengow_Main::set_log_message( 'log.import.anonymized_order' );
			$this->errors[] = Lengow_Main::decode_log_message( $message, Lengow_Translation::DEFAULT_ISO_CODE );
			Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $this->marketplace_sku );

			return false;
		}
		// skip import if the order is older than 3 months.
		try {
			$date_time_order = new DateTime( $this->order_data->marketplace_order_date );
			$interval        = $date_time_order->diff( new DateTime() );
			$months_interval = $interval->m + ( $interval->y * 12 );
			if ( $months_interval >= Lengow_Import::MONTH_INTERVAL_TIME ) {
				$message        = Lengow_Main::set_log_message( 'log.import.old_order' );
				$this->errors[] = Lengow_Main::decode_log_message( $message, Lengow_Translation::DEFAULT_ISO_CODE );
				Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $this->marketplace_sku );

				return false;
			}
		} catch ( Exception $e ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.unable_verify_date' ),
				$this->log_output,
				$this->marketplace_sku
			);
		}

		return true;
	}

	/**
	 * Checks if an external id already exists.
	 *
	 * @return boolean
	 */
	private function external_id_already_exist() {
		if ( empty( $this->order_data->merchant_order_id ) || $this->debug_mode || $this->is_reimported ) {
			return false;
		}
		foreach ( $this->order_data->merchant_order_id as $external_id ) {
			if ( Lengow_Order::get_id_from_lengow_marketplace_sku( (int) $external_id, $this->marketplace_sku, $this->marketplace->name) ) {
				$message        = Lengow_Main::set_log_message(
					'log.import.external_id_exist',
					array( 'order_id' => $external_id )
				);
				$this->errors[] = Lengow_Main::decode_log_message( $message, Lengow_Translation::DEFAULT_ISO_CODE );
				Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $this->marketplace_sku );

				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the order status is valid for order creation.
	 *
	 * @return boolean
	 */
	private function order_status_is_valid() {
		if ( Lengow_Import::check_state( $this->order_state_marketplace, $this->marketplace ) ) {
			return true;
		}
		$order_process_state = Lengow_Order::get_order_process_state( $this->order_state_lengow );
		// check and complete an order not imported if it is canceled or refunded.
		if ( $this->order_lengow_id && Lengow_Order::PROCESS_STATE_FINISH === $order_process_state ) {
			Lengow_Order_Error::finish_order_errors( $this->order_lengow_id );
			Lengow_Order::update(
				$this->order_lengow_id,
				array(
					Lengow_Order::FIELD_ORDER_LENGOW_STATE  => $this->order_state_lengow,
					Lengow_Order::FIELD_ORDER_PROCESS_STATE => $order_process_state,
				)
			);
		}
		$message        = Lengow_Main::set_log_message(
			'log.import.current_order_state_unavailable',
			array(
				'order_state_marketplace' => $this->order_state_marketplace,
				'marketplace_name'        => $this->marketplace->name,
			)
		);
		$this->errors[] = Lengow_Main::decode_log_message( $message, Lengow_Translation::DEFAULT_ISO_CODE );
		Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $this->marketplace_sku );

		return false;
	}

	/**
	 * Create an order in lengow orders table.
	 *
	 * @return boolean
	 */
	private function create_lengow_order() {
		// load order comment from marketplace.
		$this->load_order_comment();
		// load order types data.
		$this->load_order_types_data();
		// load order date.
		$this->load_order_date();
		// if the Lengow order already exists do not recreate it.
		if ( $this->order_lengow_id ) {
			return true;
		}
		$data   = array(
			Lengow_Order::FIELD_MARKETPLACE_SKU     => $this->marketplace_sku,
			Lengow_Order::FIELD_MARKETPLACE_NAME    => $this->marketplace->name,
			Lengow_Order::FIELD_MARKETPLACE_LABEL   => $this->marketplace_label,
			Lengow_Order::FIELD_DELIVERY_ADDRESS_ID => $this->delivery_address_id,
			Lengow_Order::FIELD_ORDER_DATE          => $this->order_date,
			Lengow_Order::FIELD_ORDER_LENGOW_STATE  => $this->order_state_lengow,
			Lengow_Order::FIELD_ORDER_TYPES         => json_encode( $this->order_types ),
			Lengow_Order::FIELD_CUSTOMER_VAT_NUMBER => $this->get_vat_number_from_order_data(),
			Lengow_Order::FIELD_MESSAGE             => $this->message,
			Lengow_Order::FIELD_EXTRA               => json_encode( $this->order_data ),
			Lengow_Order::FIELD_IS_IN_ERROR         => 1,
		);
		$result = Lengow_Order::create( $data );
		if ( $result ) {
			$this->order_lengow_id = Lengow_Order::get_id_from_lengow_orders(
				$this->marketplace_sku,
				$this->marketplace->name,
				$this->delivery_address_id
			);
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.lengow_order_saved' ),
				$this->log_output,
				$this->marketplace_sku
			);

			return true;
		}
		$message        = Lengow_Main::set_log_message( 'log.import.lengow_order_not_saved' );
		$this->errors[] = Lengow_Main::decode_log_message( $message, Lengow_Translation::DEFAULT_ISO_CODE );
		Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $this->marketplace_sku );

		return false;
	}

	/**
	 * Get order comment from marketplace.
	 */
	private function load_order_comment() {
		if ( isset( $this->order_data->comments ) && is_array( $this->order_data->comments ) ) {
			$order_comment = implode( ',', $this->order_data->comments );
		} else {
			$order_comment = (string) $this->order_data->comments;
		}

		$this->message = $order_comment;
	}

	/**
	 * Get order types data and update Lengow order record.
	 */
	private function load_order_types_data() {
		$order_types = array();
		if ( null !== $this->order_data->order_types && ! empty( $this->order_data->order_types ) ) {
			foreach ( $this->order_data->order_types as $order_type ) {
				$order_types[ $order_type->type ] = $order_type->label;
				if ( Lengow_Order::TYPE_DELIVERED_BY_MARKETPLACE === $order_type->type ) {
					$this->sent_marketplace = true;
				}
			}
		}
		$this->order_types = $order_types;
	}

	/**
	 * Load order date for order creation.
	 */
	private function load_order_date() {
		$order_date       = (string) ( $this->order_data->marketplace_order_date ?: $this->order_data->imported_at );
		$this->order_date = date( Lengow_Main::DATE_FULL, strtotime( $order_date ) );
	}

	/**
	 * Get vat_number from lengow order data.
	 *
	 * @return string|null
	 */
	private function get_vat_number_from_order_data() {
		if ( isset( $this->order_data->billing_address->vat_number ) ) {
			return $this->order_data->billing_address->vat_number;
		}
		if ( isset( $this->package_data->delivery->vat_number ) ) {
			return $this->package_data->delivery->vat_number;
		}

		return null;
	}

	/**
	 * Checks if the required order data is present and update Lengow order record.
	 *
	 * @return boolean
	 */
	private function check_and_update_lengow_order_data() {
		// checks if the required order data is present.
		if ( ! $this->check_order_data() ) {
			return false;
		}
		// get order amount and load processing fees and shipping cost.
		$this->load_order_amount();
		// load tracking data.
		$this->load_tracking_data();
		// update Lengow order with new data.
		Lengow_Order::update(
			$this->order_lengow_id,
			array(
				Lengow_Order::FIELD_CURRENCY             => (string) $this->order_data->currency->iso_a3,
				Lengow_Order::FIELD_TOTAL_PAID           => $this->total_paid,
				Lengow_Order::FIELD_ORDER_ITEM           => $this->order_item,
				Lengow_Order::FIELD_CUSTOMER_NAME        => $this->get_customer_name(),
				Lengow_Order::FIELD_CUSTOMER_EMAIL       => $this->get_customer_email(),
                                Lengow_Order::FIELD_CUSTOMER_VAT_NUMBER  => $this->get_vat_number_from_order_data(),
				Lengow_Order::FIELD_CARRIER              => $this->carrier,
				Lengow_Order::FIELD_CARRIER_METHOD       => $this->carrier_method,
				Lengow_Order::FIELD_CARRIER_TRACKING     => $this->carrier_tracking,
				Lengow_Order::FIELD_CARRIER_RELAY_ID     => $this->carrier_id_relay,
				Lengow_Order::FIELD_SENT_MARKETPLACE     => (int) $this->sent_marketplace,
				Lengow_Order::FIELD_DELIVERY_COUNTRY_ISO =>
					(string) $this->package_data->delivery->common_country_iso_a2,
				Lengow_Order::FIELD_ORDER_LENGOW_STATE   => $this->order_state_lengow,
				Lengow_Order::FIELD_EXTRA                => json_encode( $this->order_data ),
			)
		);

		return true;
	}

	/**
	 * Checks if order data are present.
	 *
	 * @return boolean
	 */
	private function check_order_data() {
		$error_messages = array();
		if ( empty( $this->package_data->cart ) ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_product' );
		}
		if ( ! isset( $this->order_data->currency->iso_a3 ) ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_currency' );
		}
		if ( - 1 == $this->order_data->total_order ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_change_rate' );
		}
		if ( null === $this->order_data->billing_address ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_billing_address' );
		} elseif ( null === $this->order_data->billing_address->common_country_iso_a2 ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_country_for_billing_address' );
		}
		if ( null === $this->package_data->delivery->common_country_iso_a2 ) {
			$error_messages[] = Lengow_Main::set_log_message( 'lengow_log.error.no_country_for_delivery_address' );
		}
		if ( empty( $error_messages ) ) {
			return true;
		}
		foreach ( $error_messages as $error_message ) {
			Lengow_Order_Error::create(
				array(
					Lengow_Order_Error::FIELD_ORDER_LENGOW_ID => $this->order_lengow_id,
					Lengow_Order_Error::FIELD_MESSAGE         => $error_message,
				)
			);
			$decoded_message = Lengow_Main::decode_log_message(
				$error_message,
				Lengow_Translation::DEFAULT_ISO_CODE
			);
			$this->errors[]  = $decoded_message;
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.order_import_failed',
					array( 'decoded_message' => $decoded_message )
				),
				$this->log_output,
				$this->marketplace_sku
			);
		}

		return false;
	}

	/**
	 * Load order amount, processing fees and shipping costs.
	 */
	private function load_order_amount() {
		$this->processing_fee = (float) $this->order_data->processing_fee;
		$this->shipping_cost  = (float) $this->order_data->shipping;
		// rewrite processing fees and shipping cost.
		if ( ! $this->first_package ) {
			$this->processing_fee = 0;
			$this->shipping_cost  = 0;
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.rewrite_processing_fee' ),
				$this->log_output,
				$this->marketplace_sku
			);
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.rewrite_shipping_cost' ),
				$this->log_output,
				$this->marketplace_sku
			);
		}
		// get total amount and the number of items.
		$nb_items     = 0;
		$total_amount = 0;
		foreach ( $this->package_data->cart as $product ) {
			// check whether the product is canceled for amount.
			if ( null !== $product->marketplace_status ) {
				$product_state = $this->marketplace->get_state_lengow( (string) $product->marketplace_status );
				if ( in_array(
					$product_state,
					array( Lengow_Order::STATE_CANCELED, Lengow_Order::STATE_REFUSED ),
					true
				) ) {
					continue;
				}
			}
			$nb_items     += (int) $product->quantity;
			$total_amount += (float) $product->amount;
		}
		$this->order_item = $nb_items;
		$this->total_paid = $total_amount + $this->processing_fee + $this->shipping_cost;
	}

	/**
	 * Load tracking data for order creation.
	 */
	private function load_tracking_data() {
		$tracks = $this->package_data->delivery->trackings;
		if ( ! empty( $tracks ) ) {
			$tracking               = $tracks[0];
			$this->carrier          = $tracking->carrier;
			$this->carrier_method   = $tracking->method;
			$this->carrier_tracking = $tracking->number;
			$this->carrier_id_relay = $tracking->relay->id;
		}
	}

	/**
	 * Get customer name.
	 *
	 * @return string
	 */
	private function get_customer_name() {
		$firstname = (string) $this->order_data->billing_address->first_name;
		$lastname  = (string) $this->order_data->billing_address->last_name;
		$firstname = ucfirst( strtolower( $firstname ) );
		$lastname  = ucfirst( strtolower( $lastname ) );
		if ( empty( $firstname ) && empty( $lastname ) ) {
			return (string) $this->order_data->billing_address->full_name;
		}
		if ( empty( $firstname ) ) {
			return $lastname;
		}
		if ( empty( $lastname ) ) {
			return $firstname;
		}

		return $firstname . ' ' . $lastname;
	}

	/**
	 * Get customer email.
	 *
	 * @return string
	 */
	private function get_customer_email() {
		return $this->order_data->billing_address->email !== null
			? (string) $this->order_data->billing_address->email
			: (string) $this->package_data->delivery->email;
	}

	/**
	 * Checks if an order sent by the marketplace must be created or not.
	 *
	 * @return boolean
	 */
	private function can_create_order_shipped_by_marketplace() {
		// check if the order is shipped by marketplace.
		if ( $this->sent_marketplace ) {
			$message = Lengow_Main::set_log_message(
				'log.import.order_shipped_by_marketplace',
				array( 'marketplace_name' => $this->marketplace->name )
			);
			Lengow_Main::log( Lengow_Log::CODE_IMPORT, $message, $this->log_output, $this->marketplace_sku );
			if ( ! Lengow_Configuration::get( Lengow_Configuration::SHIPPED_BY_MARKETPLACE_ENABLED ) ) {
				$this->errors[] = Lengow_Main::decode_log_message( $message, Lengow_Translation::DEFAULT_ISO_CODE );
				Lengow_Order::update(
					$this->order_lengow_id,
					array(
						Lengow_Order::FIELD_ORDER_PROCESS_STATE => Lengow_Order::PROCESS_STATE_FINISH,
						Lengow_Order::FIELD_IS_IN_ERROR         => 0,
						Lengow_Order::FIELD_IS_REIMPORTED       => 0,
					)
				);

				return false;
			}
		}

		return true;
	}

	/**
	 * Create a WooCommerce order.
	 *
	 * @return boolean
	 */
	private function create_order() {
		try {
			// search and get all products.
			$products = $this->get_products();
			// get billing and shipping addresses for the user and the order.
			$billing_address  = new Lengow_Address(
				$this->order_data->billing_address,
				Lengow_Address::TYPE_BILLING
			);
			$shipping_address = new Lengow_Address(
				$this->package_data->delivery,
				Lengow_Address::TYPE_SHIPPING,
				$this->carrier_id_relay
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
			$user_email = $this->get_user_email();
			// get or create a WordPress user.
			$user = get_user_by( 'email', $user_email );
			if ( ! $user ) {
				$user = $this->create_user( $user_email, $billing_address, $shipping_address );
			}
			// if the order is B2B, activate switch_product_tax_class_for_b2b hook.
			$this->enable_b2b_hook();
			// create a WooCommerce order with all necessary data.
			$order = $this->create_woocommerce_order( $user, $products, $billing_address, $shipping_address );
			// remove hook after creating the order to avoid any change to other order.
			$this->disable_b2b_hook();
			// load order data for return
			$order_id              = $order->get_id();
			$this->order_id        = $order_id;
			$this->order_reference = (string) $order_id;
			// save order line id in lengow_order_line table.
			$this->save_lengow_order_lines( $order, $products );
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.order_successfully_imported',
					array( 'order_id' => $order_id )
				),
				$this->log_output,
				$this->marketplace_sku
			);
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce error]: "' . $e->getMessage()
			                 . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
		}
		if ( ! isset( $error_message ) ) {
			return true;
		}
		Lengow_Order::add_order_error( $this->order_lengow_id, $error_message );
		$decoded_message = Lengow_Main::decode_log_message( $error_message, Lengow_Translation::DEFAULT_ISO_CODE );
		$this->errors[]  = $decoded_message;
		Lengow_Main::log(
			Lengow_Log::CODE_IMPORT,
			Lengow_Main::set_log_message(
				'log.import.order_import_failed',
				array( 'decoded_message' => $decoded_message )
			),
			$this->log_output,
			$this->marketplace_sku
		);
		Lengow_Order::update(
			$this->order_lengow_id,
			array(
				Lengow_Order::FIELD_ORDER_PROCESS_STATE => $this->order_state_lengow,
				Lengow_Order::FIELD_IS_REIMPORTED       => 0,
			)
		);

		return false;
	}

	/**
	 * Get products from the API and check that they exist in WooCommerce database.
	 *
	 * @return array
	 * @throws Lengow_Exception
	 *
	 */
	private function get_products() {
		$products = array();
		foreach ( $this->package_data->cart as $api_product ) {
			$found          = false;
			$order_line_id  = (string) $api_product->marketplace_order_line_id;
			$product_data   = Lengow_Product::extract_product_data_from_api( $api_product );

			$api_product_id = null !== $product_data['merchant_product_id']->id
				? (string) $product_data['merchant_product_id']->id
				: (string) $product_data['marketplace_product_id'];

			if ( null !== $product_data['marketplace_status'] ) {
				$product_state = $this->marketplace->get_state_lengow( (string) $product_data['marketplace_status'] );
				if ( in_array(
					$product_state,
					array( Lengow_Order::STATE_CANCELED, Lengow_Order::STATE_REFUSED ),
					true
				) ) {
					Lengow_Main::log(
						Lengow_Log::CODE_IMPORT,
						Lengow_Main::set_log_message(
							'log.import.product_state_canceled',
							array(
								'product_id'    => $api_product_id,
								'product_state' => $product_state,
							)
						),
						$this->log_output,
						$this->marketplace_sku
					);
					continue;
				}
			}
			$product = Lengow_Product::match_product( $product_data, $this->marketplace_sku, $this->log_output );
			if ( $product ) {
				$product_id = $product->get_id();
				if ( array_key_exists( $product_id, $products ) ) {
					$products[ $product_id ]['quantity']         += (integer) $product_data['quantity'];
					$products[ $product_id ]['amount']           += (float) $product_data['amount'];
					$products[ $product_id ]['order_line_ids'][] = $order_line_id;
				} else {
					$products[ $product_id ] = array(
						'woocommerce_product' => $product,
						'name'                => $product->get_name(),
						'amount'              => (float) $product_data['amount'],
						'price_unit'          => $product_data['price_unit'],
						'quantity'            => (int) $product_data['quantity'],
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
		if ( empty( $products ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'lengow_log.exception.product_list_is_empty' )
			);
		}

		return $products;
	}

	/**
	 * Get fictitious email for user creation.
	 *
	 * @return string
	 */
	private function get_user_email() {
		$domain = implode( '.', array_slice( explode( '.', parse_url( get_site_url(), PHP_URL_HOST ) ), - 2 ) );
		$domain = preg_match( '`^([\w]+)\.([a-z]+)$`', $domain ) ? $domain : 'lengow.com';
		$email  = md5($this->marketplace_sku . '-' . $this->marketplace->name). '@' . $domain;
		Lengow_Main::log(
			Lengow_Log::CODE_IMPORT,
			Lengow_Main::set_log_message( 'log.import.generate_unique_email', array( 'email' => $email ) ),
			$this->log_output,
			$this->marketplace_sku
		);

		return $email;
	}

	/**
	 * Create WordPress user with billing and shipping addresses.
	 *
	 * @param string $user_email fictitious email for user
	 * @param Lengow_Address $billing_address Lengow billing address
	 * @param Lengow_Address $shipping_address Lengow shipping address
	 *
	 * @return WP_User|false
	 * @throws Lengow_Exception
	 *
	 */
	private function create_user( $user_email, $billing_address, $shipping_address ) {
		// create WordPress user.
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
	 * Activate switch_product_tax_class_for_b2b hook
	 */
	private function enable_b2b_hook() {
		// if the order is B2B, activate switch_product_tax_class_for_b2b hook
		if ( isset( $this->order_types[ Lengow_Order::TYPE_BUSINESS ] )
		     && Lengow_Configuration::get( Lengow_Configuration::B2B_WITHOUT_TAX_ENABLED )
		) {
			// add hook on tax calculation for b2b order
			add_filter(
				'woocommerce_product_get_tax_class',
				array( 'Lengow_Hook', 'switch_product_tax_class_for_b2b' ),
				100,
				2
			);
			add_filter(
				'woocommerce_product_variation_get_tax_class',
				array( 'Lengow_Hook', 'switch_product_tax_class_for_b2b' ),
				100,
				2
			);
		}
	}

	/**
	 * Disable switch_product_tax_class_for_b2b hook
	 */
	private function disable_b2b_hook() {
		// remove hook after creating the order to avoid any change to other order
		if ( isset( $this->order_types[ Lengow_Order::TYPE_BUSINESS ] )
		     && Lengow_Configuration::get( Lengow_Configuration::B2B_WITHOUT_TAX_ENABLED )
		) {
			remove_filter(
				'woocommerce_product_get_tax_class',
				array( 'Lengow_Hook', 'switch_product_tax_class_for_b2b' ),
				100
			);
			remove_filter(
				'woocommerce_product_variation_get_tax_class',
				array( 'Lengow_Hook', 'switch_product_tax_class_for_b2b' ),
				100
			);
		}
	}

	/**
	 * Create a WooCommerce order with all necessary data.
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
	private function create_woocommerce_order( $user, $products, $billing_address, $shipping_address ) {
		// create a generic order.
		$wc_order = $this->create_generic_woocommerce_order();
                $order_id = $wc_order->get_id();
		// get billing data formatted for WooCommerce address.
		$billing_data  = $billing_address->get_formatted_data();
		$shipping_data = $shipping_address->get_formatted_data();
		// adds shipping and billing addresses to the order.

                $billing = [];
		foreach ( $billing_data as $key => $field ) {
                    $billingKey = str_replace('billing_', '', $key);
                    $billing[$billingKey] = $field;
		}

                $shipping = [];
		foreach ( $shipping_data as $key => $field ) {
                    $shippingKey = str_replace('shipping_', '', $key);
                    $shipping[$shippingKey] = $field;
		}
                $wc_order->set_address($billing, 'billing');
                $wc_order->set_address($shipping, 'shipping');

                $wc_order->set_customer_id(absint( $user->ID ));
                $wc_order->save();

		// load WooCommerce customer.
		$customer = new WC_Customer( $user->ID );
		// add products, shipping cost, tax and processing fees to the order.
		$tax_amount = 0;
		foreach ( $products as $product_data ) {
			$tax_amount += $this->add_product( $order_id, $customer, $product_data );
		}
		// add shipping cost to the WooCommerce order.
		$shipping_cost = $this->add_shipping_cost( $order_id, $customer, $products );
		// add tax to the WooCommerce order.
		$this->add_tax( $order_id, $customer, $tax_amount, $shipping_cost['tax_amount'] );
		// add processing fee to the WooCommerce order.
		$this->add_processing_fee( $order_id );
		// add post meta to the WooCommerce order.
		$this->add_post_meta( $order_id, $tax_amount, $shipping_cost );
		// load WooCommerce order.


		// change order state.
		$order_state = Lengow_Order::get_woocommerce_state(
			$this->order_state_marketplace,
			$this->marketplace,
			$this->sent_marketplace
		);
		$wc_order->update_status( $order_state );
		// add quantity back for re-import order and order shipped by marketplace.
		$this->add_quantity_back( $order );
                $wc_order->save();

		return $wc_order;
	}

	/**
	 * Create a generic WooCommerce order.
	 *
	 * @return WC_Order
	 * @throws Exception|Lengow_Exception
	 */
	private function create_generic_woocommerce_order() {

                $wc_order =  wc_create_order();
                if ( is_wp_error($wc_order->get_id()) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'lengow_log.exception.woocommerce_order_not_saved' )
			);
		}


		do_action( 'woocommerce_new_order', $wc_order->get_id(), $wc_order);
		// update lengow_orders table directly after creating the WooCommerce order.
		$success = Lengow_Order::update(
			$this->order_lengow_id,
			array(
				Lengow_Order::FIELD_ORDER_ID            => $wc_order->get_id(),
				Lengow_Order::FIELD_ORDER_PROCESS_STATE => Lengow_Order::get_order_process_state(
					$this->order_state_lengow
				),
				Lengow_Order::FIELD_ORDER_LENGOW_STATE  => $this->order_state_lengow,
				Lengow_Order::FIELD_IS_IN_ERROR         => 0,
				Lengow_Order::FIELD_IS_REIMPORTED       => 0,
			)
		);
		if ( ! $success ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.lengow_order_not_updated' ),
				$this->log_output,
				$this->marketplace_sku
			);
		} else {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.lengow_order_updated' ),
				$this->log_output,
				$this->marketplace_sku
			);
		}

		return $wc_order;
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
	private function add_product( $order_id, $customer, $product_data ) {
		$line_tax = 0;
		// get product and product data.
		$product    = $product_data['woocommerce_product'];
		$price_unit = $product_data['price_unit'];
		$quantity   = $product_data['quantity'];
		try {
			// add line item.
			$new_product_data = array( 'order_item_name' => $product_data['name'], 'order_item_type' => 'line_item' );
			$item_id          = wc_add_order_item( $order_id, $new_product_data );
			// calculated tax per line.
			$tax_rates         = WC_Tax::get_rates( $product->get_tax_class(), $customer );
			$taxes             = WC_Tax::calc_tax( $price_unit, $tax_rates, true );
			$tax_id            = ! empty( $taxes ) ? (int) key( $taxes ) : false;
			$product_tax       = $tax_id ? $taxes[ $tax_id ] : 0;
			$line_subtotal     = wc_format_decimal( $price_unit - $product_tax, 8 );
			$line_total        = wc_format_decimal( ( $price_unit - $product_tax ) * $quantity, 8 );
			$line_subtotal_tax = wc_format_decimal( $product_tax, 8 );
			$line_tax          = wc_format_decimal( $product_tax * $quantity, 8 );
			$line_tax_data     = array(
				'total'    => array( $tax_id => $line_tax ),
				'subtotal' => array( $tax_id => $line_subtotal_tax ),
			);
			// add line item meta.
			wc_add_order_item_meta( $item_id, '_product_id', Lengow_Product::get_product_id( $product ) );
			wc_add_order_item_meta( $item_id, '_variation_id', Lengow_Product::get_variation_id( $product ) );
			wc_add_order_item_meta( $item_id, '_qty', apply_filters( 'woocommerce_stock_amount', $quantity ) );
			wc_add_order_item_meta( $item_id, '_tax_class', $product->get_tax_class() );
			wc_add_order_item_meta( $item_id, '_line_subtotal', $line_subtotal );
			wc_add_order_item_meta( $item_id, '_line_subtotal_tax', $line_subtotal_tax );
			wc_add_order_item_meta( $item_id, '_line_total', $line_total );
			wc_add_order_item_meta( $item_id, '_line_tax', $line_tax );
			wc_add_order_item_meta( $item_id, '_line_tax_data', $line_tax_data );
		} catch ( Exception $e ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.product_not_saved',
					array( 'product_id' => $product->get_id(), 'error_message' => $e->getMessage() )
				),
				$this->log_output,
				$this->marketplace_sku
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
	private function add_shipping_cost( $order_id, $customer, $products ) {
		$no_tax = false;
		if ( isset( $this->order_types[ Lengow_Order::TYPE_BUSINESS ] )
		     && Lengow_Configuration::get( Lengow_Configuration::B2B_WITHOUT_TAX_ENABLED )
		) {
			// if order is B2B, add shipping cost without tax.
			$no_tax = true;
		}
		// set shipping cost tax.
		$shipping   = $this->shipping_cost;
		$tax_rates  = WC_Tax::get_shipping_tax_rates( '', $customer );
		$taxes      = WC_Tax::calc_tax( $shipping, $tax_rates, true );
		$tax_id     = ! empty( $taxes ) ? (int) key( $taxes ) : false;
		$tax_amount = ( ! $no_tax && $tax_id ) ? $taxes[ $tax_id ] : 0;
		$amount     = $shipping - $tax_amount;
		// get default shipping method.
		$wc_shipping             = new WC_Shipping();
		$shipping_methods        = $wc_shipping->load_shipping_methods();
		$default_shipping_method = Lengow_Configuration::get( Lengow_Configuration::DEFAULT_IMPORT_CARRIER_ID );
		$shipping_method         = array_key_exists( $default_shipping_method, $shipping_methods )
			? $shipping_methods[ $default_shipping_method ]
			: current( $shipping_methods );
		$shipping_method_title   = $shipping_method->get_method_title();
                $wc_order = new WC_Order($order_id);
		try {
			$new_shipping_data = array( 'order_item_name' => $shipping_method_title, 'order_item_type' => 'shipping' );
			//$item_id           = wc_add_order_item( $order_id, $new_shipping_data );
			// add line item meta for shipping.
			$articles = array();
			foreach ( $products as $product ) {
				$articles[] = $product['name'] . ' &times; ' . $product['quantity'];
			}

                        $wc_shipping_item = new WC_Order_Item_Shipping();
                        $wc_shipping_item->set_method_id($shipping_method->id);
                        $wc_shipping_item->set_method_title($shipping_method_title);
                        $wc_shipping_item->set_taxes(array('total' => array( $tax_id => $tax_amount ) ));
                        $wc_shipping_item->set_total($amount);
                        $wc_shipping_item->set_instance_id($shipping_method->instance_id);
                        $wc_shipping_item->add_meta_data('Articles', implode( ', ', $articles ));
                        $wc_shipping_item->add_meta_data('cost', $amount);
                        $wc_shipping_item->add_meta_data('total_tax', $tax_amount);
                        $wc_order->add_item($wc_shipping_item);
                        $wc_order->save();


		} catch ( Exception $e ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.shipping_not_saved',
					array( 'error_message' => $e->getMessage() )
				),
				$this->log_output,
				$this->marketplace_sku
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
	 * Add tax to the WooCommerce order.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param WC_Customer $customer WooCommerce customer instance
	 * @param float $tax_amount order tax amount without shipping
	 * @param float $shipping_tax_amount shipping tax amount
	 */
	private function add_tax( $order_id, $customer, $tax_amount, $shipping_tax_amount ) {
		$tax_rates = WC_Tax::get_rates( '', $customer );
                $wc_order = new WC_Order($order_id);
		if ( ! empty( $tax_rates ) ) {
			$tax_id = key( $tax_rates );
			$tax    = $tax_rates[ $tax_id ];
			try {
				$new_tax_data = array(
					'order_item_name' => WC_Tax::get_rate_code( $tax_id ),
					'order_item_type' => 'tax',
				);
				$item_id      = wc_add_order_item( $order_id, $new_tax_data );

                                $wc_order->add_tax($tax_id, $tax_amount);
                                $wc_order->save();
				// add line item meta for tax.
				wc_add_order_item_meta( $item_id, 'rate_id', $tax_id );
				wc_add_order_item_meta( $item_id, 'label', $tax['label'] );
				wc_add_order_item_meta( $item_id, 'compound', $tax['compound'] === 'yes' ? 1 : 0 );
				wc_add_order_item_meta( $item_id, 'tax_amount', $tax_amount );
				wc_add_order_item_meta( $item_id, 'shipping_tax_amount', $shipping_tax_amount );
				wc_add_order_item_meta( $item_id, 'rate_percent', $tax['rate'] );
			} catch ( Exception $e ) {
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message(
						'log.import.tax_not_saved',
						array( 'error_message' => $e->getMessage() )
					),
					$this->log_output,
					$this->marketplace_sku
				);
			}
		}
	}

	/**
	 * Add processing fee to the WooCommerce order.
	 *
	 * @param integer $order_id WooCommerce order id
	 */
	private function add_processing_fee( $order_id ) {
		if ( $this->processing_fee > 0 ) {
			try {
				$locale                  = new Lengow_Translation();
				$new_processing_fee_data = array(
					'order_item_name' => $locale->t( 'module.processing_fee' ),
					'order_item_type' => 'fee',
				);
				$item_id                 = wc_add_order_item( $order_id, $new_processing_fee_data );
				// add line item meta for processing fee.
				wc_add_order_item_meta( $item_id, '_tax_class', '0' );
				wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $this->processing_fee ) );
				wc_add_order_item_meta( $item_id, '_line_tax', '0' );
			} catch ( Exception $e ) {
				Lengow_Main::log(
					Lengow_Log::CODE_IMPORT,
					Lengow_Main::set_log_message(
						'log.import.processing_fee_not_saved',
						array( 'error_message' => $e->getMessage() )
					),
					$this->log_output,
					$this->marketplace_sku
				);
			}
		}
	}

	/**
	 * Add post meta to the WooCommerce order.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param float $tax_amount order tax amount without shipping
	 * @param array $shipping_cost shipping cost data
	 */
	private function add_post_meta( $order_id, $tax_amount, $shipping_cost ) {
		// add post meta.
		$order_shipping      = wc_format_decimal( $shipping_cost['amount'] );
		$order_tax           = wc_format_decimal( $tax_amount );
		$order_shipping_tax  = wc_format_decimal( $shipping_cost['tax_amount'] );
		$order_total         = wc_format_decimal( $this->total_paid );

		$order_currency      = (string) $this->order_data->currency->iso_a3;
		$customer_ip_address = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] )
			? $_SERVER['HTTP_X_FORWARDED_FOR']
			: $_SERVER['REMOTE_ADDR'];
		$customer_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$prices_include_tax  = get_option( 'woocommerce_prices_include_tax' );

                $wc_order = new WC_Order($order_id);
                $wc_order->set_currency($order_currency);
                $wc_order->set_total($order_total);
                $wc_order->set_cart_tax($order_tax);




                $wc_order->set_shipping_total($shipping_cost['amount']);
                $wc_order->set_shipping_tax($order_shipping_tax);
                $wc_order->set_payment_method(
                    WC_Lengow_Payment_Gateway::PAYMENT_LENGOW_ID
                );
                $wc_order->set_payment_method_title($this->marketplace->label_name);
                $wc_order->set_date_paid(strtotime( $this->order_date ));
                $wc_order->set_prices_include_tax($prices_include_tax);
                $wc_order->set_customer_ip_address($customer_ip_address);
                $wc_order->set_customer_user_agent($customer_user_agent);
                $wc_order->add_meta_data('_order_shipping', $order_shipping);
                $wc_order->add_meta_data('_order_shipping_tax', $order_shipping_tax);
                $wc_order->add_meta_data('_paid_date', $this->order_date);
                $wc_order->save();

	}

	/**
	 * Add quantity back to stock.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 */
	private function add_quantity_back( $order ) {
		// don't reduce stock for re-import order and order shipped by marketplace.
		if ( $this->is_reimported
		     || ( $this->sent_marketplace && ! (bool) Lengow_Configuration::get(
					Lengow_Configuration::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED
				) )
		) {
			$log_message = $this->is_reimported
				? Lengow_Main::set_log_message( 'log.import.quantity_back_reimported_order' )
				: Lengow_Main::set_log_message( 'log.import.quantity_back_shipped_by_marketplace' );
			Lengow_Main::log( Lengow_Log::CODE_IMPORT, $log_message, $this->log_output, $this->marketplace_sku );
			wc_increase_stock_levels( $order->get_id() );
		}
	}

	/**
	 * Save order line in lengow orders line table.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 * @param array $products order products
	 */
	private function save_lengow_order_lines( $order, $products ) {
		$order_line_saved = false;
		foreach ( $products as $product_id => $product_data ) {
			foreach ( $product_data['order_line_ids'] as $order_line_id ) {
				$result = Lengow_Order_Line::create(
					array(
						Lengow_Order_Line::FIELD_ORDER_ID      => $order->get_id(),
						Lengow_Order_Line::FIELD_ORDER_LINE_ID => $order_line_id,
						Lengow_Order_Line::FIELD_PRODUCT_ID    => $product_id,
					)
				);
				if ( $result ) {
					$order_line_saved .= ! $order_line_saved ? $order_line_id : ' / ' . $order_line_id;
				}
			}
		}
		if ( $order_line_saved ) {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message(
					'log.import.lengow_order_line_saved',
					array( 'order_line_saved' => $order_line_saved )
				),
				$this->log_output,
				$this->marketplace_sku
			);
		} else {
			Lengow_Main::log(
				Lengow_Log::CODE_IMPORT,
				Lengow_Main::set_log_message( 'log.import.lengow_order_line_not_saved' ),
				$this->log_output,
				$this->marketplace_sku
			);
		}
	}
}
