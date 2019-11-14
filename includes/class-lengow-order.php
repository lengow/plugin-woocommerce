<?php
/**
 * All function to synchronised orders
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
 * Lengow_Order Class.
 */
class Lengow_Order {

	/**
	 * @var integer order process state for order not imported.
	 */
	const PROCESS_STATE_NOT_IMPORTED = 0;

	/**
	 * @var integer order process state for order imported.
	 */
	const PROCESS_STATE_IMPORT = 1;

	/**
	 * @var integer order process state for order finished.
	 */
	const PROCESS_STATE_FINISH = 2;

	/**
	 * @var string order state accepted.
	 */
	const STATE_ACCEPTED = 'accepted';

	/**
	 * @var string order state waiting_shipment.
	 */
	const STATE_WAITING_SHIPMENT = 'waiting_shipment';

	/**
	 * @var string order state shipped.
	 */
	const STATE_SHIPPED = 'shipped';

	/**
	 * @var string order state closed.
	 */
	const STATE_CLOSED = 'closed';

	/**
	 * @var string order state refused.
	 */
	const STATE_REFUSED = 'refused';

	/**
	 * @var string order state canceled.
	 */
	const STATE_CANCELED = 'canceled';

	/**
	 * @var string order state refunded.
	 */
	const STATE_REFUNDED = 'refunded';

	/**
	 * @var integer Lengow order record id.
	 */
	public $id;

	/**
	 * @var integer WooCommerce order record id.
	 */
	public $order_id;

	/**
	 * @var integer Lengow feed id.
	 */
	public $feed_id;

	/**
	 * @var integer id of the delivery address.
	 */
	public $delivery_address_id;

	/**
	 * @var string ISO code for country.
	 */
	public $delivery_country_iso;

	/**
	 * @var string Lengow marketplace sku.
	 */
	public $marketplace_sku;

	/**
	 * @var string marketplace's name.
	 */
	public $marketplace_name;

	/**
	 * @var string marketplace's label.
	 */
	public $marketplace_label;

	/**
	 * @var string current Lengow order state.
	 */
	public $order_lengow_state;

	/**
	 * @var integer Lengow process state (0 => error, 1 => imported, 2 => finished).
	 */
	public $order_process_state;

	/**
	 * @var string marketplace order date.
	 */
	public $order_date;

	/**
	 * @var integer number of items.
	 */
	public $order_item;

	/**
	 * @var string order currency.
	 */
	public $currency;

	/**
	 * @var float total paid on marketplace.
	 */
	public $total_paid;

	/**
	 * @var float commission on marketplace.
	 */
	public $commission;

	/**
	 * @var string the name of the customer.
	 */
	public $customer_name;

	/**
	 * @var string email of the customer.
	 */
	public $customer_email;

	/**
	 * @var string carrier from marketplace.
	 */
	public $carrier;

	/**
	 * @var string carrier Method from marketplace.
	 */
	public $carrier_method;

	/**
	 * @var string carrier tracking number.
	 */
	public $carrier_tracking;

	/**
	 * @var string carrier id relay.
	 */
	public $carrier_id_relay;

	/**
	 * @var boolean order shipped by marketplace.
	 */
	public $sent_marketplace;

	/**
	 * @var boolean order is in error.
	 */
	public $is_in_error;

	/**
	 * @var boolean order is reimported (ready to be reimported).
	 */
	public $is_reimported;

	/**
	 * @var string message.
	 */
	public $message;

	/**
	 * @var string created date.
	 */
	public $created_at;

	/**
	 * @var string updated date.
	 */
	public $updated_at;

	/**
	 * @var string extra information (json node form import).
	 */
	public $extra;

	/**
	 * Construct.
	 *
	 * @param integer $id Lengow order id
	 */
	public function __construct( $id ) {
		$row = self::get( array( 'id' => $id ) );
		if ( $row ) {
			$this->id                   = (int) $row->id;
			$this->order_id             = null !== $row->order_id ? (int) $row->order_id : null;
			$this->feed_id              = null !== $row->feed_id ? (int) $row->feed_id : null;
			$this->delivery_address_id  = (int) $row->delivery_address_id;
			$this->delivery_country_iso = $row->delivery_country_iso;
			$this->marketplace_sku      = $row->marketplace_sku;
			$this->marketplace_name     = $row->marketplace_name;
			$this->marketplace_label    = $row->marketplace_label;
			$this->order_lengow_state   = $row->order_lengow_state;
			$this->order_process_state  = (int) $row->order_process_state;
			$this->order_date           = $row->order_date;
			$this->order_item           = (int) $row->order_item;
			$this->currency             = $row->currency;
			$this->total_paid           = null !== $row->total_paid ? (float) $row->total_paid : null;
			$this->commission           = null !== $row->commission ? (float) $row->commission : null;
			$this->customer_name        = $row->customer_name;
			$this->customer_email       = $row->customer_email;
			$this->carrier              = $row->carrier;
			$this->carrier_method       = $row->carrier_method;
			$this->carrier_tracking     = $row->carrier_tracking;
			$this->carrier_id_relay     = $row->carrier_id_relay;
			$this->sent_marketplace     = (bool) $row->sent_marketplace;
			$this->is_in_error          = (bool) $row->is_in_error;
			$this->is_reimported        = (bool) $row->is_reimported;
			$this->message              = $row->message;
			$this->created_at           = $row->created_at;
			$this->updated_at           = $row->updated_at;
			$this->extra                = $row->extra;
		}
	}

	/**
	 * Get Lengow order.
	 *
	 * @param array $where a named array of WHERE clauses
	 * @param boolean $single get a single result or not
	 *
	 * @return false|object[]|object
	 *
	 */
	public static function get( $where = array(), $single = true ) {
		return Lengow_Crud::read( Lengow_Crud::LENGOW_ORDER, $where, $single );
	}

	/**
	 * Create Lengow order.
	 *
	 * @param array $data Lengow order data
	 *
	 * @return boolean
	 *
	 */
	public static function create( $data = array() ) {
		$data['created_at'] = date( 'Y-m-d H:i:s' );

		return Lengow_Crud::create( Lengow_Crud::LENGOW_ORDER, $data );
	}

	/**
	 * Update Lengow order.
	 *
	 * @param integer $order_lengow_id Lengow order id
	 * @param array $data Lengow order data
	 *
	 * @return boolean
	 *
	 */
	public static function update( $order_lengow_id, $data = array() ) {
		$data['updated_at'] = date( 'Y-m-d H:i:s' );

		return Lengow_Crud::update( Lengow_Crud::LENGOW_ORDER, $data, array( 'id' => $order_lengow_id ) );
	}

	/**
	 * Get order process state.
	 *
	 * @param string $state state to be matched
	 *
	 * @return integer
	 */
	public static function get_order_process_state( $state ) {
		switch ( $state ) {
			case self::STATE_ACCEPTED:
			case self::STATE_WAITING_SHIPMENT:
			default:
				return self::PROCESS_STATE_IMPORT;
			case self::STATE_SHIPPED:
			case self::STATE_CLOSED:
			case self::STATE_REFUSED:
			case self::STATE_CANCELED:
			case self::STATE_REFUNDED:
				return self::PROCESS_STATE_FINISH;
		}
	}

	/**
	 * Get WooCommerce state id corresponding to the current order state.
	 *
	 * @param string $order_state_marketplace order state marketplace
	 * @param Lengow_Marketplace $marketplace Lengow marketplace instance
	 * @param boolean $shipped_by_mp order shipped by marketplace
	 *
	 * @return string
	 */
	public static function get_woocommerce_state( $order_state_marketplace, $marketplace, $shipped_by_mp ) {
		if ( $shipped_by_mp ) {
			$order_state = 'shipped_by_mp';
		} elseif ( $marketplace->get_state_lengow( $order_state_marketplace ) === self::STATE_SHIPPED
		           || $marketplace->get_state_lengow( $order_state_marketplace ) === self::STATE_CLOSED
		) {
			$order_state = self::STATE_SHIPPED;
		} else {
			$order_state = self::STATE_WAITING_SHIPMENT;
		}

		return self::get_order_state( $order_state );
	}

	/**
	 * Get the matching WooCommerce order state to the one given.
	 *
	 * @param string $state state to be matched
	 *
	 * @return string
	 */
	public static function get_order_state( $state ) {
		switch ( $state ) {
			case self::STATE_ACCEPTED:
			case self::STATE_WAITING_SHIPMENT:
			default:
				$order_state = Lengow_Configuration::get( 'lengow_id_waiting_shipment' );
				break;
			case self::STATE_SHIPPED:
			case self::STATE_CLOSED:
				$order_state = Lengow_Configuration::get( 'lengow_id_shipped' );
				break;
			case self::STATE_REFUSED:
			case self::STATE_CANCELED:
				$order_state = Lengow_Configuration::get( 'lengow_id_cancel' );
				break;
			case 'shipped_by_mp':
				$order_state = Lengow_Configuration::get( 'lengow_id_shipped_by_mp' );
				break;
		}

		return $order_state;
	}

	/**
	 * Get compatibility for WooCommerce order id.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 *
	 * @return integer
	 */
	public static function get_order_id( $order ) {
		return Lengow_Main::compare_version( '3.0' ) ? $order->get_id() : (int) $order->id;
	}

	/**
	 * Get compatibility for WooCommerce order status.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 *
	 * @return string
	 */
	public static function get_order_status( $order ) {
		$status = Lengow_Main::compare_version( '3.0' ) ? $order->get_status() : $order->status;
		$status = Lengow_Main::compare_version( '2.2' ) ? 'wc-' . $status : $status;

		return $status;
	}

	/**
	 * Get order id from lengow orders table.
	 *
	 * @param string $marketplace_sku Lengow marketplace sku
	 * @param string $marketplace_name marketplace name
	 * @param integer $delivery_address_id delivery address id
	 * @param string $marketplace_name_legacy old marketplace name for v2 compatibility
	 *
	 * @return integer|false
	 */
	public static function get_order_id_from_lengow_orders(
		$marketplace_sku,
		$marketplace_name,
		$delivery_address_id,
		$marketplace_name_legacy
	) {
		global $wpdb;

		// v2 compatibility.
		$marketplace_name_legacy = null === $marketplace_name_legacy
			? $marketplace_name
			: strtolower( $marketplace_name_legacy );

		$query = '
			SELECT order_id, delivery_address_id, feed_id
			FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE marketplace_sku = %s
			AND marketplace_name IN (%s, %s)
		';

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace_name, $marketplace_name_legacy ) )
		);

		if ( empty( $results ) ) {
			return false;
		}
		foreach ( $results as $result ) {
			if ( null === $result->delivery_address_id && null !== $result->feed_id ) {
				return (int) $result->order_id;
			} elseif ( (int) $result->delivery_address_id === $delivery_address_id ) {
				return (int) $result->order_id;
			}
		}

		return false;
	}

	/**
	 * Get order id from lengow orders table.
	 *
	 * @param string $marketplace_sku Lengow marketplace sku
	 * @param string $marketplace_name marketplace name
	 *
	 * @return array
	 */
	public static function get_all_order_id_from_lengow_orders( $marketplace_sku, $marketplace_name ) {
		global $wpdb;

		$query   = '
			SELECT order_id FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE marketplace_sku = %s
			AND marketplace_name = %s
		';
		$results = $wpdb->get_results(
			$wpdb->prepare( $query, array( $marketplace_sku, $marketplace_name ) )
		);

		return $results ? $results : array();
	}

	/**
	 * Get ID record from lengow orders table.
	 *
	 * @param string $marketplace_sku Lengow id
	 * @param integer $delivery_address_id delivery address id
	 *
	 * @return integer|false
	 */
	public static function get_id_from_lengow_orders( $marketplace_sku, $delivery_address_id ) {
		global $wpdb;

		$query           = '
			SELECT id FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE marketplace_sku = %s
			AND delivery_address_id = %d
		';
		$order_lengow_id = $wpdb->get_var(
			$wpdb->prepare( $query, array( $marketplace_sku, $delivery_address_id ) )
		);
		if ( $order_lengow_id ) {
			return (int) $order_lengow_id;
		}

		return false;
	}

	/**
	 * Get id from WooCommerce order id.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return integer|false
	 */
	public static function get_id_from_order_id( $order_id ) {
		global $wpdb;

		$query           = '
			SELECT id FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE order_id = %d
		';
		$order_lengow_id = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_id ) )
		);
		if ( $order_lengow_id ) {
			return (int) $order_lengow_id;
		}

		return false;
	}

	/**
	 * Get id from Lengow delivery address id.
	 *
	 * @param integer $order_id WooCommerce order id
	 * @param integer $delivery_address_id Lengow delivery address id
	 *
	 * @return integer|false
	 */
	public static function get_id_from_lengow_delivery_address( $order_id, $delivery_address_id ) {
		global $wpdb;

		$query           = '
			SELECT id FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE order_id = %d
			AND delivery_address_id = %d
		';
		$order_lengow_id = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_id, $delivery_address_id ) )
		);
		if ( $order_lengow_id ) {
			return (int) $order_lengow_id;
		}

		return false;
	}

	/**
	 * Get marketplace label by WooCommerce order id.
	 *
	 * @param integer $order_id WooCommerce order id
	 *
	 * @return string|false
	 */
	public static function get_marketplace_label_by_order_id( $order_id ) {
		global $wpdb;
		if ( null === $order_id ) {
			return false;
		}
		$query            = '
			SELECT marketplace_label FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE order_id = %d
		';
		$marketplace_name = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_id ) )
		);

		return $marketplace_name;
	}

	/**
	 * Get total order by statuses.
	 *
	 * @param string $order_status Lengow order state
	 *
	 * @return integer
	 */
	public static function get_total_order_by_status( $order_status ) {
		global $wpdb;
		$query = '
			SELECT COUNT(*) as total FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . '
			WHERE order_lengow_state = %s
		';
		$total = $wpdb->get_var(
			$wpdb->prepare( $query, array( $order_status ) )
		);

		return (int) $total;
	}

	/**
	 * Get total orders in error.
	 **
	 * @return integer
	 */
	public static function get_total_order_in_error() {
		$result = self::get( array( 'is_in_error' => 1 ), false );

		return count( $result );
	}

	/**
	 * Get all unset orders.
	 *
	 * @return array|false
	 */
	public static function get_unsent_orders() {
		global $wpdb;

		if ( Lengow_Main::compare_version( '2.2' ) ) {
			$query = '
				SELECT lo.id as order_lengow_id, p.ID as order_id, p.post_status as order_status
				FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . ' lo
				LEFT JOIN ' . $wpdb->posts . ' p ON p.ID = lo.order_id
	            WHERE lo.order_process_state = %d
	            AND lo.is_in_error = %d
	            AND p.post_status IN (%s,%s)
	            AND p.post_modified >= %s
	        ';
		} else {
			$query = '
				SELECT lo.id as order_lengow_id, p.ID as order_id, t.slug as order_status
				FROM ' . $wpdb->prefix . Lengow_Crud::LENGOW_ORDER . ' lo
				LEFT JOIN ' . $wpdb->posts . ' p ON p.ID = lo.order_id
				LEFT JOIN ' . $wpdb->term_relationships . ' tr ON tr.object_id = p.ID
				LEFT JOIN ' . $wpdb->term_taxonomy . ' tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				LEFT JOIN ' . $wpdb->terms . ' t ON t.term_id = tt.term_id
	            WHERE lo.order_process_state = %d
	            AND lo.is_in_error = %d
	            AND t.slug IN (%s,%s)
	            AND p.post_modified >= %s
	        ';
		}
		$results = $wpdb->get_results(
			$wpdb->prepare(
				$query,
				array(
					self::PROCESS_STATE_IMPORT,
					0,
					self::get_order_state( self::STATE_SHIPPED ),
					self::get_order_state( self::STATE_CANCELED ),
					date( 'Y-m-d H:i:s', strtotime( '-5 days', time() ) ),
				)
			)
		);

		return $results ? $results : false;
	}

	/**
	 * Update order state to marketplace state.
	 *
	 * @param WC_Order $order WooCommerce order instance
	 * @param Lengow_Order $order_lengow Lengow order instance
	 * @param string $order_lengow_state Lengow order status
	 * @param mixed $package_data package data
	 *
	 * @return string|false
	 */
	public static function update_state( $order, $order_lengow, $order_lengow_state, $package_data ) {
		// finish actions if lengow order is shipped, closed, cancel or refunded.
		$order_process_state = self::get_order_process_state( $order_lengow_state );
		// update Lengow order if necessary.
		$params = [];
		if ( self::PROCESS_STATE_FINISH === $order_process_state ) {
			Lengow_Action::finish_all_actions( Lengow_Order::get_order_id( $order ) );
			Lengow_Order_Error::finish_order_errors( $order_lengow->id, Lengow_Order_Error::ERROR_TYPE_SEND );
			if ( $order_process_state !== $order_lengow->order_process_state ) {
				$params['order_process_state'] = $order_process_state;
			}
			if ( $order_lengow->is_in_error ) {
				$params['is_in_error'] = 0;
			}
		}
		if ( $order_lengow_state !== $order_lengow->order_lengow_state ) {
			$params['order_lengow_state'] = $order_lengow_state;
			if ( ! empty( $package_data->delivery->trackings ) ) {
				$tracking                   = $package_data->delivery->trackings[0];
				$params['carrier']          = null !== $tracking->carrier ? (string) $tracking->carrier : null;
				$params['carrier_tracking'] = null !== $tracking->number ? (string) $tracking->number : null;
				$params['carrier_id_relay'] = null !== $tracking->relay->id ? (string) $tracking->relay->id : null;
			}
		}
		if ( ! empty( $params ) ) {
			self::update( $order_lengow->id, $params );
		}
		// update WooCommerce order's status only if in accepted, waiting_shipment, shipped, closed or cancel.
		$order_status           = self::get_order_status( $order );
		$waiting_shipment_state = self::get_order_state( self::STATE_WAITING_SHIPMENT );
		$shipped_state          = self::get_order_state( self::STATE_SHIPPED );
		$canceled_state         = self::get_order_state( self::STATE_CANCELED );
		if ( self::get_order_state( $order_lengow_state ) !== $order_status ) {
			if ( $order_status === $waiting_shipment_state
			     && in_array( $order_lengow_state, array( self::STATE_SHIPPED, self::STATE_CLOSED ) )
			) {
				$order->update_status( $shipped_state );

				return self::STATE_SHIPPED;
			} else {
				if ( ( $order_status === $waiting_shipment_state || $order_status === $shipped_state )
				     && in_array( $order_lengow_state, array( self::STATE_CANCELED, self::STATE_REFUSED ) )
				) {
					$order->update_status( $canceled_state );

					return self::STATE_CANCELED;
				}
			}
		}

		return false;
	}

	/**
	 * Re Import Order.
	 *
	 * @param integer $order_lengow_id Lengow order id
	 *
	 * @return array|false
	 */
	public static function re_import_order( $order_lengow_id ) {
		$order_lengow = self::get( array( 'id' => $order_lengow_id ) );
		if ( $order_lengow ) {
			$import  = new Lengow_Import(
				array(
					'order_lengow_id'     => $order_lengow->id,
					'marketplace_sku'     => $order_lengow->marketplace_sku,
					'marketplace_name'    => $order_lengow->marketplace_name,
					'delivery_address_id' => $order_lengow->delivery_address_id,
					'log_output'          => false,
				)
			);
			$results = $import->exec();

			return $results;
		}

		return false;
	}

	/**
	 * Resend an action.
	 *
	 * @param $order_lengow_id
	 *
	 * @return bool
	 */
	public static function re_send_order( $order_lengow_id ) {
		$order_lengow = New Lengow_Order( $order_lengow_id );
		if ( $order_lengow->order_id ) {
			$order        = new WC_Order( $order_lengow->order_id );
			$order_status = self::get_order_status( $order );
			// sending an API call for sending or canceling an order.
			if ( self::get_order_state( Lengow_Order::STATE_SHIPPED ) === $order_status ) {
				return $order_lengow->call_action( Lengow_Action::TYPE_SHIP );
			} else {
				return $order_lengow->call_action( Lengow_Action::TYPE_CANCEL );
			}
		}

		return false;
	}

	/**
	 * Create an error and update the order in error.
	 *
	 * @param integer $order_lengow_id Lengow order id
	 * @param string $message error message
	 * @param string $type order error type (import or send)
	 *
	 * @return array|false
	 */
	public static function add_order_error( $order_lengow_id, $message, $type = null ) {
		$error_created = Lengow_Order_Error::create(
			array(
				'order_lengow_id' => $order_lengow_id,
				'message'         => $message,
				'type'            => null === $type ? Lengow_Order_Error::ERROR_TYPE_IMPORT : $type,
			)
		);
		$order_updated = Lengow_Order::update( $order_lengow_id, array( 'is_in_error' => 1 ) );

		return ( $error_created && $order_updated ) ? true : false;
	}

	/**
	 * Synchronize order with Lengow API.
	 *
	 * @param Lengow_Connector|null $connector Lengow connector instance
	 *
	 * @return boolean
	 */
	public function synchronize_order( $connector = null ) {
		list( $account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
		if ( null === $connector ) {
			if ( Lengow_Connector::is_valid_auth() ) {
				$connector = new Lengow_Connector( $access_token, $secret_token );
			} else {
				return false;
			}
		}
		$results = self::get_all_order_id_from_lengow_orders( $this->marketplace_sku, $this->marketplace_name );
		if ( $results ) {
			$woocommerce_order_ids = array();
			foreach ( $results as $result ) {
				$woocommerce_order_ids[] = $result->order_id;
			}
			// compatibility V2.
			if ( ! Lengow_Marketplace::marketplace_exist( $this->marketplace_name ) && null !== $this->feed_id ) {
				$this->check_and_change_marketplace_name( $connector );
			}
			try {
				$return = $connector->patch(
					'/v3.0/orders/moi/',
					array(
						'account_id'           => $account_id,
						'marketplace_order_id' => $this->marketplace_sku,
						'marketplace'          => $this->marketplace_name,
						'merchant_order_id'    => $woocommerce_order_ids,
					)
				);
			} catch ( Exception $e ) {
				return false;
			}
			if ( null === $return
			     || ( isset( $return['detail'] ) && 'Pas trouvé.' === $return['detail'] )
			     || isset( $return['error'] )
			) {
				return false;
			} else {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check and change the name of the marketplace for v3 compatibility.
	 *
	 * @param Lengow_Connector|null $connector Lengow connector instance
	 *
	 * @return boolean
	 */
	public function check_and_change_marketplace_name( $connector = null ) {
		list( $account_id, $access_token, $secret_token ) = Lengow_Configuration::get_access_id();
		if ( null === $connector ) {
			if ( Lengow_Connector::is_valid_auth() ) {
				$connector = new Lengow_Connector( $access_token, $secret_token );
			} else {
				return false;
			}
		}
		try {
			$return = $connector->get(
				'/v3.0/orders',
				array(
					'marketplace_order_id' => $this->marketplace_sku,
					'account_id'           => $account_id,
				),
				'stream'
			);
		} catch ( Exception $e ) {
			return false;
		}
		if ( null === $return ) {
			return false;
		}
		$results = json_decode( $return );
		if ( isset( $results->error ) ) {
			return false;
		}
		foreach ( $results->results as $order ) {
			$new_marketplace_name = (string) $order->marketplace;
			if ( $new_marketplace_name !== $this->marketplace_name ) {
				self::update( $this->id, array( 'marketplace_name' => $new_marketplace_name ) );
				$this->marketplace_name = $new_marketplace_name;
			}
		}

		return true;
	}

	/**
	 * Check if order is closed.
	 *
	 * @return boolean
	 */
	public function is_closed() {
		if ( self::PROCESS_STATE_FINISH === $this->order_process_state ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if order has an action in progress.
	 *
	 * @return boolean
	 */
	public function has_an_action_in_progress() {
		$actions = Lengow_Action::get_active_action_by_order_id( $this->order_id );

		return ! $actions ? false : true;
	}

	/**
	 * Check if order is in good status for resend and has no action in progress.
	 *
	 * @return bool
	 */
	public function can_resend_action() {
		$order = new WC_Order( $this->order_id );
		if ( ! $this->is_closed() && ! $this->has_an_action_in_progress() ) {
			$status = self::get_order_status( $order );
			if ( Lengow_Order::get_order_state( Lengow_Order::STATE_CANCELED ) === $status ||
			     Lengow_Order::get_order_state( Lengow_Order::STATE_SHIPPED ) === $status ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Send an action for a specific order.
	 *
	 * @param string $action Lengow Actions type (ship or cancel)
	 *
	 * @return boolean
	 */
	public function call_action( $action ) {
		// do nothing if the order is closed.
		if ( $this->is_closed() ) {
			return false;
		}
		$success = true;
		Lengow_Main::log(
			Lengow_Log::CODE_ACTION,
			Lengow_Main::set_log_message(
				'log.order_action.try_to_send_action',
				array(
					'action'   => $action,
					'order_id' => $this->order_id,
				)
			),
			false,
			$this->marketplace_sku
		);
		// finish all order logs send.
		Lengow_Order_Error::finish_order_errors( $this->id, Lengow_Order_Error::ERROR_TYPE_SEND );
		try {
			// compatibility V2.
			if ( ! Lengow_Marketplace::marketplace_exist( $this->marketplace_name ) && null !== $this->feed_id ) {
				$this->check_and_change_marketplace_name();
			}
			$marketplace = Lengow_Main::get_marketplace_singleton( $this->marketplace_name );
			if ( $marketplace->contain_order_line( $action ) ) {
				$order_lines = false;
				$order_lines = Lengow_Order_Line::get_all_order_line_id_by_order_id( $this->order_id, ARRAY_A );
				// compatibility V2 and security.
				if ( ! $order_lines ) {
					$order_lines = $this->get_order_line_by_api();
				}
				if ( ! $order_lines ) {
					throw new Lengow_Exception(
						Lengow_Main::set_log_message( 'lengow_log.exception.order_line_required' )
					);
				}
				$results = array();
				foreach ( $order_lines as $order_line ) {
					$results[] = true;
					$results[] = $marketplace->call_action( $action, $this, $order_line['order_line_id'] );
				}
				$success = ! in_array( false, $results );
			} else {
				$success = true;
				$success = $marketplace->call_action( $action, $this );
			}
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			Lengow_Order::add_order_error( $this->id, $error_message, Lengow_Order_Error::ERROR_TYPE_SEND );
			$decoded_message = Lengow_Main::decode_log_message( $error_message, Lengow_Translation::DEFAULT_ISO_CODE );
			Lengow_Main::log(
				Lengow_Log::CODE_ACTION,
				Lengow_Main::set_log_message(
					'log.order_action.call_action_failed',
					array( 'decoded_message' => $decoded_message )
				),
				false,
				$this->marketplace_sku
			);
			$success = false;
		}

		if ( $success ) {
			$message = Lengow_Main::set_log_message(
				'log.order_action.action_send',
				array(
					'action'   => $action,
					'order_id' => $this->order_id,
				)
			);
		} else {
			$message = Lengow_Main::set_log_message(
				'log.order_action.action_not_send',
				array(
					'action'   => $action,
					'order_id' => $this->order_id,
				)
			);
		}
		Lengow_Main::log( Lengow_Log::CODE_ACTION, $message, false, $this->marketplace_sku );

		return $success;
	}

	/**
	 * Get order line by API.
	 *
	 * @return array|false
	 */
	public function get_order_line_by_api() {
		$order_lines = array();
		$results     = Lengow_Connector::query_api(
			'get',
			'/v3.0/orders',
			array(
				'marketplace_order_id' => $this->marketplace_sku,
				'marketplace'          => $this->marketplace_name,
			)
		);
		if ( ! isset( $results->count ) || ( isset( $results->count ) && 0 === (int) $results->count ) ) {
			return false;
		}
		$order_data = $results->results[0];
		foreach ( $order_data->packages as $package ) {
			$product_lines = array();
			foreach ( $package->cart as $product ) {
				$product_lines[] = array( 'order_line_id' => (string) $product->marketplace_order_line_id );
			}
			if ( 0 === $this->delivery_address_id ) {
				return ! empty( $product_lines ) ? $product_lines : false;
			} else {
				$order_lines[ (int) $package->delivery->id ] = $product_lines;
			}
		}
		$return = $order_lines[ $this->delivery_address_id ];

		return ! empty( $return ) ? $return : false;
	}

}
