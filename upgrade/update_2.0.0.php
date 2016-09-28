<?php
/**
 * @author   Lengow
 * @category Admin
 * @package  Lengow/upgrade
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) || !Lengow_Install::is_installation_in_progress()) {
    exit;
}

$table_name = $wpdb->prefix . 'lengow_product';

$sql        = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' ('
    . ' `product_id` bigint(20) NOT NULL,'
    . ' UNIQUE KEY `product_id` (`product_id`)) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
dbDelta( $sql );

$table_name = $wpdb->prefix . 'lengow_orders';

// if table lengow_orders exist we update it
if ($wpdb->get_var('SHOW TABLES LIKE \''.$table_name.'\'')) {
    if (!Lengow_Install::check_field_exists('lengow_orders', 'id')) {
        $wpdb->query(
            'ALTER TABLE '.$table_name.' DROP PRIMARY KEY'
        );
        $wpdb->query(
            'ALTER TABLE '.$table_name.' ADD `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST'
        );
    }
    if (!Lengow_Install::check_field_exists('lengow_orders', 'created_at')) {
        $wpdb->query(
            'ALTER TABLE '.$table_name.' ADD `created_at` datetime NOT NULL'
        );
    }
    if (Lengow_Install::check_field_exists('lengow_orders', 'id_order_lengow')) {
        $wpdb->query(
            'ALTER TABLE '.$table_name.' CHANGE `id_order_lengow` `marketplace_sku` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
        );
        $wpdb->query(
            'DROP INDEX `id_order_lengow` ON '.$table_name
        );
    }
    if (Lengow_Install::check_field_exists('lengow_orders', 'marketplace')) {
        $wpdb->query(
            'ALTER TABLE '.$table_name.' CHANGE `marketplace` `marketplace_name` VARCHAR(100) COLLATE utf8_unicode_ci NOT NULL'
        );
        $wpdb->query(
            'DROP INDEX `marketplace` ON '.$table_name
        );
    }
    if (Lengow_Install::check_field_exists('lengow_orders', 'extra')) {
        $wpdb->query(
            'ALTER TABLE '.$table_name.' MODIFY `extra` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci;'
        );
    }
    if (!Lengow_Install::check_field_exists('lengow_orders', 'delivery_address_id')) {
        $wpdb->query(
            'ALTER TABLE '.$table_name.' ADD `delivery_address_id` INTEGER(11) NOT NULL'
        );
    }
    if (!Lengow_Install::check_field_exists('lengow_orders', 'order_date')) {
        $wpdb->query(
            'ALTER TABLE '.$table_name.' ADD `order_date` DATETIME NOT NULL'
        );
        $wpdb->query(
            'UPDATE '.$table_name.' SET `order_date` = `date_add`'
        );
        $wpdb->query(
            'ALTER TABLE  ' . $table_name . ' DROP COLUMN `date_add`'
        );
    }
    Lengow_Install::check_field_and_drop('lengow_orders', 'id_flux');
    Lengow_Install::check_field_and_drop('lengow_orders', 'id_order');
    Lengow_Install::check_field_and_drop('lengow_orders', 'total_paid');
    Lengow_Install::check_field_and_drop('lengow_orders', 'message');
    Lengow_Install::check_field_and_drop('lengow_orders', 'carrier');
    Lengow_Install::check_field_and_drop('lengow_orders', 'tracking');
}

$sql        = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' ('
    . ' `id` INTEGER(11) UNSIGNED NOT NULL AUTO_INCREMENT,'
    . ' `delivery_address_id` int(11) NOT NULL,'
    . ' `marketplace_sku` varchar(100) COLLATE utf8_unicode_ci NOT NULL,'
    . ' `marketplace_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,'
    . ' `order_date` datetime NOT NULL,'
    . ' `created_at` datetime NOT NULL,'
    . ' `extra` longtext COLLATE utf8_unicode_ci,'
    . ' PRIMARY KEY (`id`)) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
dbDelta( $sql );

update_option( 'lengow_version', LENGOW_VERSION );

// Rename old settings
Lengow_Install::rename_configuration_key('lengow_debug', 'lengow_preprod_enabled');
Lengow_Install::rename_configuration_key('lengow_export_type', 'lengow_product_types');
Lengow_Install::rename_configuration_key('lengow_export_cron', 'lengow_cron_enabled');
Lengow_Install::rename_configuration_key('is_import_processing', 'lengow_import_in_progress');

// Add new settings
$keys = Lengow_Configuration::get_keys();
foreach ($keys as $key => $value) {
    if(get_option($key)) continue;
    if (isset($value['default_value'])) {
        $val = $value['default_value'];
    } else {
        $val = '';
    }
    add_option( $key, $val);
}

// Delete old settings
$configuration_to_delete = array(
    'lengow_export_format',
    'lengow_export_all_product',
    'lengow_export_attributes',
    'lengow_export_meta',
    'lengow_export_full_title',
    'lengow_export_images',
    'lengow_export_image_size',
    'lengow_export_file',
    'lengow_order_process',
    'lengow_order_shipped',
    'lengow_order_cancel',
    'lengow_method_name',
    'lengow_force_price',
    'lengow_send_admin_mail',
    'lengow_logs_day',
    'lengow_id_user',
    'lengow_id_group',
    'lengow_api_key',
    'lengow_default_carrier',
    'lengow_import_cron',
    'lengow_time_import_start'
);
foreach ($configuration_to_delete as $config_name) {
    Lengow_Configuration::delete($config_name);
}