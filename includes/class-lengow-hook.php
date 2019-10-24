<?php
/**
 * Lengow Hooks.
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
 * Lengow_Hook Class.
 */
class Lengow_Hook {

	/**
	 * Add meta box for orders created by Lengow.
	 *
	 * @param WP_Post $post Wordpress Post instance
	 */
	public static function adding_shop_order_meta_boxes( $post ) {
		if ( Lengow_Order::get_id_from_order_id( (int) $post->ID ) ) {
			$locale = new Lengow_Translation();
			add_meta_box(
				'lengow-order-infos',
				$locale->t( 'meta_box.order_info.box_title' ),
				array( 'Lengow_Box_Order_Info', 'html_display' ),
				'shop_order',
				'normal'
			);
			add_meta_box(
				'lengow-shipping-infos',
				$locale->t( 'meta_box.order_shipping.box_title' ),
				array( 'Lengow_Box_Order_Shipping', 'html_display' ),
				'shop_order',
				'side'
			);
		}
	}

	/**
	 * Update status on Lengow.
	 *
	 * @param integer $post_id Wordpress current post id
	 *
	 * @return integer|false
	 */
	public static function save_lengow_shipping( $post_id ) {
		$order_lengow_id = Lengow_Order::get_id_from_order_id( $post_id );
		if ( $order_lengow_id ) {
			// check if our nonce is set.
			if ( ! isset( $_POST['lengow_woocommerce_custom_box_nonce'] ) ) {
				return $post_id;
			}
			$nonce = $_POST['lengow_woocommerce_custom_box_nonce'];
			// verify that the nonce is valid.
			if ( ! wp_verify_nonce( $nonce, 'lengow_woocommerce_custom_box' ) ) {
				return $post_id;
			}
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $post_id;
			}
			// check the user's permissions.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
			// load WooCommerce order.
			$order = new WC_Order( $post_id );
			// get old WooCommerce order status and Lengow shipping data.
			$old_order_status    = Lengow_Order::get_order_status( $order );
			$old_tracking_number = get_post_meta( $post_id, '_lengow_tracking_number', true );
			$old_carrier         = get_post_meta( $post_id, '_lengow_carrier', true );
			$old_custom_carrier  = get_post_meta( $post_id, '_lengow_custom_carrier', true );
			$old_tracking_url    = get_post_meta( $post_id, '_lengow_tracking_url', true );
			// get new WooCommerce order status.
			$order_status = sanitize_text_field( $_POST['order_status'] );
			// get new Lengow shipping data.
			$carrier         = isset ( $_POST['lengow_carrier'] )
				? sanitize_text_field( $_POST['lengow_carrier'] )
				: '';
			$custom_carrier  = isset ( $_POST['lengow_custom_carrier'] )
				? sanitize_text_field( $_POST['lengow_custom_carrier'] )
				: '';
			$tracking_number = isset ( $_POST['lengow_tracking_number'] )
				? sanitize_text_field( $_POST['lengow_tracking_number'] )
				: '';
			$tracking_url    = isset ( $_POST['lengow_tracking_url'] )
				? sanitize_text_field( $_POST['lengow_tracking_url'] )
				: '';
			// save Lengow shipping data only if they changed.
			$shipping_data_updated = false;
			if ( $carrier !== $old_carrier ) {
				update_post_meta( $post_id, '_lengow_carrier', $carrier );
				$shipping_data_updated = true;
			}
			if ( $custom_carrier !== $old_custom_carrier ) {
				update_post_meta( $post_id, '_lengow_custom_carrier', $custom_carrier );
				$shipping_data_updated = true;
			}
			if ( $tracking_number !== $old_tracking_number ) {
				update_post_meta( $post_id, '_lengow_tracking_number', $tracking_number );
				$shipping_data_updated = true;
			}
			if ( $tracking_url !== $old_tracking_url ) {
				update_post_meta( $post_id, '_lengow_tracking_url', $tracking_url );
				$shipping_data_updated = true;
			}
			// sending an API call for sending or canceling an order.
			$shipped_state  = Lengow_Order::get_order_state( Lengow_Order::STATE_SHIPPED );
			$canceled_state = Lengow_Order::get_order_state( Lengow_Order::STATE_CANCELED );
			if ( $order_status !== $old_order_status
			     || ($shipping_data_updated && $order_status === $shipped_state)
			) {
				$order_lengow = new Lengow_Order( $order_lengow_id );
				if ( $order_status === $shipped_state ) {
					$order_lengow->call_action( Lengow_Action::TYPE_SHIP );
				} elseif ( $order_status === $canceled_state ) {
					$order_lengow->call_action( Lengow_Action::TYPE_CANCEL );
				}
				unset( $order_lengow );
			}
		}

		return $post_id;
	}
}
