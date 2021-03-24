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
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Sync Class.
 */
class Lengow_Sync {

	/**
	 * @var string cms type.
	 */
	const CMS_TYPE = 'woocommerce';

	/**
	 * @var string sync catalog action.
	 */
	const SYNC_CATALOG = 'catalog';

	/**
	 * @var string sync cms option action.
	 */
	const SYNC_CMS_OPTION = 'cms_option';

	/**
	 * @var string sync status account action.
	 */
	const SYNC_STATUS_ACCOUNT = 'status_account';

	/**
	 * @var string sync marketplace action.
	 */
	const SYNC_MARKETPLACE = 'marketplace';

	/**
	 * @var string sync order action.
	 */
	const SYNC_ORDER = 'order';

	/**
	 * @var string sync action action.
	 */
	const SYNC_ACTION = 'action';

	/**
	 * @var string sync plugin version action.
	 */
	const SYNC_PLUGIN_DATA = 'plugin';

	/**
	 * @var array cache time for catalog, account status, cms options and marketplace synchronisation.
	 */
	protected static $_cache_times = array(
		self::SYNC_CATALOG        => 21600,
		self::SYNC_CMS_OPTION     => 86400,
		self::SYNC_STATUS_ACCOUNT => 86400,
		self::SYNC_MARKETPLACE    => 43200,
		self::SYNC_PLUGIN_DATA    => 86400,
	);

	/**
	 * @var array valid sync actions.
	 */
	public static $sync_actions = array(
		self::SYNC_ORDER,
		self::SYNC_CMS_OPTION,
		self::SYNC_STATUS_ACCOUNT,
		self::SYNC_MARKETPLACE,
		self::SYNC_ACTION,
		self::SYNC_CATALOG,
		self::SYNC_PLUGIN_DATA,
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
			'type'           => self::CMS_TYPE,
			'version'        => $wp_version,
			'plugin_version' => LENGOW_VERSION,
			'email'          => Lengow_Configuration::get( 'admin_email' ),
			'cron_url'       => Lengow_Main::get_cron_url(),
			'shops'          => array(),
		);
		$data['shops'][] = array(
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
	 * Sync Lengow catalogs for order synchronisation.
	 *
	 * @param boolean $force Force cache Update
	 * @param boolean $log_output see log or not
	 *
	 * @return boolean
	 */
	public static function sync_catalog( $force = false, $log_output = false ) {
		$success         = false;
		$setting_updated = false;
		if ( Lengow_Configuration::is_new_merchant() ) {
			return $success;
		}
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_catalog_update' );
			if ( null !== $updated_at
			     && ( time() - (int) $updated_at ) < self::$_cache_times[ self::SYNC_CATALOG ]
			) {
				return $success;
			}
		}
		$result = Lengow_Connector::query_api(
			Lengow_Connector::GET,
			Lengow_Connector::API_CMS,
			array(),
			'',
			$log_output
		);
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
					$success = true;
					break;
				}
			}
		}
		// save last update date for a specific settings (change synchronisation interval time).
		if ( $setting_updated ) {
			Lengow_Configuration::update_value( 'lengow_last_setting_update', time() );
		}
		Lengow_Configuration::update_value( 'lengow_catalog_update', time() );

		return $success;
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
	 * @param boolean $log_output see log or not
	 *
	 * @return boolean
	 */
	public static function set_cms_option( $force = false, $log_output = false ) {
		if ( Lengow_Configuration::is_new_merchant() || (bool) Lengow_Configuration::debug_mode_is_active() ) {
			return false;
		}
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_last_option_update' );
			if ( null !== $updated_at
			     && ( time() - (int) $updated_at ) < self::$_cache_times[ self::SYNC_CMS_OPTION ]
			) {
				return false;
			}
		}
		$options = json_encode( self::get_option_data() );
		Lengow_Connector::query_api(
			Lengow_Connector::PUT,
			Lengow_Connector::API_CMS,
			array(),
			$options,
			$log_output
		);
		Lengow_Configuration::update_value( 'lengow_last_option_update', time() );

		return true;
	}

	/**
	 * Get Status Account.
	 *
	 * @param boolean $force Force cache Update
	 * @param boolean $log_output see log or not
	 *
	 * @return array|false
	 */
	public static function get_status_account( $force = false, $log_output = false ) {
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_last_account_status_update' );
			if ( null !== $updated_at
			     && ( time() - (int) $updated_at ) < self::$_cache_times[ self::SYNC_STATUS_ACCOUNT ]
			) {
				return json_decode( Lengow_Configuration::get( 'lengow_account_status' ), true );
			}
		}
		$result = Lengow_Connector::query_api(
			Lengow_Connector::GET,
			Lengow_Connector::API_PLAN,
			array(),
			'',
			$log_output
		);
		if ( isset( $result->isFreeTrial ) ) {
			$status = array(
				'type'    => $result->isFreeTrial ? 'free_trial' : '',
				'day'     => (int) $result->leftDaysBeforeExpired < 0 ? 0 : (int) $result->leftDaysBeforeExpired,
				'expired' => (bool) $result->isExpired,
				'legacy'  => 'v2' === $result->accountVersion ? true : false,
			);
			Lengow_Configuration::update_value( 'lengow_account_status', json_encode( $status ) );
			Lengow_Configuration::update_value( 'lengow_last_account_status_update', time() );

			return $status;
		} else {
			if ( Lengow_Configuration::get( 'lengow_last_account_status_update' ) ) {
				return json_decode( Lengow_Configuration::get( 'lengow_account_status' ), true );
			}
		}

		return false;
	}

	/**
	 * Get marketplace data.
	 *
	 * @param boolean $force force cache update
	 * @param boolean $log_output see log or not
	 *
	 * @return array|false
	 */
	public static function get_marketplaces( $force = false, $log_output = false ) {
		$file_path = Lengow_Marketplace::get_file_path();
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_marketplace_update' );
			if ( null !== $updated_at
			     && ( time() - (int) $updated_at ) < self::$_cache_times[ self::SYNC_MARKETPLACE ]
			     && file_exists( $file_path )
			) {
				// recovering data with the marketplaces.json file.
				$marketplaces_data = file_get_contents( $file_path );
				if ( $marketplaces_data ) {
					return json_decode( $marketplaces_data );
				}
			}
		}
		// recovering data with the API.
		$result = Lengow_Connector::query_api(
			Lengow_Connector::GET,
			Lengow_Connector::API_MARKETPLACE,
			array(),
			'',
			$log_output
		);
		if ( $result && is_object( $result ) && ! isset( $result->error ) ) {
			// updated marketplaces.json file.
			try {
				$marketplace_file = new Lengow_File(
					Lengow_Main::$lengow_config_folder,
					Lengow_Marketplace::$marketplace_json,
					'w+'
				);
				$marketplace_file->write( json_encode( $result ) );
				$marketplace_file->close();
				Lengow_Configuration::update_value( 'lengow_marketplace_update', time() );
			} catch ( Lengow_Exception $e ) {
				Lengow_Main::log(
					Lengow_Log::CODE_IMPORT,
					Lengow_Main::set_log_message(
						'log.import.marketplace_update_failed',
						array(
							'decoded_message' => Lengow_Main::decode_log_message(
								$e->getMessage(),
								Lengow_Translation::DEFAULT_ISO_CODE
							),
						)
					),
					$log_output
				);
			}

			return $result;
		} else {
			// if the API does not respond, use marketplaces.json if it exists.
			if ( file_exists( $file_path ) ) {
				$marketplaces_data = file_get_contents( $file_path );
				if ( $marketplaces_data ) {
					return json_decode( $marketplaces_data );
				}
			}
		}

		return false;
	}

	/**
	 * Get Lengow plugin data (last version and download link)
	 *
	 * @param boolean $force force cache update
	 * @param boolean $log_output see log or not
	 *
	 * @return array|false
	 */
	public static function get_plugin_data( $force = false, $log_output = false ) {
		if ( Lengow_Configuration::is_new_merchant() ) {
			return false;
		}
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_plugin_data_update' );
			if ( $updated_at !== null
			     && ( time() - (int) $updated_at ) < self::$_cache_times[ self::SYNC_PLUGIN_DATA ]
			) {
				return json_decode( Lengow_Configuration::get( 'lengow_plugin_data' ), true );
			}
		}
		$plugins = Lengow_Connector::query_api(
			Lengow_Connector::GET,
			Lengow_Connector::API_PLUGIN,
			array(),
			'',
			$log_output
		);
		if ( $plugins ) {
			$plugin_data = false;
			foreach ( $plugins as $plugin ) {
				if ( $plugin->type === self::CMS_TYPE ) {
					$plugin_data = array(
						'version'       => $plugin->version,
						'download_link' => $plugin->archive,
					);
					break;
				}
			}
			if ( $plugin_data ) {
				Lengow_Configuration::update_value( 'lengow_plugin_data', json_encode( $plugin_data ) );
				Lengow_Configuration::update_value( 'lengow_plugin_data_update', time() );

				return $plugin_data;
			}
		} else {
			if ( Lengow_Configuration::get( 'lengow_plugin_data' ) ) {
				return json_decode( Lengow_Configuration::get( 'lengow_plugin_data' ), true );
			}
		}

		return false;
	}
}
