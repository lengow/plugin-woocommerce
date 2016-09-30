<?php
/**
 * Import process to synchronise stock
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
 * Lengow_Import Class.
 */
class Lengow_Import {

	/**
	 * @var string marketplace order sku
	 */
	private $_marketplace_sku = null;

	/**
	 * @var string markeplace name
	 */
	private $_marketplace_name = null;

	/**
	 * @var integer delivery address id
	 */
	private $_delivery_address_id = null;

	/**
	 * @var integer number of orders to import
	 */
	private $_limit = 0;

	/**
	 * @var string start import date
	 */
	private $_date_from = null;

	/**
	 * @var string end import date
	 */
	private $_date_to = null;

	/**
	 * @var boolean import one order
	 */
	private $_import_one_order = false;

	/**
	 * @var boolean use preprod mode
	 */
	private $_preprod_mode = false;

	/**
	 * @var boolean display log messages
	 */
	private $_log_output = false;

	/**
	 * @var string type import (manual or cron)
	 */
	private $_type;

	/**
	 * Construct the import manager
	 *
	 * @param $params array Optional options
	 * string  marketplace_sku     lengow marketplace order id to import
	 * string  marketplace_name    lengow marketplace name to import
	 * string  type                type of current import
	 * integer delivery_address_id Lengow delivery address id to import
	 * integer shop_id             shop id for current import
	 * integer days                import period
	 * integer limit               number of orders to import
	 * boolean log_output          display log messages
	 * boolean preprod_mode        preprod mode
	 */
	public function __construct( $params = array() ) {
		// params for re-import order
		if ( isset( $params['marketplace_sku'] ) && isset( $params['marketplace_name'] ) ) {
			$this->_marketplace_sku  = $params['marketplace_sku'];
			$this->_marketplace_name = $params['marketplace_name'];
			$this->_limit            = 1;
			$this->_import_one_order = true;
			if ( isset( $params['delivery_address_id'] ) && $params['delivery_address_id'] != '' ) {
				$this->_delivery_address_id = $params['delivery_address_id'];
			}
		} else {
			// recovering the time interval
			$days             = (
			isset( $params['days'] )
				? $params['days']
				: (int) Lengow_Configuration::get( 'lengow_import_days' )
			);
			$this->_date_from = date( 'c', strtotime( date( 'Y-m-d' ) . ' -' . $days . 'days' ) );
			$this->_date_to   = date( 'c' );
			$this->_limit     = ( isset( $params['limit'] ) ? $params['limit'] : 0 );
		}
		// get other params
		$this->_preprod_mode = (
		isset( $params['preprod_mode'] )
			? $params['preprod_mode']
			: (bool) Lengow_Configuration::get( 'lengow_preprod_enabled' )
		);
		$this->_type         = ( isset( $params['type'] ) ? $params['type'] : 'manual' );
		$this->_log_output   = ( isset( $params['log_output'] ) ? $params['log_output'] : false );
	}

	/**
	 * Execute import : fetch orders and import them
	 *
	 * @return mixed
	 */
	public function exec() {
		if ( ! (bool) Lengow_Configuration::get( 'lengow_import_enabled' ) ) {
			Lengow_Main::log(
				'Import',
				Lengow_Main::set_log_message( 'log.import.import_not_active' ),
				$this->_log_output
			);

			return false;
		}
	}
}

