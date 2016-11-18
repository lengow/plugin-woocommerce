<?php
/**
 * All Lengow configuration options
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Configuration Class.
 */
class Lengow_Configuration {

	/**
	 * Get all Lengow configuration keys
	 *
	 * @return array
	 */
	public static function get_keys() {
		static $keys = null;
		if ( $keys === null ) {
			$locale = new Lengow_Translation();

			$keys = array(
				'lengow_token'                       => array(
					'global' => true,
					'shop'   => true,
					'label'  => $locale->t( 'lengow_settings.lengow_token_title' ),
				),
				'lengow_store_enabled'               => array(
					'shop'  => true,
					'label' => $locale->t( 'lengow_settings.lengow_store_active_title' ),
					'type'  => 'checkbox'
				),
				'lengow_account_id'                  => array(
					'shop'  => true,
					'label' => $locale->t( 'lengow_settings.lengow_account_id_title' ),
				),
				'lengow_access_token'                => array(
					'shop'  => true,
					'label' => $locale->t( 'lengow_settings.lengow_access_token_title' ),
				),
				'lengow_secret_token'                => array(
					'shop'  => true,
					'label' => $locale->t( 'lengow_settings.lengow_secret_token_title' ),
				),
				'lengow_authorized_ip'               => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_authorized_ip_title' ),
					'legend' => $locale->t( 'lengow_settings.lengow_authorized_ip_legend' ),
				),
				'lengow_last_order_statistic_update' => array(
					'export' => false
				),
				'lengow_order_statistic'             => array(
					'type'   => 'json',
					'export' => false
				),
				'lengow_last_option_update'          => array(
					'type'   => 'datetime',
					'export' => false
				),
				'lengow_last_account_status_update'  => array(
					'export' => false
				),
				'lengow_account_status'              => array(
					'export' => false
				),
				'lengow_selection_enabled'           => array(
					'shop'          => true,
					'label'         => $locale->t( 'lengow_settings.lengow_selection_enabled_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_selection_enabled_legend' ),
					'default_value' => false,
					'type'          => 'checkbox'
				),
				'lengow_out_stock'                   => array(
					'shop'  => true,
					'label' => $locale->t( 'lengow_settings.lengow_out_stock_title' ),
					'type'  => 'checkbox'
				),
				'lengow_variation_enabled'           => array(
					'shop'          => true,
					'label'         => $locale->t( 'lengow_setting.lengow_variation_enabled_title' ),
					'legend'        => $locale->t( 'lengow_setting.lengow_variation_enabled_legend' ),
					'default_value' => true,
				),
				'lengow_product_types'               => array(
					'shop'          => true,
					'label'         => $locale->t( 'lengow_settings.lengow_product_types_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_product_types_legend' ),
					'default_value' => array( 'simple', 'variable' ),
				),
				'lengow_last_export'                 => array(
					'shop'  => true,
					'label' => $locale->t( 'lengow_settings.lengow_last_export_title' ),
				),
				'lengow_cron_enabled'                => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_cron_enabled_title' ),
					'default_value' => false,
					'type'          => 'checkbox'
				),
				'lengow_import_enabled'              => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_import_enabled_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_import_enabled_legend' ),
					'default_value' => false,
					'type'          => 'checkbox'
				),
				'lengow_import_days'                 => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_import_days_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_import_days_legend' ),
					'default_value' => 5,
				),
				'lengow_import_ship_mp_enabled'      => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_import_ship_mp_enabled_title' ),
					'legend'        => $locale->t( 'lengow_settings.lengow_import_ship_mp_enabled_legend' ),
					'default_value' => false,
					'type'          => 'checkbox'
				),
				'lengow_preprod_enabled'             => array(
					'global'        => true,
					'label'         => $locale->t( 'lengow_settings.lengow_preprod_enabled_title' ),
					'default_value' => false,
					'type'          => 'checkbox'
				),
				'lengow_import_in_progress'          => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_import_in_progress_title' ),
				),
				'lengow_last_import_manual'          => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_last_import_manual_title' )
				),
				'lengow_last_import_cron'            => array(
					'global' => true,
					'label'  => $locale->t( 'lengow_settings.lengow_last_import_cron_title' )
				),
			);
		}

		return $keys;
	}

	/**
	 * Get Lengow value
	 *
	 * @param string $key lengow configuration key
	 *
	 * @return mixed
	 */
	public static function get( $key ) {

		return get_option( $key );
	}

	/**
	 * Update Lengow value by shop
	 *
	 * @param string $key lengow configuration key
	 * @param mixed $value configuration value
	 */
	public static function add_value( $key, $value ) {
		add_option( $key, $value );
	}

	/**
	 * Update Lengow value by shop
	 *
	 * @param string $key lengow configuration key
	 * @param mixed $value configuration value
	 */
	public static function update_value( $key, $value ) {
		update_option( $key, $value );
	}

	/**
	 * Delete Lengow value by shop
	 *
	 * @param string $key lengow configuration key
	 */
	public static function delete( $key ) {
		delete_option( $key );
	}

	/**
	 * Get Values
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
	 * Check value and create a log if necessary
	 *
	 * @param string $key name of lengow setting
	 * @param mixed $value setting value
	 */
	public static function check_and_log( $key, $value ) {
		$old_value = self::get( $key );

		if ( $key == 'lengow_access_token' || $key == 'lengow_secret_token' ) {
			$value     = preg_replace( "/[a-zA-Z0-9]/", '*', $value );
			$old_value = preg_replace( "/[a-zA-Z0-9]/", '*', $old_value );
		}
		if ( $old_value != $value ) {
			Lengow_Main::log(
				'Setting',
				Lengow_Main::set_log_message( 'log.setting.setting_change', array(
					'key'       => $key,
					'old_value' => is_array( $old_value ) ? implode( ",", $old_value ) : $old_value,
					'value'     => is_array( $value ) ? implode( ",", $value ) : $value,
				) )
			);
		}
	}
}

