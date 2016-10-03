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
     * Process Get Parameters
     */
    public static function get_process()
    {
        $action = isset( $_GET['do_action']) ?  $_GET['do_action']: false;
        if ($action) {
            switch ($action) {
                case 'refresh_status':
                    Lengow_Sync::get_status_account(true);
                    wp_redirect(admin_url( 'admin.php?page=lengow' ));
                    break;
            }
            exit();
        }

    }

	/**
	 * Display dashboard page
	 */
	public static function display() {

        $keys   = Lengow_Configuration::get_keys();
        $locale = new Lengow_Translation();
        $stats = Lengow_Sync::get_statistic();
        $merchant_status = Lengow_Sync::get_status_account();
        $is_new_merchant = Lengow_Main::is_new_merchant();

        //TODO
        if($is_new_merchant /*|| $isSync*/){
            include_once 'views/dashboard/html-admin-new.php';
        }elseif (($merchant_status['type'] == 'free_trial' && $merchant_status['day'] <= 0) || $merchant_status['type'] == 'bad_payer'){
            include_once 'views/dashboard/html-admin-status.php';
        }else{
            include_once 'views/dashboard/html-admin-dashboard.php';
        }

	}
}
