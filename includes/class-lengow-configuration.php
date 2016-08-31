<?php
/**
 * All Lengow configuration options
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Configuration Class.
 */
class Lengow_Configuration {
    /**
     * All Lengow Options Path
     */
    public $options = array(
        'token',
        'store_enable',
        'account_id' => array(
            'path'   => 'lengow_global_options/store_credential/global_account_id',
            'store'  => true,
        ),
        'access_token' => array(
            'path'   => 'lengow_global_options/store_credential/global_access_token',
            'store'  => true,
        ),
        'secret_token' => array(
            'path'   => 'lengow_global_options/store_credential/global_secret_token',
            'store'  => true,
        ),
        'authorized_ip' => array(
            'path'   => 'lengow_global_options/advanced/global_authorized_ip',
        ),
        'last_order_statistic_update' => array(
            'path'   => 'lengow_global_options/advanced/last_statistic_update',
            'export' => false,
        ),
        'order_statistic' => array(
            'path'   => 'lengow_global_options/advanced/order_statistic',
            'export' => false,
        ),
        'lengow_last_option_update',
        'last_account_status_update' => array(
            'path'   => 'lengow_global_options/advanced/last_status_update',
            'export' => false,
        ),
        'account_status' => array(
            'path'   => 'lengow_global_options/advanced/account_status',
            'export' => false,
        ),
        'selection_enable' => array(
            'path'   => 'lengow_export_options/simple/export_selection_enable',
            'store'  => true,
        ),
        'out_stock' => array(
            'path'   => 'lengow_export_options/simple/export_out_stock',
            'store'  => true,
        ),
        'product_type' => array(
            'path'   => 'lengow_export_options/simple/export_product_type',
            'store'  => true,
        ),
        'legacy_enable' => array(
            'path'   => 'lengow_export_options/advanced/export_legacy_enable',
        ),
        'file_enable' => array(
            'path'   => 'lengow_export_options/advanced/export_file_enable',
        ),
        'cron_enable' => array(
            'path'   => 'lengow_export_options/advanced/export_cron_enable',
        ),
        'last_export' => array(
            'path'   => 'lengow_export_options/advanced/export_last_export',
        ),
        'lengow_import_days' => array(
            'path'   => 'lengow_import_options/simple/import_days',
            'store'  => true,
        ),
        'import_ship_mp_enabled' => array(
            'path'   =>  'lengow_import_options/advanced/import_ship_mp_enabled',
        ),
        'import_stock_ship_mp' => array(
            'path'   =>  'lengow_import_options/advanced/import_stock_ship_mp',
        ),
        'preprod_mode_enable' => array(
            'path'   => 'lengow_import_options/advanced/import_preprod_mode_enable',
        ),
        'import_in_progress' => array(
            'path'   => 'lengow_import_options/advanced/import_in_progress',
        ),
        'last_import_manual' => array(
            'path'   => 'lengow_import_options/advanced/last_import_manual',
        ),
        'last_import_cron' => array(
            'path'   => 'lengow_import_options/advanced/last_import_cron',
        ),
    );
}

