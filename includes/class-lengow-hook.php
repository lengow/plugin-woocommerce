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
 * Dependencies for front office.
 */
require_once 'class-lengow-configuration.php';
require_once 'class-lengow-main.php';
require_once 'class-lengow-product.php';
require_once 'class-lengow-order.php';

/**
 * Lengow_Hook Class.
 */
class Lengow_Hook {

	// tax rate to apply to b2b orders
	const B2B_RATES = 'Zero Rate';

	/**
	 * Add meta box for orders created by Lengow.
	 *
	 * @param WC_Order $wc_order
	 */
	public static function adding_shop_order_meta_boxes( $wc_order ) {

		if ( $wc_order && Lengow_Order::get_id_from_order_id( (int) $wc_order->get_id() ) ) {

			$locale = new Lengow_Translation();

			add_meta_box(
				'lengow-order-infos',
				$locale->t( 'meta_box.order_info.box_title' ),
				array( 'Lengow_Box_Order_Info', 'html_display' ),
				'woocommerce_page_wc-orders',
				'normal'
			);
			add_meta_box(
				'lengow-shipping-infos',
				$locale->t( 'meta_box.order_shipping.box_title' ),
				array( 'Lengow_Box_Order_Shipping', 'html_display' ),
				'woocommerce_page_wc-orders',
				'side',
				'high'
			);
		}
	}

	/**
	 * Add meta box for orders created by Lengow.
	 * Compatibility mode if woocommerce is using old storing engine with WP_Post.
	 * see option woocommerce_custom_orders_table_enabled.
	 */
	public static function adding_shop_order_meta_boxes_compat() {
		$post = get_post();
		if ( empty( $post->ID ) ) {
			return;
		}

		$lengow_order = Lengow_Order::get_id_from_order_id( $post->ID );
		if ( ! $lengow_order ) {
			return;
		}

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
			'side',
			'default'
		);
	}

	/**
	 * Disable all customer mails if order came from Lengow.
	 *
	 * @param WC_Emails $email_class WooCommerce email instance
	 */
	public static function unhook_woocommerce_mail( $email_class ) {
		global $post;

		if ( $post && Lengow_Order::get_id_from_order_id( $post->ID ) ) {
			remove_action(
				'woocommerce_order_status_pending_to_processing_notification',
				array( &$email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' )
			);
			remove_action(
				'woocommerce_order_status_pending_to_on-hold_notification',
				array( &$email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' )
			);
			remove_action(
				'woocommerce_order_status_completed_notification',
				array( &$email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' )
			);
		}
	}

	/**
	 * Update status on Lengow.
	 *
	 * @param integer $wc_order_id woocommerce order id
	 *
	 * @return integer|false
	 */
	public static function save_lengow_shipping( $wc_order_id ) {

		try {
			$wc_order = new WC_Order( $wc_order_id );
		} catch ( \Exception $e ) {
			return false;
		}
		$order_lengow_id = Lengow_Order::get_id_from_order_id( $wc_order->get_id() );
		if ( $order_lengow_id ) {
			// check if our nonce is set.
			if ( ! isset( $_POST['lengow_woocommerce_custom_box_nonce'] ) ) {
				return $wc_order_id;
			}
			$nonce = sanitize_text_field( $_POST['lengow_woocommerce_custom_box_nonce'] );
			// verify that the nonce is valid.
			if ( ! wp_verify_nonce( $nonce, 'lengow_woocommerce_custom_box' ) ) {
				return $wc_order_id;
			}
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return $wc_order_id;
			}
			// check the user's permissions.
			if ( ! current_user_can( 'edit_post', $wc_order_id ) ) {
				return $wc_order_id;
			}
			// get new WooCommerce order status.
			$order_status = sanitize_text_field( $_POST['order_status'] );
			// get new Lengow shipping data.
			$carrier                = isset( $_POST['lengow_carrier'] )
				? sanitize_text_field( $_POST['lengow_carrier'] )
				: '';
			$custom_carrier         = isset( $_POST['lengow_custom_carrier'] )
				? sanitize_text_field( $_POST['lengow_custom_carrier'] )
				: '';
			$tracking_number        = isset( $_POST['lengow_tracking_number'] )
				? sanitize_text_field( $_POST['lengow_tracking_number'] )
				: '';
			$tracking_url           = isset( $_POST['lengow_tracking_url'] )
				? sanitize_text_field( $_POST['lengow_tracking_url'] )
				: '';
			$return_carrier         = isset( $_POST['lengow_return_carrier'] )
				? sanitize_text_field( $_POST['lengow_return_carrier'] )
				: '';
			$return_tracking_number = isset( $_POST['lengow_return_tracking_number'] )
				? sanitize_text_field( $_POST['lengow_return_tracking_number'] )
				: '';
			// save Lengow shipping data.

			$wc_order_carrier                = $wc_order->get_meta( '_lengow_carrier', true );
			$wc_order_custom_carrier         = $wc_order->get_meta( '_lengow_custom_carrier', true );
			$wc_order_return_carrier         = $wc_order->get_meta( '_lengow_return_carrier', true );
			$wc_order_return_tracking_number = $wc_order->get_meta( '_lengow_return_tracking_number', true );

			$need_save = false;
			if ( $carrier && ( $wc_order_carrier !== $carrier ) ) {
				$wc_order->update_meta_data( '_lengow_carrier', $carrier );
				$wc_order->update_meta_data( '_lengow_tracking_number', $tracking_number );
				$wc_order->update_meta_data( '_lengow_tracking_url', $tracking_url );
				$need_save = true;
			}

			if ( $custom_carrier && ( $wc_order_custom_carrier !== $custom_carrier ) ) {
				$wc_order->update_meta_data( '_lengow_custom_carrier', $custom_carrier );
				$wc_order->update_meta_data( '_lengow_tracking_number', $tracking_number );
				$wc_order->update_meta_data( '_lengow_tracking_url', $tracking_url );
				$need_save = true;
			}

			if ( $return_carrier && $wc_order_return_carrier !== $return_carrier ) {
				$wc_order->update_meta_data( '_lengow_return_carrier', $return_carrier );
				$need_save = true;
			}

			if ( $return_tracking_number && $wc_order_return_tracking_number !== $return_tracking_number ) {
				$wc_order->update_meta_data( '_lengow_return_tracking_number', $return_tracking_number );
				$need_save = true;
			}

			if ( $need_save ) {
				$wc_order->save();
			}

			$order_lengow = new Lengow_Order( $order_lengow_id );

			// do nothing if the order is closed or an action is being processed.
			if ( ! $order_lengow->is_closed() && ! $order_lengow->has_an_action_in_progress() ) {
				// sending an API call for sending or canceling an order.
				if ( $order_status === Lengow_Order::get_order_state( Lengow_Order::STATE_SHIPPED ) ) {
					$order_lengow->call_action( Lengow_Action::TYPE_SHIP );
				} elseif ( $order_status === Lengow_Order::get_order_state( Lengow_Order::STATE_CANCELED ) ) {
					$order_lengow->call_action( Lengow_Action::TYPE_CANCEL );
				}
			}
		}

		return $wc_order_id;
	}

	/**
	 * Switch woocommerce tax class for Lengow b2b orders
	 *
	 * @param $tax_class string Magento tax class
	 * @param $product WC_Product Magento product
	 *
	 * @return string
	 */
	public static function switch_product_tax_class_for_b2b( $tax_class, $product ) {
		return self::B2B_RATES;
	}
}
