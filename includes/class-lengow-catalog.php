<?php
/**
 * All components for toolbox
 *
 * Copyright 2021 Lengow SAS
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
 * @copyright   2021 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Catalog Class.
 */
class Lengow_Catalog {

	/**
	 * Check if the account has catalogs not linked to a cms.
	 *
	 * @return boolean
	 */
	public static function has_catalog_not_linked() {
		$lengow_catalogs = Lengow_Connector::query_api(
			Lengow_Connector::GET,
			Lengow_Connector::API_CMS_CATALOG
		);
		if ( ! $lengow_catalogs ) {
			return false;
		}
		foreach ( $lengow_catalogs as $catalog ) {
			if ( ! is_object( $catalog ) || $catalog->shop ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Get all catalogs available in Lengow.
	 *
	 * @return array
	 */
	public static function get_catalog_list() {
		$catalog_list    = array();
		$lengow_catalogs = Lengow_Connector::query_api( Lengow_Connector::GET, Lengow_Connector::API_CMS_CATALOG );
		if ( ! $lengow_catalogs ) {
			return $catalog_list;
		}
		foreach ( $lengow_catalogs as $catalog ) {
			if ( ! is_object( $catalog ) || $catalog->shop ) {
				continue;
			}
			if ( null !== $catalog->name ) {
				$name = $catalog->name;
			} else {
				$name = Lengow_Main::decode_log_message(
					'lengow_log.connection.catalog',
					null,
					array( 'catalog_id' => $catalog->id )
				);
			}
			$status         = $catalog->is_active
				? Lengow_Main::decode_log_message( 'lengow_log.connection.status_active' )
				: Lengow_Main::decode_log_message( 'lengow_log.connection.status_draft' );
			$label          = Lengow_Main::decode_log_message(
				'lengow_log.connection.catalog_label',
				null,
				array(
					'catalog_id'     => $catalog->id,
					'catalog_name'   => $name,
					'nb_products'    => $catalog->products ?: 0,
					'catalog_status' => $status,
				)
			);
			$catalog_list[] = array(
				'label' => $label,
				'value' => $catalog->id,
			);
		}

		return $catalog_list;
	}

	/**
	 * Link all catalogs by API.
	 *
	 * @param array $catalog_ids all catalog ids
	 *
	 * @return boolean
	 */
	public static function link_catalogs( array $catalog_ids ) {
		if ( empty( $catalog_ids ) ) {
			return false;
		}
		$token             = Lengow_Main::get_token();
		$link_catalog_data = array(
			'cms_token' => $token,
			'shops'     => array(
				array(
					'shop_token'  => $token,
					'catalogs_id' => $catalog_ids,
				)
			),
		);
		Lengow_Main::log(
			Lengow_Log::CODE_CONNECTION,
			Lengow_Main::set_log_message(
				'log.connection.try_link_catalog',
				array(
					'catalog_ids' => implode( ', ', $catalog_ids ),
					'shop_token'  => $token,
				)
			)
		);

		$result = Lengow_Connector::query_api(
			Lengow_Connector::POST,
			Lengow_Connector::API_CMS_MAPPING,
			array(),
			json_encode( $link_catalog_data )
		);

		return isset( $result->cms_token );
	}
}
