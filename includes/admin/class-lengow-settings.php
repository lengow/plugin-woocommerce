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
            $option = empty( $_REQUEST['lengow_import_days'] ) ? '' : $_REQUEST['lengow_import_days'];
            update_option('lengow_import_days', $option);

        }
    }

    /**
     * Display settings page
     */
    public static function display() {
        include_once 'views/html-admin-settings.php';
    }
}
