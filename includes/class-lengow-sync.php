<?php
/**
 * All components to create and synchronise account
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
 * @author      Team module <team-module@lengow.com>
 * @copyright   2017 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Sync Class.
 */
class Lengow_Sync {

	/**
	 * @var integer cache time for statistic, account status and cms options.
	 */
	protected static $_cache_time = 18000;

	/**
	 * @var array valid sync actions
	 */
	public static $sync_actions = array(
		'order',
		'action',
		'catalog',
		'option',
	);

	/**
	 * Get Sync Data (Inscription / Update).
	 *
	 * @return array
	 */
	public static function get_sync_data() {
		global $wp_version;
		$lengow_export    = new Lengow_Export();
		$data             = array(
			'domain_name'    => $_SERVER["SERVER_NAME"],
			'token'          => Lengow_Main::get_token(),
			'type'           => 'woocommerce',
			'version'        => $wp_version,
			'plugin_version' => LENGOW_VERSION,
			'email'          => Lengow_Configuration::get( 'admin_email' ),
			'cron_url'       => Lengow_Main::get_cron_url(),
			'return_url'     => 'http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"],
			'shops'          => array(),
		);
		$data['shops'][1] = array(
			'token'                   => Lengow_Main::get_token(),
			'shop_name'               => Lengow_Configuration::get( 'blogname' ),
			'domain_url'              => $_SERVER["SERVER_NAME"],
			'feed_url'                => Lengow_Main::get_export_url(),
			'total_product_number'    => $lengow_export->get_total_product(),
			'exported_product_number' => $lengow_export->get_total_export_product(),
			'enabled'                 => Lengow_Configuration::shop_is_active(),
		);

		return $data;
	}

	/**
	 * Set shop configuration key from Lengow.
	 *
	 * @param array $params Lengow API credentials
	 */
	public static function sync( $params ) {
		Lengow_Configuration::set_access_ids(
			array(
				'lengow_account_id'   => $params['account_id'],
				'lengow_access_token' => $params['access_token'],
				'lengow_secret_token' => $params['secret_token'],
			)
		);
		foreach ( $params['shops'] as $shop_token => $shop_catalog_ids ) {
			$shop = Lengow_Main::find_by_token( $shop_token );
			if ( $shop ) {
				Lengow_Configuration::set_catalog_ids( $shop_catalog_ids['catalog_ids'] );
				Lengow_Configuration::set_active_shop();
			}
		}
	}

	/**
	 * Sync Lengow catalogs for order synchronisation
	 */
	public static function sync_catalog() {
		if ( Lengow_Connector::is_new_merchant() ) {
			return false;
		}
		$result = Lengow_Connector::query_api( 'get', '/v3.1/cms' );
		if ( isset( $result->cms ) ) {
			$cms_token = Lengow_Main::get_token();
			foreach ( $result->cms as $cms ) {
				if ( $cms->token === $cms_token ) {
					foreach ( $cms->shops as $cms_shop ) {
						$shop = Lengow_Main::find_by_token( $cms_shop->token );
						if ( $shop ) {
							Lengow_Configuration::set_catalog_ids( $cms_shop->catalog_ids );
							Lengow_Configuration::set_active_shop();
						}
					}
					break;
				}
			}
		}
	}

	/**
	 * Get Sync Data (Inscription / Update).
	 *
	 * @return array
	 */
	public static function get_option_data() {
		global $wp_version;
		$lengow_export   = new Lengow_Export();
		$data            = array(
			'token'          => Lengow_Main::get_token(),
			'version'        => $wp_version,
			'plugin_version' => LENGOW_VERSION,
			'options'        => Lengow_Configuration::get_all_values( false ),
			'shops'          => array(),
		);
		$data['shops'][] = array(
			'token'                   => Lengow_Main::get_token(),
			'enabled'                 => Lengow_Configuration::shop_is_active(),
			'total_product_number'    => $lengow_export->get_total_product(),
			'exported_product_number' => $lengow_export->get_total_export_product(),
			'options'                 => Lengow_Configuration::get_all_values( false, true ),
		);

		return $data;
	}

	/**
	 * Set CMS options.
	 *
	 * @param boolean $force Force cache Update
	 *
	 * @return boolean
	 */
	public static function set_cms_option( $force = false ) {
		if ( Lengow_Connector::is_new_merchant() || (bool) Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) {
			return false;
		}
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_last_option_update' );
			if ( ! is_null( $updated_at ) && ( time() - strtotime( $updated_at ) ) < self::$_cache_time ) {
				return false;
			}
		}
		$options = json_encode( self::get_option_data() );
		Lengow_Connector::query_api( 'put', '/v3.1/cms', array(), $options );
		Lengow_Configuration::update_value( 'lengow_last_option_update', date( 'Y-m-d H:i:s' ) );

		return true;
	}

	/**
	 * Get Status Account.
	 *
	 * @param boolean $force Force cache Update
	 *
	 * @return array|false
	 */
	public static function get_status_account( $force = false ) {
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_last_account_status_update' );
			if ( ! is_null( $updated_at ) && ( time() - strtotime( $updated_at ) ) < self::$_cache_time ) {

				return json_decode( Lengow_Configuration::get( 'lengow_account_status' ), true );
			}
		}
		$result = Lengow_Connector::query_api( 'get', '/v3.0/plans' );
		if ( isset( $result->isFreeTrial ) ) {
			$status            = array();
			$status['type']    = $result->isFreeTrial ? 'free_trial' : '';
			$status['day']     = (int) $result->leftDaysBeforeExpired;
			$status['expired'] = (bool) $result->isExpired;
			if ( $status['day'] < 0 ) {
				$status['day'] = 0;
			}
			if ( $status ) {
				Lengow_Configuration::update_value( 'lengow_account_status', json_encode( $status ) );
				Lengow_Configuration::update_value( 'lengow_last_account_status_update', date( 'Y-m-d H:i:s' ) );

				return $status;
			}
		} else {
			if ( Lengow_Configuration::get( 'lengow_last_account_status_update' ) ) {
				return json_decode( Lengow_Configuration::get( 'lengow_account_status' ), true );
			}
		}

		return false;
	}

	/**
	 * Get Statistic.
	 *
	 * @param boolean $force Force cache Update
	 *
	 * @return array
	 */
	public static function get_statistic( $force = false ) {
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_last_order_statistic_update' );
			if ( ( time() - strtotime( $updated_at ) ) < self::$_cache_time ) {
				return json_decode( Lengow_Configuration::get( 'lengow_order_statistic' ), true );
			}
		}
		$result = Lengow_Connector::query_api(
			'get',
			'/v3.0/stats',
			array(
				'date_from' => date( 'c', strtotime( date( 'Y-m-d' ) . ' -10 years' ) ),
				'date_to'   => date( 'c' ),
				'metrics'   => 'year',
			)
		);
		if ( isset( $result->level0 ) ) {
			$stats  = $result->level0[0];
			$return = array(
				'total_order' => $stats->revenue,
				'nb_order'    => (int) $stats->transactions,
				'currency'    => $result->currency->iso_a3,
				'available'   => false,
			);
		} else {
			if ( Lengow_Configuration::get( 'lengow_last_order_statistic_update' ) ) {
				return json_decode( Lengow_Configuration::get( 'lengow_order_statistic' ), true );
			} else {
				return array(
					'total_order' => 0,
					'nb_order'    => 0,
					'currency'    => '',
					'available'   => false,
				);
			}
		}
		if ( $return['total_order'] > 0 || $return['nb_order'] > 0 ) {
			$return['available'] = true;
		}
		if ( $return['currency']
		     && get_woocommerce_currency_symbol( $return['currency'] )
		     && function_exists( 'wc_price' )
		) {
			$return['total_order'] = wc_price( $return['total_order'], array( 'currency' => $return['currency'] ) );
		} else {
			$return['total_order'] = number_format( $return['total_order'], 2, ',', ' ' );
		}
		Lengow_Configuration::update_value( 'lengow_order_statistic', json_encode( $return ) );
		Lengow_Configuration::update_value( 'lengow_last_order_statistic_update', date( 'Y-m-d H:i:s' ) );

		return $return;
	}
}
