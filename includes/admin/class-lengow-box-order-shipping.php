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
 * Lengow_Box_Order_Shipping Class.
 */
class Lengow_Box_Order_Shipping {

	/**
	 * Display Lengow Box Order infos.
	 *
	 * @param WP_Post $post Wordpress Post instance
	 */
	public static function html_display( $post ) {
		try {
			$order_lengow_id = Lengow_Order::get_id_from_order_id( $post->ID );
			$order_lengow    = new Lengow_Order( $order_lengow_id );
			// compatibility v2.
			if ( ! Lengow_Marketplace::marketplace_exist( $order_lengow->marketplace_name )
			     && null !== $order_lengow->feed_id
			) {
				$order_lengow->check_and_change_marketplace_name();
			}
			$marketplace = Lengow_Main::get_marketplace_singleton( $order_lengow->marketplace_name );
			wp_nonce_field( 'lengow_woocommerce_custom_box', 'lengow_woocommerce_custom_box_nonce' );
			include_once( 'views/box-order-shipping/html-order-shipping.php' );
		} catch ( Exception $e ) {
			echo Lengow_Main::decode_log_message( $e->getMessage() );
		}
	}
}
