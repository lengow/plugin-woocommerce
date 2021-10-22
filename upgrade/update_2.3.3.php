<?php
/**
 * Update 2.2.3
 *
 * Copyright 2020 Lengow SAS
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
 * @copyright   2020 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) || ! Lengow_Install::is_installation_in_progress() ) {
	exit;
}

$table = Lengow_Order::TABLE_ORDER;
if ( Lengow_Install::check_table_exists( $table ) ) {
	$table_name = $wpdb->prefix . $table;
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_CUSTOMER_VAT_NUMBER ) ) {
		$wpdb->query(
			'ALTER TABLE '
			. $table_name
			. ' ADD `customer_vat_number` VARCHAR(100) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
}