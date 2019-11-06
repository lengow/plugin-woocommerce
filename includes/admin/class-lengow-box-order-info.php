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
 * Lengow_Box_Order_Info Class.
 */
class Lengow_Box_Order_Info {

	/**
	 * Display Lengow Box Order infos.
	 *
	 * @param WP_Post $post Wordpress Post instance
	 */
	public static function html_display( $post ) {
		try {
			$order_lengow_id = Lengow_Order::get_id_from_order_id($post->ID);
			$order_lengow = New Lengow_Order($order_lengow_id);
			$action_type = $order_lengow->order_lengow_state == Lengow_Order::STATE_CANCELED
				? 'cancel'
				: 'ship';
			$locale = new Lengow_Translation();
			include_once( 'views/box-order-info/html-order-info.php' );
		} catch ( Exception $e ) {
			echo Lengow_Main::decode_log_message( $e->getMessage() );
		}
	}

	public function post_process() {
		$action = isset( $_POST['do_action'] ) ? $_POST['do_action'] : false;
		if ( $action ) {
			switch ( $action ) {
				case 'resend_ship':
					echo json_encode('success');
			}
		exit();
		}
	}
}
