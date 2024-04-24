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

use Lengow\Sdk\Client\Exception\HttpException;

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

	/* Sync actions */
	const SYNC_CATALOG        = 'catalog';
	const SYNC_CMS_OPTION     = 'cms_option';
	const SYNC_STATUS_ACCOUNT = 'status_account';
	const SYNC_MARKETPLACE    = 'marketplace';
	const SYNC_ORDER          = 'order';
	const SYNC_ACTION         = 'action';
	const SYNC_PLUGIN_DATA    = 'plugin';

	/* Plugin link types */
	const LINK_TYPE_HELP_CENTER  = 'help_center';
	const LINK_TYPE_CHANGELOG    = 'changelog';
	const LINK_TYPE_UPDATE_GUIDE = 'update_guide';
	const LINK_TYPE_SUPPORT      = 'support';

	/* Default plugin links */
	const LINK_HELP_CENTER  = 'https://help.lengow.com/hc/en-us/articles/10059898927388-WooCommerce-Set-up-the-plugin';
	const LINK_CHANGELOG    = 'https://help.lengow.com/hc/en-us/articles/360011089440-Woocommerce-Lengow-plugin-changelogs';
	const LINK_UPDATE_GUIDE = 'https://help.lengow.com/hc/en-us/articles/10058794907164-WooCommerce-Update-the-plugin-version';
	const LINK_SUPPORT      = 'https://help.lengow.com/hc/en-us/requests/new';

	/* Api iso codes */
	const API_ISO_CODE_EN = 'en';
	const API_ISO_CODE_FR = 'fr';

	/**
	 * @var array cache time for catalog, account status, cms options and marketplace synchronisation.
	 */
	private static $cache_times = array(
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
	 * @var array iso code correspondence for plugin links
	 */
	public static $generic_iso_codes = array(
		self::API_ISO_CODE_EN => Lengow_Translation::ISO_CODE_EN,
		self::API_ISO_CODE_FR => Lengow_Translation::ISO_CODE_FR,
	);

	/**
	 * @var array default plugin links when the API is not available
	 */
	public static $default_plugin_links = array(
		self::LINK_TYPE_HELP_CENTER  => self::LINK_HELP_CENTER,
		self::LINK_TYPE_CHANGELOG    => self::LINK_CHANGELOG,
		self::LINK_TYPE_UPDATE_GUIDE => self::LINK_UPDATE_GUIDE,
		self::LINK_TYPE_SUPPORT      => self::LINK_SUPPORT,
	);

	/**
	 * Get Sync Data (Inscription / Update).
	 *
	 * @return array
	 */
	public static function get_sync_data() {
		global $wp_version;
		$lengow_export   = new Lengow_Export();
		$data            = array(
			'domain_name'    => $_SERVER['SERVER_NAME'],
			'token'          => Lengow_Main::get_token(),
			'type'           => self::CMS_TYPE,
			'version'        => $wp_version,
			'plugin_version' => LENGOW_VERSION,
			'email'          => Lengow_Configuration::get( 'admin_email' ),
			'cron_url'       => Lengow_Main::get_cron_url(),
			'toolbox_url'    => Lengow_Main::get_toolbox_url(),
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
	 *
	 * @return boolean
	 */
	public static function sync_catalog( $force = false) {
		$success         = false;
		$setting_updated = false;
		if ( Lengow_Configuration::is_new_merchant() ) {
			return $success;
		}
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_CATALOG );
			if ( null !== $updated_at
				&& ( time() - (int) $updated_at ) < self::$cache_times[ self::SYNC_CATALOG ]
			) {
				return $success;
			}
		}
		try {
			$result = Lengow::sdk()->cms()->list();
		} catch ( HttpException|Exception $e ) {
			Lengow_Main::get_log_instance()->log_exception( $e );
		}
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
			Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_SETTING, time() );
		}
		Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_CATALOG, time() );

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
	 *
	 * @return boolean
	 */
	public static function set_cms_option( $force = false ) {
		if ( Lengow_Configuration::is_new_merchant() || Lengow_Configuration::debug_mode_is_active() ) {
			return false;
		}
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_OPTION_CMS );
			if ( null !== $updated_at
				&& ( time() - (int) $updated_at ) < self::$cache_times[ self::SYNC_CMS_OPTION ]
			) {
				return false;
			}
		}
		$options = self::get_option_data();
		try {
			Lengow::sdk()->cms()->put( $options );
		} catch ( HttpException|Exception $e ) {
			Lengow_Main::get_log_instance()->log_exception( $e );
			return false;
		}

		Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_OPTION_CMS, time() );
		return true;
	}

	/**
	 * Get Status Account.
	 *
	 * @param boolean $force Force cache Update
	 *
	 * @return array|false
	 */
	public static function get_status_account( bool $force = false ) {
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_ACCOUNT_STATUS_DATA );
			if ( null !== $updated_at
				&& ( time() - (int) $updated_at ) < self::$cache_times[ self::SYNC_STATUS_ACCOUNT ]
			) {
				return json_decode( Lengow_Configuration::get( Lengow_Configuration::ACCOUNT_STATUS_DATA ), true );
			}
		}
		try {
			$plan = Lengow::sdk()->plan()->me();
		} catch ( HttpException|Exception $e ) {
			Lengow_Main::get_log_instance()->log_exception($e);
			return false;
		}

		if ( isset( $plan->isFreeTrial ) ) {
			$status = array(
				'type'    => $plan->isFreeTrial ? 'free_trial' : '',
				'day'     => $plan->leftDaysBeforeExpired < 0 ? 0 : $plan->leftDaysBeforeExpired,
				'expired' => $plan->isExpired,
				'legacy'  => 'v2' === $plan->accountVersion,
			);
			Lengow_Configuration::update_value( Lengow_Configuration::ACCOUNT_STATUS_DATA, wp_json_encode( $status ) );
			Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_ACCOUNT_STATUS_DATA, time() );

			return $status;
		}
		if ( Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_ACCOUNT_STATUS_DATA ) ) {
			return json_decode( Lengow_Configuration::get( Lengow_Configuration::ACCOUNT_STATUS_DATA ), true );
		}

		return false;
	}

	/**
	 * Get marketplace data.
	 *
	 * @param boolean $force force cache update
	 *
	 * @return array|false
	 */
	public static function get_marketplaces( $force = false ) {
		$file_path = Lengow_Marketplace::get_file_path();
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_MARKETPLACE );
			if ( null !== $updated_at
				&& ( time() - (int) $updated_at ) < self::$cache_times[ self::SYNC_MARKETPLACE ]
				&& file_exists( $file_path )
			) {
				// recovering data with the marketplaces.json file.
				$marketplaces_data = file_get_contents( $file_path );
				if ( $marketplaces_data ) {
					// don't decode into array as we use the result as an object.
					return json_decode( $marketplaces_data );
				}
			}
		}
		// recovering data with the API.

		try {
			$result = (object) Lengow::sdk()->marketplace()->list();
		} catch ( HttpException|Exception $e ) {
			Lengow_Main::get_log_instance()->log_exception( $e );
		}

		if ( isset( $result ) ) {
			// updated marketplaces.json file.
			try {
				$marketplace_file = new Lengow_File(
					Lengow_Main::FOLDER_CONFIG,
					Lengow_Marketplace::FILE_MARKETPLACE,
					'w+'
				);
				$marketplace_file->write( wp_json_encode( $result ) );
				$marketplace_file->close();
				Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_MARKETPLACE, time() );
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
				);
			}

			return $result;
		}
		// if the API does not respond, use marketplaces.json if it exists.
		if ( file_exists( $file_path ) ) {
			$marketplaces_data = file_get_contents( $file_path );
			if ( $marketplaces_data ) {
				// don't decode into array as we use the result as an object.
				return json_decode( $marketplaces_data );
			}
		}

		return false;
	}

	/**
	 * Get Lengow plugin data (last version and download link)
	 *
	 * @param boolean $force force cache update
	 *
	 * @return array|false
	 */
	public static function get_plugin_data( $force = false ) {
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( Lengow_Configuration::LAST_UPDATE_PLUGIN_DATA );
			if ( $updated_at !== null
				&& ( time() - (int) $updated_at ) < self::$cache_times[ self::SYNC_PLUGIN_DATA ]
			) {
				return json_decode( Lengow_Configuration::get( Lengow_Configuration::PLUGIN_DATA ), true );
			}
		}

		try {
			$plugins = Lengow::sdk()->plugin()->list();
		} catch ( HttpException|Exception $e ) {
			Lengow_Main::get_log_instance()->log_exception($e);
			return false;
		}

		if ( $plugins ) {
			$plugin_data = false;
			foreach ( $plugins as $plugin ) {
				if ( $plugin->type === self::CMS_TYPE ) {
					$cms_min_version = '';
					$cms_max_version = '';
					$plugin_links    = array();
					$current_version = $plugin->version;
					if ( ! empty( $plugin->versions ) ) {
						foreach ( $plugin->versions as $version ) {
							if ( $version->version === $current_version ) {
								$cms_min_version = $version->cms_min_version;
								$cms_max_version = $version->cms_max_version;
								break;
							}
						}
					}
					if ( ! empty( $plugin->links ) ) {
						foreach ( $plugin->links as $link ) {
							if ( array_key_exists( $link->language->iso_a2, self::$generic_iso_codes ) ) {
								$generic_iso_code                                      = self::$generic_iso_codes[ $link->language->iso_a2 ];
								$plugin_links[ $generic_iso_code ][ $link->link_type ] = $link->link;
							}
						}
					}
					$plugin_data = array(
						'version'         => $current_version,
						'download_link'   => $plugin->archive,
						'cms_min_version' => $cms_min_version,
						'cms_max_version' => $cms_max_version,
						'links'           => $plugin_links,
						'extensions'      => $plugin->extensions,
					);
					break;
				}
			}
			if ( $plugin_data ) {
				Lengow_Configuration::update_value( Lengow_Configuration::PLUGIN_DATA, wp_json_encode( $plugin_data ) );
				Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_PLUGIN_DATA, time() );

				return json_decode( Lengow_Configuration::get( Lengow_Configuration::PLUGIN_DATA ), true );
			}
		} elseif ( Lengow_Configuration::get( Lengow_Configuration::PLUGIN_DATA ) ) {
			return json_decode( Lengow_Configuration::get( Lengow_Configuration::PLUGIN_DATA ), true );
		}

		return false;
	}

	/**
	 * Get an array of plugin links for a specific iso code
	 *
	 * @param string|null $iso_code locale iso code
	 *
	 * @return array
	 */
	public static function get_plugin_links( $iso_code = null ) {
		return self::$default_plugin_links;
	}
}
