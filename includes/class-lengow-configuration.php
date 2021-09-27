<?php
/**
 * All Lengow configuration options.
 *
 * Copyright 2017 Lengow SAS
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
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Configuration Class.
 */
class Lengow_Configuration {

	/* Settings database key */
	const ACCOUNT_ID = 'lengow_account_id';
	const ACCESS_TOKEN = 'lengow_access_token';
	const SECRET = 'lengow_secret_token';
	const CMS_TOKEN = 'lengow_token';
	const AUTHORIZED_IP_ENABLED = 'lengow_ip_enabled';
	const AUTHORIZED_IPS = 'lengow_authorized_ip';
	const TRACKING_ENABLED = 'lengow_tracking_enabled';
	const TRACKING_ID = 'lengow_tracking_id';
	const DEBUG_MODE_ENABLED = 'lengow_debug_enabled';
	const REPORT_MAIL_ENABLED = 'lengow_report_mail_enabled';
	const REPORT_MAILS = 'lengow_report_mail_address';
	const AUTHORIZATION_TOKEN = 'lengow_authorization_token';
	const PLUGIN_DATA = 'lengow_plugin_data';
	const ACCOUNT_STATUS_DATA = 'lengow_account_status';
	const SHOP_TOKEN = 'lengow_shop_token';
	const SHOP_ACTIVE = 'lengow_store_enabled';
	const CATALOG_IDS = 'lengow_catalog_id';
	const SELECTION_ENABLED = 'lengow_selection_enabled';
	const EXPORT_PRODUCT_TYPES = 'lengow_product_types';
	const EXPORT_FORMAT = 'lengow_export_format';
	const EXPORT_FILE_ENABLED = 'lengow_export_file_enabled';
	const WAITING_SHIPMENT_ORDER_ID = 'lengow_id_waiting_shipment';
	const SHIPPED_ORDER_ID = 'lengow_id_shipped';
	const CANCELED_ORDER_ID = 'lengow_id_cancel';
	const SHIPPED_BY_MARKETPLACE_ORDER_ID = 'lengow_id_shipped_by_mp';
	const SYNCHRONIZATION_DAY_INTERVAL = 'lengow_import_days';
	const DEFAULT_IMPORT_CARRIER_ID = 'lengow_import_default_shipping_method';
	const CURRENCY_CONVERSION_ENABLED = 'lengow_currency_conversion';
	const B2B_WITHOUT_TAX_ENABLED = 'lengow_import_b2b_without_tax';
	const SHIPPED_BY_MARKETPLACE_ENABLED = 'lengow_import_ship_mp_enabled';
	const SHIPPED_BY_MARKETPLACE_STOCK_ENABLED = 'lengow_import_stock_ship_mp';
	const SYNCHRONIZATION_IN_PROGRESS = 'lengow_import_in_progress';
	const LAST_UPDATE_EXPORT = 'lengow_last_export';
	const LAST_UPDATE_CRON_SYNCHRONIZATION = 'lengow_last_import_cron';
	const LAST_UPDATE_MANUAL_SYNCHRONIZATION = 'lengow_last_import_manual';
	const LAST_UPDATE_ACTION_SYNCHRONIZATION = 'lengow_last_action_sync';
	const LAST_UPDATE_CATALOG = 'lengow_catalog_update';
	const LAST_UPDATE_MARKETPLACE = 'lengow_marketplace_update';
	const LAST_UPDATE_ACCOUNT_STATUS_DATA = 'lengow_last_account_status_update';
	const LAST_UPDATE_OPTION_CMS = 'lengow_last_option_update';
	const LAST_UPDATE_SETTING = 'lengow_last_setting_update';
	const LAST_UPDATE_PLUGIN_DATA = 'lengow_plugin_data_update';
	const LAST_UPDATE_AUTHORIZATION_TOKEN = 'lengow_last_authorization_token_update';
	const LAST_UPDATE_PLUGIN_MODAL = 'lengow_last_plugin_modal';

	/* Configuration parameters */
	const PARAM_DEFAULT_VALUE = 'default_value';
	const PARAM_EXPORT = 'export';
	const PARAM_EXPORT_TOOLBOX = 'export_toolbox';
	const PARAM_GLOBAL = 'global';
	const PARAM_LABEL = 'label';
	const PARAM_LEGEND = 'legend';
	const PARAM_PLACEHOLDER = 'placeholder';
	const PARAM_RESET_TOKEN = 'reset_token';
	const PARAM_RETURN = 'return';
	const PARAM_SECRET = 'secret';
	const PARAM_SHOP = 'shop';
	const PARAM_UPDATE = 'update';

	/* Configuration value return type */
	const RETURN_TYPE_BOOLEAN = 'boolean';
	const RETURN_TYPE_INTEGER = 'integer';
	const RETURN_TYPE_ARRAY = 'array';

	/**
	 * @var array params correspondence keys for toolbox.
	 */
	public static $generic_param_keys = array(
		self::ACCOUNT_ID                           => 'account_id',
		self::ACCESS_TOKEN                         => 'access_token',
		self::SECRET                               => 'secret',
		self::CMS_TOKEN                            => 'cms_token',
		self::AUTHORIZED_IP_ENABLED                => 'authorized_ip_enabled',
		self::AUTHORIZED_IPS                       => 'authorized_ips',
		self::TRACKING_ENABLED                     => 'tracking_enabled',
		self::TRACKING_ID                          => 'tracking_id',
		self::DEBUG_MODE_ENABLED                   => 'debug_mode_enabled',
		self::REPORT_MAIL_ENABLED                  => 'report_mail_enabled',
		self::REPORT_MAILS                         => 'report_mails',
		self::AUTHORIZATION_TOKEN                  => 'authorization_token',
		self::PLUGIN_DATA                          => 'plugin_data',
		self::ACCOUNT_STATUS_DATA                  => 'account_status_data',
		self::SHOP_TOKEN                           => 'shop_token',
		self::SHOP_ACTIVE                          => 'shop_active',
		self::CATALOG_IDS                          => 'catalog_ids',
		self::SELECTION_ENABLED                    => 'selection_enabled',
		self::EXPORT_PRODUCT_TYPES                 => 'export_product_types',
		self::EXPORT_FORMAT                        => 'export_format',
		self::EXPORT_FILE_ENABLED                  => 'export_file_enabled',
		self::WAITING_SHIPMENT_ORDER_ID            => 'waiting_shipment_order_id',
		self::SHIPPED_ORDER_ID                     => 'shipped_order_id',
		self::CANCELED_ORDER_ID                    => 'canceled_order_id',
		self::SHIPPED_BY_MARKETPLACE_ORDER_ID      => 'shipped_by_marketplace_order_id',
		self::SYNCHRONIZATION_DAY_INTERVAL         => 'synchronization_day_interval',
		self::DEFAULT_IMPORT_CARRIER_ID            => 'default_import_carrier_id',
		self::CURRENCY_CONVERSION_ENABLED          => 'currency_conversion_enabled',
		self::B2B_WITHOUT_TAX_ENABLED              => 'b2b_without_tax_enabled',
		self::SHIPPED_BY_MARKETPLACE_ENABLED       => 'shipped_by_marketplace_enabled',
		self::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED => 'shipped_by_marketplace_stock_enabled',
		self::SYNCHRONIZATION_IN_PROGRESS          => 'synchronization_in_progress',
		self::LAST_UPDATE_EXPORT                   => 'last_update_export',
		self::LAST_UPDATE_CRON_SYNCHRONIZATION     => 'last_update_cron_synchronization',
		self::LAST_UPDATE_MANUAL_SYNCHRONIZATION   => 'last_update_manual_synchronization',
		self::LAST_UPDATE_ACTION_SYNCHRONIZATION   => 'last_update_action_synchronization',
		self::LAST_UPDATE_CATALOG                  => 'last_update_catalog',
		self::LAST_UPDATE_MARKETPLACE              => 'last_update_marketplace',
		self::LAST_UPDATE_ACCOUNT_STATUS_DATA      => 'last_update_account_status_data',
		self::LAST_UPDATE_OPTION_CMS               => 'last_update_option_cms',
		self::LAST_UPDATE_SETTING                  => 'last_update_setting',
		self::LAST_UPDATE_PLUGIN_DATA              => 'last_update_plugin_data',
		self::LAST_UPDATE_AUTHORIZATION_TOKEN      => 'last_update_authorization_token',
		self::LAST_UPDATE_PLUGIN_MODAL             => 'last_update_plugin_modal',
	);

	/**
	 * Get all Lengow configuration keys.
	 *
	 * @param string $key Lengow configuration key
	 *
	 * @return array
	 */
	public static function get_keys( $key = null ) {
		static $keys = null;
		if ( null === $keys ) {
			$locale = new Lengow_Translation();
			$keys   = array(
				self::ACCOUNT_ID                           => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_EXPORT => false,
					self::PARAM_LABEL  => $locale->t( 'lengow_settings.lengow_account_id_title' ),
				),
				self::ACCESS_TOKEN                         => array(
					self::PARAM_GLOBAL      => true,
					self::PARAM_EXPORT      => false,
					self::PARAM_LABEL       => $locale->t( 'lengow_settings.lengow_access_token_title' ),
					self::PARAM_SECRET      => true,
					self::PARAM_RESET_TOKEN => true,
				),
				self::SECRET                               => array(
					self::PARAM_GLOBAL      => true,
					self::PARAM_EXPORT      => false,
					self::PARAM_LABEL       => $locale->t( 'lengow_settings.lengow_secret_token_title' ),
					self::PARAM_SECRET      => true,
					self::PARAM_RESET_TOKEN => true,
				),
				self::CMS_TOKEN                            => array(
					self::PARAM_GLOBAL         => true,
					self::PARAM_SHOP           => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_token_title' ),
				),
				self::AUTHORIZED_IP_ENABLED                => array(
					self::PARAM_GLOBAL         => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_ip_enable_title' ),
					self::PARAM_LEGEND         => $locale->t( 'lengow_settings.lengow_ip_enable_legend' ),
					self::PARAM_DEFAULT_VALUE  => 0,
					self::PARAM_RETURN         => self::RETURN_TYPE_BOOLEAN,
				),
				self::AUTHORIZED_IPS                       => array(
					self::PARAM_GLOBAL         => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_authorized_ip_title' ),
					self::PARAM_LEGEND         => $locale->t( 'lengow_settings.lengow_authorized_ip_legend' ),
					self::PARAM_RETURN         => self::RETURN_TYPE_ARRAY,
				),
				self::TRACKING_ENABLED                     => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_tracking_enabled_title' ),
					self::PARAM_DEFAULT_VALUE => 0,
					self::PARAM_RETURN        => self::RETURN_TYPE_BOOLEAN,
				),
				self::TRACKING_ID                          => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_tracking_id_title' ),
					self::PARAM_LEGEND        => $locale->t( 'lengow_settings.lengow_tracking_id_legend' ),
					self::PARAM_DEFAULT_VALUE => 'id',
				),
				self::DEBUG_MODE_ENABLED                   => array(
					self::PARAM_GLOBAL         => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_debug_enabled_title' ),
					self::PARAM_DEFAULT_VALUE  => 0,
					self::PARAM_RETURN         => self::RETURN_TYPE_BOOLEAN,
				),
				self::REPORT_MAIL_ENABLED                  => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_report_mail_enabled_title' ),
					self::PARAM_DEFAULT_VALUE => 1,
					self::PARAM_RETURN        => self::RETURN_TYPE_BOOLEAN,
				),
				self::REPORT_MAILS                         => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_PLACEHOLDER   => $locale->t( 'lengow_settings.lengow_report_mail_address_title' ),
					self::PARAM_LEGEND        => $locale->t( 'lengow_settings.lengow_report_mail_address_legend' ),
					self::PARAM_DEFAULT_VALUE => '',
					self::PARAM_RETURN        => self::RETURN_TYPE_ARRAY,
				),
				self::AUTHORIZATION_TOKEN                  => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_EXPORT => false,
				),
				self::PLUGIN_DATA                          => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_EXPORT => false,
				),
				self::ACCOUNT_STATUS_DATA                  => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_EXPORT => false,
				),
				self::SHOP_ACTIVE                          => array(
					self::PARAM_SHOP           => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_store_active_title' ),
					self::PARAM_RETURN         => self::RETURN_TYPE_BOOLEAN,
				),
				self::CATALOG_IDS                          => array(
					self::PARAM_SHOP           => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_catalog_id_title' ),
					self::PARAM_LEGEND         => $locale->t( 'lengow_settings.lengow_catalog_id_legend' ),
					self::PARAM_UPDATE         => true,
					self::PARAM_RETURN         => self::RETURN_TYPE_ARRAY,
				),
				self::SELECTION_ENABLED                    => array(
					self::PARAM_SHOP          => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_selection_enabled_title' ),
					self::PARAM_LEGEND        => $locale->t( 'lengow_settings.lengow_selection_enabled_legend' ),
					self::PARAM_DEFAULT_VALUE => 0,
					self::PARAM_RETURN        => self::RETURN_TYPE_BOOLEAN,
				),
				self::EXPORT_PRODUCT_TYPES                 => array(
					self::PARAM_SHOP          => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_product_types_title' ),
					self::PARAM_LEGEND        => $locale->t( 'lengow_settings.lengow_product_types_legend' ),
					self::PARAM_DEFAULT_VALUE => array( 'simple', 'variable', 'external', 'grouped' ),
					self::PARAM_RETURN        => self::RETURN_TYPE_ARRAY,
				),
				self::EXPORT_FORMAT                        => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_export_format_title' ),
					self::PARAM_DEFAULT_VALUE => Lengow_Feed::FORMAT_CSV,
				),
				self::EXPORT_FILE_ENABLED                  => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_export_file_enabled_title' ),
					self::PARAM_LEGEND        => $locale->t( 'lengow_settings.lengow_export_file_enabled_legend' ),
					self::PARAM_DEFAULT_VALUE => 0,
					self::PARAM_RETURN        => self::RETURN_TYPE_BOOLEAN,
				),
				self::WAITING_SHIPMENT_ORDER_ID            => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_id_waiting_shipment_title' ),
					self::PARAM_DEFAULT_VALUE => Lengow_Main::compare_version( '2.2' ) ? 'wc-on-hold' : 'on-hold',
				),
				self::SHIPPED_ORDER_ID                     => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_id_shipped_title' ),
					self::PARAM_DEFAULT_VALUE => Lengow_Main::compare_version( '2.2' ) ? 'wc-completed' : 'completed',
				),
				self::CANCELED_ORDER_ID                    => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_id_cancel_title' ),
					self::PARAM_DEFAULT_VALUE => Lengow_Main::compare_version( '2.2' ) ? 'wc-cancelled' : 'cancelled',
				),
				self::SHIPPED_BY_MARKETPLACE_ORDER_ID      => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_id_shipped_by_mp_title' ),
					self::PARAM_DEFAULT_VALUE => Lengow_Main::compare_version( '2.2' ) ? 'wc-completed' : 'completed',
				),
				self::SYNCHRONIZATION_DAY_INTERVAL         => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_import_days_title' ),
					self::PARAM_LEGEND        => $locale->t( 'lengow_settings.lengow_import_days_legend' ),
					self::PARAM_DEFAULT_VALUE => 3,
					self::PARAM_UPDATE        => true,
					self::PARAM_RETURN        => self::RETURN_TYPE_INTEGER,
				),
				self::DEFAULT_IMPORT_CARRIER_ID            => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t(
						'lengow_settings.lengow_import_default_shipping_method_title'
					),
					self::PARAM_DEFAULT_VALUE => 'flat_rate',
				),
				self::CURRENCY_CONVERSION_ENABLED          => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'order_setting.screen.currency_conversion_label' ),
					self::PARAM_DEFAULT_VALUE => true,
					self::PARAM_RETURN        => self::RETURN_TYPE_BOOLEAN,
				),
				self::B2B_WITHOUT_TAX_ENABLED              => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'order_setting.screen.import_b2b_without_tax_label' ),
					self::PARAM_DEFAULT_VALUE => false,
					self::PARAM_RETURN        => self::RETURN_TYPE_BOOLEAN,
				),
				self::SHIPPED_BY_MARKETPLACE_ENABLED       => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_import_ship_mp_enabled_title' ),
					self::PARAM_DEFAULT_VALUE => 0,
					self::PARAM_RETURN        => self::RETURN_TYPE_BOOLEAN,
				),
				self::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED => array(
					self::PARAM_GLOBAL        => true,
					self::PARAM_LABEL         => $locale->t( 'lengow_settings.lengow_import_stock_ship_mp_title' ),
					self::PARAM_LEGEND        => $locale->t( 'lengow_settings.lengow_import_stock_ship_mp_legend' ),
					self::PARAM_DEFAULT_VALUE => 0,
					self::PARAM_RETURN        => self::RETURN_TYPE_BOOLEAN,
				),
				self::SYNCHRONIZATION_IN_PROGRESS          => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_EXPORT => false,
					self::PARAM_LABEL  => $locale->t( 'lengow_settings.lengow_import_in_progress_title' ),
				),
				self::LAST_UPDATE_EXPORT                   => array(
					self::PARAM_SHOP           => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_last_export_title' ),
					self::PARAM_RETURN         => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_CRON_SYNCHRONIZATION     => array(
					self::PARAM_GLOBAL         => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_last_import_cron_title' ),
					self::PARAM_RETURN         => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_MANUAL_SYNCHRONIZATION   => array(
					self::PARAM_GLOBAL         => true,
					self::PARAM_EXPORT_TOOLBOX => false,
					self::PARAM_LABEL          => $locale->t( 'lengow_settings.lengow_last_import_manual_title' ),
					self::PARAM_RETURN         => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_ACTION_SYNCHRONIZATION   => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_CATALOG                  => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_MARKETPLACE              => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_ACCOUNT_STATUS_DATA      => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_OPTION_CMS               => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_SETTING                  => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_PLUGIN_DATA              => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_AUTHORIZATION_TOKEN      => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
				self::LAST_UPDATE_PLUGIN_MODAL             => array(
					self::PARAM_GLOBAL => true,
					self::PARAM_RETURN => self::RETURN_TYPE_INTEGER,
				),
			);
		}

		return isset( $key, $keys[ $key ] ) ? $keys[ $key ] : $keys;
	}

	/**
	 * Get Lengow value.
	 *
	 * @param string $key Lengow configuration key
	 *
	 * @return mixed
	 */
	public static function get( $key ) {

		return get_option( $key );
	}

	/**
	 * Update Lengow value by shop.
	 *
	 * @param string $key Lengow configuration key
	 * @param mixed $value configuration value
	 */
	public static function add_value( $key, $value ) {
		add_option( $key, $value );
	}

	/**
	 * Update Lengow value by shop.
	 *
	 * @param string $key Lengow configuration key
	 * @param mixed $value configuration value
	 */
	public static function update_value( $key, $value ) {
		update_option( $key, $value );
	}

	/**
	 * Delete Lengow value by shop.
	 *
	 * @param string $key Lengow configuration key
	 */
	public static function delete( $key ) {
		delete_option( $key );
	}

	/**
	 * Get Valid Account / Access / Secret.
	 *
	 * @return array
	 */
	public static function get_access_id() {
		if ( '' !== self::get( self::ACCOUNT_ID )
		     && '' !== self::get( self::ACCESS_TOKEN )
		     && '' !== self::get( self::SECRET )
		) {
			return array(
				(int) self::get( self::ACCOUNT_ID ),
				self::get( self::ACCESS_TOKEN ),
				self::get( self::SECRET ),
			);
		}

		return array( null, null, null );
	}

	/**
	 * Set Valid Account id / Access token / Secret token.
	 *
	 * @param array $access_ids Account id / Access token / Secret token
	 *
	 * @return boolean
	 */
	public static function set_access_ids( $access_ids ) {
		$count    = 0;
		$list_key = array( self::ACCOUNT_ID, self::ACCESS_TOKEN, self::SECRET );
		foreach ( $access_ids as $key => $value ) {
			if ( ! in_array( $key, $list_key, true ) ) {
				continue;
			}
			if ( '' !== $value ) {
				$count ++;
				self::update_value( $key, $value );
			}
		}

		return $count === count( $list_key );
	}

	/**
	 * Reset access ids for old customer.
	 */
	public static function reset_access_ids() {
		$access_ids = array( self::ACCOUNT_ID, self::ACCESS_TOKEN, self::SECRET );
		foreach ( $access_ids as $access_id ) {
			$value = self::get( $access_id );
			if ( '' !== $value ) {
				self::update_value( $access_id, '' );
			}
		}
	}

	/**
	 * Reset authorization token.
	 */
	public static function reset_authorization_token() {
		self::update_value( self::AUTHORIZATION_TOKEN, '' );
		self::update_value( self::LAST_UPDATE_AUTHORIZATION_TOKEN, '' );
	}

	/**
	 * Check if is a new merchant.
	 *
	 * @return boolean
	 */
	public static function is_new_merchant() {
		list( $account_id, $access_token, $secret_token ) = self::get_access_id();

		return ! ( null !== $account_id && null !== $access_token && null !== $secret_token );
	}

	/**
	 * Get catalogs ids.
	 *
	 * @return array
	 */
	public static function get_catalog_ids() {
		$catalog_ids      = array();
		$shop_catalog_ids = self::get( self::CATALOG_IDS );
		if ( strlen( $shop_catalog_ids ) > 0 && $shop_catalog_ids != 0 ) {
			$ids = trim( str_replace( array( "\r\n", ',', '-', '|', ' ', '/' ), ';', $shop_catalog_ids ), ';' );
			$ids = array_filter( explode( ';', $ids ) );
			foreach ( $ids as $id ) {
				if ( is_numeric( $id ) && $id > 0 ) {
					$catalog_ids[] = (int) $id;
				}
			}
		}

		return $catalog_ids;
	}

	/**
	 * Set catalog ids.
	 *
	 * @param array $catalog_ids Lengow catalog ids
	 *
	 * @return boolean
	 */
	public static function set_catalog_ids( $catalog_ids ) {
		$value_change     = false;
		$shop_catalog_ids = self::get_catalog_ids();
		foreach ( $catalog_ids as $catalog_id ) {
			if ( $catalog_id > 0 && is_numeric( $catalog_id ) && ! in_array( $catalog_id, $shop_catalog_ids, true ) ) {
				$shop_catalog_ids[] = (int) $catalog_id;
				$value_change       = true;
			}
		}
		self::update_value( self::CATALOG_IDS, implode( ';', $shop_catalog_ids ) );

		return $value_change;
	}

	/**
	 * Reset all catalog ids.
	 */
	public static function reset_catalog_ids() {
		if ( self::shop_is_active() ) {
			self::update_value( self::CATALOG_IDS, '' );
			self::update_value( self::SHOP_ACTIVE, false );
		}
	}

	/**
	 * Recovers if a shop is active or not.
	 *
	 * @return boolean
	 */
	public static function shop_is_active() {
		return (bool) self::get( self::SHOP_ACTIVE );
	}

	/**
	 * Set active shop or not.
	 *
	 * @return boolean
	 */
	public static function set_active_shop() {
		$shop_is_active   = self::shop_is_active();
		$catalog_ids      = self::get_catalog_ids();
		$shop_has_catalog = ! empty( $catalog_ids );
		self::update_value( self::SHOP_ACTIVE, $shop_has_catalog );

		return $shop_is_active !== $shop_has_catalog;
	}

	/**
	 * Recovers if Debug Mode is active or not.
	 *
	 * @return boolean
	 */
	public static function debug_mode_is_active() {
		return (bool) self::get( self::DEBUG_MODE_ENABLED );
	}

	/**
	 * Get Report Email Address for error report.
	 *
	 * @return array
	 */
	public static function get_report_email_address() {
		$report_email_address = [];
		$emails               = self::get( self::REPORT_MAILS );
		$emails               = trim( str_replace( array( "\r\n", ',', ' ' ), ';', $emails ), ';' );
		$emails               = explode( ';', $emails );
		foreach ( $emails as $email ) {
			if ( $email !== '' && is_email( $email ) ) {
				$report_email_address[] = $email;
			}
		}
		if ( empty( $report_email_address ) ) {
			$report_email_address[] = self::get( 'admin_email' );
		}

		return $report_email_address;
	}

	/**
	 * Get authorized IPS.
	 *
	 * @return array
	 */
	public static function get_authorized_ips() {
		$authorized_ips = array();
		$ips            = self::get( self::AUTHORIZED_IPS );
		if ( ! empty( $ips ) ) {
			$authorized_ips = trim( str_replace( array( "\r\n", ',', '-', '|', ' ' ), ';', $ips ), ';' );
			$authorized_ips = array_filter( explode( ';', $authorized_ips ) );
		}

		return $authorized_ips;
	}

	/**
	 * Get product types for export.
	 *
	 * @return array
	 */
	public static function get_product_types() {
		$product_types = self::get( self::EXPORT_PRODUCT_TYPES );

		return is_array( $product_types ) ? $product_types : json_decode( $product_types, true );
	}

	/**
	 * Reset all Lengow settings.
	 *
	 * @param boolean $overwrite rewrite all Lengow settings
	 *
	 * @return boolean
	 */
	public static function reset_all( $overwrite = false ) {
		$keys = self::get_keys();
		foreach ( $keys as $key => $value ) {
			$val = isset( $value[ self::PARAM_DEFAULT_VALUE ] ) ? $value[ self::PARAM_DEFAULT_VALUE ] : '';
			if ( $overwrite ) {
				if ( isset( $value[ self::PARAM_DEFAULT_VALUE ] ) ) {
					self::add_value( $key, $val );
				}
			} else {
				$old_value = self::get( $key );
				if ( ! $old_value ) {
					self::add_value( $key, $val );
				}
			}
		}
		if ( $overwrite ) {
			Lengow_Main::log( Lengow_Log::CODE_SETTING, Lengow_Main::set_log_message( 'log.setting.setting_reset' ) );
		} else {
			Lengow_Main::log( Lengow_Log::CODE_SETTING, Lengow_Main::set_log_message( 'log.setting.setting_updated' ) );
		}

		return true;
	}

	/**
	 * Active ip authorization if authorized ips exist for old customer.
	 */
	public static function check_ip_authorization() {
		$authorized_ips = self::get( self::AUTHORIZED_IPS );
		if ( $authorized_ips !== '' ) {
			self::update_value( self::AUTHORIZED_IP_ENABLED, true );
		}
	}

	/**
	 * Migrate product selection for old version.
	 */
	public static function migrate_product_selection() {
		$export_all_product = self::get( 'lengow_export_all_product' );
		if ( false !== $export_all_product ) {
			$value = ( '' === $export_all_product || '0' === $export_all_product ) ? 1 : 0;
			self::update_value( self::SELECTION_ENABLED, $value );
			self::delete( 'lengow_export_all_product' );
		}
	}

	/**
	 * Migrate product types for old version - convert string to array.
	 */
	public static function migrate_product_types() {
		$old_product_types = self::get( 'lengow_export_type' );
		if ( false !== $old_product_types ) {
			$old_product_types = json_decode( $old_product_types, true );
			if ( is_array( $old_product_types ) ) {
				self::update_value( self::EXPORT_PRODUCT_TYPES, $old_product_types );
			}
			self::delete( 'lengow_export_type' );
		}
	}

	/**
	 * Get all values.
	 *
	 * @param boolean $all get all shop value
	 * @param boolean $shop get only shop value for get_option_data()
	 * @param boolean $toolbox get all values for toolbox or not
	 *
	 * @return array
	 */
	public static function get_all_values( $all = true, $shop = false, $toolbox = false ) {
		$rows = array();
		$keys = self::get_keys();
		foreach ( $keys as $key => $key_params ) {
			if ( $all ) {
				$rows[ $key ] = self::get( $key );
			} else {
				$value = null;
				if ( ( isset( $key_params[ self::PARAM_EXPORT ] ) && ! $key_params[ self::PARAM_EXPORT ] )
				     || ( $toolbox
				          && isset( $key_params[ self::PARAM_EXPORT_TOOLBOX ] )
				          && ! $key_params[ self::PARAM_EXPORT_TOOLBOX ]
				     )
				) {
					continue;
				}
				if ( $shop ) {
					if ( isset( $key_params[ self::PARAM_SHOP ] ) && $key_params[ self::PARAM_SHOP ] ) {
						$value = self::get( $key );
						// added a check to differentiate the token shop from the cms token which are the same.
						$generic_key          = self::CMS_TOKEN === $key
							? self::$generic_param_keys[ self::SHOP_TOKEN ]
							: self::$generic_param_keys[ $key ];
						$rows[ $generic_key ] = self::get_value_with_correct_type( $key, $value );
					}
				} else if ( isset( $key_params[ self::PARAM_GLOBAL ] ) && $key_params[ self::PARAM_GLOBAL ] ) {
					$value                                     = self::get( $key );
					$rows[ self::$generic_param_keys[ $key ] ] = self::get_value_with_correct_type( $key, $value );
				}
			}
		}

		return $rows;
	}

	/**
	 * Check value and create a log if necessary.
	 *
	 * @param string $key name of lengow setting
	 * @param mixed $value setting value
	 */
	public static function check_and_log( $key, $value ) {
		$keys = self::get_keys();
		if ( array_key_exists( $key, $keys ) ) {
			$setting   = $keys[ $key ];
			$old_value = self::get( $key );
			if ( $old_value != $value ) {
				if ( isset( $setting[ self::PARAM_SECRET ] ) && $setting[ self::PARAM_SECRET ] ) {
					$value     = preg_replace( "/[a-zA-Z0-9]/", '*', $value );
					$old_value = preg_replace( "/[a-zA-Z0-9]/", '*', $old_value );
				}
				Lengow_Main::log(
					Lengow_Log::CODE_SETTING,
					Lengow_Main::set_log_message(
						'log.setting.setting_change',
						array(
							'key'       => self::$generic_param_keys[ $key ],
							'old_value' => is_array( $old_value ) ? implode( ',', $old_value ) : $old_value,
							'value'     => is_array( $value ) ? implode( ',', $value ) : $value,
						)
					)
				);
				// save last update date for a specific settings (change synchronisation interval time).
				if ( isset( $setting[ self::PARAM_UPDATE ] ) && $setting[ self::PARAM_UPDATE ] ) {
					self::update_value( self::LAST_UPDATE_SETTING, time() );
				}
				// reset the authorization token when a configuration parameter is changed.
				if ( isset( $setting[ self::PARAM_RESET_TOKEN ] ) && $setting[ self::PARAM_RESET_TOKEN ] ) {
					self::reset_authorization_token();
				}
			}
		}
	}

	/**
	 * Get configuration value in correct type.
	 *
	 * @param string $key Lengow configuration key
	 * @param string|null $value configuration value for conversion
	 *
	 * @return array|boolean|integer|string|string[]|null
	 */
	private static function get_value_with_correct_type( $key, $value = null ) {
		$key_params = self::get_keys( $key );
		if ( isset( $key_params[ self::PARAM_RETURN ] ) ) {
			switch ( $key_params[ self::PARAM_RETURN ] ) {
				case self::RETURN_TYPE_BOOLEAN:
					return (bool) $value;
				case self::RETURN_TYPE_INTEGER:
					return (int) $value;
				case self::RETURN_TYPE_ARRAY:
					if ( ! is_array( $value ) ) {
						return ! empty( $value )
							? explode( ';', trim( str_replace( array( "\r\n", ',', ' ' ), ';', $value ), ';' ) )
							: array();
					}
			}
		}

		return $value;
	}
}
