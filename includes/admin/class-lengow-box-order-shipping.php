<?php
/**
 * WooCommerce Box Order Shipping
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
 * Lengow_Box_Order_Shipping Class.
 */
class Lengow_Box_Order_Shipping {

	/**
	 * Display Lengow Box Order infos.
	 *
	 * @param WC_Order|WP_Post $order Order instance from woocommerce
	 */
	public static function html_display( $order ) {
		try {
			$order_id = null;
			if ( $order instanceof WC_Order ) {
				$order_id = $order->get_id();
			} elseif ( $order instanceof WP_Post ) {
				// retro compatibility
				// see option woocommerce_custom_orders_table_enabled
				$order_id = $order->ID;
			}

			if ( ! $order_id ) {
				return;
			}

			$order_lengow_id = Lengow_Order::get_id_from_order_id( $order_id );
			$order_lengow    = new Lengow_Order( $order_lengow_id );
			// compatibility v2.
			if ( null !== $order_lengow->feed_id
				&& ! Lengow_Marketplace::marketplace_exist( $order_lengow->marketplace_name )
			) {
				$order_lengow->check_and_change_marketplace_name();
			}
			$marketplace = Lengow_Main::get_marketplace_singleton( $order_lengow->marketplace_name );
			wp_nonce_field( 'lengow_woocommerce_custom_box', 'lengow_woocommerce_custom_box_nonce' );
			include_once 'views/box-order-shipping/html-order-shipping.php';
		} catch ( Exception $e ) {
			echo Lengow_Main::decode_log_message( $e->getMessage() );
		}
	}
}
