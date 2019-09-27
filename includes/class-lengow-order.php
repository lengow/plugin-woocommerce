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
 * Lengow_Order Class.
 */
class Lengow_Order {

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
	 * @var string order currency.
	 */
	public $currency;

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
		$row = Lengow_Crud::read( Lengow_Crud::LENGOW_ORDER, array( 'id' => $id ) );
		if ( $row ) {
			$this->id                   = (int) $row->id;
			$this->order_id             = null !== $row->order_id ? (int) $row->order_id : null;
			$this->feed_id              = null !== $row->feed_id ? (int) $row->feed_id : null;
			$this->delivery_address_id  = (int) $row->delivery_address_id;
			$this->delivery_country_iso = $row->delivery_country_iso;
			$this->marketplace_sku      = $row->marketplace_sku;
			$this->marketplace_name     = $row->marketplace_name;
			$this->marketplace_label    = $row->marketplace_label;
			$this->order_lengow_state   = $row->order_lengow_state;
			$this->order_process_state  = (int) $row->order_process_state;
			$this->order_date           = $row->order_date;
			$this->order_item           = (int) $row->order_item;
			$this->currency             = $row->currency;
			$this->total_paid           = null !== $row->total_paid ? (float) $row->total_paid : null;
			$this->commission           = null !== $row->commission ? (float) $row->commission : null;
			$this->customer_name        = $row->customer_name;
			$this->customer_email       = $row->customer_email;
			$this->carrier              = $row->carrier;
			$this->carrier_method       = $row->carrier_method;
			$this->carrier_tracking     = $row->carrier_tracking;
			$this->carrier_id_relay     = $row->carrier_id_relay;
			$this->sent_marketplace     = (bool) $row->sent_marketplace;
			$this->is_in_error          = (bool) $row->is_in_error;
			$this->is_reimported        = (bool) $row->is_reimported;
			$this->message              = $row->message;
			$this->created_at           = $row->created_at;
			$this->updated_at           = $row->updated_at;
			$this->extra                = $row->extra;
		}
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
		$data['created_at'] = date( 'Y-m-d H:i:s' );

		return Lengow_Crud::create( Lengow_Crud::LENGOW_ORDER, $data );
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
		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		return Lengow_Crud::update( Lengow_Crud::LENGOW_ORDER, $data, array( 'id' => $order_lengow_id ) );
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
		$delivery_address_id,
		$marketplace_name_legacy
	) {
		global $wpdb;

		// v2 compatibility.
		$marketplace_name_legacy = null === $marketplace_name_legacy
			? $marketplace_name
			: strtolower( $marketplace_name_legacy );

		$query = '
			SELECT order_id, delivery_address_id, feed_id
			FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE marketplace_sku = %s
			AND marketplace_name IN (%s, %s)
		';

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace_name, $marketplace_name_legacy ) )
		);

		if ( empty( $results ) ) {
			return false;
		}
		foreach ( $results as $result ) {
			if ( null === $result->delivery_address_id && null !== $result->feed_id ) {
				return (int) $result->order_id;
			} elseif ( (int) $result->delivery_address_id === $delivery_address_id ) {
				return (int) $result->order_id;
			}
		}

		return false;
	}

	/**
	 * Get ID record from lengow orders table.
	 *
	 * @param string $marketplace_sku Lengow id
	 * @param integer $delivery_address_id delivery address id
	 *
	 * @return integer|false
	 */
	public static function get_id_from_lengow_orders( $marketplace_sku, $delivery_address_id ) {
		global $wpdb;

		$query           = '
			SELECT id FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE marketplace_sku = %s
			AND delivery_address_id = %d
		';
		$order_lengow_id = $wpdb->get_var(
			$wpdb->prepare( $query, array( $marketplace_sku, $delivery_address_id ) )
		);
		if ( $order_lengow_id ) {
			return (int) $order_lengow_id;
		}

		return false;
	}

	/**
	 * Get id from Lengow delivery address id.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param integer $delivery_address_id Lengow delivery address id
	 *
	 * @return integer|false
	 */
	public static function get_id_from_lengow_delivery_address( $order_id, $delivery_address_id ) {
		global $wpdb;

		$query           = '
			SELECT id FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE order_id = %d
			AND delivery_address_id = %d
		';
		$order_lengow_id = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_id, $delivery_address_id ) )
		);
		if ( $order_lengow_id ) {
			return (int) $order_lengow_id;
		}

		return false;
	}

	/**
	 * Get marketplace name by WooCommerce order id.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return string|false
	 */
	public static function get_marketplace_name_by_order_id( $order_id ) {
		global $wpdb;
		if ( null === $order_id ) {
			return false;
		}
		$query            = '
			SELECT marketplace_name FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE order_id = %d
		';
		$marketplace_name = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_id ) )
		);

		return $marketplace_name;
	}

	/**
	 * Get total order by statuses.
	 *
	 * @param string $order_status Lengow order state
	 *
	 * @return integer
	 */
	public static function get_total_order_by_status( $order_status ) {
		global $wpdb;
		$query = '
			SELECT COUNT(*) as total FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE order_lengow_state = %s
		';
		$total = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_status ) )
		);

		return (int) $total;
	}
}
