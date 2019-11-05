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
			$account_id     = Lengow_Configuration::get( 'lengow_account_id' );
			$order_ref      = Lengow_Order::get_order_id( $order );
			$amount         = (float) $order->get_total();
			$currency       = self::_get_currency( $order );
			$payment_method = self::_get_payment_method( $order );
			$cart           = htmlspecialchars( json_encode( self::_get_product_cart( $order->get_items() ) ) );
			$cart_number    = 0;
			$newbiz         = 1;
			$valid          = 1;
			include_once( 'views/tracker/html-tracker.php' );
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
	private static function _get_product_cart( $items ) {
		$tracking_id  = Lengow_Configuration::get( 'lengow_tracking_id' );
		$product_cart = array();
		foreach ( $items as $item ) {
			if ( Lengow_Main::compare_version( '3.0' ) ) {
				$product  = Lengow_Product::get_product( $item->get_product_id() );
				$price    = wc_get_price_including_tax( $product );
				$quantity = (int) $item->get_quantity();
			} else {
				$product  = Lengow_Product::get_product( $item['product_id'] );
				$price    = (float) $product->get_price_including_tax();
				$quantity = (int) $item['qty'];
			}
			if ( 'sku' === $tracking_id ) {
				$product_id = $product->get_sku();
			} else {
				$product_id = 'variation' === Lengow_Product::get_product_type( $product )
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
	private static function _get_currency( $order ) {
		if ( Lengow_Main::compare_version( '3.0' ) ) {
			$currency = $order->get_currency();
		} else {
			$currency = get_post_meta( Lengow_Order::get_order_id( $order ), '_order_currency', true );
		}

		return $currency;
	}

	/**
	 * Get payment method for old WooCommerce versions.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 *
	 * @return string
	 */
	private static function _get_payment_method( $order ) {
		if ( Lengow_Main::compare_version( '3.0' ) ) {
			$payment_method = $order->get_payment_method();
		} else {
			$payment_method = get_post_meta( Lengow_Order::get_order_id( $order ), '_payment_method', true );
		}

		return $payment_method;
	}
}