<?php
/**
 * Admin help page
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package        lengow-woocommerce
 * @subpackage    includes
 * @author        Team Connector <team-connector@lengow.com>
 * @copyright    2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Admin_Help Class.
 */
class Lengow_Admin_Help {

	/**
	 * Display help page.
	 */
	public static function display() {
		$locale       = new Lengow_Translation();
		$keys         = Lengow_Configuration::get_keys();
		$plugin_links = Lengow_Sync::get_plugin_links( get_locale() );
		include_once 'views/help/html-admin-help.php';
	}
}
