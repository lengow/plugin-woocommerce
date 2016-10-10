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
     * Process Post Parameters
     */
    public static function post_process()
    {
        $isSync = isset($_POST['isSync']) ? $_POST['isSync'] : false;
        $action = isset( $_POST['do_action']) ?  $_POST['do_action']: false;
        if ($action) {
            switch ($action) {
                case 'get_sync_data':
                    $data = array();
                    $data['function'] = 'sync';
                    $data['parameters'] = Lengow_Sync::get_sync_data();
                    echo json_encode($data);
                    break;
                case 'sync':
                    $data = isset($_POST['data']) ?$_POST['data'] : false;
                    Lengow_Sync::sync($data);
                    break;
            }
            exit();
        }
    }

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
        $isSync = isset($_GET['isSync']) ? $_GET['isSync'] : false;

        $refresh_status = admin_url( 'admin.php?action=dashboard_get_process&do_action=refresh_status' );

        if($is_new_merchant || $isSync){
            include_once 'views/dashboard/html-admin-new.php';
        }elseif (($merchant_status['type'] == 'free_trial' && $merchant_status['day'] <= 0) || $merchant_status['type'] == 'bad_payer'){
            include_once 'views/dashboard/html-admin-status.php';
        }else{
            include_once 'views/dashboard/html-admin-dashboard.php';
        }

	}
}
