<?php
/**
 * Installation related functions and actions.
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
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Install Class.
 */
class Lengow_Install {

	/**
	 * @var boolean installation status.
	 */
	public static $installation_status;

	/**
	 * @var string old version for update scripts.
	 */
	public static $old_version;

	/**
	 * @var array old configuration keys to remove.
	 */
	public static $old_configuration_keys = array(
		'lengow_export_attributes',
		'lengow_export_meta',
		'lengow_export_full_title',
		'lengow_export_images',
		'lengow_export_image_size',
		'lengow_export_cron',
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
		'lengow_import_enabled',
		'lengow_time_import_start',
		'LENGOW_MP_CONF',
		'lengow_order_statistic',
		'lengow_last_order_statistic_update',
		'lengow_preprod_enabled',
	);

	/**
	 * Installation of module.
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook().
	 */
	public static function install() {
		Lengow_Main::log(
			Lengow_Log::CODE_INSTALL,
			Lengow_Main::set_log_message( 'log.install.install_start', array( 'version' => LENGOW_VERSION ) )
		);
		$old_version = Lengow_Configuration::get( 'lengow_version' );
		$old_version = $old_version ?: false;
		$old_version = LENGOW_VERSION === $old_version ? false : $old_version;
		self::update( $old_version );
		Lengow_Main::log(
			Lengow_Log::CODE_INSTALL,
			Lengow_Main::set_log_message( 'log.install.install_end', array( 'version' => LENGOW_VERSION ) )
		);
	}

	/**
	 * Update process from previous versions.
	 *
	 * @param boolean|string $old_version old version for update
	 *
	 * @return boolean
	 */
	public static function update( $old_version = false ) {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		if ( $old_version ) {
			self::$old_version = $old_version;
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message(
					'log.install.update_start',
					array( 'old_version' => $old_version, 'new_version' => LENGOW_VERSION )
				)
			);
		}
		// check if update is in progress.
		self::set_installation_status( true );
		// create all Lengow tables.
		self::create_lengow_tables();
		// run sql script and configuration upgrade for specific version.
		$upgrade_files = array_diff( scandir( LENGOW_PLUGIN_PATH . '/upgrade' ), array( '..', '.' ) );
		foreach ( $upgrade_files as $file ) {
			include LENGOW_PLUGIN_PATH . '/upgrade/' . $file;
			$number_version = preg_replace( '/update_|\.php$/', '', $file );
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.add_upgrade_version', array( 'version' => $number_version ) )
			);
		}
		// delete old configuration.
		self::remove_old_configuration_keys();
		// set default value for old version.
		self::set_default_values();
		// update lengow version.
		if ( isset( $number_version ) ) {
			Lengow_Configuration::update_value( 'lengow_version', LENGOW_VERSION );
		}
		self::set_installation_status( false );
		if ( $old_version ) {
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message(
					'log.install.update_end',
					array( 'old_version' => $old_version, 'new_version' => LENGOW_VERSION )
				)
			);
		}

		return true;
	}

	/**
	 * Add Lengow tables.
	 */
	public static function create_lengow_tables() {
		global $wpdb;
		// create table lengow_product.
		$name = Lengow_Product::TABLE_PRODUCT;
		if ( ! self::check_table_exists( $name ) ) {
			$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . $name . ' (
				`id` INTEGER(11) NOT NULL AUTO_INCREMENT,
				`product_id` BIGINT(20) NOT NULL,
				PRIMARY KEY (`id`),
				INDEX (`product_id`)
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			dbDelta( $sql );
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_created', array( 'name' => $name ) )
			);
		} else {
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_already_created', array( 'name' => $name ) )
			);
		}

		// create table lengow_orders.
		$name = Lengow_Order::TABLE_ORDER;
		if ( ! self::check_table_exists( $name ) ) {
			$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . $name . ' (
				`id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				`order_id` INTEGER(11) UNSIGNED NULL,
				`feed_id` INTEGER(11) UNSIGNED NULL,
				`delivery_address_id` INTEGER(11) NOT NULL,
				`delivery_country_iso` VARCHAR(3) NULL,
				`marketplace_sku` VARCHAR(100) NOT NULL,
				`marketplace_name` VARCHAR(100) NOT NULL,
				`marketplace_label` VARCHAR(100) NULL DEFAULT NULL,
                `order_lengow_state` VARCHAR(100) NOT NULL,
                `order_process_state` INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
				`order_date` DATETIME NOT NULL,
				`order_item` INTEGER(11) UNSIGNED NULL,
				`order_types` TEXT NULL DEFAULT NULL,
                `currency` VARCHAR(3) NULL DEFAULT NULL,
                `customer_vat_number` VARCHAR(100) NULL DEFAULT NULL,
                `total_paid` DECIMAL(17,2) UNSIGNED NULL,
				`commission` DECIMAL(17,2) UNSIGNED NULL,
				`customer_name` VARCHAR(255) NULL DEFAULT NULL,
                `customer_email` VARCHAR(255) NULL DEFAULT NULL,
				`carrier` VARCHAR(100) NULL DEFAULT NULL,
				`carrier_method` VARCHAR(100) NULL DEFAULT NULL,
				`carrier_tracking` VARCHAR(100) NULL DEFAULT NULL,
				`carrier_id_relay` VARCHAR(100) NULL DEFAULT NULL,
				`sent_marketplace` TINYINT(1) NOT NULL DEFAULT 0,
				`is_in_error` TINYINT(1) NOT NULL DEFAULT 1,
				`is_reimported` TINYINT(1) NOT NULL DEFAULT 0,
				`message` TEXT NULL DEFAULT NULL,
				`created_at` DATETIME NOT NULL,
				`updated_at` DATETIME NULL DEFAULT NULL,
				`extra` LONGTEXT NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				INDEX (`order_id`),
				INDEX (`feed_id`),
                INDEX (`marketplace_sku`),
                INDEX (`marketplace_name`)
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			dbDelta( $sql );
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_created', array( 'name' => $name ) )
			);
		} else {
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_already_created', array( 'name' => $name ) )
			);
		}

		// create table lengow_order_line.
		$name = Lengow_Order_Line::TABLE_ORDER_LINE;
		if ( ! self::check_table_exists( $name ) ) {
			$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . $name . ' (
				`id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` INTEGER(11) UNSIGNED NOT NULL,
                `order_line_id` VARCHAR(100) NOT NULL,
                `product_id` INTEGER(11) UNSIGNED NOT NULL,
                PRIMARY KEY(`id`),
                INDEX (`order_id`)
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			dbDelta( $sql );
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_created', array( 'name' => $name ) )
			);
		} else {
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_already_created', array( 'name' => $name ) )
			);
		}

		// create table lengow_order_error.
		$name = Lengow_Order_Error::TABLE_ORDER_ERROR;
		if ( ! self::check_table_exists( $name ) ) {
			$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . $name . ' (
				`id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_lengow_id` INTEGER(11) UNSIGNED NOT NULL,
                `message` TEXT NULL DEFAULT NULL,
                `type` INTEGER(11) UNSIGNED NOT NULL,
                `is_finished` TINYINT(1) NOT NULL DEFAULT 0,
                `mail` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL,
				`updated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY(`id`),
                INDEX (`order_lengow_id`)
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			dbDelta( $sql );
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_created', array( 'name' => $name ) )
			);
		} else {
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_already_created', array( 'name' => $name ) )
			);
		}

		// create table lengow_action.
		$name = Lengow_Action::TABLE_ACTION;
		if ( ! self::check_table_exists( $name ) ) {
			$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . $name . ' (
				`id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                `order_id` INTEGER(11) UNSIGNED NOT NULL,
                `action_id` INTEGER(11) UNSIGNED NOT NULL,
                `order_line_sku` VARCHAR(100) NULL DEFAULT NULL,
                `action_type` VARCHAR(32) NOT NULL,
                `retry` SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
                `parameters` TEXT NOT NULL,
                `state` SMALLINT(5) UNSIGNED NOT NULL,
                `created_at` DATETIME NOT NULL,
				`updated_at` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY(`id`),
                INDEX (`order_id`)
				) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			dbDelta( $sql );
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_created', array( 'name' => $name ) )
			);
		} else {
			Lengow_Main::log(
				Lengow_Log::CODE_INSTALL,
				Lengow_Main::set_log_message( 'log.install.table_already_created', array( 'name' => $name ) )
			);
		}
	}

	/**
	 * Set default value for Lengow configuration.
	 *
	 * @return boolean
	 */
	public static function set_default_values() {
		return Lengow_Configuration::reset_all();
	}

	/**
	 * Checks if a table exists in BDD.
	 *
	 * @param string $table Lengow table
	 *
	 * @return boolean
	 */
	public static function check_table_exists( $table ) {
		global $wpdb;

		return (bool) $wpdb->get_var( 'SHOW TABLES LIKE \'' . $wpdb->prefix . $table . '\'' );
	}

	/**
	 * Checks if a field exists in database.
	 *
	 * @param string $table Lengow table
	 * @param string $field Lengow field
	 *
	 * @return boolean
	 */
	public static function check_field_exists( $table, $field ) {
		global $wpdb;
		$result = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $wpdb->prefix . $table . ' LIKE \'' . $field . '\'' );

		return ! empty( $result );
	}

	/**
	 * Checks if a field exists in BDD and dropped it.
	 *
	 * @param string $table Lengow table
	 * @param string $field Lengow field
	 */
	public static function check_field_and_drop( $table, $field ) {
		global $wpdb;
		if ( self::check_field_exists( $table, $field ) ) {
			$wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . $table . ' DROP COLUMN `' . $field . '`' );
		}
	}

	/**
	 * Checks if index exists in table.
	 *
	 * @param string $table Lengow table
	 * @param string $index Lengow index
	 *
	 * @return boolean
	 */
	public static function check_index_exists( $table, $index ) {
		global $wpdb;
		$result = $wpdb->get_results(
			'SHOW INDEXES FROM ' . $wpdb->prefix . $table . ' WHERE `Key_name` = \'' . $index . '\''
		);

		return ! empty( $result );
	}

	/**
	 * Checks if an index exists in BDD and dropped it.
	 *
	 * @param string $table Lengow table
	 * @param string $index Lengow index
	 */
	public static function check_index_and_drop( $table, $index ) {
		global $wpdb;
		if ( self::check_index_exists( $table, $index ) ) {
			$wpdb->query( 'DROP INDEX ' . $index . ' ON ' . $wpdb->prefix . $table );
		}
	}

	/**
	 * Delete old configuration keys.
	 */
	public static function remove_old_configuration_keys() {
		foreach ( self::$old_configuration_keys as $configuration ) {
			Lengow_Configuration::delete( $configuration );
		}
	}

	/**
	 * Rename configuration key.
	 *
	 * @param string $old_name old Lengow configuration name
	 * @param string $new_name new Lengow configuration name
	 */
	public static function rename_configuration_key( $old_name, $new_name ) {
		$temp_value = Lengow_Configuration::get( $old_name );
		if ( false !== $temp_value ) {
			Lengow_Configuration::update_value( $new_name, $temp_value );
			Lengow_Configuration::delete( $old_name );
		}
	}

	/**
	 * Set Installation Status.
	 *
	 * @param boolean $status Installation Status
	 */
	public static function set_installation_status( $status ) {
		self::$installation_status = $status;
	}

	/**
	 * Is Installation In Progress.
	 *
	 * @return boolean
	 */
	public static function is_installation_in_progress() {
		return self::$installation_status;
	}
}
