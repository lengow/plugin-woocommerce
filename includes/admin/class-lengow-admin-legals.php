<?php
/**
 * Admin legals page
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 * 
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category   	Lengow
 * @package    	lengow-woocommerce
 * @subpackage 	includes
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Admin_Legals Class.
 */
class Lengow_Admin_Legals {

	/**
	 * Display legals page.
	 */
	public static function display() {
		$locale = new Lengow_Translation();
		$keys   = Lengow_Configuration::get_keys();
		include_once 'views/legals/html-admin-legals.php';
	}
}
