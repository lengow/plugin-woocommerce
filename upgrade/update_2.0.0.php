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
 * @category   	Lengow
 * @package    	lengow-woocommerce
 * @subpackage 	upgrade
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) || ! Lengow_Install::is_installation_in_progress() ) {
	exit;
}

$table_name = $wpdb->prefix . 'lengow_product';

if ( $wpdb->get_var( 'SHOW TABLES LIKE \'' . $table_name . '\'' ) ) {
	if ( ! Lengow_Install::check_field_exists( 'lengow_product', 'id' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
		);
	}
}

$sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' (
	`id` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `product_id` bigint(20) NOT NULL,
    PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
dbDelta( $sql );

$table_name = $wpdb->prefix . 'lengow_orders';

// if table lengow_orders exist we update it
if ( $wpdb->get_var( 'SHOW TABLES LIKE \'' . $table_name . '\'' ) ) {
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'id' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' DROP PRIMARY KEY'
		);
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'created_at' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `created_at` datetime NOT NULL'
		);
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'id_order_lengow' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' CHANGE `id_order_lengow` `marketplace_sku` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
		);
		$wpdb->query(
			'DROP INDEX `id_order_lengow` ON ' . $table_name
		);
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'marketplace' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name
			. ' CHANGE `marketplace` `marketplace_name` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
		);
		$wpdb->query(
			'DROP INDEX `marketplace` ON ' . $table_name
		);
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'extra' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' MODIFY `extra` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'delivery_address_id' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `delivery_address_id` INTEGER(11) NOT NULL'
		);
	}
	if ( ! Lengow_Install::check_field_exists( 'lengow_orders', 'order_date' ) ) {
		$wpdb->query(
			'ALTER TABLE ' . $table_name . ' ADD `order_date` DATETIME NOT NULL'
		);
		$wpdb->query(
			'UPDATE ' . $table_name . ' SET `order_date` = `date_add`'
		);
		$wpdb->query(
			'ALTER TABLE  ' . $table_name . ' DROP COLUMN `date_add`'
		);
	}
	// Keep and change old columns
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'id_flux' ) ) {
		$wpdb->query(
			'ALTER TABLE  ' . $table_name . ' CHANGE `id_flux` `id_flux` INTEGER(11) UNSIGNED NULL'
		);
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'id_order' ) ) {
		$wpdb->query(
			'ALTER TABLE  ' . $table_name . ' CHANGE `id_order` `id_order` INTEGER(11) UNSIGNED NULL'
		);
	}
	if ( Lengow_Install::check_field_exists( 'lengow_orders', 'total_paid' ) ) {
		$wpdb->query(
			'ALTER TABLE  ' . $table_name . ' CHANGE `total_paid` `total_paid` DECIMAL(17,2) UNSIGNED NULL'
		);
	}
}

$sql = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' (
	`id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `delivery_address_id` int(11) NOT NULL,
    `marketplace_sku` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
    `marketplace_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
    `order_date` datetime NOT NULL,
    `created_at` datetime NOT NULL,
    `extra` longtext COLLATE utf8_unicode_ci,
    `id_flux` INTEGER(11) UNSIGNED NULL,
    `id_order` INTEGER(11) UNSIGNED NULL,
    `total_paid` DECIMAL(17,2) UNSIGNED NULL,
    `message` TEXT,
    `carrier` VARCHAR(100),
    `tracking` VARCHAR(100),
    PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
dbDelta( $sql );

// Rename old settings
Lengow_Install::rename_configuration_key( 'lengow_debug', 'lengow_preprod_enabled' );
Lengow_Install::rename_configuration_key( 'lengow_export_type', 'lengow_product_types' );
Lengow_Install::rename_configuration_key( 'lengow_export_cron', 'lengow_cron_enabled' );
Lengow_Install::rename_configuration_key( 'is_import_processing', 'lengow_import_in_progress' );

// Add new settings
$keys = Lengow_Configuration::get_keys();
foreach ( $keys as $key => $value ) {
	if ( Lengow_Configuration::get( $key ) ) {
		continue;
	}
	if ( isset( $value['default_value'] ) ) {
		$val = $value['default_value'];
	} else {
		$val = '';
	}
	Lengow_Configuration::add_value( $key, $val );
}

// Delete old settings
$configuration_to_delete = array(
	'lengow_export_format',
	'lengow_export_all_product',
	'lengow_export_attributes',
	'lengow_export_meta',
	'lengow_export_full_title',
	'lengow_export_images',
	'lengow_export_image_size',
	'lengow_export_file',
	'lengow_order_process',
	'lengow_order_shipped',
	'lengow_order_cancel',
	'lengow_method_name',
	'lengow_force_price',
	'lengow_send_admin_mail',
	'lengow_logs_day',
	'lengow_id_user',
	'lengow_id_group',
	'lengow_api_key',
	'lengow_default_carrier',
	'lengow_import_cron',
	'lengow_time_import_start',
);
foreach ( $configuration_to_delete as $config_name ) {
	Lengow_Configuration::delete( $config_name );
}
