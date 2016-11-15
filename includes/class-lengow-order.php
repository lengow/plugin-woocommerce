<?php
/**
 * All function to synchronised orders
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Order Class.
 */
class Lengow_Order {

	/**
	 * Get ID record from lengow orders table
	 *
	 * @param string $marketplace_sku Lengow order id
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

		$query = "
			SELECT id_order, delivery_address_id, id_flux
			FROM " . $wpdb->prefix . "lengow_orders 
			WHERE marketplace_sku = %s
			AND marketplace_name IN (%s, %s)
		";

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace, $marketplace_legacy ) )
		);

		if ( count( $results ) == 0 ) {
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
	 * Get ID record from lengow orders table
	 *
	 * @param string $marketplace_sku lengow order id
	 * @param integer $delivery_address_id delivery address id
	 *
	 * @return mixed
	 */
	public static function get_id_from_lengow_orders( $marketplace_sku, $delivery_address_id ) {
		global $wpdb;

		$query = "
			SELECT id FROM " . $wpdb->prefix . "lengow_orders 
			WHERE marketplace_sku = %s
			AND delivery_address_id = %d
		";

		$id_order_lengow = $wpdb->get_var(
			$wpdb->prepare( $query, array( $marketplace_sku, $delivery_address_id ) )
		);

		if ( $id_order_lengow ) {
			return (int) $id_order_lengow;
		}

		return false;
	}
}

