<?php
/**
 * All function to manage order line
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
 * Lengow_Order_Line Class.
 */
class Lengow_Order_Line {

	/**
	 * Create Lengow order line.
	 *
	 * @param array $data Lengow order line data
	 *
	 * @return boolean
	 *
	 */
	public static function create( $data = array() ) {
		return Lengow_Crud::create( Lengow_Crud::LENGOW_ORDER_LINE, $data );
	}

	/**
	 * Get all order line ids by WooCommerce order id.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param string $output Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *
	 * @return array|false
	 */
	public static function get_all_order_line_id_by_order_id( $order_id, $output = OBJECT ) {
		global $wpdb;

		$query   = '
			SELECT order_line_id FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER_LINE . '
			WHERE order_id = %d
		';
		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $order_id ) ),
			$output
		);

		return $results ? $results : false;
	}
}
