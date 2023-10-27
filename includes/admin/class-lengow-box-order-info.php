<?php
/**
 * WooCommerce Box Order Info
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
 * Lengow_Box_Order_Info Class.
 */
class Lengow_Box_Order_Info {

	/**
	 * Display Lengow Box Order infos.
	 *
	 * @param WC_Order $wc_order WC_Order instance from woocommerce
	 */
	public static function html_display( $wc_order ) {
		try {
			$order_lengow_id = Lengow_Order::get_id_from_order_id( $wc_order->get_id() );
			$order_lengow    = new Lengow_Order( $order_lengow_id );
			$can_send_action = $order_lengow->can_resend_action();
			$imported_at     = Lengow_Order::get_date_imported( $order_lengow->order_id );
			$imported_date   = $imported_at ?: $order_lengow->created_at;
			$action_type     = false;
			if ( $can_send_action ) {
				$order        = new WC_Order( $order_lengow->order_id );
				$order_status = Lengow_Order::get_order_status( $order );
				$action_type  = Lengow_Order::get_order_state( Lengow_Order::STATE_CANCELED ) === $order_status
					? 'cancel'
					: 'ship';
			}
			$locale     = new Lengow_Translation();
			$debug_mode = Lengow_Configuration::debug_mode_is_active();
			include_once( 'views/box-order-info/html-order-info.php' );
		} catch ( Exception $e ) {
			echo Lengow_Main::decode_log_message( $e->getMessage() );
		}
	}

	/**
	 * Process for ajax actions.
	 */
	public static function post_process() {
		$data         = array();
		$action       = $_POST['do_action'];
		$order_lengow = new Lengow_Order( (int) $_POST['order_lengow_id'] );
		switch ( $action ) {
			case 'resend_ship':
				$data['success'] = $order_lengow->call_action( Lengow_Action::TYPE_SHIP );
				break;
			case 'resend_cancel':
				$data['success'] = $order_lengow->call_action( Lengow_Action::TYPE_CANCEL );
				break;
			case 'synchronize':
				$data['success'] = $order_lengow->synchronize_order();
				break;
			case 'reimport':
				$new_id_order = $order_lengow->cancel_and_reimport_order();
				if ( $new_id_order ) {
					$data['success'] = $new_id_order;
					$data['url']     = get_edit_post_link( $new_id_order );
				} else {
					$data['success'] = false;
				}
				break;
			default:
				$data['success'] = false;
				break;
		}
		echo json_encode( $data );
		exit();
	}
}
