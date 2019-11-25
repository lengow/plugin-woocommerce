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
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Configuration Class.
 */
class Lengow_Configuration {

	/**
	 * Get all Lengow configuration keys.
	 *
	 * @return array
	 */
	public static function get_keys() {
		static $keys = null;
		if ( null === $keys ) {
			$locale = new Lengow_Translation();
			$keys   = array(
				'lengow_token'                          => array(
					'global' => true,
					'shop'   => true,
					'label'  => $locale->t( 'lengow_settings.lengow_token_title' ),
				),
				'lengow_store_enabled'                  => array(
					'shop'  => true,
					'label' => $locale->t( 'lengow_settings.lengow_store_active_title' ),
				),
				'lengow_account_id'                     => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_account_id_title' ),
				),
				'lengow_access_token'                   => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_access_token_title' ),
					'secret' => true,
				),
				'lengow_secret_token'                   => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_secret_token_title' ),
					'secret' => true,
				),
				'lengow_catalog_id'                     => array(
					'shop'   => true,
					'label'  => $locale->t( 'lengow_settings.lengow_catalog_id_title' ),
					'legend' => $locale->t( 'lengow_settings.lengow_catalog_id_legend' ),
					'update' => true,
				),
				'lengow_ip_enabled'                     => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_ip_enable_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_ip_enable_legend' ),
					'default_value' => 0,
				),
				'lengow_authorized_ip'                  => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_authorized_ip_title' ),
					'legend' => $locale->t( 'lengow_settings.lengow_authorized_ip_legend' ),
				),
				'lengow_tracking_enabled'               => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_tracking_enabled_title' ),
					'default_value' => 0,
				),
				'lengow_tracking_id'                    => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_tracking_id_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_tracking_id_legend' ),
					'default_value' => 'id',
				),
				'lengow_last_order_statistic_update'    => array(
					'global' => true,
				),
				'lengow_order_statistic'                => array(
					'export' => false,
					'global' => true,
				),
				'lengow_last_option_update'             => array(
					'global' => true,
				),
				'lengow_last_account_status_update'     => array(
					'global' => true,
				),
				'lengow_account_status'                 => array(
					'export' => false,
					'global' => true,
				),
				'lengow_catalog_update'                 => array(
					'global' => true,
				),
				'lengow_marketplace_update'             => array(
					'global' => true,
				),
				'lengow_last_setting_update'            => array(
					'global' => true,
				),
				'lengow_selection_enabled'              => array(
					'shop'          => true,
					'label'         => $locale->t( 'lengow_settings.lengow_selection_enabled_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_selection_enabled_legend' ),
					'default_value' => 0,
				),
				'lengow_export_format'                  => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_export_format_title' ),
					'default_value' => 'csv',
				),
				'lengow_export_file_enabled'            => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_export_file_enabled_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_export_file_enabled_legend' ),
					'default_value' => 0,
				),
				'lengow_product_types'                  => array(
					'shop'          => true,
					'label'         => $locale->t( 'lengow_settings.lengow_product_types_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_product_types_legend' ),
					'default_value' => array( 'simple', 'variable', 'external', 'grouped' ),
				),
				'lengow_last_export'                    => array(
					'shop'  => true,
					'label' => $locale->t( 'lengow_settings.lengow_last_export_title' ),
				),
				'lengow_report_mail_enabled'            => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_report_mail_enabled_title' ),
					'default_value' => 1,
				),
				'lengow_report_mail_address'            => array(
					'global'        => true,
					'placeholder'   => $locale->t( 'lengow_settings.lengow_report_mail_address_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_report_mail_address_legend' ),
					'default_value' => '',
				),
				'lengow_import_default_shipping_method' => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_import_default_shipping_method_title' ),
					'default_value' => 'flat_rate',
				),
				'lengow_id_waiting_shipment'            => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_id_waiting_shipment_title' ),
					'default_value' => Lengow_Main::compare_version( '2.2' ) ? 'wc-on-hold' : 'on-hold',
				),
				'lengow_id_shipped'                     => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_id_shipped_title' ),
					'default_value' => Lengow_Main::compare_version( '2.2' ) ? 'wc-completed' : 'completed',
				),
				'lengow_id_cancel'                      => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_id_cancel_title' ),
					'default_value' => Lengow_Main::compare_version( '2.2' ) ? 'wc-cancelled' : 'cancelled',
				),
				'lengow_id_shipped_by_mp'               => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_id_shipped_by_mp_title' ),
					'default_value' => Lengow_Main::compare_version( '2.2' ) ? 'wc-completed' : 'completed',
				),
				'lengow_import_days'                    => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_import_days_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_import_days_legend' ),
					'default_value' => 3,
					'update'        => true,
				),
				'lengow_import_ship_mp_enabled'         => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_import_ship_mp_enabled_title' ),
					'default_value' => 0,
				),
				'lengow_import_stock_ship_mp'           => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_import_stock_ship_mp_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_import_stock_ship_mp_legend' ),
					'default_value' => 0,
				),
				'lengow_preprod_enabled'                => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_preprod_enabled_title' ),
					'default_value' => 0,
				),
				'lengow_import_in_progress'             => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_import_in_progress_title' ),
				),
				'lengow_last_import_manual'             => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_last_import_manual_title' ),
				),
				'lengow_last_import_cron'               => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_last_import_cron_title' ),
				),
			);
		}

		return $keys;
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
		if ( strlen( self::get( 'lengow_account_id' ) ) > 0
		     && strlen( self::get( 'lengow_access_token' ) ) > 0
		     && strlen( self::get( 'lengow_secret_token' ) ) > 0
		) {
			return array(
				(int) self::get( 'lengow_account_id' ),
				self::get( 'lengow_access_token' ),
				self::get( 'lengow_secret_token' ),
			);
		} else {
			return array( null, null, null );
		}
	}

	/**
	 * Set Valid Account id / Access token / Secret token.
	 *
	 * @param array $access_ids Account id / Access token / Secret token
	 */
	public static function set_access_ids( $access_ids ) {
		$list_key = array( 'lengow_account_id', 'lengow_access_token', 'lengow_secret_token' );
		foreach ( $access_ids as $key => $value ) {
			if ( ! in_array( $key, array_keys( $list_key ) ) ) {
				continue;
			}
			if ( strlen( $value ) > 0 ) {
				self::update_value( $key, $value );
			}
		}
	}

	/**
	 * Get catalogs ids.
	 *
	 * @return array
	 */
	public static function get_catalog_ids() {
		$catalog_ids      = array();
		$shop_catalog_ids = self::get( 'lengow_catalog_id' );
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
			if ( ! in_array( $catalog_id, $shop_catalog_ids ) && is_numeric( $catalog_id ) && $catalog_id > 0 ) {
				$shop_catalog_ids[] = (int) $catalog_id;
				$value_change       = true;
			}
		}
		self::update_value( 'lengow_catalog_id', implode( ';', $shop_catalog_ids ) );

		return $value_change;
	}

	/**
	 * Recovers if a shop is active or not.
	 *
	 * @return boolean
	 */
	public static function shop_is_active() {
		return (bool) self::get( 'lengow_store_enabled' );
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
		self::update_value( 'lengow_store_enabled', $shop_has_catalog );

		return $shop_is_active !== $shop_has_catalog ? true : false;
	}

	/**
	 * Get Report Email Address for error report.
	 *
	 * @return array
	 */
	public static function get_report_email_address() {
		$report_email_address = [];
		$emails               = self::get( 'lengow_report_mail_address' );
		$emails               = trim( str_replace( array( "\r\n", ',', ' ' ), ';', $emails ), ';' );
		$emails               = explode( ';', $emails );
		foreach ( $emails as $email ) {
			if ( strlen( $email ) > 0 && is_email( $email ) ) {
				$report_email_address[] = $email;
			}
		}
		if ( empty( $report_email_address ) ) {
			$report_email_address[] = self::get( 'admin_email' );
		}

		return $report_email_address;
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
			$val = isset( $value['default_value'] ) ? $value['default_value'] : '';
			if ( $overwrite ) {
				if ( isset( $value['default_value'] ) ) {
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
		$authorized_ips = self::get( 'lengow_authorized_ip' );
		if ( strlen( $authorized_ips ) > 0 ) {
			self::update_value( 'lengow_ip_enabled', true );
		}
	}

	/**
	 * Migrate product selection for old version.
	 */
	public static function migrate_product_selection() {
		$export_all_product = self::get( 'lengow_export_all_product' );
		if ( false !== $export_all_product ) {
			$value = ( '' === $export_all_product || '0' === $export_all_product ) ? 1 : 0;
			self::update_value( 'lengow_selection_enabled', $value );
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
				self::update_value( 'lengow_product_types', $old_product_types );
			}
			self::delete( 'lengow_export_type' );
		}
	}

	/**
	 * Get all values.
	 *
	 * @param boolean $all get all shop value
	 * @param boolean $shop get only shop value for get_option_data()
	 *
	 * @return array
	 */
	public static function get_all_values( $all = true, $shop = false ) {
		$rows = array();
		$keys = self::get_keys();
		foreach ( $keys as $key => $value ) {
			if ( $all ) {
				$rows[ $key ] = self::get( $key );
			} else {
				if ( isset( $value['export'] ) && ! $value['export'] ) {
					continue;
				}
				if ( $shop ) {
					if ( isset( $value['shop'] ) && $value['shop'] ) {
						$key_value    = self::get( $key );
						$rows[ $key ] = is_array( $key_value ) ? implode( ",", $key_value ) : $key_value;
					}
				} else {
					if ( isset( $value['global'] ) && $value['global'] ) {
						$key_value    = self::get( $key );
						$rows[ $key ] = is_array( $key_value ) ? implode( ",", $key_value ) : $key_value;
					}
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
				if ( isset( $setting['secret'] ) && $setting['secret'] ) {
					$value     = preg_replace( "/[a-zA-Z0-9]/", '*', $value );
					$old_value = preg_replace( "/[a-zA-Z0-9]/", '*', $old_value );
				}
				Lengow_Main::log(
					Lengow_Log::CODE_SETTING,
					Lengow_Main::set_log_message(
						'log.setting.setting_change',
						array(
							'key'       => $key,
							'old_value' => is_array( $old_value ) ? implode( ',', $old_value ) : $old_value,
							'value'     => is_array( $value ) ? implode( ',', $value ) : $value,
						)
					)
				);
				// save last update date for a specific settings (change synchronisation interval time).
				if ( isset( $setting['update'] ) && $setting['update'] ) {
					self::update_value( 'lengow_last_setting_update', date( 'Y-m-d H:i:s' ) );
				}
			}
		}
	}
}
