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
	 * Get Lengow action.
	 *
	 * @param array $where a named array of WHERE clauses
	 * @param boolean $single get a single result or not
	 *
	 * @return false|object[]|object
	 *
	 */
	public static function get( $where = array(), $single = true ) {
		return Lengow_Crud::read( Lengow_Crud::LENGOW_ACTION, $where, $single );
	}

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
	 * Find active actions by order id.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param string|null $action_type action type (ship or cancel)
	 *
	 * @return array|false
	 */
	public static function get_active_action_by_order_id( $order_id, $action_type = null ) {
		$where = array( 'order_id' => $order_id, 'state' => self::STATE_NEW );
		if ( null !== $action_type ) {
			$where['action_type'] = $action_type;
		}
		$actions = self::get( $where, false );

		return ! empty( $actions ) ? $actions : false;
	}

	/**
	 * Get all active actions.
	 *
	 * @return array|false
	 */
	public static function get_all_active_actions() {
		$actions = self::get( array( 'state' => self::STATE_NEW ), false );

		return ! empty( $actions ) ? $actions : false;
	}

	/**
	 * Get last order action type.
	 *
	 * @param $order_id
	 *
	 * @return bool|string
	 */
	public static function get_last_order_action_type($order_id) {
		$where = array( 'order_id' => $order_id, 'state' => self::STATE_NEW );
		$actions = self::get($where, false);
		if (!$actions) {
			return false;
		}
		$last_action = end($actions);
		return (string)$last_action->action_type;
	}

	/**
	 * Finish action.
	 *
	 * @param integer $action_id Lengow action id
	 *
	 * @return boolean
	 */
	public static function finish_action( $action_id ) {
		return self::update( $action_id, array( 'state' => self::STATE_FINISH ) );
	}

	/**
	 * Removes all actions for one order WooCommerce.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param string|null $action_type action type (ship or cancel)
	 *
	 * @return boolean
	 */
	public static function finish_all_actions( $order_id, $action_type = null ) {
		$active_action = self::get_active_action_by_order_id( $order_id, $action_type );
		if ( $active_action ) {
			$update_success = 0;
			foreach ( $active_action as $action ) {
				$result = self::finish_action( $action->id );
				if ( $result ) {
					$update_success ++;
				}
			}

			return $update_success === count( $active_action ) ? true : false;
		}

		return true;
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
		// do nothing if the order is closed.
		if ( $order_lengow->is_closed() ) {
			return false;
		}
		// check if an action with the same parameters has already been sent.
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
				$action = self::get( array( 'action_id' => (int) $row->id ) );
				if ( $action ) {
					// if the action already exists, the number of retries is increased.
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
	 * Send a new action on the order via the Lengow API.
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
						'parameters'     => json_encode( $params ),
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

	/**
	 * Check if active actions are finished.
	 *
	 * @return boolean
	 */
	public static function check_finish_action() {
		if ( Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) {
			return false;
		}
		Lengow_Main::log(
			'API-OrderAction',
			Lengow_Main::set_log_message( 'log.order_action.check_completed_action' )
		);
		$active_actions = self::get_all_active_actions();
		if ( ! $active_actions ) {
			return true;
		}
		// get all actions with API for 3 days.
		$page        = 1;
		$api_actions = array();
		do {
			$results = Lengow_Connector::query_api(
				'get',
				'/v3.0/orders/actions/',
				array(
					'updated_from' => date( 'c', strtotime( date( 'Y-m-d' ) . ' -3days' ) ),
					'updated_to'   => date( 'c' ),
					'page'         => $page,
				)
			);
			if ( ! is_object( $results ) || isset( $results->error ) ) {
				break;
			}
			// construct array actions.
			foreach ( $results->results as $action ) {
				if ( isset( $action->id ) ) {
					$api_actions[ $action->id ] = $action;
				}
			}
			$page ++;
		} while ( null !== $results->next );
		if ( empty( $api_actions ) ) {
			return false;
		}
		// check foreach action if is complete.
		foreach ( $active_actions as $action ) {
			$action_id = (int) $action->action_id;
			if ( ! isset( $api_actions[ $action_id ] ) ) {
				continue;
			}
			$api_action = $api_actions[ $action_id ];
			if ( isset( $api_action->queued ) && isset( $api_action->processed ) && isset( $api_action->errors ) ) {
				if ( false == $api_action->queued ) {
					// order action is waiting to return from the marketplace.
					if ( false == $api_action->processed && empty( $api_action->errors ) ) {
						continue;
					}
					// finish action in lengow_action table.
					self::finish_action( $action->id );
					$order_lengow_id = Lengow_Order::get_id_from_order_id( $action->order_id );
					$order_lengow    = new Lengow_Order( $order_lengow_id );
					// finish all order logs send.
					Lengow_Order_Error::finish_order_errors( $order_lengow->id, Lengow_Order_Error::ERROR_TYPE_SEND );
					if ( $order_lengow->is_in_error ) {
						Lengow_Order::update( $order_lengow->id, array( 'is_in_error' => 0 ) );
					}
					if ( ! $order_lengow->is_closed() ) {
						// if action is accepted -> close order and finish all order actions.
						if ( true == $api_action->processed && empty( $api_action->errors ) ) {
							Lengow_Order::update(
								$order_lengow->id,
								array( 'order_process_state' => Lengow_Order::PROCESS_STATE_FINISH )
							);
							self::finish_all_actions( $order_lengow->order_id );
						} else {
							// if action is denied -> create order logs and finish all order actions.
							Lengow_Order::add_order_error(
								$order_lengow->id,
								$api_action->errors,
								Lengow_Order_Error::ERROR_TYPE_SEND
							);
							Lengow_Main::log(
								'API-OrderAction',
								Lengow_Main::set_log_message(
									'log.order_action.call_action_failed',
									array( 'decoded_message' => $api_action->errors )
								),
								false,
								$order_lengow->marketplace_sku
							);
						}
					}
					unset( $order_lengow );
				}
			}
		}

		return true;
	}

	/**
	 * Remove old actions > 3 days.
	 *
	 * @return boolean
	 */
	public static function check_old_action() {
		if ( Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) {
			return false;
		}
		Lengow_Main::log( 'API-OrderAction', Lengow_Main::set_log_message( 'log.order_action.check_old_action' ) );
		// get all old order action (+ 3 days).
		$actions = self::get_old_actions();
		if ( $actions ) {
			foreach ( $actions as $action ) {
				// finish action in lengow_action table.
				self::finish_action( $action->id );
				$order_lengow_id = Lengow_Order::get_id_from_order_id( $action->order_id );
				$order_lengow    = new Lengow_Order( $order_lengow_id );
				// finish all order logs send.
				Lengow_Order_Error::finish_order_errors( $order_lengow->id, Lengow_Order_Error::ERROR_TYPE_SEND );
				if ( $order_lengow->is_in_error ) {
					Lengow_Order::update( $order_lengow->id, array( 'is_in_error' => 0 ) );
				}
				if ( ! $order_lengow->is_closed() ) {
					// if action is denied -> create order error.
					$error_message = Lengow_Main::set_log_message( 'lengow_log.exception.action_is_too_old' );
					Lengow_Order::add_order_error(
						$order_lengow->id,
						$error_message,
						Lengow_Order_Error::ERROR_TYPE_SEND
					);
					$decodedMessage = Lengow_Main::decode_log_message( $error_message, 'en_GB' );
					Lengow_Main::log(
						'API-OrderAction',
						Lengow_Main::set_log_message(
							'log.order_action.call_action_failed',
							array( 'decoded_message' => $decodedMessage )
						),
						false,
						$order_lengow->marketplace_sku
					);
				}
				unset( $order_lengow );
			}
		}

		return true;
	}

	/**
	 * Get old untreated actions of more than 3 days.
	 *
	 * @return array|false
	 */
	public static function get_old_actions() {
		global $wpdb;

		$date    = date( 'Y-m-d H:i:s', strtotime( '-3 days', time() ) );
		$query   = '
			SELECT * FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ACTION . '
			WHERE created_at <= %s
			AND state = %d
		';
		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $date, self::STATE_NEW ) )
		);

		return $results ? $results : false;
	}

	/**
	 * Check if actions are not sent.
	 *
	 * @return boolean
	 */
	public static function check_action_not_sent() {
		if ( Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) {
			return false;
		}
		Lengow_Main::log( 'API-OrderAction', Lengow_Main::set_log_message( 'log.order_action.check_action_not_sent' ) );
		// get unsent orders.
		$unsent_orders = Lengow_Order::get_unsent_orders();
		if ( $unsent_orders ) {
			foreach ( $unsent_orders as $unsent_order ) {
				if ( ! self::get_active_action_by_order_id( $unsent_order->order_id ) ) {
					$canceled_state = Lengow_Order::get_order_state( Lengow_Order::STATE_CANCELED );
					$action         = $canceled_state === $unsent_order->order_status
						? self::TYPE_CANCEL
						: self::TYPE_SHIP;
					$order_lengow   = new Lengow_Order( $unsent_order->order_lengow_id );
					$order_lengow->call_action( $action );
				}
			}
		}

		return true;
	}
}
