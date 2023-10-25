<?php
/**
 * All function to synchronised orders
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
 * Lengow_Order Class.
 */
class Lengow_Order {

	/**
	 * @var string Lengow order table name
	 */
	const TABLE_ORDER = 'lengow_orders';

	/* Order fields */
	const FIELD_ID = 'id';
	const FIELD_ORDER_ID = 'order_id';
	const FIELD_FEED_ID = 'feed_id';
	const FIELD_DELIVERY_ADDRESS_ID = 'delivery_address_id';
	const FIELD_DELIVERY_COUNTRY_ISO = 'delivery_country_iso';
	const FIELD_MARKETPLACE_SKU = 'marketplace_sku';
	const FIELD_MARKETPLACE_NAME = 'marketplace_name';
	const FIELD_MARKETPLACE_LABEL = 'marketplace_label';
	const FIELD_ORDER_LENGOW_STATE = 'order_lengow_state';
	const FIELD_ORDER_PROCESS_STATE = 'order_process_state';
	const FIELD_ORDER_DATE = 'order_date';
	const FIELD_ORDER_ITEM = 'order_item';
	const FIELD_ORDER_TYPES = 'order_types';
	const FIELD_CURRENCY = 'currency';
	const FIELD_TOTAL_PAID = 'total_paid';
	const FIELD_COMMISSION = 'commission';
	const FIELD_CUSTOMER_NAME = 'customer_name';
	const FIELD_CUSTOMER_EMAIL = 'customer_email';
	const FIELD_CUSTOMER_VAT_NUMBER = 'customer_vat_number';
	const FIELD_CARRIER = 'carrier';
	const FIELD_CARRIER_METHOD = 'carrier_method';
	const FIELD_CARRIER_TRACKING = 'carrier_tracking';
	const FIELD_CARRIER_RELAY_ID = 'carrier_id_relay';
	const FIELD_SENT_MARKETPLACE = 'sent_marketplace';
	const FIELD_IS_IN_ERROR = 'is_in_error';
	const FIELD_IS_REIMPORTED = 'is_reimported';
	const FIELD_MESSAGE = 'message';
	const FIELD_CREATED_AT = 'created_at';
	const FIELD_UPDATED_AT = 'updated_at';
	const FIELD_EXTRA = 'extra';

	/* Order process states */
	const PROCESS_STATE_NEW = 0;
	const PROCESS_STATE_IMPORT = 1;
	const PROCESS_STATE_FINISH = 2;

	/* Order states */
	const STATE_ACCEPTED = 'accepted';
	const STATE_WAITING_SHIPMENT = 'waiting_shipment';
	const STATE_SHIPPED = 'shipped';
	const STATE_CLOSED = 'closed';
	const STATE_REFUSED = 'refused';
	const STATE_CANCELED = 'canceled';
	const STATE_REFUNDED = 'refunded';
        const STATE_WC_COMPLETED = 'wc-completed';
        const STATE_WC_PROCESSING = 'wc-processing';
        const STATE_WC_CANCELED = 'wc-cancelled';

	/* Order types */
	const TYPE_PRIME = 'is_prime';
	const TYPE_EXPRESS = 'is_express';
	const TYPE_BUSINESS = 'is_business';
	const TYPE_DELIVERED_BY_MARKETPLACE = 'is_delivered_by_marketplace';

	/**
	 * @var string label fulfillment for old orders without order type.
	 */
	const LABEL_FULFILLMENT = 'Fulfillment';

	/**
	 * @var integer Lengow order record id.
	 */
	public $id;

	/**
	 * @var integer WooCommerce order record id.
	 */
	public $order_id;

	/**
	 * @var integer Lengow feed id.
	 */
	public $feed_id;

	/**
	 * @var integer id of the delivery address.
	 */
	public $delivery_address_id;

	/**
	 * @var string ISO code for country.
	 */
	public $delivery_country_iso;

	/**
	 * @var string Lengow marketplace sku.
	 */
	public $marketplace_sku;

	/**
	 * @var string marketplace's name.
	 */
	public $marketplace_name;

	/**
	 * @var string marketplace's label.
	 */
	public $marketplace_label;

	/**
	 * @var string current Lengow order state.
	 */
	public $order_lengow_state;

	/**
	 * @var integer Lengow process state (0 => error, 1 => imported, 2 => finished).
	 */
	public $order_process_state;

	/**
	 * @var string marketplace order date.
	 */
	public $order_date;

	/**
	 * @var integer number of items.
	 */
	public $order_item;

	/**
	 * @var array order types (is_express, is_prime...).
	 */
	public $order_types;

	/**
	 * @var string order currency.
	 */
	public $currency;

	/**
	 * @var string customer vat number
	 */
	public $customer_vat_number;

	/**
	 * @var float total paid on marketplace.
	 */
	public $total_paid;

	/**
	 * @var float commission on marketplace.
	 */
	public $commission;

	/**
	 * @var string the name of the customer.
	 */
	public $customer_name;

	/**
	 * @var string email of the customer.
	 */
	public $customer_email;

	/**
	 * @var string carrier from marketplace.
	 */
	public $carrier;

	/**
	 * @var string carrier Method from marketplace.
	 */
	public $carrier_method;

	/**
	 * @var string carrier tracking number.
	 */
	public $carrier_tracking;

	/**
	 * @var string carrier id relay.
	 */
	public $carrier_id_relay;

	/**
	 * @var boolean order shipped by marketplace.
	 */
	public $sent_marketplace;

	/**
	 * @var boolean order is in error.
	 */
	public $is_in_error;

	/**
	 * @var boolean order is reimported (ready to be reimported).
	 */
	public $is_reimported;

	/**
	 * @var string message.
	 */
	public $message;

	/**
	 * @var string created date.
	 */
	public $created_at;

	/**
	 * @var string updated date.
	 */
	public $updated_at;

	/**
	 * @var string extra information (json node form import).
	 */
	public $extra;

	/**
	 * Construct.
	 *
	 * @param integer $id Lengow order id
	 */
	public function __construct( $id ) {
		$row = self::get( array( self::FIELD_ID => $id ) );
		if ( $row ) {
			$this->id                   = (int) $row->{self::FIELD_ID};
			$this->order_id             = null !== $row->{self::FIELD_ORDER_ID}
				? (int) $row->{self::FIELD_ORDER_ID}
				: null;
			$this->feed_id              = null !== $row->{self::FIELD_FEED_ID}
				? (int) $row->{self::FIELD_FEED_ID}
				: null;
			$this->delivery_address_id  = (int) $row->{self::FIELD_DELIVERY_ADDRESS_ID};
			$this->delivery_country_iso = $row->{self::FIELD_DELIVERY_COUNTRY_ISO};
			$this->marketplace_sku      = $row->{self::FIELD_MARKETPLACE_SKU};
			$this->marketplace_name     = $row->{self::FIELD_MARKETPLACE_NAME};
			$this->marketplace_label    = $row->{self::FIELD_MARKETPLACE_LABEL};
			$this->order_lengow_state   = $row->{self::FIELD_ORDER_LENGOW_STATE};
			$this->order_process_state  = (int) $row->{self::FIELD_ORDER_PROCESS_STATE};
			$this->order_date           = $row->{self::FIELD_ORDER_DATE};
			$this->order_item           = (int) $row->{self::FIELD_ORDER_ITEM};
			$this->order_types          = null !== $row->{self::FIELD_ORDER_TYPES}
				? json_decode( $row->{self::FIELD_ORDER_TYPES}, true )
				: array();
			$this->currency             = $row->{self::FIELD_CURRENCY};
			$this->customer_vat_number  = null !== $row->{self::FIELD_CUSTOMER_VAT_NUMBER}
				? $row->{self::FIELD_CUSTOMER_VAT_NUMBER}
				: '';
			$this->total_paid           = null !== $row->{self::FIELD_TOTAL_PAID}
				? (float) $row->{self::FIELD_TOTAL_PAID} : null;
			$this->commission           = null !== $row->{self::FIELD_COMMISSION}
				? (float) $row->{self::FIELD_COMMISSION}
				: null;
			$this->customer_name        = $row->{self::FIELD_CUSTOMER_NAME};
			$this->customer_email       = $row->{self::FIELD_CUSTOMER_EMAIL};
			$this->carrier              = $row->{self::FIELD_CARRIER};
			$this->carrier_method       = $row->{self::FIELD_CARRIER_METHOD};
			$this->carrier_tracking     = $row->{self::FIELD_CARRIER_TRACKING};
			$this->carrier_id_relay     = $row->{self::FIELD_CARRIER_RELAY_ID};
			$this->sent_marketplace     = (bool) $row->{self::FIELD_SENT_MARKETPLACE};
			$this->is_in_error          = (bool) $row->{self::FIELD_IS_IN_ERROR};
			$this->is_reimported        = (bool) $row->{self::FIELD_IS_REIMPORTED};
			$this->message              = $row->{self::FIELD_MESSAGE};
			$this->created_at           = $row->{self::FIELD_CREATED_AT};
			$this->updated_at           = $row->{self::FIELD_UPDATED_AT};
			$this->extra                = $row->{self::FIELD_EXTRA};
		}
	}

	/**
	 * Get Lengow order.
	 *
	 * @param array $where a named array of WHERE clauses
	 * @param boolean $single get a single result or not
	 *
	 * @return false|object[]|object
	 *
	 */
	public static function get( $where = array(), $single = true ) {
		return Lengow_Crud::read( self::TABLE_ORDER, $where, $single );
	}

	/**
	 * Create Lengow order.
	 *
	 * @param array $data Lengow order data
	 *
	 * @return boolean
	 *
	 */
	public static function create( $data = array() ) {
		$data[ self::FIELD_CREATED_AT ] = date( Lengow_Main::DATE_FULL );

		return Lengow_Crud::create( self::TABLE_ORDER, $data );
	}

	/**
	 * Update Lengow order.
	 *
	 * @param integer $order_lengow_id Lengow order id
	 * @param array $data Lengow order data
	 *
	 * @return boolean
	 *
	 */
	public static function update( $order_lengow_id, $data = array() ) {
		$data[ self::FIELD_UPDATED_AT ] = date( Lengow_Main::DATE_FULL );

		return Lengow_Crud::update( self::TABLE_ORDER, $data, array( self::FIELD_ID => $order_lengow_id ) );
	}

	/**
	 * Get order process state.
	 *
	 * @param string $state state to be matched
	 *
	 * @return integer
	 */
	public static function get_order_process_state( $state ) {
		switch ( $state ) {
			case self::STATE_ACCEPTED:
			case self::STATE_WAITING_SHIPMENT:
			default:
				return self::PROCESS_STATE_IMPORT;
			case self::STATE_SHIPPED:
			case self::STATE_CLOSED:
			case self::STATE_REFUSED:
			case self::STATE_CANCELED:
			case self::STATE_REFUNDED:
				return self::PROCESS_STATE_FINISH;
		}
	}

	/**
	 * Get WooCommerce state id corresponding to the current order state.
	 *
	 * @param string $order_state_marketplace order state marketplace
	 * @param Lengow_Marketplace $marketplace Lengow marketplace instance
	 * @param boolean $shipped_by_mp order shipped by marketplace
	 *
	 * @return string
	 */
	public static function get_woocommerce_state( $order_state_marketplace, $marketplace, $shipped_by_mp ) {
		$order_state_lengow = $marketplace->get_state_lengow( $order_state_marketplace );
		if ( $shipped_by_mp ) {
			$order_state = 'shipped_by_mp';
		} elseif ( $order_state_lengow === self::STATE_SHIPPED || $order_state_lengow === self::STATE_CLOSED ) {
			$order_state = self::STATE_SHIPPED;
		} else {
			$order_state = self::STATE_WAITING_SHIPMENT;
		}

		return self::get_order_state( $order_state );
	}

	/**
	 * Get the matching WooCommerce order state to the one given.
	 *
	 * @param string $state state to be matched
	 *
	 * @return string
	 */
	public static function get_order_state( $state ) {
		switch ( $state ) {
			case self::STATE_ACCEPTED:
			case self::STATE_WAITING_SHIPMENT:
                        case self::STATE_WC_PROCESSING:
			default:
				$order_state = Lengow_Configuration::get( Lengow_Configuration::WAITING_SHIPMENT_ORDER_ID );
				break;
			case self::STATE_SHIPPED:
			case self::STATE_CLOSED:
                        case self::STATE_WC_COMPLETED:
				$order_state = Lengow_Configuration::get( Lengow_Configuration::SHIPPED_ORDER_ID );
				break;
			case self::STATE_REFUSED:
			case self::STATE_CANCELED:
                        case self::STATE_WC_CANCELED:
				$order_state = Lengow_Configuration::get( Lengow_Configuration::CANCELED_ORDER_ID );
				break;
			case 'shipped_by_mp':
				$order_state = Lengow_Configuration::get( Lengow_Configuration::SHIPPED_BY_MARKETPLACE_ORDER_ID );
				break;
		}

		return $order_state;
	}

	/**
	 * Get compatibility for WooCommerce order status.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 *
	 * @return string
	 */
	public static function get_order_status( $order ) {
		return 'wc-' . $order->get_status();
	}

	/**
	 * Get order id from lengow orders table.
	 *
	 * @param string $marketplace_sku Lengow marketplace sku
	 * @param string $marketplace_name marketplace name
	 * @param integer $delivery_address_id delivery address id
	 * @param string $marketplace_name_legacy old marketplace name for v2 compatibility
	 *
	 * @return integer|false
	 */
	public static function get_order_id_from_lengow_orders(
		$marketplace_sku,
		$marketplace_name,
		$marketplace_name_legacy
	) {
		global $wpdb;

		// v2 compatibility.
		$marketplace_name_legacy = null === $marketplace_name_legacy
			? $marketplace_name
			: strtolower( $marketplace_name_legacy );

		$query = '
			SELECT order_id, delivery_address_id, feed_id
			FROM ' . $wpdb->prefix . self::TABLE_ORDER . '
			WHERE marketplace_sku = %s
			AND marketplace_name IN (%s, %s)
		';

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace_name, $marketplace_name_legacy ) )
		);

		if ( empty( $results ) ) {
			return false;
		}

		return (int) reset($results)->order_id;


	}

	/**
	 * Get order id from lengow orders table.
	 *
	 * @param string $marketplace_sku Lengow marketplace sku
	 * @param string $marketplace_name marketplace name
	 *
	 * @return array
	 */
	public static function get_all_order_id_from_lengow_orders( $marketplace_sku, $marketplace_name ) {
		global $wpdb;

		$query   = '
			SELECT order_id FROM ' . $wpdb->prefix . self::TABLE_ORDER . '
			WHERE marketplace_sku = %s
			AND marketplace_name = %s
		';
		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace_name ) )
		);

		return $results ?: array();
	}

	/**
	 * Get ID record from lengow orders table.
	 *
	 * @param string $marketplace_sku Lengow id
	 * @param string $marketplace_name marketplace name
	 *
	 *
	 * @return integer|false
	 */
	public static function get_id_from_lengow_orders( $marketplace_sku, $marketplace_name) {
		global $wpdb;

		$query           = '
			SELECT id FROM ' . $wpdb->prefix . self::TABLE_ORDER . '
			WHERE marketplace_sku = %s
			AND marketplace_name = %s
		';
		$order_lengow_id = $wpdb->get_var(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace_name) )
		);
		if ( $order_lengow_id ) {
			return (int) $order_lengow_id;
		}

		return false;
	}

	/**
	 * Get id from WooCommerce order id.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return integer|false
	 */
	public static function get_id_from_order_id( $order_id ) {
		global $wpdb;

		$query           = '
			SELECT id FROM ' . $wpdb->prefix . self::TABLE_ORDER . '
			WHERE order_id = %d
		';
		$order_lengow_id = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_id ) )
		);
		if ( $order_lengow_id ) {
			return (int) $order_lengow_id;
		}

		return false;
	}

	/**
	 * Get id from Lengow delivery address id.
	 *
	 * @param integer   $order_id           WooCommerce order id
	 * @param string    $marketplace_sku    Marketplace order reference
         * @param string    $marketplace_name   The name of the marketplace
	 *
	 * @return integer|false
	 */
	public static function get_id_from_lengow_marketplace_sku( $order_id, $marketplace_sku, $marketplace_name ) {
		global $wpdb;

		$query           = '
			SELECT id FROM ' . $wpdb->prefix . self::TABLE_ORDER . '
			WHERE order_id = %d
                        AND marketplace_sku =  %s
                        AND marketplace_name =  %s
		';
		$order_lengow_id = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_id, $marketplace_sku, $marketplace_name ) )
		);
		if ( $order_lengow_id ) {
			return (int) $order_lengow_id;
		}

		return false;
	}

	/**
	 * Get marketplace label by WooCommerce order id.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return string|false
	 */
	public static function get_marketplace_label_by_order_id( $order_id ) {
		global $wpdb;
		if ( null === $order_id ) {
			return false;
		}
		$query = '
			SELECT marketplace_label FROM ' . $wpdb->prefix . self::TABLE_ORDER . '
			WHERE order_id = %d
		';

		return $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_id ) )
		);
	}

	/**
	 * Get marketplace list for order grid.
	 *
	 * @return array
	 */
	public static function get_marketplace_list() {
		global $wpdb;

		$marketplaces = array();
		$query        = '
			SELECT DISTINCT(marketplace_name) as marketplace_name,
            IFNULL(marketplace_label, marketplace_name) as marketplace_label
            FROM ' . $wpdb->prefix . self::TABLE_ORDER;
		$results      = $wpdb->get_results( $query );
		$results      = $results ?: array();
		foreach ( $results as $result ) {
			$marketplaces[ $result->{self::FIELD_MARKETPLACE_NAME} ] = $result->{self::FIELD_MARKETPLACE_LABEL};
		}

		return $marketplaces;
	}

	/**
	 * Return the number of Lengow orders imported in WooCommerce.
	 *
	 * @return integer
	 */
	public static function count_order_imported_by_lengow() {
		global $wpdb;
		$query = '
			SELECT COUNT(*) as total FROM ' . $wpdb->prefix . self::TABLE_ORDER . '
			WHERE order_id IS NOT NULL
		';

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Return the number of Lengow orders with error.
	 *
	 * @return integer
	 */
	public static function count_order_with_error() {
		$result = self::get( array( self::FIELD_IS_IN_ERROR => 1 ), false );

		return count( $result );
	}

	/**
	 * Return the number of Lengow orders to be sent.
	 *
	 * @return integer
	 */
	public static function count_order_to_be_sent() {
		$result = self::get( array( self::FIELD_ORDER_PROCESS_STATE => self::PROCESS_STATE_IMPORT ), false );

		return count( $result );
	}

	/**
	 * Retrieves all the Lengow order ids from a marketplace reference.
	 *
	 * @param string|null $marketplace_sku marketplace order reference
	 * @param string|null $marketplace_name marketplace code
	 *
	 * @return self[]
	 */
	public static function get_all_lengow_orders( $marketplace_sku, $marketplace_name ) {
		$lengowOrders = array();
		$results      = self::get(
			array(
				self::FIELD_MARKETPLACE_SKU  => $marketplace_sku,
				self::FIELD_MARKETPLACE_NAME => $marketplace_name,
			),
			false
		);
		foreach ( $results as $result ) {
			$lengowOrders[] = new self( $result->{self::FIELD_ID} );
		}

		return $lengowOrders;
	}

	/**
	 * Get all unset orders.
	 *
	 * @return array|false
	 */
	public static function get_unsent_orders() {
		global $wpdb;

		$query = '
			SELECT lo.id as order_lengow_id, p.ID as order_id, p.post_status as order_status
			FROM ' . $wpdb->prefix . self::TABLE_ORDER . ' lo
			LEFT JOIN ' . $wpdb->posts . ' p ON p.ID = lo.order_id
            WHERE lo.order_process_state = %d
            AND lo.is_in_error = %d
            AND p.post_status IN (%s,%s)
            AND p.post_modified >= %s
        ';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				$query,
				array(
					self::PROCESS_STATE_IMPORT,
					0,
					self::get_order_state( self::STATE_SHIPPED ),
					self::get_order_state( self::STATE_CANCELED ),
					date( Lengow_Main::DATE_FULL, strtotime( '-5 days' ) ),
				)
			)
		);

		return $results ?: false;
	}

	/**
	 * Update order state to marketplace state.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 * @param Lengow_Order $order_lengow Lengow order instance
	 * @param string $order_lengow_state Lengow order status
	 * @param mixed $package_data package data
	 *
	 * @return string|false
	 */
	public static function update_state( $order, $order_lengow, $order_lengow_state, $package_data ) {
		// finish actions if lengow order is shipped, closed, cancel or refunded.
		$order_process_state = self::get_order_process_state( $order_lengow_state );
		// update Lengow order if necessary.
		$params = array();
		if ( self::PROCESS_STATE_FINISH === $order_process_state ) {
			Lengow_Action::finish_all_actions( $order->get_id() );
			Lengow_Order_Error::finish_order_errors( $order_lengow->id, Lengow_Order_Error::ERROR_TYPE_SEND );
			if ( $order_process_state !== $order_lengow->order_process_state ) {
				$params[ self::FIELD_ORDER_PROCESS_STATE ] = $order_process_state;
			}
			if ( $order_lengow->is_in_error ) {
				$params[ self::FIELD_IS_IN_ERROR ] = 0;
			}
		}
		if ( $order_lengow_state !== $order_lengow->order_lengow_state ) {
			$params[ self::FIELD_ORDER_LENGOW_STATE ] = $order_lengow_state;
			if ( ! empty( $package_data->delivery->trackings ) ) {
				$tracking                               = $package_data->delivery->trackings[0];
				$params[ self::FIELD_CARRIER ]          = $tracking->carrier;
				$params[ self::FIELD_CARRIER_TRACKING ] = $tracking->number;
				$params[ self::FIELD_CARRIER_RELAY_ID ] = $tracking->relay->id;
			}
		}
		if ( ! empty( $params ) ) {
			self::update( $order_lengow->id, $params );
		}
		// update WooCommerce order's status only if in accepted, waiting_shipment, shipped, closed or cancel.
		$order_status           = self::get_order_status( $order );
		$waiting_shipment_state = self::get_order_state( self::STATE_WAITING_SHIPMENT );
		$shipped_state          = self::get_order_state( self::STATE_SHIPPED );
		$canceled_state         = self::get_order_state( self::STATE_CANCELED );
		if ( self::get_order_state( $order_lengow_state ) !== $order_status ) {
			if ( $order_status === $waiting_shipment_state
			     && in_array( $order_lengow_state, array( self::STATE_SHIPPED, self::STATE_CLOSED ) )
			) {
				$order->update_status( $shipped_state );

				return self::STATE_SHIPPED;
			}
			if ( ( $order_status === $waiting_shipment_state || $order_status === $shipped_state )
			     && in_array( $order_lengow_state, array( self::STATE_CANCELED, self::STATE_REFUSED ) )
			) {
				$order->update_status( $canceled_state );

				return self::STATE_CANCELED;
			}
		}

		return false;
	}

	/**
	 * Re Import Order.
	 *
	 * @param integer $order_lengow_id Lengow order id
	 *
	 * @return array|false
	 */
	public static function re_import_order( $order_lengow_id ) {
		$order_lengow = self::get( array( self::FIELD_ID => $order_lengow_id ) );
		if ( $order_lengow ) {
			$import = new Lengow_Import(
				array(
					Lengow_Import::PARAM_ORDER_LENGOW_ID     => $order_lengow->id,
					Lengow_Import::PARAM_MARKETPLACE_SKU     => $order_lengow->marketplace_sku,
					Lengow_Import::PARAM_MARKETPLACE_NAME    => $order_lengow->marketplace_name,
					Lengow_Import::PARAM_DELIVERY_ADDRESS_ID => $order_lengow->delivery_address_id,
					Lengow_Import::PARAM_LOG_OUTPUT          => false,
				)
			);

			return $import->exec();
		}

		return false;
	}

	/**
	 * Resend an action.
	 *
	 * @param integer $order_lengow_id Lengow order id
	 *
	 * @return bool
	 */
	public static function re_send_order( $order_lengow_id ) {
		$order_lengow = new Lengow_Order( $order_lengow_id );
		if ( $order_lengow->order_id ) {
			$order        = new WC_Order( $order_lengow->order_id );
			$order_status = self::get_order_status( $order );
			// sending an API call for sending or canceling an order.
			if ( self::get_order_state( self::STATE_SHIPPED ) === $order_status ) {
				return $order_lengow->call_action( Lengow_Action::TYPE_SHIP );
			}

			return $order_lengow->call_action( Lengow_Action::TYPE_CANCEL );
		}

		return false;
	}

	/**
	 * Create an error and update the order in error.
	 *
	 * @param integer $order_lengow_id Lengow order id
	 * @param string $message error message
	 * @param string $type order error type (import or send)
	 *
	 * @return boolean
	 */
	public static function add_order_error( $order_lengow_id, $message, $type = null ) {
		$error_created = Lengow_Order_Error::create(
			array(
				Lengow_Order_Error::FIELD_ORDER_LENGOW_ID => $order_lengow_id,
				Lengow_Order_Error::FIELD_MESSAGE         => $message,
				Lengow_Order_Error::FIELD_TYPE            => null === $type
					? Lengow_Order_Error::ERROR_TYPE_IMPORT
					: $type,
			)
		);
		$order_updated = self::update( $order_lengow_id, array( self::FIELD_IS_IN_ERROR => 1 ) );

		return $error_created && $order_updated;
	}

	/**
	 * Retrieves the real import date of the woocommerce order.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return string|null
	 */
	public static function get_date_imported( $order_id ) {
		$order_notes = wc_get_order_notes( array( 'order_id' => $order_id ) );
		if ( empty( $order_notes ) ) {
			return null;
		}
		$first_order_note_created = end( $order_notes );

		return get_gmt_from_date( $first_order_note_created->date_created->date( Lengow_Main::DATE_FULL ) );
	}

	/**
	 * Synchronize order with Lengow API.
	 *
	 * @param Lengow_Connector|null $connector Lengow connector instance
	 * @param boolean $log_output see log or not
	 *
	 * @return boolean
	 */
	public function synchronize_order( $connector = null, $log_output = false ) {
		list( $account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
		if ( null === $connector ) {
			if ( Lengow_Connector::is_valid_auth( $log_output ) ) {
				$connector = new Lengow_Connector( $access_token, $secret_token );
			} else {
				return false;
			}
		}
		$results = self::get_all_order_id_from_lengow_orders( $this->marketplace_sku, $this->marketplace_name );
		if ( $results ) {
			$woocommerce_order_ids = array();
			foreach ( $results as $result ) {
				$woocommerce_order_ids[] = $result->order_id;
			}
			// compatibility v2.
			if ( null !== $this->feed_id && ! Lengow_Marketplace::marketplace_exist( $this->marketplace_name ) ) {
				$this->check_and_change_marketplace_name( $connector, $log_output );
			}
			$body = array(
				Lengow_Import::ARG_ACCOUNT_ID           => $account_id,
				Lengow_Import::ARG_MARKETPLACE_ORDER_ID => $this->marketplace_sku,
				Lengow_Import::ARG_MARKETPLACE          => $this->marketplace_name,
				Lengow_Import::ARG_MERCHANT_ORDER_ID    => $woocommerce_order_ids,
			);
			try {
				$return = $connector->patch(
					Lengow_Connector::API_ORDER_MOI,
					array(),
					Lengow_Connector::FORMAT_JSON,
					json_encode( $body ),
					$log_output
				);
			} catch ( Exception $e ) {
				$message = Lengow_Main::decode_log_message( $e->getMessage(), Lengow_Translation::DEFAULT_ISO_CODE );
				$error   = Lengow_Main::set_log_message(
					'log.connector.error_api',
					array(
						'error_code'    => $e->getCode(),
						'error_message' => $message,
					)
				);
				Lengow_Main::log( Lengow_Log::CODE_CONNECTOR, $error, $log_output );

				return false;
			}

			return ! ( null === $return
			           || ( isset( $return['detail'] ) && 'Pas trouvé.' === $return['detail'] )
			           || isset( $return['error'] ) );
		}

		return false;
	}

	/**
	 * Check and change the name of the marketplace for v3 compatibility.
	 *
	 * @param Lengow_Connector|null $connector Lengow connector instance
	 * @param boolean $log_output see log or not
	 *
	 * @return boolean
	 */
	public function check_and_change_marketplace_name( $connector = null, $log_output = false ) {
		list( $account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
		if ( null === $connector ) {
			if ( Lengow_Connector::is_valid_auth( $log_output ) ) {
				$connector = new Lengow_Connector( $access_token, $secret_token );
			} else {
				return false;
			}
		}
		try {
			$return = $connector->get(
				Lengow_Connector::API_ORDER,
				array(
					Lengow_Import::ARG_MARKETPLACE_ORDER_ID => $this->marketplace_sku,
					Lengow_Import::ARG_ACCOUNT_ID           => $account_id,
				),
				Lengow_Connector::FORMAT_STREAM,
				'',
				$log_output
			);
		} catch ( Exception $e ) {
			$message = Lengow_Main::decode_log_message( $e->getMessage(), Lengow_Translation::DEFAULT_ISO_CODE );
			$error   = Lengow_Main::set_log_message(
				'log.connector.error_api',
				array(
					'error_code'    => $e->getCode(),
					'error_message' => $message,
				)
			);
			Lengow_Main::log( Lengow_Log::CODE_CONNECTOR, $error, $log_output );

			return false;
		}
		if ( null === $return ) {
			return false;
		}
		// don't decode into array as we use the result as an object.
		$results = json_decode( $return );
		if ( isset( $results->error ) ) {
			return false;
		}
		foreach ( $results->results as $order ) {
			$new_marketplace_name = (string) $order->marketplace;
			if ( $new_marketplace_name !== $this->marketplace_name ) {
				self::update( $this->id, array( self::FIELD_MARKETPLACE_NAME => $new_marketplace_name ) );
				$this->marketplace_name = $new_marketplace_name;
			}
		}

		return true;
	}

	/**
	 * Check if order is express.
	 *
	 * @return boolean
	 */
	public function is_express() {
		return isset( $this->order_types[ self::TYPE_EXPRESS ] ) || isset( $this->order_types[ self::TYPE_PRIME ] );
	}

	/**
	 * Check if order is B2B.
	 *
	 * @return boolean
	 */
	public function is_business() {
		return isset( $this->order_types[ self::TYPE_BUSINESS ] );
	}

	/**
	 * Check if order is delivered by marketplace.
	 *
	 * @return boolean
	 */
	public function is_delivered_by_marketplace() {
		return isset( $this->order_types[ self::TYPE_DELIVERED_BY_MARKETPLACE ] ) || $this->sent_marketplace;
	}

	/**
	 * Check if order is closed.
	 *
	 * @return boolean
	 */
	public function is_closed() {
		return self::PROCESS_STATE_FINISH === $this->order_process_state;
	}

	/**
	 * Check if order has an action in progress.
	 *
	 * @return boolean
	 */
	public function has_an_action_in_progress() {
		$actions = Lengow_Action::get_action_by_order_id( $this->order_id, true );

		return (bool) $actions;
	}

	/**
	 * Check if order is in good status for resend and has no action in progress.
	 *
	 * @return bool
	 */
	public function can_resend_action() {
		$order = new WC_Order( $this->order_id );
		if ( ! $this->is_closed() && ! $this->has_an_action_in_progress() ) {
			$status = self::get_order_status( $order );
			if ( self::get_order_state( self::STATE_CANCELED ) === $status ||
			     self::get_order_state( self::STATE_SHIPPED ) === $status ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Send an action for a specific order.
	 *
	 * @param string $action Lengow Actions type (ship or cancel)
	 *
	 * @return boolean
	 */
	public function call_action( $action ) {
		// do nothing if the order is closed.
		if ( $this->is_closed() ) {
			return false;
		}
		$success = true;
		Lengow_Main::log(
			Lengow_Log::CODE_ACTION,
			Lengow_Main::set_log_message(
				'log.order_action.try_to_send_action',
				array(
					'action'   => $action,
					'order_id' => $this->order_id,
				)
			),
			false,
			$this->marketplace_sku
		);
		// finish all order logs send.
		Lengow_Order_Error::finish_order_errors( $this->id, Lengow_Order_Error::ERROR_TYPE_SEND );
		try {
			// compatibility v2.
			if ( null !== $this->feed_id && ! Lengow_Marketplace::marketplace_exist( $this->marketplace_name ) ) {
				$this->check_and_change_marketplace_name();
			}
			$marketplace = Lengow_Main::get_marketplace_singleton( $this->marketplace_name );
			if ( $marketplace->contain_order_line( $action ) ) {
				$order_lines = Lengow_Order_Line::get_all_order_line_id_by_order_id( $this->order_id, ARRAY_A );
				// compatibility v2 and security.
				if ( ! $order_lines ) {
					$order_lines = $this->get_order_line_by_api();
				}
				if ( ! $order_lines ) {
					throw new Lengow_Exception(
						Lengow_Main::set_log_message( 'lengow_log.exception.order_line_required' )
					);
				}
				$results = array();
				foreach ( $order_lines as $order_line ) {
					$results[] = true;
					$results[] = $marketplace->call_action(
						$action,
						$this,
						$order_line[ Lengow_Order_Line::FIELD_ORDER_LINE_ID ]
					);
				}
				$success = ! in_array( false, $results, true );
			} else {
				$success = $marketplace->call_action( $action, $this );
			}
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce error]: "' . $e->getMessage()
			                 . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			self::add_order_error( $this->id, $error_message, Lengow_Order_Error::ERROR_TYPE_SEND );
			$decoded_message = Lengow_Main::decode_log_message( $error_message, Lengow_Translation::DEFAULT_ISO_CODE );
			Lengow_Main::log(
				Lengow_Log::CODE_ACTION,
				Lengow_Main::set_log_message(
					'log.order_action.call_action_failed',
					array( 'decoded_message' => $decoded_message )
				),
				false,
				$this->marketplace_sku
			);
			$success = false;
		}

		if ( $success ) {
			$message = Lengow_Main::set_log_message(
				'log.order_action.action_send',
				array(
					'action'   => $action,
					'order_id' => $this->order_id,
				)
			);
		} else {
			$message = Lengow_Main::set_log_message(
				'log.order_action.action_not_send',
				array(
					'action'   => $action,
					'order_id' => $this->order_id,
				)
			);
		}
		Lengow_Main::log( Lengow_Log::CODE_ACTION, $message, false, $this->marketplace_sku );

		return $success;
	}

	/**
	 * Get order line by API.
	 *
	 * @return array|false
	 */
	public function get_order_line_by_api() {
		$order_lines = array();
		$results     = Lengow_Connector::query_api(
			Lengow_Connector::GET,
			Lengow_Connector::API_ORDER,
			array(
				Lengow_Import::ARG_MARKETPLACE_ORDER_ID => $this->marketplace_sku,
				Lengow_Import::ARG_MARKETPLACE          => $this->marketplace_name,
			)
		);
		if ( ! isset( $results->count ) || 0 === (int) $results->count ) {
			return false;
		}
		$order_data = $results->results[0];
		foreach ( $order_data->packages as $package ) {
			$product_lines = array();
			foreach ( $package->cart as $product ) {
				$product_lines[] = array(
					Lengow_Order_Line::FIELD_ORDER_LINE_ID => (string) $product->marketplace_order_line_id,
				);
			}
			if ( 0 === $this->delivery_address_id ) {
				return ! empty( $product_lines ) ? $product_lines : false;
			}
			$order_lines[ (int) $package->delivery->id ] = $product_lines;
		}
		$return = $order_lines[ $this->delivery_address_id ];

		return ! empty( $return ) ? $return : false;
	}

	/**
	 * Reimport order and pass current order in technical error.
	 *
	 * @return integer|false
	 */
	public function cancel_and_reimport_order() {
		$update = self::update( $this->id, array( self::FIELD_IS_REIMPORTED => true ) );
		if ( ! $update ) {
			return false;
		}

		$import = new Lengow_Import(
			array(
				Lengow_Import::PARAM_ORDER_LENGOW_ID     => $this->id,
				Lengow_Import::PARAM_MARKETPLACE_SKU     => $this->marketplace_sku,
				Lengow_Import::PARAM_MARKETPLACE_NAME    => $this->marketplace_name,
				Lengow_Import::PARAM_DELIVERY_ADDRESS_ID => $this->delivery_address_id,
			)
		);
		$result = $import->exec();
		if ( ! empty( $result[ Lengow_Import::ORDERS_CREATED ] ) ) {
			$order_created = $result[ Lengow_Import::ORDERS_CREATED ][0];
			if ( $order_created[ Lengow_Import_Order::LENGOW_ORDER_ID ] === $this->id ) {
				$this->set_state_to_error();

				return (int) $order_created[ Lengow_Import_Order::MERCHANT_ORDER_ID ];
			}
		}
		// in the event of an error, all new order errors are finished and the order is reset.
		Lengow_Order_Error::finish_order_errors( $this->id );
		self::update(
			$this->id,
			array(
				self::FIELD_IS_REIMPORTED => false,
				self::FIELD_IS_IN_ERROR   => false,
			)
		);

		return false;
	}

	/**
	 * Pass order in technical error.
	 */
	public function set_state_to_error() {
		$order = new WC_Order( $this->order_id );
		$order->update_status( Lengow::STATE_LENGOW_TECHNICAL_ERROR );
	}
}