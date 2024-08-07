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
 * (at your option) any later version.
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
		// @TODO if $merchant_status is false ?
		if ( false !== $merchant_status && 'free_trial' === $merchant_status['type'] && $merchant_status['expired'] ) {
			$refresh_status = admin_url( 'admin.php?action=dashboard_get_process&do_action=refresh_status' );
			include_once 'views/dashboard/html-admin-status.php';
		} else {
			$plugin_data         = Lengow_Sync::get_plugin_data();
			$plugin_links        = Lengow_Sync::get_plugin_links( get_locale() );
			$total_pending_order = Lengow_Order::count_order_to_be_sent();
			include_once 'views/dashboard/html-admin-dashboard.php';
		}
	}

	/**
	 * Process Get Parameters.
	 */
	public static function get_process() {
		$action = isset( $_GET['do_action'] ) ? sanitize_text_field( $_GET['do_action'] ) : false;
		if ( $action ) {
			switch ( $action ) {
				case 'refresh_status':
					Lengow_Sync::get_status_account( true );
					wp_redirect( admin_url( 'admin.php?page=lengow&tab=lengow_admin_dashboard' ) );
					break;
			}
			exit();
		}
	}

	/**
	 * Process Post Parameters.
	 */
	public static function post_process() {
		$data   = array();
		$action = isset( $_POST['do_action'] ) ? sanitize_text_field( $_POST['do_action'] ) : false;
		if ( $action ) {
			switch ( $action ) {
				case 'remind_me_later':
					$timestamp = time() + ( 7 * 86400 );
					Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_PLUGIN_MODAL, $timestamp );
					$data['success'] = true;
					break;
				default:
					$data['success'] = false;
					break;
			}
			echo wp_json_encode( $data );
			exit();
		}
	}
}
