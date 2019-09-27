<?php
/**
 * Lengow crud (create, read, update, delete).
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
 * Lengow_Crud Class.
 */
class Lengow_Crud {

	/**
	 * @var string Lengow action table name.
	 */
	const LENGOW_ACTION = 'lengow_action';

	/**
	 * @var string Lengow product table name.
	 */
	const LENGOW_PRODUCT = 'lengow_product';

	/**
	 * @var string Lengow order table name.
	 */
	const LENGOW_ORDER = 'lengow_orders';

	/**
	 * @var string Lengow order line table name.
	 */
	const LENGOW_ORDER_LINE = 'lengow_order_line';

	/**
	 * @var string Lengow order error table name.
	 */
	const LENGOW_ORDER_ERROR = 'lengow_order_error';

	/**
	 * @var array $field_list field list.
	 * required => Required fields when creating registration
	 * update   => Fields allowed when updating registration
	 * format   => Format required in database (%s => string, %d => integer or %f => float)
	 */
	public static $lengow_tables = array(
		'lengow_action'      => array(
			'id'             => array( 'required' => false, 'updated' => false, 'format' => '%d' ),
			'order_id'       => array( 'required' => true, 'updated' => false, 'format' => '%d' ),
			'action_id'      => array( 'required' => true, 'updated' => false, 'format' => '%d' ),
			'order_line_sku' => array( 'required' => false, 'updated' => false, 'format' => '%s' ),
			'action_type'    => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'retry'          => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'parameters'     => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'state'          => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'created_at'     => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'updated_at'     => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
		),
		'lengow_product'     => array(
			'id'         => array( 'required' => false, 'updated' => false, 'format' => '%d' ),
			'product_id' => array( 'required' => true, 'updated' => true, 'format' => '%d' ),
		),
		'lengow_orders'      => array(
			'id'                   => array( 'required' => false, 'updated' => false, 'format' => '%d' ),
			'order_id'             => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'feed_id'              => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'delivery_address_id'  => array( 'required' => true, 'updated' => false, 'format' => '%d' ),
			'delivery_country_iso' => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'marketplace_sku'      => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'marketplace_name'     => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'marketplace_label'    => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'order_lengow_state'   => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'order_process_state'  => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'order_date'           => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'order_item'           => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'currency'             => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'total_paid'           => array( 'required' => false, 'updated' => true, 'format' => '%f' ),
			'commission'           => array( 'required' => false, 'updated' => true, 'format' => '%f' ),
			'customer_name'        => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'customer_email'       => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'carrier'              => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'carrier_method'       => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'carrier_tracking'     => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'carrier_id_relay'     => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'sent_marketplace'     => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'is_in_error'          => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'message'              => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'created_at'           => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'updated_at'           => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
			'extra'                => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
		),
		'lengow_order_line'  => array(
			'id'            => array( 'required' => false, 'updated' => false, 'format' => '%d' ),
			'order_id'      => array( 'required' => true, 'updated' => false, 'format' => '%d' ),
			'order_line_id' => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'product_id'    => array( 'required' => true, 'updated' => false, 'format' => '%d' ),
		),
		'lengow_order_error' => array(
			'id'              => array( 'required' => false, 'updated' => false, 'format' => '%d' ),
			'order_lengow_id' => array( 'required' => true, 'updated' => false, 'format' => '%d' ),
			'message'         => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'type'            => array( 'required' => true, 'updated' => false, 'format' => '%d' ),
			'is_finished'     => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'mail'            => array( 'required' => false, 'updated' => true, 'format' => '%d' ),
			'created_at'      => array( 'required' => true, 'updated' => false, 'format' => '%s' ),
			'updated_at'      => array( 'required' => false, 'updated' => true, 'format' => '%s' ),
		),
	);

	/**
	 * Create a record.
	 *
	 * @param string $table table name
	 * @param array $data data to insert
	 *
	 * @return boolean
	 */
	public static function create( $table, $data ) {
		global $wpdb;

		if ( ! array_key_exists( $table, self::$lengow_tables ) ) {
			return false;
		}
		$field_list = self::$lengow_tables[ $table ];
		foreach ( $field_list as $key => $value ) {
			if ( ! array_key_exists( $key, $data ) && $value['required'] ) {
				return false;
			}
		}
		$formats = array();
		foreach ( $data as $key => $value ) {
			$formats[] = $field_list[ $key ]['format'];
		}
		$result = $wpdb->insert( $wpdb->prefix . $table, $data, $formats );

		return $result ? true : false;
	}

	/**
	 * Read a record.
	 *
	 * @param string $table table name
	 * @param array $where a named array of WHERE clauses
	 * @param boolean $single get a single result or not
	 *
	 * @return false|object[]|object
	 */
	public static function read( $table, $where = array(), $single = true ) {
		global $wpdb;

		if ( ! array_key_exists( $table, self::$lengow_tables ) ) {
			return false;
		}
		$args  = array();
		$query = 'SELECT * FROM ' . $wpdb->prefix . $table;
		if ( ! empty( $where ) ) {
			$conditions = array();
			$field_list = self::$lengow_tables[ $table ];
			foreach ( $where as $key => $value ) {
				$args[]       = $value;
				$conditions[] = $key . ' = ' . $field_list[ $key ]['format'];
			}
			if ( ! empty( $conditions ) ) {
				$query .= ' WHERE ' . join( ' AND ', $conditions );
			}
		}
		$prepare_query = empty( $args ) ? $query : $wpdb->prepare( $query, $args );
		$result        = $single ? $wpdb->get_row( $prepare_query ) : $wpdb->get_results( $prepare_query );

		return $result ? $result : false;
	}

	/**
	 * Update a record.
	 *
	 * @param string $table table name
	 * @param array $data data to update
	 * @param array $where a named array of WHERE clauses
	 *
	 * @return boolean
	 */
	public static function update( $table, $data, $where ) {
		global $wpdb;

		if ( ! array_key_exists( $table, self::$lengow_tables ) ) {
			return false;
		}
		$formats       = array();
		$where_formats = array();
		$updated_data  = array();
		$field_list    = self::$lengow_tables[ $table ];
		foreach ( $field_list as $key => $value ) {
			if ( $value['updated'] && isset( $data[ $key ] ) ) {
				$updated_data[ $key ] = $data[ $key ];
				$formats[]            = $value['format'];
			}
		}
		foreach ( $where as $key => $value ) {
			$where_formats[] = $field_list[ $key ]['format'];
		}
		$result = $wpdb->update( $wpdb->prefix . $table, $updated_data, $where, $formats, $where_formats );

		return $result ? true : false;
	}

	/**
	 * Delete a record.
	 *
	 * @param string $table table name
	 * @param array $where a named array of WHERE clauses
	 *
	 * @return boolean
	 */
	public static function delete( $table, $where ) {
		global $wpdb;

		if ( ! array_key_exists( $table, self::$lengow_tables ) ) {
			return false;
		}
		$where_formats = array();
		$field_list    = self::$lengow_tables[ $table ];
		foreach ( $where as $key => $value ) {
			$where_formats[] = $field_list[ $key ]['format'];
		}
		$result = $wpdb->delete( $wpdb->prefix . $table, $where, $where_formats );

		return $result ? true : false;
	}
}
