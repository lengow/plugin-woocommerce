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
 * Lengow_Admin_Legals Class.
 */
class Lengow_Admin_Legals {
	/**
	 * Display legals page
	 */
	public static function display() {
		$locale = new Lengow_Translation();
		$keys   = Lengow_Configuration::get_keys();
		include_once 'views/legals/html-admin-legals.php';
	}
}