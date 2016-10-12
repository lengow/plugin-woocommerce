<?php
/**
 * All components to create and synchronise account
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
 * Lengow_Sync Class.
 */
class Lengow_Sync {

	/**
	 * Get Account Status every 5 hours
	 */
	protected static $cacheTime = 18000;

	/**
	 * Get Sync Data (Inscription / Update)
	 *
	 * @return array
	 */
	public static function get_sync_data() {
		global $wp_version;
		$lengow_export                               = new Lengow_Export();
		$data                                        = array();
		$data['domain_name']                         = $_SERVER["SERVER_NAME"];
		$data['token']                               = Lengow_Main::get_token();
		$data['type']                                = 'woocommerce';
		$data['version']                             = $wp_version;
		$data['plugin_version']                      = LENGOW_VERSION;
		$data['email']                               = Lengow_Configuration::get( 'admin_email' );
		$data['return_url']                          = 'http://' . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		$data['shops'][1]['token']                   = Lengow_Main::get_token();
		$data['shops'][1]['name']                    = Lengow_Configuration::get( 'blogname' );
		$data['shops'][1]['domain']                  = $_SERVER["SERVER_NAME"];
		$data['shops'][1]['feed_url']                = Lengow_Main::get_export_url();
		$data['shops'][1]['cron_url']                = Lengow_Main::get_cron_url();
		$data['shops'][1]['total_product_number']    = $lengow_export->get_total_product();
		$data['shops'][1]['exported_product_number'] = $lengow_export->get_total_export_product();
		$data['shops'][1]['configured']              = self::check_sync_shop();

		return $data;
	}

	/**
	 * Store Configuration Key From Lengow
	 *
	 * @param $params
	 */
	public static function sync( $params ) {
		foreach ( $params as $shop_token => $values ) {
			if ( $shop = Lengow_Main::find_by_token( $shop_token ) ) {
				$list_key = array(
					'account_id'   => false,
					'access_token' => false,
					'secret_token' => false
				);
				foreach ( $values as $k => $v ) {
					if ( ! in_array( $k, array_keys( $list_key ) ) ) {
						continue;
					}
					if ( strlen( $v ) > 0 ) {
						$list_key[ $k ] = true;
						Lengow_Configuration::update_value( ( 'lengow_' . $k ), $v );
					}
				}
				$find_false_value = false;
				foreach ( $list_key as $k => $v ) {
					if ( ! $v ) {
						$find_false_value = true;
						break;
					}
				}
				if ( ! $find_false_value ) {
					Lengow_Configuration::update_value( 'lengow_store_enabled', true );
				} else {
					Lengow_Configuration::update_value( 'lengow_store_enabled', false );
				}
			}
		}
	}

	/**
	 * Check Synchronisation shop
	 *
	 * @return boolean
	 */
	public static function check_sync_shop() {
		return Lengow_Configuration::get( 'lengow_store_enabled' )
		       && Lengow_Check::is_valid_auth();
	}

	/**
	 * Get Sync Data (Inscription / Update)
	 *
	 * @return array
	 */
	public static function get_option_data() {
		global $wp_version;
		$data             = array();
		$data['cms']      = array(
			'token'          => Lengow_Main::get_token(),
			'type'           => 'woocommerce',
			'version'        => $wp_version,
			'plugin_version' => LENGOW_VERSION,
			'options'        => Lengow_Configuration::get_all_values( false )
		);
		$lengow_export    = new Lengow_Export();
		$data['shops'][1] = array(
			'enabled'                 => Lengow_Configuration::get( 'lengow_store_enabled' ),
			'token'                   => Lengow_Main::get_token(),
			'store_name'              => Lengow_Configuration::get( 'blogname' ),
			'domain_url'              => $_SERVER["SERVER_NAME"],
			'feed_url'                => Lengow_Main::get_export_url(),
			'cron_url'                => Lengow_Main::get_cron_url(),
			'total_product_number'    => $lengow_export->get_total_product(),
			'exported_product_number' => $lengow_export->get_total_export_product(),
			'options'                 => Lengow_Configuration::get_all_values( false, true )
		);

		return $data;
	}

	/**
	 * Set CMS options
	 *
	 * @param boolean $force Force cache Update
	 *
	 * @return boolean
	 */
	public static function set_cms_option( $force = false ) {
		if ( Lengow_Main::is_new_merchant() ) {
			return false;
		}
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get( 'lengow_last_option_update' );
			if ( ! is_null( $updated_at ) && ( time() - strtotime( $updated_at ) ) < self::$cacheTime ) {
				return false;
			}
		}
		$options = json_encode( self::get_option_data() );
		Lengow_Connector::query_api( 'put', '/v3.0/cms', array(), $options );
		Lengow_Configuration::update_value( 'lengow_last_option_update', date( 'Y-m-d H:i:s' ) );

		return true;
	}


	/**
	 * Get Statistic
	 *
	 * @param boolean $force Force cache Update
	 *
	 * @return array
	 */
	public static function get_statistic( $force = false ) {
		if ( ! $force ) {
			$updatedAt = Lengow_Configuration::get( 'lengow_last_order_statistic_update' );
			if ( ( time() - strtotime( $updatedAt ) ) < self::$cacheTime ) {
				return json_decode( Lengow_Configuration::get( 'lengow_order_statistic' ), true );
			}
		}
		$return                = array();
		$return['total_order'] = 0;
		$return['nb_order']    = 0;
		$return['currency']    = '';

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
			$stats                 = $result->level0[0];
			$return['total_order'] = $stats->revenue;
			$return['nb_order']    = (int) $stats->transactions;
			$return['currency']    = $result->currency->iso_a3;
		}
		if ( $return['currency'] && get_woocommerce_currency_symbol( $return['currency'] ) ) {
			$return['total_order'] = wc_price( $return['total_order'], array( 'currency' => $return['currency'] ) );
		} else {
			$return['total_order'] = number_format( $return['total_order'], 2, ',', ' ' );
		}
		Lengow_Configuration::update_value( 'lengow_order_statistic', json_encode( $return ) );
		Lengow_Configuration::update_value( 'lengow_last_order_statistic_update', date( 'Y-m-d H:i:s' ) );

		return $return;

	}

	/**
	 * Get Status Account
	 *
	 * @param boolean $force Force cache Update
	 *
	 * @return mixed
	 */
	public static function get_status_account( $force = false ) {
		if ( ! $force ) {
			$updated_at = Lengow_Configuration::get(
				'lengow_last_account_status_update'
			);
			if ( ! is_null( $updated_at ) && ( time() - strtotime( $updated_at ) ) < self::$cacheTime ) {
				$config = Lengow_Configuration::get(
					'lengow_account_status'
				);

				return json_decode( $config, true );
			}
		}

		$result = Lengow_Connector::query_api(
			'get',
			'/v3.0/subscriptions'
		);
		if ( $result && !isset($result->error->code) ) {
			$status         = array();
			$status['type'] = $result->subscription->billing_offer->type;
			$status['day']  = - round( ( strtotime( date( "c" ) ) - strtotime( $result->subscription->renewal ) ) / 86400 );
			if ( $status['day'] < 0 ) {
				$status['day'] = "0";
			}
			if ( $status ) {
				$jsonStatus = json_encode( $status );
				$date       = date( 'Y-m-d H:i:s' );
				Lengow_Configuration::update_value(
					'lengow_account_status',
					$jsonStatus
				);
				Lengow_Configuration::update_value(
					'lengow_last_account_status_update',
					$date
				);

				return $status;
			}
		}

		return false;
	}
}

