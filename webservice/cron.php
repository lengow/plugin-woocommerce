<?php
/**
 * Copyright 2016 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/**
 * list params
 * string  sync                Number of products exported
 * integer days                Import period
 * integer limit               Number of orders to import
 * string  $marketplace_sku    Lengow marketplace order id to import
 * string  marketplace_name    Lengow marketplace name to import
 * integer delivery_address_id Lengow delivery address id to import
 * boolean preprod_mode        Activate preprod mode
 * boolean log_output          See logs (1) or not (0)
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
