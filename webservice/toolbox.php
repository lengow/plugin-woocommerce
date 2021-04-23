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
 * at your option) any later version.
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
 * string action toolbox specific action
 * string type   type of data to display
 * string date   date of the log to export
 */

@set_time_limit( 0 );
@ini_set( 'memory_limit', '512M' );

// init wordpress.
require( dirname( dirname( dirname( dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ) ) ) ) ) . '/wp-load.php' );

// dependencies.
require_once( '../includes/class-lengow-crud.php' );
require_once( '../includes/class-lengow-export.php' );
require_once( '../includes/class-lengow-feed.php' );
require_once( '../includes/class-lengow-file.php' );
require_once( '../includes/class-lengow-import.php' );
require_once( '../includes/class-lengow-log.php' );
require_once( '../includes/class-lengow-order.php' );
require_once( '../includes/class-lengow-toolbox.php' );

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
	default:
		$type = isset( $_GET[ Lengow_Toolbox::PARAM_TYPE ] ) ? $_GET[ Lengow_Toolbox::PARAM_TYPE ] : null;
		echo json_encode( Lengow_Toolbox::get_data( $type ) );
		break;
}
