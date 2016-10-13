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
 * boolean mode               Number of products exported
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
 */

@set_time_limit( 0 );
@ini_set( 'memory_limit', '512M' );

// Init wordpress
require(dirname(dirname(dirname(dirname(dirname($_SERVER["SCRIPT_FILENAME"]))))). '/wp-load.php');

// Dependencies
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

// check if WooCommerce plugin is activated
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	wp_die( 'WooCommerce plugin is not active', '', array( 'response' => 400 ) );
}

// check if Lengow plugin is activated
if ( ! in_array(
	'lengow-woocommerce/lengow.php',
	apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
)
) {
	wp_die( 'Lengow plugin is not active', '', array( 'response' => 400 ) );
}

// check IP
if ( ! Lengow_Main::check_ip() ) {
	wp_die( 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'], '', array( 'response' => 403 ) );
}

// get params data
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

$export = new Lengow_Export( array(
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
) );

if ( $get_params ) {
	echo $export->get_export_params();
} elseif ( $mode == 'size' ) {
    echo $export->get_total_export_product();
} elseif ( $mode == 'total' ) {
	echo $export->get_total_product();
} else {
	$export->exec();
}

