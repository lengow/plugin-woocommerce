<?php
/**
 * Woocommerce Order Info
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
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Order_Info Class.
 */
class Lengow_Order_Info {

	/**
	 * Display Lengow Order data.
	 *
	 * @param WP_Post $post Wordpress Post instance
	 */
	public static function display_lengow_order_infos_meta_box( $post ) {
		try {
			$lengow_order = Lengow_Crud::read( Lengow_Crud::LENGOW_ORDER, array( 'order_id' => (int) $post->ID ) );
			include_once( 'views/order-info/html-order-info.php' );
		} catch ( Exception $e ) {
			echo $e->getMessage();
		}

	}
}
