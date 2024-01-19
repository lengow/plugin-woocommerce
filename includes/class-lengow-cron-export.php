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




// dependencies.
require_once('class-lengow-action.php' );
require_once('class-lengow-address.php' );
require_once('class-lengow-catalog.php' );
require_once('class-lengow-configuration.php' );
require_once('class-lengow-connector.php' );
require_once('class-lengow-crud.php' );
require_once('class-lengow-exception.php' );
require_once('class-lengow-export.php' );
require_once('class-lengow-feed.php' );
require_once('class-lengow-file.php' );
require_once('class-lengow-hook.php' );
require_once('class-lengow-import.php' );
require_once('class-lengow-import-order.php' );
require_once('class-lengow-install.php' );
require_once('class-lengow-log.php' );
require_once('class-lengow-main.php' );
require_once('class-lengow-marketplace.php' );
require_once('class-lengow-order.php' );
require_once('class-lengow-order-error.php' );
require_once('class-lengow-order-line.php' );
require_once('class-lengow-product.php' );
require_once('class-lengow-sync.php' );
require_once('class-lengow-toolbox.php' );
require_once('class-lengow-toolbox-element.php' );
require_once('class-lengow-translation.php' );

class LengowCronExport
{

    public function launch()
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');
        // check if WooCommerce plugin is activated.
        $woocommercePlugin = 'woocommerce/woocommerce.php';
        if (!in_array($woocommercePlugin, apply_filters('active_plugins', get_option('active_plugins')), true)) {
            wp_die('WooCommerce plugin is not active', '', array('response' => 400));
        }

        // check if Lengow plugin is activated.
        $lengowPlugin = 'lengow/lengow.php';
        if (!in_array($lengowPlugin, apply_filters('active_plugins', get_option('active_plugins')), true)) {
            wp_die('Lengow plugin is not active', '', array('response' => 400));
        }

        // get token for authorisation.
        $token = isset($_GET[Lengow_Export::PARAM_TOKEN]) ? $_GET[Lengow_Export::PARAM_TOKEN] : '';

        // check webservices access.
        if (!Lengow_Main::check_webservice_access($token)) {
            if ((bool) Lengow_Configuration::get(Lengow_Configuration::AUTHORIZED_IP_ENABLED)) {
                $errorMessage = 'Unauthorized access for IP: ' . $_SERVER['REMOTE_ADDR'];
            } else {
                $errorMessage = strlen($token) > 0 ? 'unauthorised access for this token: ' . $token : 'unauthorised access: token parameter is empty';
            }
            wp_die($errorMessage, '', array('response' => 403));
        }

        // get params data.
        $get_params = isset($_GET[Lengow_Export::PARAM_GET_PARAMS]) && $_GET[Lengow_Export::PARAM_GET_PARAMS];
        $mode = isset($_GET[Lengow_Export::PARAM_MODE]) ? $_GET[Lengow_Export::PARAM_MODE] : null;
        $format = isset($_GET[Lengow_Export::PARAM_FORMAT]) ? $_GET[Lengow_Export::PARAM_FORMAT] : null;
        $stream = isset($_GET[Lengow_Export::PARAM_STREAM]) ? (bool) $_GET[Lengow_Export::PARAM_STREAM] : null;
        $offset = isset($_GET[Lengow_Export::PARAM_OFFSET]) ? (int) $_GET[Lengow_Export::PARAM_OFFSET] : null;
        $limit = isset($_GET[Lengow_Export::PARAM_LIMIT]) ? (int) $_GET[Lengow_Export::PARAM_LIMIT] : null;
        $selection = isset($_GET[Lengow_Export::PARAM_LEGACY_SELECTION]) ? !(bool) $_GET[Lengow_Export::PARAM_LEGACY_SELECTION] : null;
        $selection = ( is_null($selection) && isset($_GET[Lengow_Export::PARAM_SELECTION]) ) ? (bool) $_GET[Lengow_Export::PARAM_SELECTION] : $selection;
        $out_of_stock = isset($_GET[Lengow_Export::PARAM_OUT_OF_STOCK]) ? (bool) $_GET[Lengow_Export::PARAM_OUT_OF_STOCK] : null;
        $product_ids = isset($_GET[Lengow_Export::PARAM_PRODUCT_IDS]) ? $_GET[Lengow_Export::PARAM_PRODUCT_IDS] : null;
        $product_types = isset($_GET[Lengow_Export::PARAM_LEGACY_PRODUCT_TYPES]) ? $_GET[Lengow_Export::PARAM_LEGACY_PRODUCT_TYPES] : null;
        $product_types = is_null($product_types) && isset($_GET[Lengow_Export::PARAM_PRODUCT_TYPES]) ? $_GET[Lengow_Export::PARAM_PRODUCT_TYPES] : $product_types;
        $variation = isset($_GET[Lengow_Export::PARAM_VARIATION]) ? (bool) $_GET[Lengow_Export::PARAM_VARIATION] : null;
        $legacy_fields = isset($_GET[Lengow_Export::PARAM_LEGACY_FIELDS]) ? (bool) $_GET[Lengow_Export::PARAM_LEGACY_FIELDS] : null;
        $log_output = isset($_GET[Lengow_Export::PARAM_LOG_OUTPUT]) ? (bool) $_GET[Lengow_Export::PARAM_LOG_OUTPUT] : null;
        $update_export_date = isset($_GET[Lengow_Export::PARAM_UPDATE_EXPORT_DATE]) ? (bool) $_GET[Lengow_Export::PARAM_UPDATE_EXPORT_DATE] : null;

        $export = new Lengow_Export(
                array(
            Lengow_Export::PARAM_FORMAT => $format,
            Lengow_Export::PARAM_STREAM => $stream,
            Lengow_Export::PARAM_OFFSET => $offset,
            Lengow_Export::PARAM_LIMIT => $limit,
            Lengow_Export::PARAM_SELECTION => $selection,
            Lengow_Export::PARAM_OUT_OF_STOCK => $out_of_stock,
            Lengow_Export::PARAM_PRODUCT_IDS => $product_ids,
            Lengow_Export::PARAM_PRODUCT_TYPES => $product_types,
            Lengow_Export::PARAM_VARIATION => $variation,
            Lengow_Export::PARAM_LEGACY_FIELDS => $legacy_fields,
            Lengow_Export::PARAM_LOG_OUTPUT => $log_output,
            Lengow_Export::PARAM_UPDATE_EXPORT_DATE => $update_export_date,
                )
        );

        if ($get_params) {
            echo esc_html(Lengow_Export::get_export_params());
        } elseif ('size' === $mode) {
            echo esc_html($export->get_total_export_product());
        } elseif ('total' === $mode) {
            echo esc_html($export->get_total_product());
        } else {
            $export->exec();
        }
    }
}
