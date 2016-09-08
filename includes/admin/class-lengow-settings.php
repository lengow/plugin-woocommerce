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
        $action = null;
        if ($_POST) {
            $action = $_POST['action'];
        } elseif (isset($_GET['action'])) {
            $action = $_GET['action'];
        }
        switch ($action) {
            case 'process':
                foreach ($_POST as $key => $value)
                {
                    if ($value == "on") {
                        $value = 1;
                    }

                    if (Lengow_Configuration::get($key) != $value) {
                        Lengow_Configuration::check_and_log($key, $value);
                        Lengow_Configuration::update_value($key, $value);
                    }
                }
                break;
        }

    }

    /**
     * Display settings page
     */
    public static function display() {
        $locale = new Lengow_Translation();
        include_once 'views/settings/html-admin-settings.php';
    }
}
