<?php
/**
 * Admin toolbox page
 *
 * Copyright 2022 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category     Lengow
 * @package      lengow-woocommerce
 * @subpackage   includes
 * @author       Team Connector <team-connector@lengow.com>
 * @copyright    2022 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Admin_Toolbox Class.
 */
class Lengow_Admin_Toolbox {

	/**
	 * Display toolbox page.
	 */
	public static function display() {
		$locale          = new Lengow_Translation();
		$toolbox_element = new Lengow_Toolbox_Element();
		include_once 'views/toolbox/html-admin-toolbox.php';
	}
}
