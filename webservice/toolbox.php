<?php
/**
 * Toolbox webservice
 *
 * Copyright 2021 Lengow SAS
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
 * @subpackage  webservice
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2021 Lengow SAS
 */

/**
 * List params
 * string  toolbox_action   Toolbox specific action
 * string  type             Type of data to display
 * string  created_from     Synchronization of orders since
 * string  created_to       Synchronization of orders until
 * string  date             Log date to download
 * string  marketplace_name Lengow marketplace name to synchronize
 * string  marketplace_sku  Lengow marketplace order id to synchronize
 * string  process          Type of process for order action
 * boolean force            Force synchronization order even if there are errors (1) or not (0)
 * integer shop_id          Shop id to synchronize
 * integer days             Synchronization interval time
 */

@set_time_limit( 0 );
@ini_set( 'memory_limit', '512M' );

// init WordPress.
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
$token = isset( $_GET[ Lengow_Toolbox::PARAM_TOKEN ] ) ? $_GET[ Lengow_Toolbox::PARAM_TOKEN ] : '';

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

// check if toolbox action is valid.
$action = isset( $_GET[ Lengow_Toolbox::PARAM_TOOLBOX_ACTION ] )
	? $_GET[ Lengow_Toolbox::PARAM_TOOLBOX_ACTION ]
	: Lengow_Toolbox::ACTION_DATA;
if ( ! in_array( $action, Lengow_Toolbox::$toolbox_actions, true ) ) {
	wp_die( 'Action: ' . $action . ' is not a valid action', '', array( 'response' => 400 ) );
}

switch ( $action ) {
	case Lengow_Toolbox::ACTION_LOG:
		$date = isset( $_GET[ Lengow_Toolbox::PARAM_DATE ] ) ? $_GET[ Lengow_Toolbox::PARAM_DATE ] : null;
		Lengow_Toolbox::download_log( $date );
		break;
	case Lengow_Toolbox::ACTION_ORDER:
		$result = Lengow_Toolbox::sync_orders(
			array(
				Lengow_Toolbox::PARAM_CREATED_TO       => isset( $_GET[ Lengow_Toolbox::PARAM_CREATED_TO ] )
					? $_GET[ Lengow_Toolbox::PARAM_CREATED_TO ]
					: null,
				Lengow_Toolbox::PARAM_CREATED_FROM     => isset( $_GET[ Lengow_Toolbox::PARAM_CREATED_FROM ] )
					? $_GET[ Lengow_Toolbox::PARAM_CREATED_FROM ]
					: null,
				Lengow_Toolbox::PARAM_DAYS             => isset( $_GET[ Lengow_Toolbox::PARAM_DAYS ] )
					? $_GET[ Lengow_Toolbox::PARAM_DAYS ]
					: null,
				Lengow_Toolbox::PARAM_FORCE            => isset( $_GET[ Lengow_Toolbox::PARAM_FORCE ] )
					? $_GET[ Lengow_Toolbox::PARAM_FORCE ]
					: null,
				Lengow_Toolbox::PARAM_MARKETPLACE_NAME => isset( $_GET[ Lengow_Toolbox::PARAM_MARKETPLACE_NAME ] )
					? $_GET[ Lengow_Toolbox::PARAM_MARKETPLACE_NAME ]
					: null,
				Lengow_Toolbox::PARAM_MARKETPLACE_SKU  => isset( $_GET[ Lengow_Toolbox::PARAM_MARKETPLACE_SKU ] )
					? $_GET[ Lengow_Toolbox::PARAM_MARKETPLACE_SKU ]
					: null,
			)
		);
		if ( isset( $result[ Lengow_Toolbox::ERRORS ][ Lengow_Toolbox::ERROR_CODE ] ) ) {
			if ( $result[ Lengow_Toolbox::ERRORS ][ Lengow_Toolbox::ERROR_CODE ] === Lengow_Connector::CODE_404 ) {
				header( 'HTTP/1.1 404 Not Found' );
			} else {
				header( 'HTTP/1.1 403 Forbidden' );
			}
		}
		echo json_encode( $result );
		break;
	default:
		$type = isset( $_GET[ Lengow_Toolbox::PARAM_TYPE ] ) ? $_GET[ Lengow_Toolbox::PARAM_TYPE ] : null;
		echo json_encode( Lengow_Toolbox::get_data( $type ) );
		break;
}
