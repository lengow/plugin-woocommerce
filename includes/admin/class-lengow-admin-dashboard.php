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
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 * 
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category   	Lengow
 * @package    	lengow-woocommerce
 * @subpackage 	includes
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
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
		$keys            = Lengow_Configuration::get_keys();
		$locale          = new Lengow_Translation();
		$stats           = Lengow_Sync::get_statistic();
		$merchant_status = Lengow_Sync::get_status_account();
		$is_new_merchant = Lengow_Main::is_new_merchant();
		$is_sync         = isset( $_GET['isSync'] ) ? $_GET['isSync'] : false;
		$locale_iso_code = strtolower(substr(get_locale(), 0, 2));

		$refresh_status = admin_url( 'admin.php?action=dashboard_get_process&do_action=refresh_status' );

		if ( $is_new_merchant || $is_sync ) {
			include_once 'views/dashboard/html-admin-new.php';
		} elseif ( ( $merchant_status['type'] == 'free_trial' && $merchant_status['day'] <= 0 )
		           || $merchant_status['type'] == 'bad_payer'
		) {
			include_once 'views/dashboard/html-admin-status.php';
		} else {
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
