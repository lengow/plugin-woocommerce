<?php
/**
 * Admin dashboard page
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Admin_Dashboard Class.
 */
class Lengow_Admin_Dashboard {

	/**
	 * Display dashboard page.
	 */
	public static function display() {
		$locale          = new Lengow_Translation();
		$merchant_status = Lengow_Sync::get_status_account();
		$is_new_merchant = Lengow_Configuration::is_new_merchant();
		$is_sync         = isset( $_GET['isSync'] ) ? $_GET['isSync'] : false;
		if ( $is_new_merchant || $is_sync ) {
			$locale_iso_code = strtolower( substr( get_locale(), 0, 2 ) );
			include_once 'views/dashboard/html-admin-new.php';
		} elseif ( 'free_trial' === $merchant_status['type'] && $merchant_status['expired'] ) {
			$refresh_status = admin_url( 'admin.php?action=dashboard_get_process&do_action=refresh_status' );
			include_once 'views/dashboard/html-admin-status.php';
		} else {
			$plugin_data         = Lengow_Sync::get_plugin_data();
			$total_pending_order = Lengow_Order::get_total_order_by_status( 'waiting_shipment' );
			include_once 'views/dashboard/html-admin-dashboard.php';
		}
	}

	/**
	 * Process Post Parameters.
	 */
	public static function post_process() {
		$action = isset( $_POST['do_action'] ) ? $_POST['do_action'] : false;
		if ( $action ) {
			switch ( $action ) {
				case 'get_sync_data':
					$data               = array();
					$data['function']   = 'sync';
					$data['parameters'] = Lengow_Sync::get_sync_data();
					echo json_encode( $data );
					break;
				case 'sync':
					$data = isset( $_POST['data'] ) ? $_POST['data'] : false;
					Lengow_Sync::sync( $data );
					Lengow_Sync::get_status_account( true );
					break;
			}
			exit();
		}
	}

	/**
	 * Process Get Parameters.
	 */
	public static function get_process() {
		$action = isset( $_GET['do_action'] ) ? $_GET['do_action'] : false;
		if ( $action ) {
			switch ( $action ) {
				case 'refresh_status':
					Lengow_Sync::get_status_account( true );
					wp_redirect( admin_url( 'admin.php?page=lengow' ) );
					break;
			}
			exit();
		}
	}
}
