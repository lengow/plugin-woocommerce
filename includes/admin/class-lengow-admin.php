<?php
/**
 * Admin rooting
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
 * Lengow_Admin Class.
 */
class Lengow_Admin {

	/**
	 * @var string current tab.
	 */
	public $current_tab = '';

	/**
	 * @var string default tab
	 */
	private $_default_tab = 'lengow';

	/**
	 * Init Lengow for WooCommerce.
	 * Init module administration and action.
	 */
	public function __construct() {
		global $lengow, $woocommerce;
		$this->current_tab = ( empty( $_GET['tab'] ) )
			? $this->_default_tab
			: sanitize_text_field( urldecode( $_GET['tab'] ) );
		add_action( 'admin_menu', array( $this, 'lengow_admin_menu' ) );
	}

	/**
	 * Add Lengow admin item menu.
	 */
	public function lengow_admin_menu() {

		$locale = new Lengow_Translation();

		add_menu_page(
			$locale->t( 'module.name' ),
			$locale->t( 'module.name' ),
			'manage_woocommerce',
			'lengow',
			array( $this, 'lengow_display' ),
			null,
			56
		);
	}

	/**
	 * Routing.
	 */
	public function lengow_display() {
		$locale          = new Lengow_Translation();
		$merchant_status = Lengow_Sync::get_status_account();
		$is_new_merchant = Lengow_Main::is_new_merchant();
		if ( $this->current_tab != $this->_default_tab
			&& ! ( $merchant_status['type'] == 'free_trial' && $merchant_status['day'] <= 0 )
			&& $merchant_status['type'] != 'bad_payer'
			&& ! $is_new_merchant
		) {
			include_once 'views/html-admin-header.php';
		}
		switch ( $this->current_tab ) {
			case 'lengow_admin_products':
				Lengow_Admin_Products::html_display();
				break;
			case 'lengow_admin_orders':
				Lengow_Admin_Orders::display();
				break;
			case 'lengow_admin_help':
				Lengow_Admin_Help::display();
				break;
			case 'lengow_admin_settings':
				Lengow_Admin_Settings::display();
				break;
			case 'lengow_admin_legals':
				Lengow_Admin_Legals::display();
				break;
			default:
				Lengow_Admin_Dashboard::display();
				break;
		}
		include_once 'views/html-admin-footer.php';
	}
}
