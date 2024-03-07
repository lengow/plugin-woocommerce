<?php
/**
 * Admin order setting page
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
 * Lengow_Admin_Order_Settings Class.
 */
class Lengow_Admin_Order_Settings {

	/**
	 * Display admin order setting page.
	 */
	public static function display() {
		$locale = new Lengow_Translation();
		include_once 'views/html-admin-header-order.php';
		include_once 'views/order-settings/html-admin-order-settings.php';
	}

	/**
	 * Process Post Parameters.
	 */
	public static function post_process() {
		$action = null;
		if ( $_POST ) {
			$action = sanitize_text_field( $_POST['action'] );
		} elseif ( isset( $_GET['action'] ) ) {
			$action = sanitize_text_field( $_GET['action'] );
		}
		switch ( $action ) {
			case 'process':
				foreach ( $_POST as $key => $value ) {
					if ( 'on' === $value ) {
						$value = 1;
					}
					if ( Lengow_Configuration::get( $key ) != $value ) {
						Lengow_Configuration::check_and_log( $key, $value );
						Lengow_Configuration::update_value( $key, $value );
					}
				}
				break;
		}
	}
}
