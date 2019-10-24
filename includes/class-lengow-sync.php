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
 * @author      Team Connector <team-connector@lengow.com>
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
	 * @var array cache time for statistic, account status, cms options and marketplace synchronisation.
	 */
	protected static $_cache_times = array(
		'catalog'        => 21600,
		'cms_option'     => 86400,
		'status_account' => 86400,
		'statistic'      => 86400,
		'marketplace'    => 43200,
	);

	/**
	 * @var array valid sync actions.
	 */
	public static $sync_actions = array(
		'order',
		'cms_option',
		'status_account',
		'statistic',
		'marketplace',
		'catalog',
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
			'domain_name'    => $_SERVER['SERVER_NAME'],
			'token'          => Lengow_Main::get_token(),
			'type'           => 'woocommerce',
			'version'        => $wp_version,
			'plugin_version' => LENGOW_VERSION,
			'email'          => Lengow_Configuration::get( 'admin_email' ),
			'cron_url'       => Lengow_Main::get_cron_url(),
			'return_url'     => 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'],
			'shops'          => array(),
		);
		$data['shops'][1] = array(
			'token'                   => Lengow_Main::get_token(),
			'shop_name'               => Lengow_Configuration::get( 'blogname' ),
			'domain_url'              => $_SERVER['SERVER_NAME'],
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
		if ( isset( $params['shops'] ) ) {
			foreach ( $params['shops'] as $shop_token => $shop_catalog_ids ) {
				$shop = Lengow_Main::find_by_token( $shop_token );
				if ( $shop ) {
					Lengow_Configuration::set_catalog_ids( $shop_catalog_ids['catalog_ids'] );
					Lengow_Configuration::set_active_shop();
				}
			}
		}
		// save last update date for a specific settings (change synchronisation interval time)
		Lengow_Configuration::update_value( 'lengow_last_setting_update', date( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Sync Lengow catalogs for order synchronisation
	 *
	 * @param boolean $force Force cache Update
	 *
	 * @return boolean
	 */
	public static function sync_catalog( $force = false ) {
		$setting_updated = false;
		if ( Lengow_Connector::is_new_merchant() ) {
			return false;
		}
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_catalog_update' );
			if ( null !== $updated_at
			     && ( time() - strtotime( $updated_at ) ) < self::$_cache_times['catalog']
			) {
				return false;
			}
		}
		$result = Lengow_Connector::query_api( 'get', '/v3.1/cms' );
		if ( isset( $result->cms ) ) {
			$cms_token = Lengow_Main::get_token();
			foreach ( $result->cms as $cms ) {
				if ( $cms->token === $cms_token ) {
					foreach ( $cms->shops as $cms_shop ) {
						$shop = Lengow_Main::find_by_token( $cms_shop->token );
						if ( $shop ) {
							$catalog_ids_change = Lengow_Configuration::set_catalog_ids( $cms_shop->catalog_ids );
							$active_shop_change = Lengow_Configuration::set_active_shop();
							if ( ! $setting_updated && ( $catalog_ids_change || $active_shop_change ) ) {
								$setting_updated = true;
							}
						}
					}
					break;
				}
			}
		}
		// save last update date for a specific settings (change synchronisation interval time)
		if ( $setting_updated ) {
			Lengow_Configuration::update_value( 'lengow_last_setting_update', date( 'Y-m-d H:i:s' ) );
		}
		Lengow_Configuration::update_value( 'lengow_catalog_update', date( 'Y-m-d H:i:s' ) );

		return true;
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
			if ( null !== $updated_at
			     && ( time() - strtotime( $updated_at ) ) < self::$_cache_times['cms_option']
			) {
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
			if ( null !== $updated_at
			     && ( time() - strtotime( $updated_at ) ) < self::$_cache_times['status_account']
			) {
				return json_decode( Lengow_Configuration::get( 'lengow_account_status' ), true );
			}
		}
		$result = Lengow_Connector::query_api( 'get', '/v3.0/plans' );
		if ( isset( $result->isFreeTrial ) ) {
			$status = array(
				'type'    => $result->isFreeTrial ? 'free_trial' : '',
				'day'     => (int) $result->leftDaysBeforeExpired < 0 ? 0 : (int) $result->leftDaysBeforeExpired,
				'expired' => (bool) $result->isExpired,
				'legacy'  => 'v2' === $result->accountVersion ? true : false,
			);
			Lengow_Configuration::update_value( 'lengow_account_status', json_encode( $status ) );
			Lengow_Configuration::update_value( 'lengow_last_account_status_update', date( 'Y-m-d H:i:s' ) );

			return $status;
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
			if ( null !== $updated_at
			     && ( time() - strtotime( $updated_at ) ) < self::$_cache_times['statistic']
			) {
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

	/**
	 * Get marketplace data
	 *
	 * @param boolean $force force cache update
	 *
	 * @return array|false
	 */
	public static function get_marketplaces( $force = false ) {
		$file_path = Lengow_Marketplace::get_file_path();
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_marketplace_update' );
			if ( null !== $updated_at
			     && ( time() - strtotime( $updated_at ) ) < self::$_cache_times['marketplace']
			     && file_exists( $file_path )
			) {
				// recovering data with the marketplaces.json file
				$marketplaces_data = file_get_contents( $file_path );
				if ( $marketplaces_data ) {
					return json_decode( $marketplaces_data );
				}
			}
		}
		// recovering data with the API
		$result = Lengow_Connector::query_api( 'get', '/v3.0/marketplaces' );
		if ( $result && is_object( $result ) && ! isset( $result->error ) ) {
			// updated marketplaces.json file
			try {
				$marketplace_file = new Lengow_File(
					Lengow_Main::$lengow_config_folder,
					Lengow_Marketplace::$marketplace_json,
					'w+'
				);
				$marketplace_file->write( json_encode( $result ) );
				$marketplace_file->close();
				Lengow_Configuration::update_value( 'lengow_marketplace_update', date( 'Y-m-d H:i:s' ) );
			} catch ( Lengow_Exception $e ) {
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message(
						'log.import.marketplace_update_failed',
						array( 'decoded_message' => Lengow_Main::decode_log_message( $e->getMessage(), 'en' ) )
					)
				);
			}

			return $result;
		} else {
			// if the API does not respond, use marketplaces.json if it exists
			if ( file_exists( $file_path ) ) {
				$marketplaces_data = file_get_contents( $file_path );
				if ( $marketplaces_data ) {
					return json_decode( $marketplaces_data );
				}
			}
		}

		return false;
	}
}
