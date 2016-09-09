<?php
/**
 * Installation related functions and actions.
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Lengow_Admin_Products Class.
 */
class Lengow_Admin_Products extends WP_List_Table {
    public $data;


    function get_columns(){
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'ID'            => 'ID',
            'post_title'    => 'Name',
            '_sku'          => 'Sku',
            '_stock_status' => 'Stock',
            '_price'        => 'Price',
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->data = $this->get_products();
        usort( $this->data, array( &$this, 'usort_reorder' ));
        $this->items =  $this->data;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'ID':
            case 'post_title':
            case '_sku':
            case '_stock_status':
            case '_price':
                return $item[ $column_name ];
            default:

        }
    }


    static function my_render_list_page(){
        $lengow_table = new Lengow_Admin_Products();
        $lengow_table->prepare_items();
        $lengow_table->display();
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'ID'  => array('ID',true),
            'post_title' => array('post_title',false),
            '_sku' => array('_sku',false),
            '_stock_status' => array('_stock_status',false),
            '_price' => array('_price',false),
        );
        return $sortable_columns;
    }

    function usort_reorder( $a, $b ) {
        // If no sort, default to title
        $order_by = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'ID';
        // If no order, default to asc
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
        // Determine sort order
        $result = strcmp( $a[$order_by], $b[$order_by] );
        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
    }

    function column_ID($item) {
        $actions = array(
            'edit'      => sprintf('<a href="?page=%s&action=%s&book=%s">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
        );

        return sprintf('%1$s %2$s', $item['ID'], $this->row_actions($actions) );
    }

    function get_bulk_actions() {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="product[]" value="%s" />', $item['ID']
        );
    }

    function get_products(){
        $results = array();
        $keys =  array(
            'ID',
            'post_title',
            'categories',
            '_stock_status',
            '_downloadable',
            '_virtual',
            '_sku',
            '_price'
        );

        $products = get_posts(
                        array(
                            'numberposts' => -1,
                            'post_type' => 'product'
                        ));

        foreach ($keys as $key) {
            foreach ($products as $product) {
                switch ($key) {
                    case 'ID' : $products_data = $product->ID;
                        break;
                    case 'post_title' : $products_data = $product->post_title;
                        break;
//                    case 'categories' :
//                        break;
//                    case '_downloadable' :
//                    case '_virtual' :
                        break;
                    default : $products_data = get_post_meta($product->ID, $key, true);
                }
                $results[$product->ID][$key] = $products_data;
            }
        }
        return $results;
    }

    /**
     * Display products page
     */
    public static function html_display() {
        include_once 'views/products/html-admin-products.php';

    }
}

