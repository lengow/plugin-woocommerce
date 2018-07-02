<?php
/**
 * Update 2.0.1
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
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  upgrade
 * @author      Team module <team-module@lengow.com>
 * @copyright   2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) || ! Lengow_Install::is_installation_in_progress() ) {
	exit;
}

// *********************************************************
//                         lengow_product
// *********************************************************

if ( Lengow_Install::check_table_exists( 'lengow_product' ) ) {
	if ( ! Lengow_Install::check_index_exists( 'lengow_product', 'product_id' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'lengow_product ADD INDEX(`product_id`)' );
	}
}

// *********************************************************
//                         lengow_orders
// *********************************************************

if ( Lengow_Install::check_table_exists( 'lengow_orders' ) ) {
	if ( ! Lengow_Install::check_index_exists( 'lengow_orders', 'id_order' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'lengow_orders ADD INDEX(`id_order`)' );
	}
}
