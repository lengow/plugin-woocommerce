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
			$account_id     = Lengow_Configuration::get( Lengow_Configuration::ACCOUNT_ID );
			$order_ref      = $order->get_id();
			$amount         = (float) $order->get_total();
			$currency       = self::get_currency( $order );
			$payment_method = self::get_payment_method( $order );
			$cart           = htmlspecialchars( wp_json_encode( self::get_product_cart( $order->get_items() ) ) );
			$cart_number    = 0;
			$newbiz         = 1;
			$valid          = 1;
			include_once 'views/tracker/html-tracker.php';
		} catch ( Exception $e ) {
			echo '';
		}
	}

	/**
	 * Get product cart with id, price and quantity foreach product.
	 *
	 * @param array $items WooCommerce order items
	 *
	 * @return array
	 */
	private static function get_product_cart( $items ) {
		$tracking_id  = Lengow_Configuration::get( Lengow_Configuration::TRACKING_ID );
		$product_cart = array();
		foreach ( $items as $item ) {
			$product  = wc_get_product( $item->get_product_id() );
			$price    = wc_get_price_including_tax( $product );
			$quantity = (int) $item->get_quantity();
			if ( 'sku' === $tracking_id ) {
				$product_id = $product->get_sku();
			} else {
				$product_id = 'variation' === $product->get_type()
					? Lengow_Product::get_product_id( $product ) . '_' . Lengow_Product::get_variation_id( $product )
					: Lengow_Product::get_product_id( $product );
			}
			$product_cart[] = array(
				'product_id' => $product_id,
				'price'      => $price,
				'quantity'   => $quantity,
			);
		}

		return $product_cart;
	}

	/**
	 * Get currency for old WooCommerce versions.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 *
	 * @return string
	 */
	private static function get_currency( $order ) {
		return $order->get_currency();
	}

	/**
	 * Get payment method for old WooCommerce versions.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 *
	 * @return string
	 */
	private static function get_payment_method( $order ) {
		return $order->get_payment_method();
	}
}
