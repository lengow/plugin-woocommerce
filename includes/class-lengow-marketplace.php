<?php
/**
 * Get marketplace information
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
 * Lengow_Marketplace Class.
 */
class Lengow_Marketplace {

	/**
	 * @var mixed all marketplaces allowed for an account ID
	 */
	public static $MARKETPLACES = null;

	/**
	 * @var string the name of the marketplace
	 */
	public $name;

	/**
	 * @var string the old code of the markeplace for v2 compatibility
	 */
	public $legacy_code;

	/**
	 * @var boolean if the marketplace is loaded
	 */
	public $is_loaded = false;

	/**
	 * @var array Lengow states => marketplace states
	 */
	public $states_lengow = array();

	/**
	 * @var array marketplace states => Lengow states
	 */
	public $states = array();

	/**
	 * @var array all possible actions of the marketplace
	 */
	public $actions = array();

	/**
	 * @var array all carriers of the marketplace
	 */
	public $carriers = array();

	/**
	 * Construct a new Marketplace instance with marketplace API
	 *
	 * @param string $name The name of the marketplace
	 *
	 * @throws Lengow_Exception
	 */
	public function __construct( $name ) {
		$this->_load_api_marketplace();
		$this->name = strtolower( $name );
		if ( ! isset( self::$MARKETPLACES->{$this->name} ) ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message(
					'lengow_log.exception.marketplace_not_present',
					array( 'marketplace_name' => $this->name )
				)
			);
		}
		$this->marketplace = self::$MARKETPLACES->{$this->name};
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
	 * Load the json configuration of all marketplaces
	 */
	private function _load_api_marketplace() {
		if ( is_null( self::$MARKETPLACES ) ) {
			self::$MARKETPLACES = Lengow_Connector::query_api( 'get', '/v3.0/marketplaces' );
		}
	}

	/**
	 * Get the real lengow's state
	 *
	 * @param string $name The marketplace state
	 *
	 * @return string The lengow state
	 */
	public function get_state_lengow( $name ) {
		if ( array_key_exists( $name, $this->states_lengow ) ) {
			return $this->states_lengow[ $name ];
		}

		return null;
	}

}

