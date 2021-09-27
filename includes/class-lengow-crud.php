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
 * Lengow_Crud Class.
 */
class Lengow_Crud {

	/* Field database actions */
	const FIELD_REQUIRED = 'required';
	const FIELD_CAN_BE_UPDATED = 'updated';
	const FIELD_FORMAT = 'format';

	/* Field format types */
	const FORMAT_STRING = '%s';
	const FORMAT_INTEGER = '%d';
	const FORMAT_FLOAT = '%f';

	/**
	 * @var array $field_list field list.
	 * required => Required fields when creating registration
	 * update   => Fields allowed when updating registration
	 * format   => Format required in database (%s => string, %d => integer or %f => float)
	 */
	public static $lengow_tables = array(
		Lengow_Action::TABLE_ACTION           => array(
			Lengow_Action::FIELD_ID             => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Action::FIELD_ORDER_ID       => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Action::FIELD_ACTION_ID      => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Action::FIELD_ORDER_LINE_SKU => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Action::FIELD_ACTION_TYPE    => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Action::FIELD_RETRY          => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Action::FIELD_PARAMETERS     => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Action::FIELD_STATE          => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Action::FIELD_CREATED_AT     => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Action::FIELD_UPDATED_AT     => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
		),
		Lengow_Product::TABLE_PRODUCT         => array(
			Lengow_Product::FIELD_ID         => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Product::FIELD_PRODUCT_ID => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
		),
		Lengow_Order::TABLE_ORDER             => array(
			Lengow_Order::FIELD_ID                   => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_ORDER_ID             => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_FEED_ID              => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_DELIVERY_ADDRESS_ID  => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_DELIVERY_COUNTRY_ISO => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_MARKETPLACE_SKU      => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_MARKETPLACE_NAME     => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_MARKETPLACE_LABEL    => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_ORDER_LENGOW_STATE   => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_ORDER_PROCESS_STATE  => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_ORDER_DATE           => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_ORDER_ITEM           => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_ORDER_TYPES          => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_CURRENCY             => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_TOTAL_PAID           => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_FLOAT,
			),
			Lengow_Order::FIELD_COMMISSION           => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_FLOAT,
			),
			Lengow_Order::FIELD_CUSTOMER_NAME        => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_CUSTOMER_EMAIL       => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_CUSTOMER_VAT_NUMBER  => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_CARRIER              => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_CARRIER_METHOD       => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_CARRIER_TRACKING     => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_CARRIER_RELAY_ID     => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_SENT_MARKETPLACE     => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_IS_IN_ERROR          => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_IS_REIMPORTED        => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order::FIELD_MESSAGE              => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_CREATED_AT           => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_UPDATED_AT           => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order::FIELD_EXTRA                => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
		),
		Lengow_Order_Line::TABLE_ORDER_LINE   => array(
			Lengow_Order_Line::FIELD_ID            => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order_Line::FIELD_ORDER_ID      => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order_Line::FIELD_ORDER_LINE_ID => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order_Line::FIELD_PRODUCT_ID    => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
		),
		Lengow_Order_Error::TABLE_ORDER_ERROR => array(
			Lengow_Order_Error::FIELD_ID              => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order_Error::FIELD_ORDER_LENGOW_ID => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order_Error::FIELD_MESSAGE         => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order_Error::FIELD_TYPE            => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order_Error::FIELD_IS_FINISHED     => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order_Error::FIELD_MAIL            => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_INTEGER,
			),
			Lengow_Order_Error::FIELD_CREATED_AT      => array(
				self::FIELD_REQUIRED       => true,
				self::FIELD_CAN_BE_UPDATED => false,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
			Lengow_Order_Error::FIELD_UPDATED_AT      => array(
				self::FIELD_REQUIRED       => false,
				self::FIELD_CAN_BE_UPDATED => true,
				self::FIELD_FORMAT         => self::FORMAT_STRING,
			),
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
			if ( ! array_key_exists( $key, $data ) && $value[ self::FIELD_REQUIRED ] ) {
				return false;
			}
		}
		$formats = array();
		foreach ( $data as $key => $value ) {
			$formats[] = $field_list[ $key ][ self::FIELD_FORMAT ];
		}
		$result = $wpdb->insert( $wpdb->prefix . $table, $data, $formats );

		return (bool) $result;
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
				$conditions[] = $key . ' = ' . $field_list[ $key ][ self::FIELD_FORMAT ];
			}
			if ( ! empty( $conditions ) ) {
				$query .= ' WHERE ' . join( ' AND ', $conditions );
			}
		}
		$prepare_query = empty( $args ) ? $query : $wpdb->prepare( $query, $args );
		if ( $single ) {
			$result = $wpdb->get_row( $prepare_query );
			$return = $result ?: false;
		} else {
			$result = $wpdb->get_results( $prepare_query );
			$return = $result ?: array();
		}

		return $return;
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
			if ( $value[ self::FIELD_CAN_BE_UPDATED ] && isset( $data[ $key ] ) ) {
				$updated_data[ $key ] = $data[ $key ];
				$formats[]            = $value[ self::FIELD_FORMAT ];
			}
		}
		foreach ( $where as $key => $value ) {
			$where_formats[] = $field_list[ $key ][ self::FIELD_FORMAT ];
		}
		$result = $wpdb->update( $wpdb->prefix . $table, $updated_data, $where, $formats, $where_formats );

		return (bool) $result;
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
			$where_formats[] = $field_list[ $key ][ self::FIELD_FORMAT ];
		}
		$result = $wpdb->delete( $wpdb->prefix . $table, $where, $where_formats );

		return (bool) $result;
	}
}
