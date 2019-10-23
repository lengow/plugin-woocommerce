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

		return Lengow_Crud::update( Lengow_Crud::LENGOW_ORDER_ERROR, $data, array( 'id' => $order_error_id ) );
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
            AND lo.is_in_error = %d
            AND loe.type = %d
            AND loe.is_finished = %d
        ';
		$results          = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $delivery_address_id, 1, $order_error_type, 0 ) )
		);
		if ( $results ) {
			return $results[0];
		}

		return false;
	}

	/**
	 * Get all order errors not yet sent by email.
	 *
	 * @return array|false
	 */
	public static function get_all_order_error_not_sent() {
		global $wpdb;
		$query   = '
			SELECT lo.marketplace_sku, loe.message, loe.id
			FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER_ERROR . ' loe
            LEFT JOIN ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . ' lo ON loe.order_lengow_id = lo.id
            WHERE loe.is_finished = %d
            AND loe.mail = %d
        ';
		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( 0, 0 ) )
		);

		return $results ? $results : false;
	}

	/**
	 * Removes all order logs.
	 *
	 * @param integer $idOrderLengow Lengow order id
	 * @param string|null $type order log type (import or send)
	 *
	 * @return boolean
	 */
	public static function finish_order_errors( $idOrderLengow, $type = null ) {
		$where = array( 'order_lengow_id' => $idOrderLengow );
		if ( $type ) {
			$where['type'] = self::get_order_error_type( $type );
		}
		$order_errors   = Lengow_Crud::read( Lengow_Crud::LENGOW_ORDER_ERROR, $where, false );
		$update_success = 0;
		foreach ( $order_errors as $order_error ) {
			$result = self::update( $order_error->id, array( 'is_finished' => 1 ) );
			if ( $result ) {
				$update_success ++;
			}
		}

		return $update_success === count( $order_errors ) ? true : false;
	}

	/**
	 * Return order error type value.
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

	/**
	 * Check if errors already exists for the given order.
	 *
	 * @param string $order_lengow_id Lengow order id
	 * @param string $type order error type (import or send)
	 * @param boolean $finished error finished (true or false)
	 *
	 * @return array|false
	 */
	public static function get_order_errors( $order_lengow_id, $type = null, $finished = null ) {
		$errorType               = self::get_order_error_type( $type );
		$args                    = array();
		$args['order_lengow_id'] = $order_lengow_id;
		if ( ! is_null( $errorType ) ) {
			$args['type'] = $errorType;
		}
		if ( ! is_null( $finished ) ) {
			$args['is_finished'] = $finished;
		}
		$results = Lengow_Crud::read( Lengow_Crud::LENGOW_ORDER_ERROR, $args, false );

		return $results;
	}
}
