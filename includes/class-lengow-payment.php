<?php
/**
 * Init Lengow Payment Class
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
 * Dependencies for front office
 */
include_once( 'class-lengow-translation.php' );

/**
 * Lengow_Payment Class.
 */
class WC_Lengow_Payment_Gateway extends WC_Payment_Gateway {

	/**
	 * Construct WC_Lengow_Payment_Gateway.
	 */
	public function __construct() {
		$locale                   = new Lengow_Translation();
		$this->id                 = 'lengow_payment_gateway';
		$this->has_fields         = false;
		$this->title              = $locale->t( 'module.lengow_payment_title' );
		$this->method_title       = $locale->t( 'module.lengow_payment_title' );
		$this->method_description = $locale->t( 'module.lengow_payment_description' );

		$this->init_form_fields();
		$this->init_settings();
	}

	/**
	 * Get title of payment gateway.
	 *
	 * @return string
	 */
	public function get_title() {
		global $post;

		if ( isset( $post->ID ) ) {
			$marketplace_name = Lengow_Order::get_marketplace_name_by_order_id( $post->ID );
			if ( $marketplace_name ) {
				return $marketplace_name;
			}
		}

		return $this->title;
	}
}
