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
 * @author      Team module <team-module@lengow.com>
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

		// V2 compatibility.
		$marketplace_legacy = is_null( $marketplace_legacy ) ? $marketplace : strtolower( $marketplace_legacy );

		$query = '
			SELECT id_order, delivery_address_id, id_flux
			FROM ' . $wpdb->prefix . 'lengow_orders 
			WHERE marketplace_sku = %s
			AND marketplace_name IN (%s, %s)
		';

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace, $marketplace_legacy ) )
		);

		if ( count( $results ) === 0 ) {
			return false;
		}
		foreach ( $results as $result ) {
			if ( is_null( $result->delivery_address_id ) && ! is_null( $result->id_flux ) ) {
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

		$query = '
			SELECT id FROM ' . $wpdb->prefix . 'lengow_orders 
			WHERE marketplace_sku = %s
			AND delivery_address_id = %d
		';

		$id_order_lengow = $wpdb->get_var(
			$wpdb->prepare( $query, array( $marketplace_sku, $delivery_address_id ) )
		);

		if ( $id_order_lengow ) {
			return (int) $id_order_lengow;
		}

		return false;
	}
}
