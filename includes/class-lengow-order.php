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
	 * Get ID record from lengow orders table.
	 *
	 * @param string $marketplace_sku Lengow id
	 * @param string $marketplace marketplace name
	 * @param integer $delivery_address_id delivery address id
	 * @param string $marketplace_legacy old marketplace name for v2 compatibility
	 *
	 * @return boolean
	 */
	public static function get_id_order_from_lengow_orders(
		$marketplace_sku,
		$marketplace,
		$delivery_address_id,
		$marketplace_legacy
	) {
		global $wpdb;

		// v2 compatibility.
		$marketplace_legacy = is_null( $marketplace_legacy ) ? $marketplace : strtolower( $marketplace_legacy );

		$query = '
			SELECT order_id, delivery_address_id, flux_id
			FROM ' . $wpdb->prefix . 'lengow_orders 
			WHERE marketplace_sku = %s
			AND marketplace_name IN (%s, %s)
		';

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace, $marketplace_legacy ) )
		);

		if ( empty( $results ) ) {
			return false;
		}
		foreach ( $results as $result ) {
			if ( is_null( $result->delivery_address_id ) && ! is_null( $result->flux_id ) ) {
				return true;
			} elseif ( $result->delivery_address_id == $delivery_address_id ) {
				return true;
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
			SELECT id FROM ' . $wpdb->prefix . 'lengow_orders 
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
	 * Get marketplace name by Wordpress order id
	 *
	 * @param integer $order_id Wordpress order id
	 *
	 * @return string|false
	 */
	public static function get_marketplace_name_by_order_id( $order_id ) {
		global $wpdb;
		if ( $order_id === null ) {
			return false;
		}

		$marketplace_name = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT `marketplace_name` FROM ' . $wpdb->prefix . 'lengow_orders WHERE `order_id` = %d',
				$order_id
			)
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
		$query = 'SELECT COUNT(*) as total FROM ' . $wpdb->prefix . 'lengow_orders WHERE order_lengow_state = %s';
		$total = $wpdb->get_var( $wpdb->prepare( $query, array( $order_status ) ) );

		return (int) $total;
	}
}
