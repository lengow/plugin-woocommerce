<?php
/**
 * All function to manage order errors
 *
 * Copyright 2019 Lengow SAS
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
 * @copyright   2019 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Order_Error Class.
 */
class Lengow_Order_Error {

	/**
	 * @var integer order error type import.
	 */
	const ERROR_TYPE_IMPORT = 1;

	/**
	 * @var integer order error type send.
	 */
	const ERROR_TYPE_SEND = 2;

	/**
	 * Create Lengow order error.
	 *
	 * @param array $data Lengow order error data
	 *
	 * @return boolean
	 *
	 */
	public static function create( $data = array() ) {
		$data['created_at'] = date( 'Y-m-d H:i:s' );
		if ( ! isset( $data['type'] ) ) {
			$data['type'] = self::ERROR_TYPE_IMPORT;
		}

		return Lengow_Crud::create( Lengow_Crud::LENGOW_ORDER_ERROR, $data );
	}

	/**
	 * Update Lengow order error.
	 *
	 * @param integer $order_error_id Lengow order error id
	 * @param array $data Lengow order data
	 *
	 * @return boolean
	 *
	 */
	public static function update( $order_error_id, $data = array() ) {
		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		return Lengow_Crud::update( Lengow_Crud::LENGOW_ORDER_ERROR, array( 'id' => $order_error_id ), $data );
	}

	/**
	 * Check if an order has an error.
	 *
	 * @param string $marketplace_sku Lengow marketplace sku
	 * @param integer $delivery_address_id Lengow delivery address id
	 * @param string $type order error type (import or send)
	 *
	 * @return array|false
	 */
	public static function order_is_in_error( $marketplace_sku, $delivery_address_id, $type = 'import' ) {
		global $wpdb;

		$order_error_type = self::get_order_error_type( $type );
		$query            = '
			SELECT loe.message, loe.created_at
			FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER_ERROR . ' loe
            LEFT JOIN ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . ' lo ON loe.order_lengow_id = lo.id
            WHERE lo.marketplace_sku = %s
            AND lo.delivery_address_id = %d
            AND loe.type = %d
            AND loe.is_finished = %d
        ';
		$results          = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $delivery_address_id, $order_error_type, 0 ) )
		);
		if ( $results ) {
			return $results[0];
		}

		return false;
	}

	/**
	 * Return order error type value
	 *
	 * @param string $type order error type (import or send)
	 *
	 * @return integer|null
	 */
	public static function get_order_error_type( $type ) {
		switch ( $type ) {
			case 'import':
			default:
				$error_type = self::ERROR_TYPE_IMPORT;
				break;
			case 'send':
				$error_type = self::ERROR_TYPE_SEND;
				break;
		}

		return $error_type;
	}
}
