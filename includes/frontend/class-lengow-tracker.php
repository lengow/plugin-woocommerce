<?php
/**
 * Lengow Tracker
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
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Tracker Class.
 */
class Lengow_Tracker {

	/**
	 * Display Lengow tracker.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 */
	public static function html_display( $order ) {
		try {
			$account_id   = Lengow_Configuration::get( 'lengow_account_id' );
			$tracking_id  = Lengow_Configuration::get( 'lengow_tracking_id' );
			$product_cart = array();
			foreach ( $order->get_items() as $item ) {
				$product = Lengow_Product::get_product( $item->get_product_id() );
				if ( 'sku' === $tracking_id ) {
					$product_id = $product->get_sku();
				} else {
					$product_id = 'variation' === Lengow_Product::get_product_type( $product )
						? $item->get_product_id() . '_' . $item->get_variation_id()
						: $item->get_product_id();
				}
				$product_cart[] = array(
					'product_id' => $product_id,
					'price'      => (float) $item->get_total() + (float) $item->get_total_tax(),
					'quantity'   => (int) $item->get_quantity(),
				);
			}
			$tracker = array(
				'account_id'     => $account_id,
				'order_ref'      => Lengow_Order::get_order_id( $order ),
				'amount'         => (float) $order->get_total(),
				'currency'       => $order->get_currency(),
				'payment_method' => $order->get_payment_method(),
				'cart'           => htmlspecialchars( json_encode( $product_cart ) ),
				'cart_number'    => 0,
				'newbiz'         => 1,
				'valid'          => 1,
			);

			include_once( 'views/tracker/html-tracker.php' );
		} catch ( Exception $e ) {
			echo '';
		}
	}
}