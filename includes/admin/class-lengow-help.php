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
 * Lengow_Help Class.
 */
class Lengow_Help {
    /**
     * Display help page
     */
    public static function display() {
        include_once 'views/html-admin-help.php';

    }
}