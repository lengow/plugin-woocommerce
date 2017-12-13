<?php
/**
 * Admin product page
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
 * @package        lengow-woocommerce
 * @subpackage    includes
 * @author        Team module <team-module@lengow.com>
 * @copyright    2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Lengow_Admin_Products Class.
 */
class Lengow_Admin_Products extends WP_List_Table {

	/**
	 * @var array all product datas.
	 */
	private $data;

	/**
	 * @var Lengow_Translation Lengow translation instance.
	 */
	private $locale;

	/**
	 * Display products page.
	 */
	public static function html_display() {
		// Need to instantiate a class because this method must be static.
		$lengow_admin_products         = new Lengow_Admin_Products();
		$lengow_admin_products->locale = new Lengow_Translation();
		$locale                        = $lengow_admin_products->locale;

		$keys = Lengow_Configuration::get_keys();

		$lengow_export = new Lengow_Export( null );

		$shop = array(
			'shop'                 => Lengow_Configuration::get( 'blogname' ),
			'domain'               => Lengow_Configuration::get( 'siteurl' ),
			'link'                 => Lengow_Main::get_export_url(),
			'total_product'        => $lengow_export->get_total_product(),
			'total_export_product' => $lengow_export->get_total_export_product(),
			'last_export'          => Lengow_Configuration::get( 'lengow_last_export' ),
			'option_selected'      => Lengow_Configuration::get( 'lengow_selection_enabled' ),
			'select_all'           => count( $lengow_admin_products->get_products() ),
		);
		include_once 'views/products/html-admin-products.php';
	}

	/**
	 * Process Post Parameters.
	 */
	public static function post_process() {
		$locale = new Lengow_Translation();
		$action = isset( $_POST['do_action'] ) ? $_POST['do_action'] : false;
		if ( $action ) {
			switch ( $action ) {
				case 'change_option_selected':
					$state = isset( $_POST['state'] ) ? $_POST['state'] : null;
					if ( $state !== null ) {
						Lengow_Configuration::update_value(
							'lengow_selection_enabled',
							$state
						);
						$state = Lengow_Configuration::get( 'lengow_selection_enabled' );
						$data  = array();
						if ( $state ) {
							$data["state"] = true;
						} else {
							$data["state"] = false;
						}
						$result = array_merge( $data, Lengow_Admin_Products::reload_total() );
						echo json_encode( $result );
					}
					break;
				case 'select_product':
					$state     = isset( $_POST['state'] ) ? $_POST['state'] : null;
					$productId = isset( $_POST['id_product'] ) ? $_POST['id_product'] : null;
					if ( $state !== null ) {
						Lengow_Product::publish( $productId, $state );
						echo json_encode( Lengow_Admin_Products::reload_total() );
					}
					break;
				case 'export_mass_action':
					$selection     = isset( $_POST['product'] ) ? $_POST['product'] : false;
					$select_all    = isset( $_POST['select_all'] ) ? $_POST['select_all'] : null;
					$export_action = isset( $_POST['export_action'] ) ? $_POST['export_action'] : null;
					$data          = array();
					if ( $select_all == "true" ) {
						$all_products = get_posts(
							array(
								'numberposts' => - 1,
								'post_type'   => 'product',
							) );
						$all          = array();
						foreach ( $all_products as $value ) {
							$all[] = $value->ID;
						}
						foreach ( $all as $id ) {
							if ( $export_action == 'add_to_export' ) {
								Lengow_Product::publish( $id, 1 );
							} else {
								Lengow_Product::publish( $id, 0 );
							}
							foreach ( $selection as $product ) {
								$data['product_id'][] = $product;
							}
						}
						$data = array_merge( $data, Lengow_Admin_Products::reload_total() );
					} elseif ( $selection ) {
						foreach ( $selection as $product ) {
							if ( $export_action == 'add_to_export' ) {
								Lengow_Product::publish( $product, 1 );
							} else {
								Lengow_Product::publish( $product, 0 );
							}
							$data['product_id'][] = $product;
						}
						$data = array_merge( $data, Lengow_Admin_Products::reload_total() );
					} else {
						$data['message'] = $locale->t( 'product.screen.no_product_selected' );
					}
					echo json_encode( $data );
					break;
			}
			exit();
		}
	}


	/**
	 * Display lengow product table.
	 *
	 */
	public static function render_lengow_list() {
		// Need to instantiate a class because this method must be static.
		$lengow_admin_products         = new Lengow_Admin_Products();
		$lengow_admin_products->locale = new Lengow_Translation();
		$lengow_admin_products->prepare_items();
		$lengow_admin_products->search(
			$lengow_admin_products->locale->t( 'product.table.button_search' ),
			'search_id'
		);
		$lengow_admin_products->display();
	}

	/**
	 * Display lengow product data.
	 *
	 */
	public function prepare_items() {
		$columns = $this->get_columns();
		// $hidden defines the hidden columns.
		$hidden = array();
		// $sortable defines if the table can be sorted by this column.
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->data            = $this->get_products();
		usort( $this->data, array( &$this, 'usort_reorder' ) );
		// pagination.
		$per_page     = 10;
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
			'cb'            => '<input type="checkbox" />',
			'ID'            => $this->locale->t( 'product.table.id_product' ),
			'image'         => $this->locale->t( 'product.table.image' ),
			'post_title'    => $this->locale->t( 'product.table.name' ),
			'_sku'          => $this->locale->t( 'product.table.reference' ),
			'_stock_status' => $this->locale->t( 'product.table.stock' ),
			'_price'        => $this->locale->t( 'product.table.price' ),
			'categories'    => $this->locale->t( 'product.table.category_name' ),
			'product_type'  => $this->locale->t( 'product.table.type' ),
			'lengow'        => $this->locale->t( 'product.table.lengow_status' ),
		);

		return $columns;
	}

	/**
	 * Define all columns for specific method.
	 *
	 * @param array $item product datas
	 * @param string $column_name column name
	 *
	 * @return array
	 */
	public function column_default( $item, $column_name ) {
		// To avoid the need to create a method for each column there is column_default.
		// that will process any column for which no special method is defined.
		switch ( $column_name ) {
			case 'ID':
			case 'image':
			case 'post_title':
			case '_sku':
			case '_stock_status':
			case '_price':
			case 'categories':
			case 'product_type':
			case 'lengow':
				return $item[ $column_name ];
				break;
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
		$sortable_columns = array(
			// The second parameter in the value array takes care of a possible pre-ordered column.
			// If the value is true the column is assumed to be ordered ascending.
			// if the value is false the column is assumed descending or unordered.
			'ID'            => array( 'ID', true ),
			'image'         => array( 'image', false ),
			'post_title'    => array( 'post_title', false ),
			'_sku'          => array( '_sku', false ),
			'_stock_status' => array( '_stock_status', false ),
			'_price'        => array( '_price', false ),
			'categories'    => array( 'categories', false ),
			'product_type'  => array( 'product_type', false ),
			'lengow'        => array( 'lengow', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Display product edit action.
	 *
	 * @param array $product product datas
	 *
	 * @return string
	 */
	public function column_ID( $product ) {
		$actions = array(
			$this->locale->t( 'product.table.edit' ) => sprintf(
				'<a href="post.php?post=%s&action=%s" target="_blank">Edit</a>',
				$product['ID'],
				'edit'
			),
		);

		return sprintf( '%1$s %2$s', $product['ID'], $this->row_actions( $actions ) );
	}

	/**
	 * Display checkbox.
	 *
	 * @param array $product product datas
	 *
	 * @return string
	 */
	public function column_cb( $product ) {
		return sprintf(
			'<input type="checkbox" id="js-lengow_product_checkbox"
				name="product[]" value="%s"class="js-lengow_selection"/>',
			$product['ID']
		);
	}

	/**
	 * Display lengow checkbox.
	 *
	 * @param array $product product datas
	 *
	 * @return string
	 */
	public function column_lengow( $product ) {
		$id_lengow_products = Lengow_Product::get_lengow_products();
		$checked            = isset( $id_lengow_products[ $product['ID'] ] ) ? 'checked' : '';
		$check              = isset( $id_lengow_products[ $product['ID'] ] ) ? 'checked="checked"' : '';

		return sprintf(
			'<div class="lgw-switch ' . $checked . '">
				<label><div><span></span>
				<input 
				type="checkbox"
				data-size="mini"
				class="js-lengow_switch_product"
				data-on-text="' . $this->locale->t( 'product.screen.button_yes' ) . '"
				data-off-text="' . $this->locale->t( 'product.screen.button_no' ) . '"
				name="lengow_product_selection[%s]"
				data-action="select_product"
				data-id_product="%s"
				id="js-lengow_product_%s"
				value="1" ' . $check . '/>
				</div></label></div>',
			$product['ID'],
			$product['ID'],
			$product['ID']
		);
	}

	/**
	 * Reload Total product / Exported product.
	 *
	 * @return array
	 */
	public function reload_total() {
		$lengow_export = new Lengow_Export( null );

		$result                         = array();
		$result['total_export_product'] = $lengow_export->get_total_export_product();
		$result['total_product']        = $lengow_export->get_total_product();

		return $result;
	}

	/**
	 * Sort products (default by asc ID).
	 *
	 * @param array $a product datas
	 * @param array $b product datas
	 *
	 * @return string
	 */
	private function usort_reorder( $a, $b ) {
		// If no sort, default to ID.
		$order_by = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : 'ID';

		// If no order, default to asc.
		$order = ! empty( $_GET['order'] ) ? $_GET['order'] : 'asc';

		// Determine sort order.
		$result = strcmp( $a[ $order_by ], $b[ $order_by ] );

		// Send final sort direction to usort.
		return $order === 'asc' ? $result : - $result;
	}

	/**
	 * Get all products meta.
	 *
	 * @return array
	 */
	private function get_products() {
		$results = array();
		$keys    = array(
			'ID',
			'image',
			'post_title',
			'categories',
			'_stock_status',
			'_sku',
			'_price',
			'product_type',
			'lengow',
		);

		// filter by search box.
		if ( isset( $_POST['s'] ) ) {
			$posts = get_posts(
				array(
					'numberposts' => - 1,
					'post_type'   => 'product',
					's'           => $_POST['s'],
				)
			);
		} else {
			$posts = get_posts(
				array(
					'numberposts' => - 1,
					'post_type'   => 'product',
				)
			);
		}

		foreach ( $keys as $key ) {
			foreach ( $posts as $post ) {
				switch ( $key ) {
					case 'ID' :
						$products_data = $post->ID;
						break;
					case 'image' :
						$products_data = get_the_post_thumbnail( $post->ID, array( 40, 40 ) );
						break;
					case 'post_title' :
						$products_data = $post->post_title;
						break;
					case 'categories':
						$categories          = wp_get_post_terms(
							$post->ID,
							'product_cat',
							array( 'fields' => 'names' )
						);
						$products_categories = array();
						foreach ( $categories as $value ) {
							array_push( $products_categories, $value );
						}
						$products_data = implode( ",", $products_categories );
						break;
					case '_stock_status':
						$products_data = get_post_meta( $post->ID, $key, true ) == 'instock'
							? $this->locale->t( 'product.table.in_stock' )
							: $this->locale->t( 'product.table.out_of_stock' );
						break;
					case '_price':
						$price         = get_post_meta( $post->ID, $key, true );
						$products_data = $price . ' ' . get_woocommerce_currency_symbol();
						break;
					case 'product_type':
						// Use woocommerce 2.0.0 function get_product(ID) to check type.
						$wc_product       = Lengow_Product::get_product( $post->ID );
						$product_type     = Lengow_Product::get_product_type( $wc_product );
						$downloadable     = get_post_meta( $post->ID, '_downloadable', true ) == 'yes'
							? $this->locale->t( 'product.table.type_downloadable' )
							: false;
						$virtual          = get_post_meta( $post->ID, '_virtual', true ) == 'yes'
							? $this->locale->t( 'product.table.type_virtual' )
							: false;
						$sub_product_type = false;
						if ( $downloadable && $virtual ) {
							$sub_product_type = ' (' . $downloadable . ', ' . $virtual . ')';
						} elseif ( $downloadable ) {
							$sub_product_type = ' (' . $downloadable . ')';
						} elseif ( $virtual ) {
							$sub_product_type = ' (' . $virtual . ')';
						}

						if ( $sub_product_type ) {
							$products_data = ucfirst( $product_type ) . $sub_product_type;
						} else {
							$products_data = ucfirst( $product_type );
						}
						break;
					case 'lengow':
						$products_data = (int) Lengow_Product::is_lengow_product( $post->ID );
						break;
					default :
						$products_data = get_post_meta( $post->ID, $key, true );
				}
				$results[ $post->ID ][ $key ] = $products_data;
			}
		}

		return $results;
	}

	/**
	 * Search box.
	 *
	 * @param string $text text for search
	 * @param string $input_id id for search
	 */
	private function search( $text, $input_id ) {
		echo '<form id="post-filter" method="post">';
		// The hidden element is needed to load the right page.
		echo '<input type="hidden" name="page" value="lengow_list" />';
		echo $this->search_box( $text, $input_id );
		echo '</form>';
	}
}
