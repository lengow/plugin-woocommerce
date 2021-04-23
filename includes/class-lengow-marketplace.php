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
 * Lengow_Marketplace Class.
 */
class Lengow_Marketplace {

	/**
	 * @var string marketplace file name.
	 */
	const FILE_MARKETPLACE = 'marketplaces.json';

	/**
	 * @var mixed all marketplaces allowed for an account ID.
	 */
	public static $marketplaces = false;

	/**
	 * @var array all valid actions.
	 */
	public static $valid_actions = array(
		Lengow_Action::TYPE_SHIP,
		Lengow_Action::TYPE_CANCEL,
	);

	/**
	 * @var mixed the current marketplace.
	 */
	public $marketplace;

	/**
	 * @var string the name of the marketplace.
	 */
	public $name;

	/**
	 * @var string the name of the marketplace.
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
		self::load_api_marketplace();
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
	public static function load_api_marketplace() {
		if ( ! self::$marketplaces ) {
			self::$marketplaces = Lengow_Sync::get_marketplaces();
		}
	}

	/**
	 * Check if marketplace name exist.
	 *
	 * @param string $name the name of the marketplace
	 *
	 * @return boolean
	 */
	public static function marketplace_exist( $name ) {
		self::load_api_marketplace();
		if ( self::$marketplaces && isset( self::$marketplaces->{$name} ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get marketplaces.json path.
	 *
	 * @return string
	 */
	public static function get_file_path() {
		$sep = DIRECTORY_SEPARATOR;

		return LENGOW_PLUGIN_PATH . $sep . Lengow_Main::FOLDER_CONFIG . $sep . self::FILE_MARKETPLACE;
	}

	/**
	 * Get the real Lengow's state.
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
	public function argument_is_required( $argument, $action = Lengow_Action::TYPE_SHIP ) {
		$actions = $this->get_action( $action );
		if ( isset( $actions['args'] ) && in_array( $argument, $actions['args'] ) ) {
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
		$marketplace_arguments = $this->get_marketplace_arguments( Lengow_Action::TYPE_SHIP );
		if ( array_key_exists( Lengow_Action::ARG_CARRIER_NAME, $marketplace_arguments )
		     || array_key_exists( Lengow_Action::ARG_CUSTOM_CARRIER, $marketplace_arguments )
		) {
			return true;
		} elseif ( array_key_exists( Lengow_Action::ARG_CARRIER, $this->arg_values )
		           && $this->arg_values[ Lengow_Action::ARG_CARRIER ]['accept_free_values']
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
		$actions = $this->get_action( Lengow_Action::TYPE_SHIP );
		if ( isset( $actions['args'] ) &&
		     ( in_array( Lengow_Action::ARG_CARRIER_NAME, $actions['args'] )
		       || in_array( Lengow_Action::ARG_CUSTOM_CARRIER, $actions['args'] )
		     )
		) {
			return true;
		} elseif ( isset( $actions['args'] )
		           && in_array( Lengow_Action::ARG_CARRIER, $actions['args'] )
		           && $this->arg_values[ Lengow_Action::ARG_CARRIER ]['accept_free_values']
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get the default value for argument.
	 *
	 * @param string $name argument's name
	 *
	 * @return string|false
	 */
	public function get_default_value( $name ) {
		if ( array_key_exists( $name, $this->arg_values ) ) {
			$defaultValue = $this->arg_values[ $name ]['default_value'];
			if ( ! empty( $defaultValue ) ) {
				return $defaultValue;
			}
		}

		return false;
	}

	/**
	 * Is marketplace contain order line.
	 *
	 * @param string $action Lengow order actions type (ship or cancel)
	 *
	 * @return boolean
	 */
	public function contain_order_line( $action ) {
		if ( isset( $this->actions[ $action ] ) ) {
			$actions = $this->actions[ $action ];
			if ( isset( $actions['args'] ) && is_array( $actions['args'] ) ) {
				if ( in_array( Lengow_Action::ARG_LINE, $actions['args'] ) ) {
					return true;
				}
			}
			if ( isset( $actions['optional_args'] ) && is_array( $actions['optional_args'] ) ) {
				if ( in_array( Lengow_Action::ARG_LINE, $actions['optional_args'] ) ) {
					return true;
				}
			}
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

	/**
	 * Call API action and create action in lengow_actions table.
	 *
	 * @param string $action Lengow order actions type (ship or cancel)
	 * @param Lengow_Order $order_lengow Lengow order instance
	 * @param string|null $order_line_id Lengow order line id
	 *
	 * @return boolean
	 */
	public function call_action( $action, $order_lengow, $order_line_id = null ) {
		// do nothing if the order is closed.
		if ( $order_lengow->is_closed() ) {
			return false;
		}
		try {
			// check the action and order data.
			$this->_check_action( $action );
			$this->_check_order_data( $order_lengow );
			// get all required and optional arguments for a specific marketplace.
			$marketplace_arguments = $this->get_marketplace_arguments( $action );
			// get all available values from an order.
			$params = $this->_get_all_params( $action, $order_lengow, $marketplace_arguments );
			// check required arguments and clean value for empty optionals arguments.
			$params = $this->_check_and_clean_params( $action, $params );
			// complete the values with the specific values of the account.
			if ( ! is_null( $order_line_id ) ) {
				$params[ Lengow_Action::ARG_LINE ] = $order_line_id;
			}
			$params['marketplace_order_id']           = $order_lengow->marketplace_sku;
			$params['marketplace']                    = $order_lengow->marketplace_name;
			$params[ Lengow_Action::ARG_ACTION_TYPE ] = $action;
			// checks whether the action is already created to not return an action.
			$can_send_action = Lengow_Action::can_send_action( $params, $order_lengow );
			if ( $can_send_action ) {
				// send a new action on the order via the Lengow API.
				Lengow_Action::send_action( $params, $order_lengow );
			}
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce Error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			Lengow_Order::add_order_error( $order_lengow->id, $error_message, Lengow_Order_Error::ERROR_TYPE_SEND );
			$decoded_message = Lengow_Main::decode_log_message( $error_message, Lengow_Translation::DEFAULT_ISO_CODE );
			Lengow_Main::log(
				Lengow_Log::CODE_ACTION,
				Lengow_Main::set_log_message(
					'log.order_action.call_action_failed',
					array( 'decoded_message' => $decoded_message )
				),
				false,
				$order_lengow->marketplace_sku
			);

			return false;
		}

		return true;
	}

	/**
	 * Check if the action is valid and present on the marketplace.
	 *
	 * @param string $action Lengow order actions type (ship or cancel)
	 *
	 * @throws Lengow_Exception action not valid / marketplace action not present
	 */
	private function _check_action( $action ) {
		if ( ! in_array( $action, self::$valid_actions ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'lengow_log.exception.action_not_valid', array( 'action' => $action ) )
			);
		}
		if ( ! $this->get_action( $action ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message(
					'lengow_log.exception.marketplace_action_not_present',
					array( 'action' => $action )
				)
			);
		}
	}

	/**
	 * Check if the essential data of the order are present.
	 *
	 * @param Lengow_Order $order_lengow Lengow order instance
	 *
	 * @throws Lengow_Exception marketplace sku is required / marketplace name is required
	 */
	private function _check_order_data( $order_lengow ) {
		if ( 0 === strlen( $order_lengow->marketplace_sku ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'lengow_log.exception.marketplace_sku_require' )
			);
		}
		if ( 0 === strlen( $order_lengow->marketplace_name ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'lengow_log.exception.marketplace_name_require' )
			);
		}
	}

	/**
	 * Get all available values from an order.
	 *
	 * @param string $action Lengow order actions type (ship or cancel)
	 * @param Lengow_Order $order_lengow Lengow order instance
	 * @param array $marketplace_arguments All marketplace arguments for a specific action
	 *
	 * @return array
	 */
	private function _get_all_params( $action, $order_lengow, $marketplace_arguments ) {
		$params         = [];
		$actions        = $this->get_action( $action );
		$order_id       = $order_lengow->order_id;
		$carrier        = (string) get_post_meta( $order_id, '_lengow_carrier', true );
		$custom_carrier = (string) get_post_meta( $order_id, '_lengow_custom_carrier', true );
		if ( null !== $order_lengow->carrier && strlen( $order_lengow->carrier ) > 0 ) {
			$carrier_code = $order_lengow->carrier;
		} else {
			$carrier_code = strlen( $carrier ) > 0 ? $carrier : $custom_carrier;
		}
		// get all order informations.
		foreach ( $marketplace_arguments as $arg ) {
			switch ( $arg ) {
				case Lengow_Action::ARG_TRACKING_NUMBER:
					$params[ $arg ] = (string) get_post_meta( $order_id, '_lengow_tracking_number', true );
					break;
				case Lengow_Action::ARG_CARRIER:
				case Lengow_Action::ARG_CARRIER_NAME:
				case Lengow_Action::ARG_SHIPPING_METHOD:
				case Lengow_Action::ARG_CUSTOM_CARRIER:
					$params[ $arg ] = $carrier_code;
					break;
				case Lengow_Action::ARG_TRACKING_URL:
					$params[ $arg ] = (string) get_post_meta( $order_id, '_lengow_tracking_url', true );
					break;
				case Lengow_Action::ARG_SHIPPING_PRICE:
					$shipping       = (float) get_post_meta( $order_id, '_order_shipping', true );
					$shipping_tax   = (float) get_post_meta( $order_id, '_order_shipping_tax', true );
					$params[ $arg ] = $shipping + $shipping_tax;
					break;
				case Lengow_Action::ARG_SHIPPING_DATE:
				case Lengow_Action::ARG_DELIVERY_DATE:
					$params[ $arg ] = get_date_from_gmt( date( 'Y-m-d H:i:s' ), 'c' );
					break;
				default:
					if ( isset( $actions['optional_args'] ) && in_array( $arg, $actions['optional_args'] ) ) {
						break;
					}
					$default_value  = $this->get_default_value( $arg );
					$param_value    = $default_value ? $default_value : $arg . ' not available';
					$params[ $arg ] = $param_value;
					break;
			}
		}

		return $params;
	}

	/**
	 * Check required parameters and delete empty parameters.
	 *
	 * @param string $action Lengow order actions type (ship or cancel)
	 * @param array $params all available values
	 *
	 * @return array
	 * @throws Exception argument is required
	 *
	 */
	private function _check_and_clean_params( $action, $params ) {
		$actions = $this->get_action( $action );
		if ( isset( $actions['args'] ) ) {
			foreach ( $actions['args'] as $arg ) {
				if ( ! isset( $params[ $arg ] ) || 0 === strlen( $params[ $arg ] ) ) {
					throw new Lengow_Exception(
						Lengow_Main::set_log_message(
							'lengow_log.exception.arg_is_required',
							array( 'arg_name' => $arg )
						)
					);
				}
			}
		}
		if ( isset( $actions['optional_args'] ) ) {
			foreach ( $actions['optional_args'] as $arg ) {
				if ( isset( $params[ $arg ] ) && 0 === strlen( $params[ $arg ] ) ) {
					unset( $params[ $arg ] );
				}
			}
		}

		return $params;
	}
}
