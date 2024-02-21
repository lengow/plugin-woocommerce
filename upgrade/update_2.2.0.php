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
 * @copyright   2019 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) || ! Lengow_Install::is_installation_in_progress() ) {
	exit;
}

// *********************************************************
// lengow_orders
// *********************************************************

$table = Lengow_Order::TABLE_ORDER;
if ( Lengow_Install::check_table_exists( $table ) ) {
	$table_name = $wpdb->prefix . $table;
	if ( Lengow_Install::check_field_exists( $table, 'id_order' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' CHANGE `id_order` `order_id` INTEGER(11) UNSIGNED NULL' );
	}
	if ( Lengow_Install::check_field_exists( $table, 'id_flux' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' CHANGE `id_flux` `feed_id` INTEGER(11) UNSIGNED NULL' );
	}
	if ( Lengow_Install::check_field_exists( $table, 'tracking' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' CHANGE `tracking` `carrier_tracking` VARCHAR(100) COLLATE utf8_unicode_ci NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_DELIVERY_COUNTRY_ISO ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `delivery_country_iso` VARCHAR(3) COLLATE utf8_unicode_ci NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_MARKETPLACE_LABEL ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `marketplace_label` VARCHAR(100) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_ORDER_LENGOW_STATE ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `order_lengow_state` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_ORDER_PROCESS_STATE ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `order_process_state` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0'
		);
		$wpdb->query( 'UPDATE ' . $table_name . ' SET `order_process_state` = 2' );
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_ORDER_ITEM ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `order_item` INTEGER(11) UNSIGNED NULL' );
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_CURRENCY ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `currency` VARCHAR(3) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_COMMISSION ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `commission` DECIMAL(17,2) UNSIGNED NULL' );
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_CUSTOMER_NAME ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `customer_name` VARCHAR(255) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_CUSTOMER_EMAIL ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `customer_email` VARCHAR(255) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_CARRIER_METHOD ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `carrier_method` VARCHAR(100) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_CARRIER_RELAY_ID ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' ADD `carrier_id_relay` VARCHAR(100) COLLATE utf8_unicode_ci NULL DEFAULT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_SENT_MARKETPLACE ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `sent_marketplace` TINYINT(1) NOT NULL DEFAULT 0' );
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_IS_IN_ERROR ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `is_in_error` TINYINT(1) NOT NULL DEFAULT 1' );
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_IS_REIMPORTED ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `is_reimported` TINYINT(1) NOT NULL DEFAULT 0' );
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_UPDATED_AT ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `updated_at` DATETIME NULL DEFAULT NULL' );
	}
	if ( ! Lengow_Install::check_index_exists( $table, Lengow_Order::FIELD_ORDER_ID ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD INDEX(`order_id`)' );
	}
	if ( ! Lengow_Install::check_index_exists( $table, Lengow_Order::FIELD_FEED_ID ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD INDEX(`feed_id`)' );
	}
	if ( ! Lengow_Install::check_index_exists( $table, Lengow_Order::FIELD_MARKETPLACE_SKU ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD INDEX(`marketplace_sku`)' );
	}
	if ( ! Lengow_Install::check_index_exists( $table, Lengow_Order::FIELD_MARKETPLACE_NAME ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD INDEX(`marketplace_name`)' );
	}
}
