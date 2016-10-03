<?php
/**
 * Installation related functions and actions.
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
 * Lengow_Admin_Dashboard Class.
 */
class Lengow_Admin_Dashboard {

	/**
	 * Display dashboard page
	 */
	public static function display() {

        $keys   = Lengow_Configuration::get_keys();
        $locale = new Lengow_Translation();
        $stats = Lengow_Sync::get_statistic();
        $merchant_status = Lengow_Sync::get_status_account();

        //TODO
        /*if($isNewMerchant || $isSync){
            include_once 'views/dashboard/html-admin-new.php';
        }elseif (($merchantStatus['type'] == 'free_trial' && $merchantStatus['day'] != 0) || $merchantStatus['type'] == 'bad_payer'){
            include_once 'views/dashboard/html-admin-status.php';
        }else{*/
            include_once 'views/dashboard/html-admin-dashboard.php';
        //}

	}
}
