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
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  upgrade
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) || ! Lengow_Install::is_installation_in_progress() ) {
	exit;
}

// *********************************************************
//                         lengow_product
// *********************************************************

$table = Lengow_Product::TABLE_PRODUCT;
if ( Lengow_Install::check_table_exists( $table ) ) {
	if ( ! Lengow_Install::check_index_exists( $table, Lengow_Product::FIELD_PRODUCT_ID ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . $table . ' ADD INDEX(`product_id`)' );
	}
}
