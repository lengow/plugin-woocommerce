<?php
/**
 * All components to create and synchronise account
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
 * Lengow_Sync Class.
 */
class Lengow_Sync {

    /**
     * Get Sync Data (Inscription / Update)
     *
     * @return array
     */
    public static function get_sync_data()
    {
        return true;
    }

    /**
     * Check Synchronisation shop
     *
     * @return boolean
     */
    public static function check_sync_shop()
    {
        return Lengow_Configuration::get('lengow_store_enabled')
        && Lengow_Check::is_valid_auth();
    }
}

