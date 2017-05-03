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
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team module <team-module@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
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
	 * Installation of module.
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook().
	 */
	public static function install() {
		Lengow_Install::update();
	}

	/**
	 * Update process from previous versions.
	 *
	 * @return boolean
	 */
	public static function update() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		self::set_installation_status( true );
		$upgrade_files = array_diff( scandir( LENGOW_PLUGIN_PATH . '/upgrade' ), array( '..', '.' ) );
		foreach ( $upgrade_files as $file ) {
			$number_version = preg_replace( '/update_|\.php$/', '', $file );
			if ( version_compare( get_option( 'lengow_version' ), $number_version, '>=' ) ) {
				continue;
			}
			include LENGOW_PLUGIN_PATH . '/upgrade/' . $file;
		}
		if ( isset( $number_version ) ) {
			Lengow_Configuration::update_value( 'lengow_version', $number_version );
		}
		self::set_installation_status( false );

		return true;
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
		$sql    = 'SHOW COLUMNS FROM ' . $wpdb->prefix . $table . ' LIKE \'' . $field . '\'';
		$result = $wpdb->get_results( $sql );
		$exists = count( $result ) > 0 ? true : false;

		return $exists;
	}

	/**
	 * Checks if a field exists in BDD and Dropped It.
	 *
	 * @param string $table Lengow table
	 * @param string $field Lengow field
	 */
	public static function check_field_and_drop( $table, $field ) {
		global $wpdb;
		if ( self::check_field_exists( $table, $field ) ) {
			$wpdb->query(
				'ALTER TABLE ' . $wpdb->prefix . $table . ' DROP COLUMN `' . $field . '`'
			);
		}
	}

	/**
	 * Rename configuration key.
	 *
	 * @param string $oldName old Lengow configuration name
	 * @param string $newName new Lengow configuration name
	 */
	public static function rename_configuration_key( $oldName, $newName ) {
		$tempValue = Lengow_Configuration::get( $oldName );
		Lengow_Configuration::update_value( $newName, $tempValue );
		Lengow_Configuration::delete( $oldName );
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
