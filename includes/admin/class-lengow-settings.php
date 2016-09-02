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
            foreach ($_POST as $key => $value)
            {
                if ($value == "on") {
                    $value = 1;
                }

                if (Lengow_Configuration::get($key) != $value) {
                    Lengow_Configuration::update_value($key, $value);
                }
            }
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
