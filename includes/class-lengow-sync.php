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
     * Get Account Status every 5 hours
     */
    protected static $cacheTime = 18000;

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

    /**
     * Get Statistic
     *
     * @param boolean $force Force cache Update
     *
     * @return array
     */
    public static function get_statistic($force = false)
    {
        if (!$force) {
            $updatedAt = Lengow_Configuration::get('lengow_last_order_statistic_update');
            if ((time() - strtotime($updatedAt)) < self::$cacheTime) {
                return json_decode(Lengow_Configuration::get('lengow_order_statistic'), true);
            }
        }
        $return = array();
        $return['total_order'] = 0;
        $return['nb_order'] = 0;
        $return['currency'] = '';

        $result = Lengow_Connector::query_api(
            'get',
            '/v3.0/stats',
            array(
                'date_from' => date('c', strtotime(date('Y-m-d').' -10 years')),
                'date_to'   => date('c'),
                'metrics'   => 'year',
            )
        );
        if (isset($result->level0)) {
            $stats = $result->level0[0];
            $return['total_order'] = $stats->revenue;
            $return['nb_order'] = (int)$stats->transactions;
            $return['currency'] = $result->currency->iso_a3;
        }
        if ($return['currency'] && get_woocommerce_currency_symbol($return['currency'])) {
            $return['total_order'] = wc_price($return['total_order'], array('currency' => $return['currency']));
        } else {
            $return['total_order'] = number_format($return['total_order'], 2, ',', ' ');
        }
        Lengow_Configuration::update_value('lengow_order_statistic', json_encode($return));
        Lengow_Configuration::update_value('lengow_last_order_statistic_update', date('Y-m-d H:i:s'));
        return $return;

    }
}

