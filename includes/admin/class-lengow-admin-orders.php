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
	 * @var array all order datas.
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
		include_once 'views/html-admin-header-order.php';
		include_once 'views/orders/html-admin-orders.php';
	}

	/**
	 * Process Post Parameters.
	 */
	public static function post_process() {
		$lengow_admin_orders = new Lengow_Admin_Orders();
		$locale              = new Lengow_Translation();
		$action              = isset( $_POST['do_action'] ) ? $_POST['do_action'] : false;
		if ( $action ) {
			switch ( $action ) {
				case 'import_all':
					$import                   = new Lengow_Import(
						array( 'log_output' => false )
					);
					$return                   = $import->exec();
					$message                  = $lengow_admin_orders->load_message( $return );
					$order_collection         = $lengow_admin_orders->assign_last_importation_infos();
					$data                     = array();
					$data['message']          = join( '<br/>', $message );
					$data['import_orders']    = $locale->t( 'order.screen.button_update_orders' );
					$data['last_importation'] = $order_collection['last_import_date'];
					echo json_encode( $data );
					break;
			}
			exit();
		}
	}

	/**
	 * Generate message array (new, update and errors).
	 *
	 * @param array $return import informations
	 *
	 * @return array
	 */
	public function load_message( $return ) {
		$locale   = new Lengow_Translation();
		$messages = array();
		if ( isset( $return['error'] ) && $return['error'] != false ) {
			$messages[] = Lengow_Main::decode_log_message( $return['error'] );

			return $messages;
		}
		if ( isset( $return['order_new'] ) && $return['order_new'] > 0 ) {
			$messages[] = $locale->t(
				'lengow_log.error.nb_order_imported',
				array( 'nb_order' => (int) $return['order_new'] )
			);
		}
		if ( isset( $return['order_update'] ) && $return['order_update'] > 0 ) {
			$messages[] = $locale->t(
				'lengow_log.error.nb_order_updated',
				array( 'nb_order' => (int) $return['order_update'] )
			);
		}
		if ( isset( $return['order_error'] ) && $return['order_error'] > 0 ) {
			$messages[] = $locale->t(
				'lengow_log.error.nb_order_with_error',
				array( 'nb_order' => (int) $return['order_error'] )
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
		if ( Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) {
			$warning_messages[] = $locale->t(
				'order.screen.preprod_warning_message',
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
		$last_import      = Lengow_Main::get_last_import();
		$order_collection = array(
			'last_import_date' => $last_import['timestamp'] != 'none'
				? strftime( '%A %d %B %Y @ %X', $last_import['timestamp'] )
				: '',
			'last_import_type' => $last_import['type'],
		);

		return $order_collection;
	}

	/**
	 * Display lengow order table.
	 *
	 */
	public static function render_lengow_list() {
		// need to instantiate a class because this method must be static.
		$lengow_admin_orders         = new Lengow_Admin_Orders();
		$lengow_admin_orders->locale = new Lengow_Translation();
		$lengow_admin_orders->prepare_items();
		$lengow_admin_orders->search(
			$lengow_admin_orders->locale->t( 'order.table.button_search' ),
			'search_id'
		);
		$lengow_admin_orders->display();

	}

	/**
	 * Display lengow order data.
	 *
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		// $sortable defines if the table can be sorted by this column.
		$sortable = $this->get_sortable_columns();
		// $hidden defines the hidden columns.
		$hidden                = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->data            = $this->get_orders();
		usort( $this->data, array( &$this, 'usort_reorder' ) );
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
	 * Get all columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		// columns label on the top and bottom of the table.
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'action'      => $this->locale->t( 'order.table.action' ),
			'status'      => $this->locale->t( 'order.table.lengow_status' ),
			'marketplace' => $this->locale->t( 'order.table.marketplace' ),
			'id_order'    => $this->locale->t( 'order.table.id_order' ),
			'customer'    => $this->locale->t( 'order.table.customer' ),
			'reference'   => $this->locale->t( 'order.table.reference' ),
			'date'        => $this->locale->t( 'order.table.date' ),
			'country'     => $this->locale->t( 'order.table.country' ),
			'quantity'    => $this->locale->t( 'order.table.quantity' ),
			'total'       => $this->locale->t( 'order.table.total' ),
		);

		return $columns;
	}

	/**
	 * Get all sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			// the second parameter in the value array takes care of a possible pre-ordered column.
			// if the value is true the column is assumed to be ordered ascending.
			// if the value is false the column is assumed descending or unordered.
			'action'      => array( 'action', false ),
			'status'      => array( 'status', false ),
			'marketplace' => array( 'marketplace', false ),
			'id_order'    => array( 'id_order', false ),
			'customer'    => array( 'customer', false ),
			'reference'   => array( 'reference', false ),
			'date'        => array( 'date', true ),
			'country'     => array( 'country', false ),
			'quantity'    => array( 'quantity', false ),
			'total'       => array( 'total', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Get all orders meta.
	 *
	 * @return array
	 */
	private function get_orders() {
		$results = array();
		$keys    = array(
			'id',
			'action',
			'status',
			'marketplace',
			'id_order',
			'customer',
			'reference',
			'date',
			'country',
			'quantity',
			'total',
		);

		// filter by search box.
		if ( isset( $_POST['s'] ) ) {
			$orders = $this->request_get_orders( $_POST['s'] );
		} else {
			$orders = $this->request_get_orders();
		}

		foreach ( $keys as $key ) {
			foreach ( $orders as $order ) {
				switch ( $key ) {
					case 'id':
						$orders_data = $order->id;
						break;
					case 'action' :
						$orders_data = 'action';
						break;
					case 'status' :
						$orders_data = '<span class="lgw-label lgw-label-' . $order->order_lengow_state . '">'
						               . Lengow_Main::decode_log_message( 'order.screen.status_' . $order->order_lengow_state )
						               . '</span>';
						break;
					case 'marketplace' :
						$orders_data = $order->marketplace_label;
						break;
					case 'id_order' :
						$orders_data = $order->marketplace_sku;
						break;
					case 'customer' :
						$orders_data = $order->customer_name;
						break;
					case 'reference' :
						$orders_data = '<a href="/wp-admin/post.php?post=' . $order->order_id . '&action=edit">' . $order->order_id . '</a>';
						break;
					case 'date' :
						$orders_data = $order->order_date;
						break;
					case 'country' :
						$wc_country = new WC_Countries;
						$orders_data = '<img src="/wp-content/plugins/lengow-woocommerce/assets/images/flag/' . $order->delivery_country_iso . '.png"
                                class="lengow_link_tooltip"
                                data-original-title="' . $wc_country->countries[ $order->delivery_country_iso ] . '"/>';
						break;
					case 'quantity' :
						$orders_data = $order->order_item;
						break;
					case 'total' :
						$orders_data = $order->total_paid . get_woocommerce_currency_symbol();
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
	 * @param array $a order datas
	 * @param array $b order datas
	 *
	 * @return string
	 */
	private function usort_reorder( $a, $b ) {
		// if no sort, default to status.
		$order_by = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : 'date';

		// if no order, default to asc.
		$order = ! empty( $_GET['order'] ) ? $_GET['order'] : 'asc';

		// determine sort order.
		$result = strcmp( $a[ $order_by ], $b[ $order_by ] );

		// send final sort direction to usort.
		return 'desc' === $order ? $result : - $result;
	}

	/**
	 * Search box.
	 *
	 * @param string $text text for search
	 * @param string $input_id id for search
	 */
	private function search( $text, $input_id ) {
		echo '<form id="post-filter" method="post">';
		// the hidden element is needed to load the right page.
		echo '<input type="hidden" name="page" value="lengow_list" />';
		echo $this->search_box( $text, $input_id );
		echo '</form>';
	}

	/**
	 * Define all columns for specific method.
	 *
	 * @param array $item order datas
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
			case 'marketplace':
			case 'id_order':
			case 'customer':
			case 'reference':
			case 'date':
			case 'country':
			case 'quantity':
			case 'total':
				return $item[ $column_name ];
				break;
			default:
				break;

		}
	}

	/**
	 * Construct request and return orders
	 *
	 * @param string $search search
	 *
	 * @return array
	 */
	public function request_get_orders( $search = null ) {
		global $wpdb;
		$array_search = array();

		$query = 'SELECT * FROM ' . $wpdb->prefix . 'lengow_orders';

		if ( ! empty( $search ) ) {
			// Changes $search for LIKE %% use.
			$search        = '%' . $search . '%';
			$search_fields = [ 'marketplace_label', 'marketplace_sku', 'order_id', 'customer_name' ];
			$conditions    = array();

			foreach ( $search_fields as $search_field ) {
				$conditions[] = $search_field . ' LIKE ' . '%s';
			}
			$query .= ' WHERE ' . join( ' OR ', $conditions );
			// Wordpress needs one parameter by placeholder.
			for ( $i = 0; $i < count( $search_fields ); $i ++ ) {
				$array_search[] = $search;
			}
		}
		$prepare_query = empty( $search ) ? $query : $wpdb->prepare( $query, $array_search );
		$result        = $wpdb->get_results( $prepare_query );
		$return        = $result ? $result : array();

		return $return;
	}

	/**
	 * Test if orders are present for
	 *
	 * @return int
	 */
	public static function count_orders() {
		global $wpdb;

		$query = 'SELECT count(*) FROM ' . $wpdb->prefix . 'lengow_orders';
		$result = $wpdb->get_var( $query );

		return $result;
	}

	public function column_cb( $order ) {
		return sprintf(
			'<input type="checkbox" id="js-lengow_order_checkbox"
				name="order[]" value="%s" class="js-lengow_selection"/>',
			$order['id']
		);
	}
}
