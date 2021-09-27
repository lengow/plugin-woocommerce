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
 * @copyright   2019 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Order_Error Class.
 */
class Lengow_Order_Error {

	/**
	 * @var string Lengow order error table name
	 */
	const TABLE_ORDER_ERROR = 'lengow_order_error';

	/* Order error fields */
	const FIELD_ID = 'id';
	const FIELD_ORDER_LENGOW_ID = 'order_lengow_id';
	const FIELD_TYPE = 'type';
	const FIELD_MESSAGE = 'message';
	const FIELD_IS_FINISHED = 'is_finished';
	const FIELD_MAIL = 'mail';
	const FIELD_CREATED_AT = 'created_at';
	const FIELD_UPDATED_AT = 'updated_at';

	/* Order error types */
	const ERROR_TYPE_IMPORT = 1;
	const ERROR_TYPE_SEND = 2;

	/**
	 * Get Lengow order error.
	 *
	 * @param array $where a named array of WHERE clauses
	 * @param boolean $single get a single result or not
	 *
	 * @return false|object[]|object
	 *
	 */
	public static function get( $where = array(), $single = true ) {
		return Lengow_Crud::read( self::TABLE_ORDER_ERROR, $where, $single );
	}

	/**
	 * Create Lengow order error.
	 *
	 * @param array $data Lengow order error data
	 *
	 * @return boolean
	 *
	 */
	public static function create( $data = array() ) {
		$data[ self::FIELD_CREATED_AT ] = date( Lengow_Main::DATE_FULL );
		if ( ! isset( $data[ self::FIELD_TYPE ] ) ) {
			$data[ self::FIELD_TYPE ] = self::ERROR_TYPE_IMPORT;
		}

		return Lengow_Crud::create( self::TABLE_ORDER_ERROR, $data );
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
		$data[ self::FIELD_UPDATED_AT ] = date( Lengow_Main::DATE_FULL );

		return Lengow_Crud::update( self::TABLE_ORDER_ERROR, $data, array( self::FIELD_ID => $order_error_id ) );
	}

	/**
	 * Check if an order has an error.
	 *
	 * @param string $marketplace_sku Lengow marketplace sku
	 * @param integer $delivery_address_id Lengow delivery address id
	 * @param integer|null $type order error type (import or send)
	 *
	 * @return array|false
	 */
	public static function order_is_in_error( $marketplace_sku, $delivery_address_id, $type = null ) {
		global $wpdb;

		$order_error_type = null === $type ? self::ERROR_TYPE_IMPORT : $type;
		$query            = '
			SELECT loe.message, loe.created_at
			FROM ' . $wpdb->prefix . self::TABLE_ORDER_ERROR . ' loe
            LEFT JOIN ' . $wpdb->prefix . Lengow_Order::TABLE_ORDER . ' lo ON loe.order_lengow_id = lo.id
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
			FROM ' . $wpdb->prefix . self::TABLE_ORDER_ERROR . ' loe
            LEFT JOIN ' . $wpdb->prefix . Lengow_Order::TABLE_ORDER . ' lo ON loe.order_lengow_id = lo.id
            WHERE loe.is_finished = %d
            AND loe.mail = %d
        ';
		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( 0, 0 ) )
		);

		return $results ?: false;
	}

	/**
	 * Finish all order errors.
	 *
	 * @param integer $order_lengow_id Lengow order id
	 * @param integer|null $type order log type (import or send)
	 *
	 * @return boolean
	 */
	public static function finish_order_errors( $order_lengow_id, $type = null ) {
		$where = array( self::FIELD_ORDER_LENGOW_ID => $order_lengow_id );
		if ( null !== $type ) {
			$where[ self::FIELD_TYPE ] = $type;
		}
		$order_errors   = self::get( $where, false );
		$update_success = 0;
		foreach ( $order_errors as $order_error ) {
			$result = self::update( $order_error->id, array( self::FIELD_IS_FINISHED => 1 ) );
			if ( $result ) {
				$update_success ++;
			}
		}

		return $update_success === count( $order_errors );
	}

	/**
	 * Check if errors already exists for the given order.
	 *
	 * @param string $order_lengow_id Lengow order id
	 * @param integer $type order error type (import or send)
	 * @param boolean $finished error finished (true or false)
	 *
	 * @return array|false
	 */
	public static function get_order_errors( $order_lengow_id, $type = null, $finished = null ) {
		$where = array( self::FIELD_ORDER_LENGOW_ID => $order_lengow_id );
		if ( null !== $type ) {
			$where[ self::FIELD_TYPE ] = $type;
		}
		if ( null !== $finished ) {
			$where[ self::FIELD_IS_FINISHED ] = (int) $finished;
		}

		return self::get( $where, false );
	}
}
