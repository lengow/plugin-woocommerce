<?php
/**
 * Admin connection page
 *
 * Copyright 2021 Lengow SAS
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
 * Lengow_Admin_Connection Class.
 */
class Lengow_Admin_Connection {

	/**
	 * Display dashboard page.
	 */
	public static function display() {
		$locale = new Lengow_Translation();
		include_once 'views/connection/html-admin-connection.php';
	}

	/**
	 * Process Post Parameters.
	 */
	public static function post_process() {
		$locale       = new Lengow_Translation();
		$plugin_links = Lengow_Sync::get_plugin_links( get_locale() );
		$action       = isset( $_POST['do_action'] ) ? $_POST['do_action'] : false;
		if ( $action ) {
			switch ( $action ) {
				case 'go_to_credentials':
					include 'views/connection/html-admin-connection-cms.php';
					break;
				case 'connect_cms':
					$cms_connected       = false;
					$has_catalog_to_link = false;
					$access_token        = isset( $_POST['access_token'] ) ? $_POST['access_token'] : '';
					$secret              = isset( $_POST['secret'] ) ? $_POST['secret'] : '';
					$credentials_valid   = self::_check_api_credentials( $access_token, $secret );
					if ( $credentials_valid ) {
						$cms_connected = self::_connect_cms();
						if ( $cms_connected ) {
							$has_catalog_to_link = self::_has_catalog_to_link();
						}
					}
					include 'views/connection/html-admin-connection-cms-result.php';
					break;
				case 'go_to_catalog':
					$retry = isset( $_POST['retry'] ) ? $_POST['retry'] !== 'false' : false;
					if ( $retry ) {
						Lengow_Configuration::reset_catalog_ids();
					}
					$catalog_list = self::_get_catalog_list();
					include 'views/connection/html-admin-connection-catalog.php';
					break;
				case 'link_catalogs':
					$catalogs_linked  = true;
					$catalog_selected = isset( $_POST['catalog_selected'] ) ? $_POST['catalog_selected'] : array();
					if ( ! empty( $catalog_selected ) ) {
						$catalogs_linked = self::_save_catalogs_linked( $catalog_selected );
					}
					if ( $catalogs_linked ) {
						echo json_encode( array( 'success' => true ) );
					} else {
						include 'views/connection/html-admin-connection-catalog-failed.php';
					}
					break;
			}
			exit();
		}
	}

	/**
	 * Check API credentials and save them in Database.
	 *
	 * @param string $access_token access token for api
	 * @param string $secret secret for api
	 *
	 * @return boolean
	 */
	private static function _check_api_credentials( $access_token, $secret ) {
		$access_ids_saved = false;
		$account_id       = Lengow_Connector::get_account_id_by_credentials( $access_token, $secret );
		if ( $account_id ) {
			$access_ids_saved = Lengow_Configuration::set_access_ids(
				array(
					Lengow_Configuration::ACCOUNT_ID   => $account_id,
					Lengow_Configuration::ACCESS_TOKEN => $access_token,
					Lengow_Configuration::SECRET       => $secret,
				)
			);
		}

		return $access_ids_saved;
	}

	/**
	 * Connect cms with Lengow.
	 *
	 * @return boolean
	 */
	private static function _connect_cms() {
		$cms_token     = Lengow_Main::get_token();
		$cms_connected = Lengow_Sync::sync_catalog( true );
		if ( ! $cms_connected ) {
			$sync_data = json_encode( Lengow_Sync::get_sync_data() );
			$result    = Lengow_Connector::query_api(
				Lengow_Connector::POST,
				Lengow_Connector::API_CMS,
				array(),
				$sync_data
			);
			if ( isset( $result->common_account ) ) {
				$cms_connected = true;
				$message_key   = 'log.connection.cms_creation_success';
			} else {
				$message_key = 'log.connection.cms_creation_failed';
			}
		} else {
			$message_key = 'log.connection.cms_already_exist';
		}
		Lengow_Main::log(
			Lengow_Log::CODE_CONNECTION,
			Lengow_Main::set_log_message(
				$message_key,
				array( 'cms_token' => $cms_token )
			)
		);
		// reset access ids if cms creation failed
		if ( ! $cms_connected ) {
			Lengow_Configuration::reset_access_ids();
		}

		return $cms_connected;
	}

	/**
	 * Get all catalogs available in Lengow.
	 *
	 * @return boolean
	 */
	private static function _has_catalog_to_link() {
		if ( ! Lengow_Configuration::shop_is_active() ) {
			return Lengow_Catalog::has_catalog_not_linked();
		}

		return false;
	}

	/**
	 * Get all catalogs available in Lengow.
	 *
	 * @return array
	 */
	private static function _get_catalog_list() {
		if ( ! Lengow_Configuration::shop_is_active() ) {
			return Lengow_Catalog::get_catalog_list();
		}

		// if cms already has one or more linked catalogs, nothing is done.
		return array();
	}

	/**
	 * Save catalogs linked in database and send data to Lengow with call API.
	 *
	 * @param array $catalog_selected
	 *
	 * @return boolean
	 */
	private static function _save_catalogs_linked( $catalog_selected ) {
		$catalogs_linked = true;
		if ( ! empty( $catalog_selected ) ) {
			// save catalogs ids and active shop in lengow configuration.
			Lengow_Configuration::set_catalog_ids( $catalog_selected );
			Lengow_Configuration::set_active_shop();
			// save last update date for a specific setting (change synchronisation interval time).
			Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_SETTING, time() );
			// link all catalogs selected by API.
			$catalogs_linked = Lengow_Catalog::link_catalogs( $catalog_selected );
			$message_key     = $catalogs_linked
				? 'log.connection.link_catalog_success'
				: 'log.connection.link_catalog_failed';
			Lengow_Main::log( Lengow_Log::CODE_CONNECTION, Lengow_Main::set_log_message( $message_key ) );
		}

		return $catalogs_linked;
	}
}
