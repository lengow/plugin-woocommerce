<?php
/**
 * Cron webservice
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
 * @category   	lengow
 * @package    	lengow-woocommerce
 * @subpackage 	webservice
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
 */

/**
 * List params
 * string  sync                Number of products exported
 * integer days                Import period
 * integer limit               Number of orders to import
 * string  $marketplace_sku    Lengow marketplace order id to import
 * string  marketplace_name    Lengow marketplace name to import
 * integer delivery_address_id Lengow delivery address id to import
 * boolean preprod_mode        Activate preprod mode
 * boolean log_output          See logs (1) or not (0)
 * boolean get_sync            See synchronisation parameters in json format (1) or not (0)
 */

@set_time_limit( 0 );
@ini_set( 'memory_limit', '512M' );

// Init wordpress
require( dirname( dirname( dirname( dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ) ) ) ) ) . '/wp-load.php' );

// Dependencies
require_once( '../includes/class-lengow-main.php' );
require_once( '../includes/class-lengow-sync.php' );
require_once( '../includes/class-lengow-export.php' );
require_once( '../includes/class-lengow-import.php' );
require_once( '../includes/class-lengow-import-order.php' );
require_once( '../includes/class-lengow-connector.php' );
require_once( '../includes/class-lengow-marketplace.php' );
require_once( '../includes/class-lengow-order.php' );
require_once( '../includes/class-lengow-product.php' );
require_once( '../includes/class-lengow-file.php' );
require_once( '../includes/class-lengow-feed.php' );
require_once( '../includes/class-lengow-log.php' );
require_once( '../includes/class-lengow-translation.php' );
require_once( '../includes/class-lengow-configuration.php' );
require_once( '../includes/class-lengow-check.php' );
require_once( '../includes/class-lengow-exception.php' );

// check if WooCommerce plugin is activated.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	wp_die( 'WooCommerce plugin is not active', '', array( 'response' => 400 ) );
}

// check if Lengow plugin is activated.
if ( ! in_array(
	'lengow-woocommerce/lengow.php',
	apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
)
) {
	wp_die( 'Lengow plugin is not active', '', array( 'response' => 400 ) );
}

// check IP.
if ( ! Lengow_Main::check_ip() ) {
	wp_die( 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'], '', array( 'response' => 403 ) );
}

if (isset( $_GET['get_sync'] ) && $_GET['get_sync'] == 1) {
	echo json_encode( Lengow_Sync::get_sync_data() );
} else {
	// get sync action if exists.
	$sync = isset( $_GET['sync'] ) ? $_GET['sync'] : false;

	// sync orders between Lengow and WooCommerce.
	if ( ! $sync || $sync === 'order' ) {
		// array of params for import order
		$params = array( 'type' => 'cron' );
		if ( isset( $_GET['preprod_mode'] ) ) {
			$params['preprod_mode'] = (bool) $_GET['preprod_mode'];
		}
		if ( isset( $_GET['log_output'] ) ) {
			$params['log_output'] = (bool) $_GET['log_output'];
		}
		if ( isset( $_GET['days'] ) ) {
			$params['days'] = (int) $_GET['days'];
		}
		if ( isset( $_GET['limit'] ) ) {
			$params['limit'] = (int) $_GET['limit'];
		}
		if ( isset( $_GET['marketplace_sku'] ) ) {
			$params['marketplace_sku'] = (string) $_GET['marketplace_sku'];
		}
		if ( isset( $_GET['marketplace_name'] ) ) {
			$params['marketplace_name'] = (string) $_GET['marketplace_name'];
		}
		if ( isset( $_GET['delivery_address_id'] ) ) {
			$params['delivery_address_id'] = (int) $_GET['delivery_address_id'];
		}
		$import = new Lengow_Import( $params );
		$import->exec();
	}
	// sync options between Lengow and WooCommerce.
	if ( ! $sync || $sync === 'option' ) {
		Lengow_Sync::set_cms_option();
	}
	// sync option is not valid.
	if ( $sync && ( $sync !== 'order' && $sync !== 'option' ) ) {
		wp_die( 'Action: ' . $sync . ' is not a valid action', '', array( 'response' => 400 ) );
	}
}
