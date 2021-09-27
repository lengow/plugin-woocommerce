<?php
/**
 * Admin order page
 *
 * Copyright 2017 Lengow SAS
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
 * @copyright   2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Lengow_Admin_Orders Class.
 */
class Lengow_Admin_Orders extends WP_List_Table {

	/**
	 * @var array with all countries.
	 */
	private $countries;

	/**
	 * @var array all order data.
	 */
	private $data;

	/**
	 * @var Lengow_Translation Lengow translation instance.
	 */
	private $locale;

	/**
	 * Display admin orders page.
	 */
	public static function html_display() {
		$lengow_admin_orders = new Lengow_Admin_Orders();
		$warning_message     = $lengow_admin_orders->assign_warning_messages();
		$order_collection    = $lengow_admin_orders->assign_last_importation_infos();
		$locale              = new Lengow_Translation();
		$report_emails       = implode( ', ', Lengow_Configuration::get_report_email_address() );
		include_once 'views/html-admin-header-order.php';
		include_once 'views/orders/html-admin-orders.php';
	}

	/**
	 * Process Post Parameters.
	 */
	public static function post_process() {
		$action = isset( $_POST['do_action'] ) ? $_POST['do_action'] : false;
		if ( $action ) {
			switch ( $action ) {
				case 'import_all':
					self::_do_action( 'import_all' );
					break;
				case 're_import':
					self::_do_action( 're_import' );
					break;
				case 're_send':
					self::_do_action( 're_send' );
					break;
				case 'reimport_mass_action':
					self::_do_action( 'reimport_mass_action' );
					break;
				case 'resend_mass_action':
					self::_do_action( 'resend_mass_action' );
					break;
			}
			exit();
		}
	}

	/**
	 *  Manage actions for post_process.
	 *
	 * @param $action
	 */
	private static function _do_action( $action ) {
		$lengow_admin_orders = new Lengow_Admin_Orders();
		$locale              = new Lengow_Translation();
		$data                = array();
		$message             = array();
		if ( 'import_all' === $action ) {
			$import  = new Lengow_Import(
				array( Lengow_Import::PARAM_LOG_OUTPUT => false )
			);
			$return  = $import->exec();
			$message = $lengow_admin_orders->load_message( $return );
		} elseif ( 're_import' === $action ) {
			$order_lengow_id = isset( $_POST['order_id'] ) ? $_POST['order_id'] : null;
			if ( null !== $order_lengow_id ) {
				Lengow_Order::re_import_order( $order_lengow_id );
			}
		} elseif ( 're_send' === $action ) {
			$order_lengow_id = isset( $_POST['order_id'] ) ? $_POST['order_id'] : null;
			if ( null !== $order_lengow_id ) {
				Lengow_Order::re_send_order( $order_lengow_id );
			}
		} elseif ( 'reimport_mass_action' === $action ) {
			$orders_lengow_ids = isset( $_POST['orders'] ) ? $_POST['orders'] : null;
			$total_reimport    = 0;
			if ( $orders_lengow_ids ) {
				foreach ( $orders_lengow_ids as $order_lengow_id ) {
					$result = Lengow_Order::re_import_order( $order_lengow_id );
					if ( $result && ! empty( $result[ Lengow_Import::ORDERS_CREATED ] ) ) {
						$total_reimport ++;
					}
				}
				$message[] = $locale->t(
					'order.screen.mass_action_reimport_success',
					array(
						'order_reimported' => $total_reimport,
						'order_total'      => count( $orders_lengow_ids ),
					)
				);
			}
		} elseif ( 'resend_mass_action' === $action ) {
			$orders_lengow_ids = isset( $_POST['orders'] ) ? $_POST['orders'] : null;
			$total_resend      = 0;
			if ( $orders_lengow_ids ) {
				foreach ( $orders_lengow_ids as $order_lengow_id ) {
					$result = Lengow_Order::re_send_order( $order_lengow_id );
					if ( $result ) {
						$total_resend ++;
					}
				}
				$message[] = $locale->t(
					'order.screen.mass_action_resend_success',
					array(
						'order_resent' => $total_resend,
						'order_total'  => count( $orders_lengow_ids ),
					)
				);
			}
		}
		$order_collection         = $lengow_admin_orders->assign_last_importation_infos();
		$data['order_with_error'] = $locale->t(
			'order.screen.order_with_error',
			array( 'nb_order' => Lengow_Order::count_order_with_error() )
		);
		$data['order_to_be_sent'] = $locale->t(
			'order.screen.order_to_be_sent',
			array( 'nb_order' => Lengow_Order::count_order_to_be_sent() )
		);
		$data['message']          = join( '<br/>', $message );
		$data['import_orders']    = $locale->t( 'order.screen.button_update_orders' );
		$data['last_importation'] = $order_collection['last_import_date'];
		echo json_encode( $data );
	}

	/**
	 * Generate message array (new, update and errors).
	 *
	 * @param array $return import data
	 *
	 * @return array
	 */
	public function load_message( $return ) {
		$locale   = new Lengow_Translation();
		$messages = array();
		// if global error or shop error return this.
		if ( isset( $return[ Lengow_Import::ERRORS ] ) && count($return[ Lengow_Import::ERRORS ]) > 0 ) {
			foreach ($return[Lengow_Import::ERRORS] as $message) {
				$messages[] = Lengow_Main::decode_log_message( $message );
			}

			return $messages;
		}
		if ( isset( $return[ Lengow_Import::NUMBER_ORDERS_CREATED ] )
		     && $return[ Lengow_Import::NUMBER_ORDERS_CREATED ] > 0
		) {
			$messages[] = $locale->t(
				'lengow_log.error.nb_order_imported',
				array( 'nb_order' => (int) $return[ Lengow_Import::NUMBER_ORDERS_CREATED ] )
			);
		}
		if ( isset( $return[ Lengow_Import::NUMBER_ORDERS_UPDATED ] )
		     && $return[ Lengow_Import::NUMBER_ORDERS_UPDATED ] > 0
		) {
			$messages[] = $locale->t(
				'lengow_log.error.nb_order_updated',
				array( 'nb_order' => (int) $return[ Lengow_Import::NUMBER_ORDERS_UPDATED ] )
			);
		}
		if ( isset( $return[ Lengow_Import::NUMBER_ORDERS_FAILED ] )
		     && $return[ Lengow_Import::NUMBER_ORDERS_FAILED ] > 0
		) {
			$messages[] = $locale->t(
				'lengow_log.error.nb_order_with_error',
				array( 'nb_order' => (int) $return[ Lengow_Import::NUMBER_ORDERS_FAILED ] )
			);
		}
		if ( empty( $messages ) ) {
			$messages[] = $locale->t( 'lengow_log.error.no_notification' );
		}

		return $messages;
	}

	/**
	 * Get warning messages.
	 */
	public function assign_warning_messages() {
		$locale           = new Lengow_Translation();
		$warning_messages = array();
		if ( Lengow_Configuration::debug_mode_is_active() ) {
			$warning_messages[] = $locale->t(
				'order.screen.debug_warning_message',
				array( 'url' => admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ) )
			);
		}
		if ( ! empty( $warning_messages ) ) {
			$warning_message = join( '<br/>', $warning_messages );
		} else {
			$warning_message = false;
		}

		return $warning_message;
	}

	/**
	 * Get all last importation information.
	 */
	public function assign_last_importation_infos() {
		$last_import = Lengow_Main::get_last_import();

		return array(
			'last_import_date' => $last_import['timestamp'] !== 'none'
				? Lengow_Main::get_date_in_correct_format( $last_import['timestamp'] )
				: '',
			'last_import_type' => $last_import['type'],
		);
	}

	/**
	 * Display lengow order table.
	 */
	public static function render_lengow_list() {
		// need to instantiate a class because this method must be static.
		$countries_instance             = new WC_Countries;
		$lengow_admin_orders            = new Lengow_Admin_Orders();
		$lengow_admin_orders->locale    = new Lengow_Translation();
		$lengow_admin_orders->countries = $countries_instance->countries;
		$lengow_admin_orders->prepare_items();
		echo '<form id="post-filter" method="post">';
		// the hidden element is needed to load the right page.
		echo '<input type="hidden" name="page" value="lengow_list" />';
		$lengow_admin_orders->search_box( $lengow_admin_orders->locale->t( 'order.table.button_search' ), 'search_id' );
		$lengow_admin_orders->display();
		echo '</form>';

	}

	/**
	 * Display lengow order data.
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		// $sortable defines if the table can be sorted by this column.
		$sortable = $this->get_sortable_columns();
		// $hidden defines the hidden columns.
		$hidden                = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->data            = $this->_get_orders();
		usort( $this->data, array( &$this, '_usort_reorder' ) );
		// pagination.
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = count( $this->data );
		$data         = array_slice( $this->data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = $data;
	}

	/**
	 * Add extra markup in the toolbars before or after the list.
	 *
	 * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			$content = '<div id="lgw-order-filter">';
			$filters = array(
				array(
					'name'        => 'order_action',
					'placeholder' => $this->locale->t( 'order.screen.placeholder_action' ),
					'options'     => array(
						'success' => $this->locale->t( 'order.screen.action_success' ),
						'error'   => $this->locale->t( 'order.screen.action_error' ),
					),
				),
				array(
					'name'        => 'order_status',
					'placeholder' => $this->locale->t( 'order.screen.placeholder_order_status' ),
					'options'     => array(
						Lengow_Order::STATE_ACCEPTED         => $this->locale->t( 'order.screen.status_accepted' ),
						Lengow_Order::STATE_WAITING_SHIPMENT => $this->locale->t(
							'order.screen.status_waiting_shipment'
						),
						Lengow_Order::STATE_SHIPPED          => $this->locale->t( 'order.screen.status_shipped' ),
						Lengow_Order::STATE_REFUNDED         => $this->locale->t( 'order.screen.status_refunded' ),
						Lengow_Order::STATE_CLOSED           => $this->locale->t( 'order.screen.status_closed' ),
						Lengow_Order::STATE_CANCELED         => $this->locale->t( 'order.screen.status_canceled' ),
					),
				),
				array(
					'name'        => 'order_type',
					'placeholder' => $this->locale->t( 'order.screen.placeholder_order_types' ),
					'options'     => array(
						Lengow_Order::TYPE_EXPRESS                  => $this->locale->t( 'order.screen.type_express' ),
						Lengow_Order::TYPE_DELIVERED_BY_MARKETPLACE => $this->locale->t(
							'order.screen.type_delivered_by_marketplace'
						),
						Lengow_Order::TYPE_BUSINESS                 => $this->locale->t( 'order.screen.type_business' ),
					),
				),
				array(
					'name'        => 'order_marketplace',
					'placeholder' => $this->locale->t( 'order.screen.placeholder_marketplace' ),
					'options'     => Lengow_Order::get_marketplace_list(),
				),
			);
			foreach ( $filters as $filter ) {
				$option_selected = ( isset( $_REQUEST[ $filter['name'] ] ) && ! empty( $_REQUEST[ $filter['name'] ] ) )
					? $_REQUEST[ $filter['name'] ]
					: '';
				$content         .= '<select name="' . $filter['name'] . '">';
				$content         .= '<option value="" >' . $filter['placeholder'] . '</option>';
				foreach ( $filter['options'] as $option_id => $option_label ) {
					$selected = $option_selected === $option_id ? 'selected' : '';
					$content  .= '<option value="' . $option_id . '" ' . $selected . ' >' . $option_label . '</option>';
				}
				$content .= '</select>';
			}
			$order_from = ( isset( $_REQUEST['order_from'] ) && ! empty( $_REQUEST['order_from'] ) )
				? $_REQUEST['order_from']
				: '';
			$order_to   = ( isset( $_REQUEST['order_to'] ) && ! empty( $_REQUEST['order_to'] ) )
				? $_REQUEST['order_to']
				: '';
			$content    .= '
				<div class="lengow_datepicker_box">
					<input type="search"
						   name="order_from"
						   placeholder="' . $this->locale->t( 'order.screen.placeholder_from' ) . '"
						   value="' . $order_from . '"
						   class="lengow_datepicker" />
					<input type="search"
						   name="order_to"
						   placeholder="' . $this->locale->t( 'order.screen.placeholder_to' ) . '"
						   value="' . $order_to . '"
						   class="lengow_datepicker" />
				</div>';
			$content    .= '
				<input type="submit" 
					   name="filter_action" 
					   id="post-query-submit" 
					   class="button" 
					   value="' . $this->locale->t( 'order.screen.filter_action' ) . '" />';
			$content    .= '</div>';
			echo $content;
		}
	}

	/**
	 * Get all columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		// columns label on the top and bottom of the table.
		return array(
			'cb'              => '<input type="checkbox" />',
			'action'          => $this->locale->t( 'order.table.action' ),
			'status'          => $this->locale->t( 'order.table.lengow_status' ),
			'order_types'     => $this->locale->t( 'order.table.order_types' ),
			'marketplace_sku' => $this->locale->t( 'order.table.marketplace_sku' ),
			'marketplace'     => $this->locale->t( 'order.table.marketplace' ),
			'reference'       => $this->locale->t( 'order.table.reference' ),
			'customer'        => $this->locale->t( 'order.table.customer' ),
			'date'            => $this->locale->t( 'order.table.date' ),
			'country'         => $this->locale->t( 'order.table.country' ),
			'total'           => $this->locale->t( 'order.table.total' ),
		);
	}

	/**
	 * Define all columns for specific method.
	 *
	 * @param array $item order data
	 * @param string $column_name column name
	 *
	 * @return array
	 */
	public function column_default( $item, $column_name ) {
		// to avoid the need to create a method for each column there is column_default.
		// that will process any column for which no special method is defined.
		switch ( $column_name ) {
			case 'action':
			case 'status':
			case 'order_types':
			case 'marketplace_sku':
			case 'marketplace':
			case 'reference':
			case 'customer':
			case 'date':
			case 'country':
			case 'total':
				return $item[ $column_name ];
			default:
				break;
		}
	}

	/**
	 * Get all sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			// the second parameter in the value array takes care of a possible pre-ordered column.
			// if the value is true the column is assumed to be ordered ascending.
			// if the value is false the column is assumed descending or unordered.
			'action'          => array( 'action', false ),
			'status'          => array( 'status', false ),
			'order_types'     => array( 'order_types', false ),
			'marketplace_sku' => array( 'marketplace_sku', false ),
			'marketplace'     => array( 'marketplace', false ),
			'reference'       => array( 'reference', false ),
			'customer'        => array( 'customer', false ),
			'date'            => array( 'date', true ),
			'country'         => array( 'country', false ),
			'total'           => array( 'total', false ),
		);
	}

	/**
	 * Return checkbox with order_id.
	 *
	 * @param object $order
	 *
	 * @return string|void
	 */
	public function column_cb( $order ) {
		if ( '' !== $order['action'] ) {
			return sprintf(
				'<input type="checkbox" id="js-lengow_order_checkbox"
				name="order[' . $order['id'] . ']" value="%s" class="js-lengow_selection_order"/>',
				$order[ Lengow_Order::FIELD_ID ]
			);
		}
	}

	/**
	 * Get all orders meta.
	 *
	 * @return array
	 */
	private function _get_orders() {
		$results = array();
		$keys    = array(
			'id',
			'action',
			'status',
			'order_types',
			'marketplace_sku',
			'marketplace',
			'reference',
			'customer',
			'date',
			'country',
			'total',
		);
		$orders  = $this->_request_get_orders( $_REQUEST );
		// get order data.
		foreach ( $keys as $key ) {
			foreach ( $orders as $order ) {
				switch ( $key ) {
					case 'id':
						$orders_data = $order->id;
						break;
					case 'action' :
						$orders_data = $this->_display_actions( $order );
						break;
					case 'status' :
						$orders_data = $this->_display_status( $order );
						break;
					case 'order_types' :
						$orders_data = $this->_display_order_types( $order );
						break;
					case 'marketplace_sku' :
						$orders_data = $order->marketplace_sku;
						break;
					case 'marketplace' :
						$orders_data = $order->marketplace_label;
						break;
					case 'reference' :
						$orders_data = $this->_display_reference( $order );
						break;
					case 'customer' :
						$orders_data = $order->customer_name;
						break;
					case 'date' :
						$orders_data = get_date_from_gmt( $order->order_date );
						break;
					case 'country' :
						$orders_data = $this->_display_country( $order );
						break;
					case 'total' :
						$orders_data = $this->_display_total( $order );
						break;
					default :
						$orders_data = null;
						break;
				}
				$results[ $order->id ][ $key ] = $orders_data;
			}
		}

		return $results;
	}

	/**
	 * Sort orders (default by desc status).
	 *
	 * @param array $a order data
	 * @param array $b order data
	 *
	 * @return string
	 */
	private function _usort_reorder( $a, $b ) {
		// if no sort, default to status.
		$order_by = ! empty( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : 'date';
		// if no order, default to asc.
		$order = ! empty( $_REQUEST['order'] ) ? $_REQUEST['order'] : 'asc';
		// determine sort order.
		$result = strcmp( $a[ $order_by ], $b[ $order_by ] );

		// send final sort direction to usort.
		return 'desc' === $order ? $result : - $result;
	}

	/**
	 * Construct request and return orders.
	 *
	 * @param array $request search request
	 *
	 * @return array
	 */
	private function _request_get_orders( $request ) {
		global $wpdb;
		$conditions   = array();
		$array_search = array();
		$fields       = array(
			'lo.id',
			'lo.order_lengow_state',
			'lo.marketplace_name',
			'lo.marketplace_label',
			'lo.marketplace_sku',
			'lo.customer_name',
			'lo.order_id',
			'lo.order_date',
			'lo.order_types',
			'lo.delivery_country_iso',
			'lo.order_item',
			'lo.total_paid',
			'lo.currency',
			'lo.is_in_error',
			'lo.order_process_state',
			'lo.sent_marketplace',
		);
		$query        = 'SELECT ' . join( ', ', $fields ) . ' FROM ' . $wpdb->prefix . 'lengow_orders AS lo';
		// search field.
		if ( isset( $request['s'] ) && ! empty( $request['s'] ) ) {
			// changes $search for LIKE %% use.
			$search              = '%' . $request['s'] . '%';
			$search_query_fields = array();
			$search_fields       = array(
				Lengow_Order::FIELD_MARKETPLACE_SKU,
				Lengow_Order::FIELD_ORDER_ID,
				Lengow_Order::FIELD_CUSTOMER_NAME,
			);
			foreach ( $search_fields as $search_field ) {
				$search_query_fields[] = 'lo.' . $search_field . ' LIKE ' . '%s';
			}
			$conditions[]       = '(' . join( ' OR ', $search_query_fields ) . ')';
			$search_field_count = count( $search_fields );
			for ( $i = 0; $i < $search_field_count; $i ++ ) {
				$array_search[] = $search;
			}
		}
		// order filters.
		if ( isset( $request['order_action'] ) && ! empty( $request['order_action'] ) ) {
			if ( 'error' === $request['order_action'] ) {
				$conditions[]   = '(lo.is_in_error = %d AND lo.order_process_state != %d)';
				$array_search[] = 1;
				$array_search[] = Lengow_Order::PROCESS_STATE_FINISH;
			} else {
				$conditions[]   = 'lo.is_in_error = %d';
				$array_search[] = 0;
			}
		}
		if ( isset( $request['order_status'] ) && ! empty( $request['order_status'] ) ) {
			$conditions[]   = 'lo.order_lengow_state = %s';
			$array_search[] = $request['order_status'];
		}
		if ( isset( $request['order_type'] ) && ! empty( $request['order_type'] ) ) {
			$orderType = $request['order_type'];
			if ( Lengow_Order::TYPE_EXPRESS === $orderType ) {
				$conditions[]   = '(lo.order_types LIKE %s OR lo.order_types LIKE %s)';
				$array_search[] = '%' . Lengow_Order::TYPE_EXPRESS . '%';
				$array_search[] = '%' . Lengow_Order::TYPE_PRIME . '%';
			} elseif ( Lengow_Order::TYPE_DELIVERED_BY_MARKETPLACE === $orderType ) {
				$conditions[]   = '(lo.order_types LIKE %s OR lo.sent_marketplace = %d)';
				$array_search[] = '%' . Lengow_Order::TYPE_DELIVERED_BY_MARKETPLACE . '%';
				$array_search[] = 1;
			} else {
				$conditions[]   = 'lo.order_types LIKE %s';
				$array_search[] = '%' . Lengow_Order::TYPE_BUSINESS . '%';
			}
		}
		if ( isset( $request['order_marketplace'] ) && ! empty( $request['order_marketplace'] ) ) {
			$conditions[]   = 'lo.marketplace_name = %s';
			$array_search[] = $request['order_marketplace'];
		}
		$order_from = ( isset( $request['order_from'] ) && ! empty( $request['order_from'] ) )
			? $request['order_from']
			: false;
		$order_to   = ( isset( $request['order_to'] ) && ! empty( $request['order_to'] ) )
			? $request['order_to']
			: false;
		if ( $order_from || $order_to ) {
			if ( preg_match( '/^\d{2}\/\d{2}\/\d{4}$/', $order_from )
			     && preg_match( '/^\d{2}\/\d{2}\/\d{4}$/', $order_to )
			) {
				$from           = DateTime::createFromFormat( 'd/m/Y', $order_from );
				$to             = DateTime::createFromFormat( 'd/m/Y', $order_to );
				$conditions[]   = ' lo.order_date BETWEEN %s AND %s';
				$array_search[] = $from->format( Lengow_Main::DATE_DAY ) . ' 00:00:00';
				$array_search[] = $to->format( Lengow_Main::DATE_DAY ) . ' 23:59:59';
			} elseif ( preg_match( '/^\d{2}\/\d{2}\/\d{4}$/', $order_from ) ) {
				$from           = DateTime::createFromFormat( 'd/m/Y', $order_from );
				$conditions[]   = ' lo.order_date >= %s';
				$array_search[] = $from->format( Lengow_Main::DATE_DAY ) . ' 00:00:00';
			} elseif ( preg_match( '/^\d{2}\/\d{2}\/\d{4}$/', $order_to ) ) {
				$to             = DateTime::createFromFormat( 'd/m/Y', $order_to );
				$conditions[]   = ' lo.order_date <= %s';
				$array_search[] = $to->format( Lengow_Main::DATE_DAY ) . ' 23:59:59';
			}
		}
		if ( ! empty( $conditions ) ) {
			$query         .= ' WHERE ' . join( ' AND ', $conditions );
			$prepare_query = $wpdb->prepare( $query, $array_search );
		} else {
			$prepare_query = $query;
		}
		$result = $wpdb->get_results( $prepare_query );

		return $result ?: array();
	}

	/**
	 * Test if orders are present for display.
	 *
	 * @return int
	 */
	public static function count_orders() {
		global $wpdb;

		$query = 'SELECT count(*) FROM ' . $wpdb->prefix . Lengow_Order::TABLE_ORDER;

		return $wpdb->get_var( $query );
	}

	/**
	 * Generate Lengow actions.
	 *
	 * @param Object $order_lengow Lengow order row
	 *
	 * @return string
	 */
	private function _display_actions( $order_lengow ) {
		$locale              = new Lengow_Translation();
		$orders_data         = '';
		$error_messages      = array();
		$order_process_state = (int) $order_lengow->order_process_state;
		// check if order is not finished and is in error.
		if ( (bool) $order_lengow->is_in_error && Lengow_Order::PROCESS_STATE_FINISH !== $order_process_state ) {
			$order_errors = Lengow_Order_Error::get_order_errors( $order_lengow->id, null, false );
			if ( ! empty( $order_errors ) ) {
				foreach ( $order_errors as $error ) {
					if ( '' !== $error->message ) {
						$error_messages[] = Lengow_Main::clean_data(
							Lengow_Main::decode_log_message( $error->message )
						);
					} else {
						$error_messages[] = $locale->t( 'order.table.no_error_message' );
					}
				}
				if ( Lengow_Order::PROCESS_STATE_NOT_IMPORTED === $order_process_state ) {
					$message     = $locale->t( 'order.table.order_not_imported' )
					               . '<br/>' . join( '<br/>', $error_messages );
					$value       = '<a href="#"
									class="lengow_action lengow_link_tooltip lgw-btn lgw-btn-white"
				                    data-action="re_import"
				                    data-order="' . $order_lengow->id . '"
				                    data-html="true"
				                    data-original-title="' . $message . '">'
					               . $locale->t( 'order.table.not_imported' )
					               . ' <i class="fa fa-refresh"></i></a>';
					$orders_data = $value;
				} else {
					$message     = $locale->t( 'order.table.action_sent_not_work' )
					               . '<br/>' . join( '<br/>', $error_messages );
					$value       = '<a href="#"
									class="lengow_action lengow_link_tooltip lgw-btn lgw-btn-white"
				                    data-action="re_send"
				                    data-order="' . $order_lengow->id . '"
				                    data-html="true"
				                    data-original-title="' . $message . '">'
					               . $locale->t( 'order.table.not_sent' )
					               . ' <i class="fa fa-refresh"></i></a>';
					$orders_data = $value;
				}
			}
		} else {
			if ( $order_lengow->order_id && Lengow_Order::PROCESS_STATE_IMPORT === $order_process_state ) {
				$last_action = Lengow_Action::get_last_order_action_type( $order_lengow->order_id );
				if ( $last_action ) {
					$value       = '<a class="lengow_action lengow_link_tooltip lgw-btn lgw-btn-white lgw-link-disabled"
				                    data-order="' . $order_lengow->id . '"
				                    data-action="' . 'none' . '"
				                    data-original-title="' . $locale->t( 'order.table.action_waiting_return' ) . '">'
					               . $locale->t( 'order.table.action_sent', array( 'action_type' => $last_action ) )
					               . '</a>';
					$orders_data = $value;
				}
			}
		}

		return $orders_data;
	}

	/**
	 * Generate status.
	 *
	 * @param Object $order_lengow Lengow order row
	 *
	 * @return string
	 */
	private function _display_status( $order_lengow ) {
		return '<span class="lgw-label lgw-order-status lgw-label-' . $order_lengow->order_lengow_state . '">'
		       . Lengow_Main::decode_log_message( 'order.screen.status_' . $order_lengow->order_lengow_state )
		       . '</span>';
	}

	/**
	 * Generate order types.
	 *
	 * @param Object $order_lengow Lengow order row
	 *
	 * @return string
	 */
	private function _display_order_types( $order_lengow ) {
		$return      = '<div>';
		$order_types = null !== $order_lengow->order_types ? json_decode( $order_lengow->order_types, true ) : array();
		if ( isset( $order_types[ Lengow_Order::TYPE_EXPRESS ] )
		     || isset( $order_types[ Lengow_Order::TYPE_PRIME ] )
		) {
			$icon_label = isset( $order_types[ Lengow_Order::TYPE_PRIME ] )
				? $order_types[ Lengow_Order::TYPE_PRIME ]
				: $order_types[ Lengow_Order::TYPE_EXPRESS ];
			$return     .= $this->_generate_order_type_icon( $icon_label, 'orange-light', 'mod-chrono' );
		}
		if ( isset( $order_types[ Lengow_Order::TYPE_DELIVERED_BY_MARKETPLACE ] ) || $order_lengow->sent_marketplace ) {
			$icon_label = isset( $order_types[ Lengow_Order::TYPE_DELIVERED_BY_MARKETPLACE ] )
				? $order_types[ Lengow_Order::TYPE_DELIVERED_BY_MARKETPLACE ]
				: Lengow_Order::LABEL_FULFILLMENT;
			$return     .= self::_generate_order_type_icon( $icon_label, 'green-light', 'mod-delivery' );
		}
		if ( isset( $order_types[ Lengow_Order::TYPE_BUSINESS ] ) ) {
			$icon_label = $order_types[ Lengow_Order::TYPE_BUSINESS ];
			$return     .= self::_generate_order_type_icon( $icon_label, 'blue-light', 'mod-pro' );
		}
		$return .= '</div>';

		return $return;
	}

	/**
	 * Generate reference.
	 *
	 * @param Object $order_lengow Lengow order row
	 *
	 * @return string
	 */
	private function _display_reference( $order_lengow ) {
		if ( $order_lengow->order_id ) {
			$return = '<a href="' . admin_url() . 'post.php?post=' . $order_lengow->order_id
			          . '&action=edit" target="_blank">' . $order_lengow->order_id . '</a>';
		} else {
			$return = '';
		}

		return $return;
	}

	/**
	 * Generate country.
	 *
	 * @param Object $order_lengow Lengow order row
	 *
	 * @return string
	 */
	private function _display_country( $order_lengow ) {
		if ( $this->countries[ $order_lengow->delivery_country_iso ] ) {
			$return = '<img src="/wp-content/plugins/lengow-woocommerce/assets/images/flag/'
			          . $order_lengow->delivery_country_iso . '.png"
                      class="lengow_link_tooltip"
                      data-original-title="' . $this->countries[ $order_lengow->delivery_country_iso ] . '"/>';
		} else {
			$return = '<img src="/wp-content/plugins/lengow-woocommerce/assets/images/flag/OTHERS.png"
                            class="lengow_link_tooltip"
                            data-original-title="OTHERS"/>';
		}

		return $return;
	}

	/**
	 * Generate total.
	 *
	 * @param Object $order_lengow Lengow order row
	 *
	 * @return string
	 */
	private function _display_total( $order_lengow ) {
		$price              = Lengow_Main::compare_version( '2.1.0', '<' )
			? $order_lengow->total_paid . get_woocommerce_currency_symbol( $order_lengow->currency )
			: wc_price( $order_lengow->total_paid, array( 'currency' => $order_lengow->currency ) );
		$nb_product_tooltip = Lengow_Main::decode_log_message(
			'order.screen.nb_product',
			null,
			array( 'nb' => $order_lengow->order_item )
		);

		return '
			<span class="lengow_link_tooltip" data-original-title="' . $nb_product_tooltip . '"/>'
		       . $price .
		       '</span>
		';
	}

	/**
	 * Generate order type icon.
	 *
	 * @param string $icon_label icon label for tooltip
	 * @param string $icon_color icon background color
	 * @param string $icon_mod icon mod for image
	 *
	 * @return string
	 */
	private function _generate_order_type_icon( $icon_label, $icon_color, $icon_mod ) {
		return '
            <div class="lgw-label ' . $icon_color . ' icon-solo lengow_link_tooltip" 
                 data-original-title="' . $icon_label . '">
                <span class="lgw-icon ' . $icon_mod . '"></span>
            </div>
        ';
	}
}
