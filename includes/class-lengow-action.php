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
 * @copyright   2019 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

use Lengow\Sdk\Client\Exception\HttpException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Action Class.
 */
class Lengow_Action {

	/**
	 * @var string Lengow action table name
	 */
	const TABLE_ACTION = 'lengow_action';

	/* Action fields */
	const FIELD_ID             = 'id';
	const FIELD_ORDER_ID       = 'order_id';
	const FIELD_ORDER_LINE_SKU = 'order_line_sku';
	const FIELD_ACTION_ID      = 'action_id';
	const FIELD_ACTION_TYPE    = 'action_type';
	const FIELD_RETRY          = 'retry';
	const FIELD_PARAMETERS     = 'parameters';
	const FIELD_STATE          = 'state';
	const FIELD_CREATED_AT     = 'created_at';
	const FIELD_UPDATED_AT     = 'updated_at';

	/* Action states */
	const STATE_NEW    = 0;
	const STATE_FINISH = 1;

	/* Action types */
	const TYPE_SHIP   = 'ship';
	const TYPE_CANCEL = 'cancel';

	/* Action API arguments */
	const ARG_ACTION_TYPE     = 'action_type';
	const ARG_LINE            = 'line';
	const ARG_CARRIER         = 'carrier';
	const ARG_CARRIER_NAME    = 'carrier_name';
	const ARG_CUSTOM_CARRIER  = 'custom_carrier';
	const ARG_SHIPPING_METHOD = 'shipping_method';
	const ARG_TRACKING_NUMBER = 'tracking_number';
	const ARG_TRACKING_URL    = 'tracking_url';
	const ARG_SHIPPING_PRICE  = 'shipping_price';
	const ARG_SHIPPING_DATE   = 'shipping_date';
	const ARG_DELIVERY_DATE   = 'delivery_date';

	/**
	 * @var integer max interval time for action synchronisation (3 days)
	 */
	const MAX_INTERVAL_TIME = 259200;

	/**
	 * @var integer security interval time for action synchronisation (2 hours)
	 */
	const SECURITY_INTERVAL_TIME = 7200;

	/**
	 * @var array Parameters to delete for GET call.
	 */
	public static $get_params_to_delete = array(
		self::ARG_SHIPPING_DATE,
		self::ARG_DELIVERY_DATE,
	);

	/**
	 * Get Lengow action.
	 *
	 * @param array   $where a named array of WHERE clauses
	 * @param boolean $single get a single result or not
	 *
	 * @return false|object[]|object
	 */
	public static function get( $where = array(), $single = true ) {
		return Lengow_Crud::read( self::TABLE_ACTION, $where, $single );
	}

	/**
	 * Create Lengow action.
	 *
	 * @param array $data Lengow action data
	 *
	 * @return boolean
	 */
	public static function create( $data = array() ) {
		$data[ self::FIELD_CREATED_AT ] = date( Lengow_Main::DATE_FULL );
		$data[ self::FIELD_STATE ]      = self::STATE_NEW;

		return Lengow_Crud::create( self::TABLE_ACTION, $data );
	}

	/**
	 * Update Lengow action.
	 *
	 * @param integer $action_id Lengow action id
	 * @param array   $data Lengow action data
	 *
	 * @return boolean
	 */
	public static function update( $action_id, $data = array() ) {
		$data[ self::FIELD_UPDATED_AT ] = date( Lengow_Main::DATE_FULL );

		return Lengow_Crud::update( self::TABLE_ACTION, $data, array( self::FIELD_ID => $action_id ) );
	}

	/**
	 * Find actions by order id.
	 *
	 * @param integer     $order_id WooCommerce order id
	 * @param boolean     $only_active get only active actions
	 * @param string|null $action_type action type (ship or cancel)
	 *
	 * @return array|false
	 */
	public static function get_action_by_order_id( $order_id, $only_active = false, $action_type = null ) {
		$where = array( self::FIELD_ORDER_ID => $order_id );
		if ( $only_active ) {
			$where[ self::FIELD_STATE ] = self::STATE_NEW;
		}
		if ( null !== $action_type ) {
			$where[ self::FIELD_ACTION_TYPE ] = $action_type;
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
		$actions = self::get( array( self::FIELD_STATE => self::STATE_NEW ), false );

		return ! empty( $actions ) ? $actions : false;
	}

	/**
	 * Get last order action type.
	 *
	 * @param $order_id
	 *
	 * @return bool|string
	 */
	public static function get_last_order_action_type( $order_id ) {
		$actions = self::get_action_by_order_id( $order_id, true );
		if ( ! $actions ) {
			return false;
		}

		return end( $actions )->action_type;
	}

	/**
	 * Finish action.
	 *
	 * @param integer $action_id Lengow action id
	 *
	 * @return boolean
	 */
	public static function finish_action( $action_id ) {
		return self::update( $action_id, array( self::FIELD_STATE => self::STATE_FINISH ) );
	}

	/**
	 * Removes all actions for one order WooCommerce.
	 *
	 * @param integer     $order_id WooCommerce order id
	 * @param string|null $action_type action type (ship or cancel)
	 *
	 * @return boolean
	 */
	public static function finish_all_actions( $order_id, $action_type = null ) {
		$active_action = self::get_action_by_order_id( $order_id, $action_type, true );
		if ( $active_action ) {
			$update_success = 0;
			foreach ( $active_action as $action ) {
				$result = self::finish_action( $action->{self::FIELD_ID} );
				if ( $result ) {
					++$update_success;
				}
			}

			return $update_success === count( $active_action );
		}

		return true;
	}

	/**
	 * Indicates whether an action can be created if it does not already exist.
	 *
	 * @param array        $params all available values
	 * @param Lengow_Order $order_lengow Lengow order instance
	 *
	 * @return boolean
	 * @throws Lengow_Exception
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

		try {
			$result = Lengow::sdk()->order()->action()->list( $get_params );
		} catch ( HttpException|Exception $e ) {
			Lengow_Main::get_log_instance()->log_exception( $e );
			throw new Lengow_Exception( $e->getMessage(), $e->getCode(), $e );
		}

		if ( isset( $result->count ) && $result->count > 0 ) {
			foreach ( $result->results as $row ) {
				$action = self::get( array( self::FIELD_ACTION_ID => (int) $row->id ) );
				if ( $action ) {
					// if the action already exists, the number of retries is increased.
					$update = self::update(
						$action->{self::FIELD_ID},
						array( self::FIELD_RETRY => (int) $action->{self::FIELD_RETRY} + 1 )
					);
					if ( $update ) {
						$send_action = false;
					}
				} else {
					// if update doesn't work, create new action.
					self::create(
						array(
							self::FIELD_ORDER_ID       => $order_lengow->order_id,
							self::FIELD_ACTION_TYPE    => $params[ self::ARG_ACTION_TYPE ],
							self::FIELD_ACTION_ID      => $row->id,
							self::FIELD_ORDER_LINE_SKU => isset( $params[ self::ARG_LINE ] )
								? $params[ self::ARG_LINE ]
								: null,
							self::FIELD_PARAMETERS     => wp_json_encode( $params ),
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
	 * @param array        $params all available values
	 * @param Lengow_Order $order_lengow Lengow order instance
	 *
	 * @throws Lengow_Exception
	 */
	public static function send_action( $params, $order_lengow ) {
		if ( ! Lengow_Configuration::debug_mode_is_active() ) {
			try {
				$result = Lengow::sdk()->order()->action()->post( $params + array(
					'account_id' => Lengow_Configuration::get( Lengow_Configuration::ACCOUNT_ID ),
				) );
			} catch ( HttpException|Exception $e ) {
				Lengow_Main::get_log_instance()->log_exception( $e );
				throw new Lengow_Exception(
					Lengow_Main::set_log_message( 'lengow_log.exception.action_not_created_api' ),
					0,
					$e
				);
			}

			self::create(
				array(
					self::FIELD_ORDER_ID       => $order_lengow->order_id,
					self::FIELD_ACTION_TYPE    => $params[ self::ARG_ACTION_TYPE ],
					self::FIELD_ACTION_ID      => $result->id,
					self::FIELD_ORDER_LINE_SKU => isset( $params[ self::ARG_LINE ] )
						? $params[ self::ARG_LINE ]
						: null,
					self::FIELD_PARAMETERS     => wp_json_encode( $params ),
				)
			);
		}
		// create log for call action.
		$param_list = false;
		foreach ( $params as $param => $value ) {
			$param_list .= ! $param_list ? '"' . $param . '": ' . $value : ' -- "' . $param . '": ' . $value;
		}
		Lengow_Main::log(
			Lengow_Log::CODE_ACTION,
			Lengow_Main::set_log_message( 'log.order_action.call_tracking', array( 'parameters' => $param_list ) ),
			false,
			$order_lengow->marketplace_sku
		);
	}

	/**
	 * Get interval time for action synchronisation.
	 *
	 * @return integer
	 */
	public static function get_interval_time() {
		$interval_time               = self::MAX_INTERVAL_TIME;
		$last_action_synchronisation = Lengow_Configuration::get(
			Lengow_Configuration::LAST_UPDATE_ACTION_SYNCHRONIZATION
		);
		if ( $last_action_synchronisation ) {
			$last_interval_time  = time() - (int) $last_action_synchronisation;
			$last_interval_time += self::SECURITY_INTERVAL_TIME;
			$interval_time       = $last_interval_time > $interval_time ? $interval_time : $last_interval_time;
		}

		return $interval_time;
	}

	/**
	 * Check if active actions are finished.
	 *
	 * @param boolean $log_output see log or not
	 *
	 * @return boolean
	 */
	public static function check_finish_action( $log_output = false ) {
		if ( Lengow_Configuration::debug_mode_is_active() ) {
			return false;
		}
		Lengow_Main::log(
			Lengow_Log::CODE_ACTION,
			Lengow_Main::set_log_message( 'log.order_action.check_completed_action' ),
			$log_output
		);
		$active_actions = self::get_all_active_actions();
		if ( ! $active_actions ) {
			return true;
		}
		// get all actions with API (max 3 days).
		$page          = 1;
		$api_actions   = array();
		$interval_time = self::get_interval_time();
		$date_from     = time() - $interval_time;
		$date_to       = time();
		Lengow_Main::log(
			Lengow_Log::CODE_ACTION,
			Lengow_Main::set_log_message(
				'log.import.connector_get_all_action',
				array(
					'date_from' => get_date_from_gmt( date( Lengow_Main::DATE_FULL, $date_from ) ),
					'date_to'   => get_date_from_gmt( date( Lengow_Main::DATE_FULL, $date_to ) ),
				)
			),
			$log_output
		);
		do {

			try {
				$results = Lengow::sdk()->order()->action()->list(
					array(
						Lengow_Import::ARG_UPDATED_FROM => get_date_from_gmt(
							date( Lengow_Main::DATE_FULL, $date_from ),
							Lengow_Main::DATE_ISO_8601
						),
						Lengow_Import::ARG_UPDATED_TO   => get_date_from_gmt(
							date( Lengow_Main::DATE_FULL, $date_to ),
							Lengow_Main::DATE_ISO_8601
						),
						Lengow_Import::ARG_PAGE         => $page,
					)
				);
			} catch ( HttpException|Exception $e ) {
				Lengow_Main::get_log_instance()->log_exception( $e );
				break;
			}

			// construct array actions.
			foreach ( $results->results as $action ) {
				if ( isset( $action->id ) ) {
					$api_actions[ $action->id ] = $action;
				}
			}
			++$page;
		} while ( null !== $results->next );
		if ( empty( $api_actions ) ) {
			return false;
		}
		// check foreach action if it's complete.
		foreach ( $active_actions as $action ) {
			$action_id = (int) $action->{self::FIELD_ACTION_ID};
			if ( ! isset( $api_actions[ $action_id ] ) ) {
				continue;
			}
			$api_action = $api_actions[ $action_id ];
			if ( isset( $api_action->queued, $api_action->processed, $api_action->errors )
				&& false == $api_action->queued
			) {
				// order action is waiting to return from the marketplace.
				if ( false == $api_action->processed && empty( $api_action->errors ) ) {
					continue;
				}
				// finish action in lengow_action table.
				self::finish_action( $action_id );
				$order_lengow_id = Lengow_Order::get_id_from_order_id( $action->{self::FIELD_ORDER_ID} );
				$order_lengow    = new Lengow_Order( $order_lengow_id );
				// finish all order logs send.
				Lengow_Order_Error::finish_order_errors( $order_lengow->id, Lengow_Order_Error::ERROR_TYPE_SEND );
				if ( $order_lengow->is_in_error ) {
					Lengow_Order::update( $order_lengow->id, array( Lengow_Order::FIELD_IS_IN_ERROR => 0 ) );
				}
				if ( ! $order_lengow->is_closed() ) {
					// if action is accepted -> close order and finish all order actions.
					if ( true == $api_action->processed && empty( $api_action->errors ) ) {
						Lengow_Order::update(
							$order_lengow->id,
							array( Lengow_Order::FIELD_ORDER_PROCESS_STATE => Lengow_Order::PROCESS_STATE_FINISH )
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
							Lengow_Log::CODE_ACTION,
							Lengow_Main::set_log_message(
								'log.order_action.call_action_failed',
								array( 'decoded_message' => $api_action->errors )
							),
							$log_output,
							$order_lengow->marketplace_sku
						);
					}
				}
				unset( $order_lengow );
			}
		}
		Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_ACTION_SYNCHRONIZATION, time() );

		return true;
	}

	/**
	 * Remove old actions > 3 days.
	 *
	 * @param boolean $log_output see log or not
	 *
	 * @return boolean
	 */
	public static function check_old_action( $log_output = false ) {
		if ( Lengow_Configuration::debug_mode_is_active() ) {
			return false;
		}
		Lengow_Main::log(
			Lengow_Log::CODE_ACTION,
			Lengow_Main::set_log_message( 'log.order_action.check_old_action' ),
			$log_output
		);
		// get all old order action (+ 3 days).
		$actions = self::get_old_actions();
		if ( $actions ) {
			foreach ( $actions as $action ) {
				// finish action in lengow_action table.
				self::finish_action( $action->{self::FIELD_ID} );
				$order_lengow_id = Lengow_Order::get_id_from_order_id( $action->{self::FIELD_ORDER_ID} );
				$order_lengow    = new Lengow_Order( $order_lengow_id );
				// finish all order logs send.
				Lengow_Order_Error::finish_order_errors( $order_lengow->id, Lengow_Order_Error::ERROR_TYPE_SEND );
				if ( $order_lengow->is_in_error ) {
					Lengow_Order::update( $order_lengow->id, array( Lengow_Order::FIELD_IS_IN_ERROR => 0 ) );
				}
				if ( ! $order_lengow->is_closed() ) {
					// if action is denied -> create order error.
					$error_message = Lengow_Main::set_log_message( 'lengow_log.exception.action_is_too_old' );
					Lengow_Order::add_order_error(
						$order_lengow->id,
						$error_message,
						Lengow_Order_Error::ERROR_TYPE_SEND
					);
					$decodedMessage = Lengow_Main::decode_log_message(
						$error_message,
						Lengow_Translation::DEFAULT_ISO_CODE
					);
					Lengow_Main::log(
						Lengow_Log::CODE_ACTION,
						Lengow_Main::set_log_message(
							'log.order_action.call_action_failed',
							array( 'decoded_message' => $decodedMessage )
						),
						$log_output,
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

		$date    = date( Lengow_Main::DATE_FULL, ( time() - self::MAX_INTERVAL_TIME ) );
		$query   = '
			SELECT * FROM ' . $wpdb->prefix . self::TABLE_ACTION . '
			WHERE created_at <= %s
			AND state = %d
		';
		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $date, self::STATE_NEW ) )
		);

		return $results ?: false;
	}

	/**
	 * Check if actions are not sent.
	 *
	 * @param boolean $log_output see log or not
	 *
	 * @return boolean
	 */
	public static function check_action_not_sent( $log_output = false ) {
		if ( Lengow_Configuration::debug_mode_is_active() ) {
			return false;
		}
		Lengow_Main::log(
			Lengow_Log::CODE_ACTION,
			Lengow_Main::set_log_message( 'log.order_action.check_action_not_sent' ),
			$log_output
		);
		// get unsent orders.
		$unsent_orders = Lengow_Order::get_unsent_orders();
		if ( $unsent_orders ) {
			foreach ( $unsent_orders as $unsent_order ) {
				if ( ! self::get_action_by_order_id( $unsent_order->order_id, true ) ) {
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
