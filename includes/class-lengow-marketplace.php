<?php
/**
 * Get marketplace information
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
 * Lengow_Marketplace Class.
 */
class Lengow_Marketplace {

	/**
	 * @var string marketplace file name.
	 */
	public static $marketplace_json = 'marketplaces.json';

	/**
	 * @var mixed all marketplaces allowed for an account ID.
	 */
	public static $marketplaces = false;

	/**
	 * @var mixed the current marketplace.
	 */
	public $marketplace;

	/**
	 * @var string the name of the marketplace.
	 */
	public $name;

	/**
	 * @var string the name of the marketplace
	 */
	public $label_name;

	/**
	 * @var string the old code of the marketplace for v2 compatibility.
	 */
	public $legacy_code;

	/**
	 * @var boolean if the marketplace is loaded.
	 */
	public $is_loaded = false;

	/**
	 * @var array Lengow states => marketplace states.
	 */
	public $states_lengow = array();

	/**
	 * @var array marketplace states => Lengow states.
	 */
	public $states = array();

	/**
	 * @var array all possible actions of the marketplace.
	 */
	public $actions = array();

	/**
	 * @var array all carriers of the marketplace.
	 */
	public $carriers = array();

	/**
	 * @var array all possible values for actions of the marketplace.
	 */
	public $arg_values = array();

	/**
	 * Construct a new Marketplace instance with marketplace API.
	 *
	 * @param string $name the name of the marketplace
	 *
	 * @throws Lengow_Exception If marketplace not present
	 */
	public function __construct( $name ) {
		$this->_load_api_marketplace();
		$this->name = strtolower( $name );
		if ( ! isset( self::$marketplaces->{$this->name} ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message(
					'lengow_log.exception.marketplace_not_present',
					array( 'marketplace_name' => $this->name )
				)
			);
		}
		$this->marketplace = self::$marketplaces->{$this->name};
		if ( ! empty( $this->marketplace ) ) {
			$this->legacy_code = $this->marketplace->legacy_code;
			$this->label_name  = $this->marketplace->name;
			foreach ( $this->marketplace->orders->status as $key => $state ) {
				foreach ( $state as $value ) {
					$this->states_lengow[ (string) $value ]           = (string) $key;
					$this->states[ (string) $key ][ (string) $value ] = (string) $value;
				}
			}
			foreach ( $this->marketplace->orders->actions as $key => $action ) {
				foreach ( $action->status as $state ) {
					$this->actions[ (string) $key ]['status'][ (string) $state ] = (string) $state;
				}
				foreach ( $action->args as $arg ) {
					$this->actions[ (string) $key ]['args'][ (string) $arg ] = (string) $arg;
				}
				foreach ( $action->optional_args as $optional_arg ) {
					$this->actions[ (string) $key ]['optional_args'][ (string) $optional_arg ] = $optional_arg;
				}
				foreach ( $action->args_description as $arg_key => $arg_description ) {
					$valid_values = array();
					if ( isset( $arg_description->valid_values ) ) {
						foreach ( $arg_description->valid_values as $code => $valid_value ) {
							$valid_values[ (string) $code ] = isset( $valid_value->label )
								? (string) $valid_value->label
								: (string) $valid_value;
						}
					}
					$default_value                         = isset( $arg_description->default_value )
						? (string) $arg_description->default_value
						: '';
					$accept_free_value                     = isset( $arg_description->accept_free_values )
						? (bool) $arg_description->accept_free_values
						: true;
					$this->arg_values[ (string) $arg_key ] = array(
						'default_value'      => $default_value,
						'accept_free_values' => $accept_free_value,
						'valid_values'       => $valid_values,
					);
				}
			}
			if ( isset( $this->marketplace->orders->carriers ) ) {
				foreach ( $this->marketplace->orders->carriers as $key => $carrier ) {
					$this->carriers[ (string) $key ] = (string) $carrier->label;
				}
			}
			$this->is_loaded = true;
		}
	}

	/**
	 * Load the json configuration of all marketplaces.
	 */
	private function _load_api_marketplace() {
		if ( ! self::$marketplaces ) {
			self::$marketplaces = Lengow_Sync::get_marketplaces();
		}
	}

	/**
	 * Get marketplaces.json path
	 *
	 * @return string
	 */
	public static function get_file_path() {
		$sep = DIRECTORY_SEPARATOR;

		return LENGOW_PLUGIN_PATH . $sep . Lengow_Main::$lengow_config_folder . $sep . self::$marketplace_json;
	}

	/**
	 * Get the real lengow's state.
	 *
	 * @param string $name the marketplace state
	 *
	 * @return string
	 */
	public function get_state_lengow( $name ) {
		if ( array_key_exists( $name, $this->states_lengow ) ) {
			return $this->states_lengow[ $name ];
		}

		return null;
	}

	/**
	 * Get the action with parameters.
	 *
	 * @param string $name action's name
	 *
	 * @return array|false
	 */
	public function get_action( $name ) {
		if ( array_key_exists( $name, $this->actions ) ) {
			return $this->actions[ $name ];
		}

		return false;
	}

	/**
	 * Check if an argument is required.
	 *
	 * @param string $argument argument name
	 * @param string $action Lengow order actions type (ship or cancel)
	 *
	 * @return boolean
	 */
	public function argument_is_required($argument, $action = 'ship') {
		$actions = $this->get_action($action);
		if (isset($actions['args']) && in_array($argument, $actions['args'])) {
			return true;
		}

		return false;
	}

	/**
	 * Check if marketplace accept custom carrier code.
	 *
	 * @return boolean
	 */
	public function accept_custom_carrier() {
		$marketplace_arguments = $this->get_marketplace_arguments( 'ship' );
		if ( array_key_exists( 'carrier_name', $marketplace_arguments )
		     || array_key_exists( 'custom_carrier', $marketplace_arguments )
		) {
			return true;
		} elseif ( array_key_exists( 'carrier', $this->arg_values )
		           && $this->arg_values['carrier']['accept_free_values']
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if custom carrier code is required.
	 *
	 * @return boolean
	 */
	public function custom_carrier_is_required() {
		$actions = $this->get_action('ship');
		if ( isset( $actions['args'] ) &&
		     (in_array( 'carrier_name', $actions['args'] ) || in_array( 'custom_carrier', $actions['args'] ))
		) {
			return true;
		} elseif ( isset( $actions['args'] )
		           && in_array( 'carrier', $actions['args'] )
		           && $this->arg_values['carrier']['accept_free_values']
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get all marketplace arguments for a specific action.
	 *
	 * @param string $action Lengow order actions type (ship or cancel)
	 *
	 * @return array
	 */
	public function get_marketplace_arguments( $action ) {
		$actions = $this->get_action( $action );
		if ( isset( $actions['args'] ) && isset( $actions['optional_args'] ) ) {
			$marketplace_arguments = array_merge( $actions['args'], $actions['optional_args'] );
		} elseif ( ! isset( $actions['args'] ) && isset( $actions['optional_args'] ) ) {
			$marketplace_arguments = $actions['optional_args'];
		} elseif ( isset( $actions['args'] ) ) {
			$marketplace_arguments = $actions['args'];
		} else {
			$marketplace_arguments = array();
		}

		return $marketplace_arguments;
	}
}
