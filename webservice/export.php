<?php
/**
 * Export webservice
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
 * @subpackage  webservice
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 */

/**
 * List params
 * string  mode               Number of products exported
 * string  format             Format of exported files ('csv','yaml','xml','json')
 * boolean stream             Stream file (1) or generate a file on server (0)
 * integer offset             Offset of total product
 * integer limit              Limit number of exported product
 * boolean selection          Export product selection (1) or all products (0)
 * boolean out_of_stock       Export out of stock product (1) Export only product in stock (0)
 * string  product_ids        List of product id separate with comma (1,2,3)
 * string  product_types      Product type separate with comma (external,grouped,simple,variable)
 * boolean variation          Export product Variation (1) Export parent product only (0)
 * boolean legacy_fields      Export feed with v2 fields (1) or v3 fields (0)
 * boolean log_output         See logs (1) or not (0)
 * boolean update_export_date Change last export date in data base (1) or not (0)
 * boolean get_params         See export parameters and authorized values in json format (1) or not (0)
 */

@set_time_limit( 0 );
@ini_set( 'memory_limit', '512M' );

// init wordpress.
require( dirname( dirname( dirname( dirname( dirname( $_SERVER['SCRIPT_FILENAME'] ) ) ) ) ) . '/wp-load.php' );

// dependencies
require_once( '../includes/class-lengow-main.php' );
require_once( '../includes/class-lengow-export.php' );
require_once( '../includes/class-lengow-product.php' );
require_once( '../includes/class-lengow-feed.php' );
require_once( '../includes/class-lengow-file.php' );
require_once( '../includes/class-lengow-log.php' );
require_once( '../includes/class-lengow-translation.php' );
require_once( '../includes/class-lengow-configuration.php' );
require_once( '../includes/class-lengow-connector.php' );
require_once( '../includes/class-lengow-exception.php' );
require_once( '../includes/class-lengow-sync.php' );

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

// get token for authorisation
$token = isset( $_GET['token'] ) ? $_GET['token'] : '';

// check webservices access
if ( ! Lengow_Main::check_webservice_access( $token ) ) {
	if ( (bool) Lengow_Configuration::get( 'lengow_ip_enabled' ) ) {
		$errorMessage = 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'];
	} else {
		$errorMessage = strlen( $token ) > 0
			? 'unauthorised access for this token: ' . $token
			: 'unauthorised access: token parameter is empty';
	}
	wp_die( $errorMessage, '', array( 'response' => 403 ) );
}

// get params data.
$get_params         = isset( $_GET['get_params'] ) ? (bool) $_GET['get_params'] : false;
$mode               = isset( $_GET['mode'] ) ? $_GET['mode'] : null;
$format             = isset( $_GET['format'] ) ? $_GET['format'] : null;
$stream             = isset( $_GET['stream'] ) ? (bool) $_GET['stream'] : null;
$offset             = isset( $_GET['offset'] ) ? (int) $_GET['offset'] : null;
$limit              = isset( $_GET['limit'] ) ? (int) $_GET['limit'] : null;
$selection          = isset( $_GET['all_products'] ) ? ! (bool) $_GET['all_products'] : null;
$selection          = is_null( $selection ) && isset( $_GET['selection'] ) ? (bool) $_GET['selection'] : $selection;
$out_of_stock       = isset( $_GET['out_of_stock'] ) ? (bool) $_GET['out_of_stock'] : null;
$product_ids        = isset( $_GET['product_ids'] ) ? $_GET['product_ids'] : null;
$product_types      = isset( $_GET['product_type'] ) ? $_GET['product_type'] : null;
$product_types      = is_null( $product_types ) && isset( $_GET['product_types'] )
	? $_GET['product_types']
	: $product_types;
$variation          = isset( $_GET['variation'] ) ? (bool) $_GET['variation'] : null;
$legacy_fields      = isset( $_GET['legacy_fields'] ) ? (bool) $_GET['legacy_fields'] : null;
$log_output         = isset( $_GET['log_output'] ) ? (bool) $_GET['log_output'] : null;
$update_export_date = isset( $_GET['update_export_date'] ) ? (bool) $_GET['update_export_date'] : null;

$export = new Lengow_Export(
	array(
		'format'             => $format,
		'stream'             => $stream,
		'offset'             => $offset,
		'limit'              => $limit,
		'selection'          => $selection,
		'out_of_stock'       => $out_of_stock,
		'product_ids'        => $product_ids,
		'product_types'      => $product_types,
		'variation'          => $variation,
		'legacy_fields'      => $legacy_fields,
		'log_output'         => $log_output,
		'update_export_date' => $update_export_date,
	)
);

if ( $get_params ) {
	echo $export->get_export_params();
} elseif ( 'size' === $mode ) {
	echo $export->get_total_export_product();
} elseif ( 'total' === $mode ) {
	echo $export->get_total_product();
} else {
	$export->exec();
}
