<?php
/**
 * All Lengow configuration options
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lengow_Configuration Class.
 */
class Lengow_Configuration
{

    /**
     * Get all Lengow configuration keys
     *
     * @return array
     */
    public static function get_keys()
    {
        static $keys = null;
        if ($keys === null) {
            $locale = new Lengow_Translation();

            $keys = array(
                'lengow_token' => array(
                    'label' => $locale->t('lengow_settings.lengow_token_title'),
                ),
                'lengow_store_enabled' => array(
                    'label' => $locale->t('lengow_settings.lengow_store_active_title'),
                ),
                'lengow_account_id' => array(
                    'label' => $locale->t('lengow_settings.lengow_account_id_title'),
                ),
                'lengow_access_token' => array(
                    'label' => $locale->t('lengow_settings.lengow_access_token_title'),
                ),
                'lengow_secret_token' => array(
                    'label' => $locale->t('lengow_settings.lengow_secret_token_title'),
                ),
                'lengow_authorized_ip' => array(
                    'label' => $locale->t('lengow_settings.lengow_authorized_ip_title'),
                    'legend' => $locale->t('lengow_settings.lengow_authorized_ip_legend'),
                ),
                'lengow_last_order_statistic_update' => array(
                    'label' => $locale->t('lengow_settings.lengow_last_order_statistic_update_title'),
                ),
                'lengow_order_statistic' => array(
                    'label' => $locale->t('lengow_settings.lengow_order_statistic_title'),
                ),
                'lengow_last_option_update' => array(
                    'label' => $locale->t('lengow_settings.lengow_last_option_update_title'),
                ),
                'lengow_last_account_status_update' => array(
                    'label' => $locale->t('lengow_settings.lengow_last_account_status_update_title'),
                ),
                'lengow_account_status' => array(
                    'label' => $locale->t('lengow_settings.lengow_account_status_title'),
                ),
                'lengow_selection_enabled' => array(
                    'label' => $locale->t('lengow_settings.lengow_selection_enabled_title'),
                    'legend' => $locale->t('lengow_settings.lengow_selection_enabled_legend'),
                    'default_value' => false,
                ),
                'lengow_out_stock' => array(
                    'label' => $locale->t('lengow_settings.lengow_out_stock_title'),
                ),
                'lengow_product_types' => array(
                    'label' => $locale->t('lengow_settings.lengow_product_types_title'),
                    'default_value' => array('simple', 'variable'),
                ),
                'lengow_legacy_enabled' => array(
                    'label' => $locale->t('lengow_settings.lengow_legacy_enabled_title'),
                    'legend' => $locale->t('lengow_settings.lengow_legacy_enabled_legend'),
                    'default_value' => false,
                ),
                'lengow_file_enabled' => array(
                    'label' => $locale->t('lengow_settings.lengow_file_enabled_title'),
                    'legend' => $locale->t('lengow_settings.lengow_file_enabled_legend'),
                ),
                'lengow_cron_enabled' => array(
                    'label' => $locale->t('lengow_settings.lengow_cron_enabled_title'),
                    'default_value' => false,
                ),
                'lengow_last_export' => array(
                    'label' => $locale->t('lengow_settings.lengow_last_export_title'),
                ),
                'lengow_import_days' => array(
                    'label' => $locale->t('lengow_settings.lengow_import_days_title'),
                    'legend' => $locale->t('lengow_settings.lengow_import_days_legend'),
                    'default_value' => 5,
                ),
                'lengow_import_ship_mp_enabled' => array(
                    'label' => $locale->t('lengow_settings.lengow_import_ship_mp_enabled_title'),
                    'default_value' => false,
                ),
                'lengow_import_stock_ship_mp' => array(
                    'label' => $locale->t('lengow_settings.lengow_import_stock_ship_mp_title'),
                    'legend' => $locale->t('lengow_settings.lengow_import_stock_ship_mp_legend'),
                    'default_value' => false,
                ),
                'lengow_preprod_enabled' => array(
                    'label' => $locale->t('lengow_settings.lengow_preprod_enabled_title'),
                ),
                'lengow_import_in_progress' => array(
                    'label' => $locale->t('lengow_settings.lengow_import_in_progress_title'),
                ),
                'lengow_last_import_manual' => array(
                    'label' => $locale->t('lengow_settings.lengow_last_import_manual_title')
                ),
                'lengow_last_import_cron' => array(
                    'label' => $locale->t('lengow_settings.lengow_last_import_cron_title')
                ),
            );
        }

        return $keys;
    }

    /**
     * Get Lengow value
     *
     * @param string $key lengow configuration key
     *
     * @return mixed
     */
    public static function get($key)
    {

        return get_option($key);
    }

    /**
     * Update Lengow value by shop
     *
     * @param string $key lengow configuration key
     * @param mixed $value configuration value
     */
    public static function update_value($key, $value)
    {
        update_option($key, $value);
    }

    /**
     * Get Values
     *
     * @return array
     */
    public static function get_all_values()
    {
        $rows = array();
        $keys = self::get_keys();
        foreach ($keys as $key => $value) {
            $rows[$key] = self::get($key);

        }
        return $rows;
    }

    /**
     * Reset all Lengow settings
     *
     * @return boolean
     */
    public static function reset_all()
    {
        $keys = self::get_keys();
        foreach ($keys as $key => $value) {
            if (isset($value['default_value'])) {
                $val = $value['default_value'];
            } else {
                $val = '';
            }

            $oldValue = self::get($key);
            if ($oldValue == "") {
                self::update_value($key, $val);
            }

        }
        Lengow_Main::log('Setting', Lengow_Main::set_log_message('log.setting.setting_reset'));

        return true;
    }
    
}

