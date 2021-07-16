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
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
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
 * Lengow_Admin Class.
 */
class Lengow_Admin {

	/**
	 * @var string current tab.
	 */
	public $current_tab = '';

	/**
	 * @var string default tab.
	 */
	private $_default_tab = 'lengow';

	/**
	 * Init Lengow for WooCommerce.
	 * Init module administration and action.
	 */
	public function __construct() {
		global $lengow, $woocommerce;
		$this->current_tab = empty( $_GET['tab'] )
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
		$locale            = new Lengow_Translation();
		$is_new_merchant   = Lengow_Configuration::is_new_merchant();
		$this->current_tab = ( ! $is_new_merchant && $this->current_tab === $this->_default_tab )
			? 'lengow_admin_dashboard'
			: $this->current_tab;
		// recovery of all plugin data for plugin update
		$plugin_is_up_to_date      = true;
		$show_plugin_upgrade_modal = false;
		$plugin_data               = Lengow_Sync::get_plugin_data();
		if ( $plugin_data && version_compare( $plugin_data['version'], LENGOW_VERSION, '>' ) ) {
			$plugin_is_up_to_date = false;
			// show upgrade plugin modal or not
			$show_plugin_upgrade_modal = $this->_show_plugin_upgrade_modal();
		}
		// get actual plugin urls in current language
		$plugin_links = Lengow_Sync::get_plugin_links( get_locale() );
		// display footer or not
		if ( ! $is_new_merchant
		     && ! in_array( $this->current_tab, array( $this->_default_tab, 'lengow_admin_dashboard' ), true )
		) {
			$merchant_status     = Lengow_Sync::get_status_account();
			$total_pending_order = Lengow_Order::count_order_to_be_sent();
			include_once 'views/html-admin-header.php';
		}
		switch ( $this->current_tab ) {
			case 'lengow_admin_dashboard':
				Lengow_Admin_Dashboard::display();
				break;
			case 'lengow_admin_products':
				Lengow_Admin_Products::html_display();
				break;
			case 'lengow_admin_orders':
				Lengow_Admin_Orders::html_display();
				break;
			case 'lengow_admin_order_settings':
				Lengow_Admin_Order_Settings::display();
				break;
			case 'lengow_admin_help':
				Lengow_Admin_Help::display();
				break;
			case 'lengow_admin_settings':
				Lengow_Admin_Main_Settings::display();
				break;
			case 'lengow_admin_legals':
				Lengow_Admin_Legals::display();
				break;
			default:
				Lengow_Admin_Connection::display();
		}
		include_once 'views/html-admin-footer.php';
	}

	/**
	 * Checks if the plugin upgrade modal should be displayed or not
	 *
	 * @return boolean
	 */
	private function _show_plugin_upgrade_modal()
	{
		// never display the upgrade modal during the connection process
		$updated_at = Lengow_Configuration::get(Lengow_Configuration::LAST_UPDATE_PLUGIN_MODAL);
		if ($updated_at !== null && (time() - (int) $updated_at) < 86400) {
			return false;
		}
		Lengow_Configuration::update_value(Lengow_Configuration::LAST_UPDATE_PLUGIN_MODAL, time());
		return true;
	}
}
