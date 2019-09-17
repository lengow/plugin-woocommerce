<?php
/**
 * Update 2.2.0
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
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2019 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) || ! Lengow_Install::is_installation_in_progress() ) {
	exit;
}

// *********************************************************
//                         lengow_orders
// *********************************************************

if ( Lengow_Install::check_table_exists( 'lengow_orders' ) ) {
	$table_name = $wpdb->prefix . 'lengow_orders';
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'id_order' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' CHANGE `id_order` `order_id` INTEGER(11) UNSIGNED NULL' );
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'id_flux' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' CHANGE `id_flux` `flux_id` INTEGER(11) UNSIGNED NULL' );
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'tracking' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' CHANGE `tracking` `carrier_tracking` VARCHAR(100) COLLATE utf8_unicode_ci NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'delivery_country_iso' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `delivery_country_iso` VARCHAR(3) COLLATE utf8_unicode_ci NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'marketplace_label' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `marketplace_label` VARCHAR(100) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'order_lengow_state' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `order_lengow_state` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'order_process_state' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `order_process_state` INTEGER(11) UNSIGNED NOT NULL' );
		$wpdb->query( 'UPDATE ' . $table_name . ' SET `order_process_state` = 2' );
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'order_item' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `order_item` INTEGER(11) UNSIGNED NULL' );
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'currency' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `currency` VARCHAR(3) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'commission' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `commission` DECIMAL(17,2) UNSIGNED NULL' );
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'customer_name' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `customer_name` VARCHAR(255) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'customer_email' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `customer_email` VARCHAR(255) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'carrier_method' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `carrier_method` VARCHAR(100) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'carrier_id_relay' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `carrier_id_relay` VARCHAR(100) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'sent_marketplace' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `sent_marketplace` TINYINT(1) NOT NULL DEFAULT 0' );
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'is_in_error' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `is_in_error` TINYINT(1) NOT NULL DEFAULT 0' );
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'is_reimported' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `is_reimported` TINYINT(1) NOT NULL DEFAULT 0' );
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'updated_at' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `updated_at` DATETIME NULL DEFAULT NULL' );
	}
	if ( ! Lengow_Install::check_index_exists( 'lengow_orders', 'order_id' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'lengow_orders ADD INDEX(`order_id`)' );
	}
	if ( ! Lengow_Install::check_index_exists( 'lengow_orders', 'flux_id' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'lengow_orders ADD INDEX(`flux_id`)' );
	}
	if ( ! Lengow_Install::check_index_exists( 'lengow_orders', 'marketplace_sku' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'lengow_orders ADD INDEX(`marketplace_sku`)' );
	}
	if ( ! Lengow_Install::check_index_exists( 'lengow_orders', 'marketplace_name' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'lengow_orders ADD INDEX(`marketplace_name`)' );
	}
}
