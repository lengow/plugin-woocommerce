<?php
/**
 * All function to manage action
 *
 * Copyright 2019 Lengow SAS
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
 * @copyright   2019 Lengow SAS
 * @license     https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Action Class.
 */
class Lengow_Action {

	/**
	 * @var integer action state for new action.
	 */
	const STATE_NEW = 0;

	/**
	 * @var integer action state for action finished.
	 */
	const STATE_FINISH = 1;

	/**
	 * @var string action type ship.
	 */
	const TYPE_SHIP = 'ship';

	/**
	 * @var string action type cancel.
	 */
	const TYPE_CANCEL = 'cancel';

	/**
	 * @var string action argument marketplace order id.
	 */
	const ARG_MARKETPLACE_ORDER_ID = 'marketplace_order_id';

	/**
	 * @var string action argument marketplace.
	 */
	const ARG_MARKETPLACE = 'marketplace';

	/**
	 * @var string action argument action type.
	 */
	const ARG_ACTION_TYPE = 'action_type';

	/**
	 * @var string action argument line.
	 */
	const ARG_LINE = 'line';

	/**
	 * @var string action argument carrier.
	 */
	const ARG_CARRIER = 'carrier';

	/**
	 * @var string action argument carrier name.
	 */
	const ARG_CARRIER_NAME = 'carrier_name';

	/**
	 * @var string action argument custom carrier.
	 */
	const ARG_CUSTOM_CARRIER = 'custom_carrier';

	/**
	 * @var string action argument shipping method.
	 */
	const ARG_SHIPPING_METHOD = 'shipping_method';

	/**
	 * @var string action argument tracking number.
	 */
	const ARG_TRACKING_NUMBER = 'tracking_number';

	/**
	 * @var string action argument tracking url.
	 */
	const ARG_TRACKING_URL = 'tracking_url';

	/**
	 * @var string action argument shipping price.
	 */
	const ARG_SHIPPING_PRICE = 'shipping_price';

	/**
	 * @var string action argument shipping date.
	 */
	const ARG_SHIPPING_DATE = 'shipping_date';

	/**
	 * @var string action argument delivery date.
	 */
	const ARG_DELIVERY_DATE = 'delivery_date';

	/**
	 * @var array Parameters to delete for Get call.
	 */
	public static $get_params_to_delete = array(
		self::ARG_SHIPPING_DATE,
		self::ARG_DELIVERY_DATE,
	);

	/**
	 * Create Lengow action.
	 *
	 * @param array $data Lengow action data
	 *
	 * @return boolean
	 *
	 */
	public static function create( $data = array() ) {
		$data['created_at'] = date( 'Y-m-d H:i:s' );
		$data['state']      = self::STATE_NEW;

		return Lengow_Crud::create( Lengow_Crud::LENGOW_ACTION, $data );
	}

	/**
	 * Update Lengow action.
	 *
	 * @param integer $action_id Lengow action id
	 * @param array $data Lengow action data
	 *
	 * @return boolean
	 *
	 */
	public static function update( $action_id, $data = array() ) {
		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		return Lengow_Crud::update( Lengow_Crud::LENGOW_ACTION, $data, array( 'id' => $action_id ) );
	}

	/**
	 * Indicates whether an action can be created if it does not already exist.
	 *
	 * @param array $params all available values
	 * @param Lengow_Order $order_lengow Lengow order instance
	 *
	 * @return boolean
	 * @throws Lengow_Exception
	 *
	 */
	public static function can_send_action( $params, $order_lengow ) {
		$send_action = true;
		$get_params  = array_merge( $params, array( 'queued' => 'True' ) );
		// array key deletion for GET verification.
		foreach ( self::$get_params_to_delete as $param ) {
			if ( isset( $get_params[ $param ] ) ) {
				unset( $get_params[ $param ] );
			}
		}
		$result = Lengow_Connector::query_api( 'get', '/v3.0/orders/actions/', $get_params );
		if ( isset( $result->error ) && isset( $result->error->message ) ) {
			throw new Lengow_Exception( $result->error->message );
		}
		if ( isset( $result->count ) && $result->count > 0 ) {
			foreach ( $result->results as $row ) {
				$action = Lengow_Crud::read( Lengow_Crud::LENGOW_ACTION, array( 'action_id' => (int) $row->id ) );
				if ( $action ) {
					$update = self::update( $action->id, array( 'retry' => (int) $action->retry + 1 ) );
					if ( $update ) {
						$send_action = false;
					}
				} else {
					// if update doesn't work, create new action.
					self::create(
						array(
							'order_id'       => $order_lengow->order_id,
							'action_type'    => $params[ self::ARG_ACTION_TYPE ],
							'action_id'      => $row->id,
							'order_line_sku' => isset( $params[ self::ARG_LINE ] ) ? $params[ self::ARG_LINE ] : null,
							'parameters'     => json_encode( $params ),
						)
					);
					$send_action = false;
				}
			}
		}

		return $send_action;
	}

	/**
	 * Send a new action on the order via the Lengow API
	 *
	 * @param array $params all available values
	 * @param Lengow_Order $order_lengow Lengow order instance
	 *
	 * @throws Lengow_Exception
	 */
	public static function send_action( $params, $order_lengow ) {
		if ( ! Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) {
			$result = Lengow_Connector::query_api( 'post', '/v3.0/orders/actions/', $params );
			if ( isset( $result->id ) ) {
				self::create(
					array(
						'order_id'       => $order_lengow->order_id,
						'action_type'    => $params[ self::ARG_ACTION_TYPE ],
						'action_id'      => $result->id,
						'order_line_sku' => isset( $params[ self::ARG_LINE ] ) ? $params[ self::ARG_LINE ] : null,
						'parameters'     => $params,
					)
				);
			} else {
				if ( null !== $result ) {
					$message = Lengow_Main::set_log_message(
						'lengow_log.exception.action_not_created',
						array( 'error_message' => json_encode( $result ) )
					);
				} else {
					// generating a generic error message when the Lengow API is unavailable.
					$message = Lengow_Main::set_log_message( 'lengow_log.exception.action_not_created_api' );
				}
				throw new Lengow_Exception( $message );
			}
		}
		// create log for call action.
		$param_list = false;
		foreach ( $params as $param => $value ) {
			$param_list .= ! $param_list ? '"' . $param . '": ' . $value : ' -- "' . $param . '": ' . $value;
		}
		Lengow_Main::log(
			'API-OrderAction',
			Lengow_Main::set_log_message( 'log.order_action.call_tracking', array( 'parameters' => $param_list ) ),
			false,
			$order_lengow->marketplace_sku
		);
	}
}
