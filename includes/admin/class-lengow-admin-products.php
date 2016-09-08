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
 * Lengow_Admin_Products Class.
 */
class Lengow_Admin_Products {
    /**
     * Display products page
     */
    public static function display() {
        include_once 'views/products/html-admin-products.php';

    }
}
