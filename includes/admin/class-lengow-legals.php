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
 * Lengow_Legals Class.
 */
class Lengow_Legals {
    /**
     * Display legals page
     */
    public static function display() {
        include_once 'views/html-admin-legals.php';

    }
}