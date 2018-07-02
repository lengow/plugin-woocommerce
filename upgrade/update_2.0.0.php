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

// alter product table for old versions
if ( Lengow_Install::check_table_exists( 'lengow_product' ) ) {
	$table_name = $wpdb->prefix . 'lengow_product';
	if ( ! Lengow_Install::check_field_exists( 'lengow_product', 'id' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
		);
		// for first install, delete unique key in product_id field
		Lengow_Install::check_index_and_drop( 'lengow_product', 'product_id' );
	}
}

// *********************************************************
//                         lengow_orders
// *********************************************************

// alter order table for old versions
if ( Lengow_Install::check_table_exists( 'lengow_orders' ) ) {
	$table_name = $wpdb->prefix . 'lengow_orders';
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'id' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY' );
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'created_at' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `created_at` datetime NOT NULL' );
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'id_order_lengow' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' CHANGE `id_order_lengow` `marketplace_sku` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
		);
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'marketplace' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' CHANGE `marketplace` `marketplace_name` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
		);
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'extra' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' MODIFY `extra` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'delivery_address_id' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `delivery_address_id` INTEGER(11) NOT NULL' );
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'order_date' ) ) {
		$wpdb->query( 'ALTER TABLE ' . $table_name . ' ADD `order_date` DATETIME NOT NULL' );
		$wpdb->query( 'UPDATE ' . $table_name . ' SET `order_date` = `date_add`' );
	}
	// Keep and change old columns
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'id_flux' ) ) {
		$wpdb->query( 'ALTER TABLE  ' . $table_name . ' CHANGE `id_flux` `id_flux` INTEGER(11) UNSIGNED NULL' );
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'id_order' ) ) {
		$wpdb->query( 'ALTER TABLE  ' . $table_name . ' CHANGE `id_order` `id_order` INTEGER(11) UNSIGNED NULL' );
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'total_paid' ) ) {
		$wpdb->query( 'ALTER TABLE  ' . $table_name . ' CHANGE `total_paid` `total_paid` DECIMAL(17,2) UNSIGNED NULL' );
	}
}
// Drop old column from lengow_orders table
Lengow_Install::check_field_and_drop( 'lengow_orders', 'date_add' );
Lengow_Install::check_index_and_drop( 'lengow_orders', 'id_order_lengow' );
Lengow_Install::check_index_and_drop( 'lengow_orders', 'marketplace' );
Lengow_Install::check_index_and_drop( 'lengow_orders', 'id_flux' );

// *********************************************************
//                  Other install process
// *********************************************************

if ( Lengow_Install::$old_version && Lengow_Install::$old_version < '2.0.0' ) {
	// Migrate specific settings for new version
	Lengow_Configuration::migrate_product_selection();
	Lengow_Configuration::migrate_product_types();
	Lengow_Configuration::check_ip_authorization();
	// Rename old settings
	Lengow_Install::rename_configuration_key( 'lengow_export_file', 'lengow_export_file_enabled' );
	Lengow_Install::rename_configuration_key( 'lengow_debug', 'lengow_preprod_enabled' );
	Lengow_Install::rename_configuration_key( 'is_import_processing', 'lengow_import_in_progress' );
}
