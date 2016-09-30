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

    private $locale;

	/**
	 * Display dashboard page
	 */
	public static function display() {

        $keys   = Lengow_Configuration::get_keys();
        $lengow_admin_dashboard = new Lengow_Admin_Dashboard();
        $lengow_admin_dashboard->locale = new Lengow_Translation();
        $locale = $lengow_admin_dashboard->locale;
        $stats = Lengow_Sync::get_statistic();

        //TODO
        //$merchantStatus = Lengow_Sync::getStatusAccount();
        /*if($isNewMerchant || $isSync){
            include_once 'views/dashboard/html-admin-new.php';
        }elseif (($merchantStatus['type'] == 'free_trial' && $merchantStatus['day'] != 0) || $merchantStatus['type'] == 'bad_payer'){
            include_once 'views/dashboard/html-admin-status.php';
        }else{
            include_once 'views/dashboard/html-admin-dashboard.php';
        }*/

        include_once 'views/dashboard/html-admin-dashboard.php';

	}
}
