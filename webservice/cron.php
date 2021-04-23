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
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  webservice
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 */

/**
 * List params
 * string  sync                Number of products exported
 * integer days                Import period
 * integer limit               Number of orders to import
 * string  marketplace_sku     Lengow marketplace order id to import
 * string  marketplace_name    Lengow marketplace name to import
 * string  created_from        import of orders since
 * string  created_to          import of orders until
 * integer delivery_address_id Lengow delivery address id to import
 * boolean force               Force synchronisation for a specific process
 * boolean debug_mode          Activate debug mode
 * boolean log_output          See logs (1) or not (0)
 * boolean get_sync            See synchronisation parameters in json format (1) or not (0)
 */

@set_time_limit( 0 );
@ini_set( 'memory_limit', '512M' );

// init wordpress.
require( dirname( dirname( dirname( dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ) ) ) ) ) . '/wp-load.php' );

// dependencies.
require_once( '../includes/class-lengow-action.php' );
require_once( '../includes/class-lengow-address.php' );
require_once( '../includes/class-lengow-configuration.php' );
require_once( '../includes/class-lengow-connector.php' );
require_once( '../includes/class-lengow-crud.php' );
require_once( '../includes/class-lengow-exception.php' );
require_once( '../includes/class-lengow-export.php' );
require_once( '../includes/class-lengow-feed.php' );
require_once( '../includes/class-lengow-file.php' );
require_once( '../includes/class-lengow-hook.php' );
require_once( '../includes/class-lengow-import.php' );
require_once( '../includes/class-lengow-import-order.php' );
require_once( '../includes/class-lengow-log.php' );
require_once( '../includes/class-lengow-main.php' );
require_once( '../includes/class-lengow-marketplace.php' );
require_once( '../includes/class-lengow-order.php' );
require_once( '../includes/class-lengow-order-error.php' );
require_once( '../includes/class-lengow-order-line.php' );
require_once( '../includes/class-lengow-product.php' );
require_once( '../includes/class-lengow-sync.php' );
require_once( '../includes/class-lengow-toolbox.php' );
require_once( '../includes/class-lengow-translation.php' );
require_once( '../includes/class-lengow-toolbox-element.php' );

// check if WooCommerce plugin is activated.
$woocommercePlugin = 'woocommerce/woocommerce.php';
if ( ! in_array( $woocommercePlugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	wp_die( 'WooCommerce plugin is not active', '', array( 'response' => 400 ) );
}

// check if Lengow plugin is activated.
$lengowPlugin = 'lengow-woocommerce/lengow.php';
if ( ! in_array( $lengowPlugin, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	wp_die( 'Lengow plugin is not active', '', array( 'response' => 400 ) );
}

// get token for authorisation.
$token = isset( $_GET[ Lengow_Import::PARAM_TOKEN ] ) ? $_GET[ Lengow_Import::PARAM_TOKEN ] : '';

// check webservices access.
if ( ! Lengow_Main::check_webservice_access( $token ) ) {
	if ( Lengow_Configuration::get( Lengow_Configuration::AUTHORIZED_IP_ENABLED ) ) {
		$errorMessage = 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'];
	} else {
		$errorMessage = strlen( $token ) > 0
			? 'unauthorised access for this token: ' . $token
			: 'unauthorised access: token parameter is empty';
	}
	wp_die( $errorMessage, '', array( 'response' => 403 ) );
}

if ( isset( $_GET[ Lengow_Import::PARAM_GET_SYNC ] ) && 1 == $_GET[ Lengow_Import::PARAM_GET_SYNC ] ) {
	echo json_encode( Lengow_Sync::get_sync_data() );
} else {
	$force      = isset( $_GET[ Lengow_Import::PARAM_FORCE ] ) && $_GET[ Lengow_Import::PARAM_FORCE ];
	$log_output = isset( $_GET[ Lengow_Import::PARAM_LOG_OUTPUT ] ) && $_GET[ Lengow_Import::PARAM_LOG_OUTPUT ];
	// get sync action if exists.
	$sync = isset( $_GET[ Lengow_Import::PARAM_SYNC ] ) ? $_GET[ Lengow_Import::PARAM_SYNC ] : false;
	// sync catalogs id between Lengow and WooCommerce.
	if ( ! $sync || Lengow_Sync::SYNC_CATALOG === $sync ) {
		Lengow_Sync::sync_catalog( $force, $log_output );
	}
	// sync orders between Lengow and WooCommerce.
	if ( ! $sync || Lengow_Sync::SYNC_ORDER === $sync ) {
		// array of params for import order.
		$params = array(
			Lengow_Import::PARAM_TYPE       => Lengow_Import::TYPE_CRON,
			Lengow_Import::PARAM_LOG_OUTPUT => $log_output,
		);
		if ( isset( $_GET[ Lengow_Import::PARAM_DEBUG_MODE ] ) ) {
			$params[ Lengow_Import::PARAM_DEBUG_MODE ] = (bool) $_GET[ Lengow_Import::PARAM_DEBUG_MODE ];
		}
		if ( isset( $_GET[ Lengow_Import::PARAM_DAYS ] ) ) {
			$params[ Lengow_Import::PARAM_DAYS ] = (int) $_GET[ Lengow_Import::PARAM_DAYS ];
		}
		if ( isset( $_GET[ Lengow_Import::PARAM_CREATED_FROM ] ) ) {
			$params[ Lengow_Import::PARAM_CREATED_FROM ] = (string) $_GET[ Lengow_Import::PARAM_CREATED_FROM ];
		}
		if ( isset( $_GET[ Lengow_Import::PARAM_CREATED_TO ] ) ) {
			$params[ Lengow_Import::PARAM_CREATED_TO ] = (string) $_GET[ Lengow_Import::PARAM_CREATED_TO ];
		}
		if ( isset( $_GET[ Lengow_Import::PARAM_LIMIT ] ) ) {
			$params[ Lengow_Import::PARAM_LIMIT ] = (int) $_GET[ Lengow_Import::PARAM_LIMIT ];
		}
		if ( isset( $_GET[ Lengow_Import::PARAM_MARKETPLACE_SKU ] ) ) {
			$params[ Lengow_Import::PARAM_MARKETPLACE_SKU ] = (string) $_GET[ Lengow_Import::PARAM_MARKETPLACE_SKU ];
		}
		if ( isset( $_GET[ Lengow_Import::PARAM_MARKETPLACE_NAME ] ) ) {
			$params[ Lengow_Import::PARAM_MARKETPLACE_NAME ] = (string) $_GET[ Lengow_Import::PARAM_MARKETPLACE_NAME ];
		}
		if ( isset( $_GET[ Lengow_Import::PARAM_DELIVERY_ADDRESS_ID ] ) ) {
			$params[ Lengow_Import::PARAM_DELIVERY_ADDRESS_ID ] = (int) $_GET[ Lengow_Import::PARAM_DELIVERY_ADDRESS_ID ];
		}
		$import = new Lengow_Import( $params );
		$import->exec();
	}
	// sync actions between Lengow and WooCommerce.
	if ( ! $sync || Lengow_Sync::SYNC_ACTION === $sync ) {
		Lengow_Action::check_finish_action( $log_output );
		Lengow_Action::check_old_action( $log_output );
		Lengow_Action::check_action_not_sent( $log_output );
	}
	// sync options between Lengow and WooCommerce.
	if ( ! $sync || Lengow_Sync::SYNC_CMS_OPTION === $sync ) {
		Lengow_Sync::set_cms_option( $force, $log_output );
	}
	// sync marketplaces between Lengow and WooCommerce.
	if ( Lengow_Sync::SYNC_MARKETPLACE === $sync ) {
		Lengow_Sync::get_marketplaces( $force, $log_output );
	}
	// sync status account between Lengow and WooCommerce.
	if ( Lengow_Sync::SYNC_STATUS_ACCOUNT === $sync ) {
		Lengow_Sync::get_status_account( $force, $log_output );
	}
	// sync plugin data between Lengow and WooCommerce.
	if ( Lengow_Sync::SYNC_PLUGIN_DATA === $sync ) {
		Lengow_Sync::get_plugin_data( $force, $log_output );
	}
	// sync option is not valid.
	if ( $sync && ! in_array( $sync, Lengow_Sync::$sync_actions, true ) ) {
		wp_die( 'Action: ' . $sync . ' is not a valid action', '', array( 'response' => 400 ) );
	}
}
