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
 * Lengow_Settings Class.
 */
class Lengow_Settings {

    public static function post_process() {
        if ( ! empty( $_POST ) ) {
            $option = empty( $_REQUEST['lengow_product_type'] ) ? '' : $_REQUEST['lengow_product_type'];
            $types = array();
            foreach ($option as $key => $value) {
                array_push($value, $types);
            }
            var_dump($types);die();
            update_option('lengow_product_type', $option);

        }
    }

    /**
     * Display settings page
     */
    public static function display() {
        Lengow_Settings::post_process();
        include_once 'views/settings/html-admin-settings.php';
    }
}
