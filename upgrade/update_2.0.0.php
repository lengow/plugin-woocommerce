<?php
/**
 * Update 2.0.0
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
 * hhttps://www.gnu.org/licenses/gpl-3.0
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
// lengow_product
// *********************************************************

// alter product table for old versions.
$table = Lengow_Product::TABLE_PRODUCT;
if ( Lengow_Install::check_table_exists( $table ) ) {
	$table_name = $wpdb->prefix . $table;
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Product::FIELD_ID ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
		);
		// for first install, delete unique key in product_id field.
		Lengow_Install::check_index_and_drop( $table, Lengow_Product::FIELD_PRODUCT_ID );
	}
}

// *********************************************************
// lengow_orders
// *********************************************************

// alter order table for old versions.
$table = Lengow_Order::TABLE_ORDER;
if ( Lengow_Install::check_table_exists( $table ) ) {
	$table_name = $wpdb->prefix . $table;
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_ID ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY' );
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_CREATED_AT ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `created_at` datetime NOT NULL' );
	}
	if ( Lengow_Install::check_field_exists( $table, 'id_order_lengow' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' CHANGE `id_order_lengow` `marketplace_sku` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
		);
	}
	if ( Lengow_Install::check_field_exists( $table, 'marketplace' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' CHANGE `marketplace` `marketplace_name` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
		);
	}
	if ( Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_CARRIER ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' MODIFY `carrier` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;'
		);
	}
	if ( Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_MESSAGE ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' MODIFY `message` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;'
		);
	}
	if ( Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_EXTRA ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' MODIFY `extra` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;'
		);
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_DELIVERY_ADDRESS_ID ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `delivery_address_id` INTEGER(11) NOT NULL' );
	}
	if ( ! Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_ORDER_DATE ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `order_date` DATETIME NOT NULL' );
		$wpdb->query( 'UPDATE ' . $table_name . ' SET `order_date` = `date_add`' );
	}
	// keep and change old columns.
	if ( Lengow_Install::check_field_exists( $table, 'id_flux' ) ) {
		$wpdb->query( 'ALTER TABLE  ' . $table_name . ' CHANGE `id_flux` `id_flux` INTEGER(11) UNSIGNED NULL' );
	}
	if ( Lengow_Install::check_field_exists( $table, 'id_order' ) ) {
		$wpdb->query( 'ALTER TABLE  ' . $table_name . ' CHANGE `id_order` `id_order` INTEGER(11) UNSIGNED NULL' );
	}
	if ( Lengow_Install::check_field_exists( $table, Lengow_Order::FIELD_TOTAL_PAID ) ) {
		$wpdb->query( 'ALTER TABLE  ' . $table_name . ' CHANGE `total_paid` `total_paid` DECIMAL(17,2) UNSIGNED NULL' );
	}
}
// drop old column from lengow_orders table.
Lengow_Install::check_field_and_drop( $table, 'date_add' );
Lengow_Install::check_index_and_drop( $table, 'id_order_lengow' );
Lengow_Install::check_index_and_drop( $table, 'marketplace' );
Lengow_Install::check_index_and_drop( $table, 'id_order' );
Lengow_Install::check_index_and_drop( $table, 'id_flux' );

// *********************************************************
// Other install process
// *********************************************************

if ( Lengow_Install::$old_version && Lengow_Install::$old_version < '2.0.0' ) {
	// migrate specific settings for new version.
	Lengow_Configuration::migrate_product_selection();
	Lengow_Configuration::migrate_product_types();
	Lengow_Configuration::check_ip_authorization();
	// rename old settings.
	Lengow_Install::rename_configuration_key( 'lengow_export_file', Lengow_Configuration::EXPORT_FILE_ENABLED );
	Lengow_Install::rename_configuration_key( 'lengow_debug', Lengow_Configuration::DEBUG_MODE_ENABLED );
	Lengow_Install::rename_configuration_key(
		'is_import_processing',
		Lengow_Configuration::SYNCHRONIZATION_IN_PROGRESS
	);
}
