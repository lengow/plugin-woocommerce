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
 * Lengow_Dashboard Class.
 */
class Lengow_Dashboard {
	/**
	 * Display dashboard page
	 */
	public static function display() {
		include 'views/html-admin-dashboard.php';
	}
}

