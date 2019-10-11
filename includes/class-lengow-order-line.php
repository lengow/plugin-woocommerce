<?php
/**
 * All function to manage order line
 *
 * Copyright 2019 Lengow SAS
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
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2019 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Order_Line Class.
 */
class Lengow_Order_Line {

	/**
	 * Create Lengow order line.
	 *
	 * @param array $data Lengow order line data
	 *
	 * @return boolean
	 *
	 */
	public static function create( $data = array() ) {
		return Lengow_Crud::create( Lengow_Crud::LENGOW_ORDER_LINE, $data );
	}
}
