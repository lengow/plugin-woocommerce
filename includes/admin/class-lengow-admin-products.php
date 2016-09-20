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
    private $data;
    private $locale;

    /**
     * Display lengow product table
     *
     */
    static function render_lengow_list(){
        $lengow_table = new Lengow_Admin_Products();
        $lengow_table->locale = new Lengow_Translation();
        $lengow_table->prepare_items();
        //TODO - trad
        $lengow_table->search('Search', 'search_id');
        $lengow_table->display();
    }

    /**
     * Display lengow product data
     *
     */
    function prepare_items() {
        $columns = $this->get_columns();
        // $hidden defines the hidden columns
        $hidden = array();
        // $sortable defines if the table can be sorted by this column
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->data = $this->get_products();

        usort( $this->data, array( &$this, 'usort_reorder' ));
        // pagination
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $total_items = count($this->data);
        $data = array_slice($this->data,(($current_page-1)*$per_page),$per_page);
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $this->items =  $data;

    }

    /**
     * Get all columns
     *
     * @return array
     */
    function get_columns(){
        // columns label on the top and bottom of the table.
        $columns = array(
            //TODO - trad
            'cb'        => '<input type="checkbox" />',
            'ID'            => 'ID',
            'image'         => 'Image',
            'post_title'    => 'Name',
            '_sku'          => 'Sku',
            '_stock_status' => 'Stock',
            '_price'        => 'Price',
            'categories'    => 'Categories',
            'product_type'  => 'Type',
            'lengow'        => 'Include to lengow ?',
        );
        return $columns;
    }

    /**
     * Define all columns for specific method
     * @param $item
     * @param $column_name
     * @return array
     */
    function column_default( $item, $column_name ) {
        // To avoid the need to create a method for each column there is column_default
        // that will process any column for which no special method is defined
        switch( $column_name ) {
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
     * Get all sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            // The second parameter in the value array takes care of a possible pre-ordered column.
            // If the value is true the column is assumed to be ordered ascending,
            // if the value is false the column is assumed descending or unordered.
            'ID'  => array('ID',true),
            'image'  => array('image',false),
            'post_title' => array('post_title',false),
            '_sku' => array('_sku',false),
            '_stock_status' => array('_stock_status',false),
            '_price' => array('_price',false),
            'categories' => array('categories',false),
            'product_type' => array('product_type',false),
            'lengow' => array('lengow',false),
        );
        return $sortable_columns;
    }

    /**
     * Sort products (default by asc ID)
     *
     * @param $a
     * @param $b
     * @return mixed
     */
    function usort_reorder( $a, $b ) {
        // If no sort, default to ID
        $order_by = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'ID';

        // If no order, default to asc
        $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';

        // Determine sort order
        $result = strcmp( $a[$order_by], $b[$order_by] );

        // Send final sort direction to usort
        return ( $order === 'asc' ) ? $result : -$result;
    }


    /**
     * Display product edit action
     *
     * @param $product
     * @return mixed
     */
    function column_ID($product) {
        //TODO - trad Edit
        $actions = array(
            'edit'      => sprintf('<a href="post.php?post=%s&action=%s">Edit</a>',$product['ID'],'edit'),
        );

        return sprintf('%1$s %2$s', $product['ID'], $this->row_actions($actions) );
    }

    /**
     * Display checkbox
     *
     * @param $product
     * @return mixed
     */
    function column_cb($product) {
        return sprintf(
            '<input type="checkbox" name="product[]" value="%s" />', $product['ID']
        );
    }

    /**
     * Display lengow checkbox
     *
     * @param $product
     * @return mixed
     */
    function column_lengow($product) {
        return sprintf(
            '<input type="checkbox" name="product[]" value="%s" />', $product['ID']
        );
    }

    /**
     * Display lengow bulk actions
     * @return array
     */
    function get_bulk_actions() {
        $actions = array(
            'publish_on_lengow'     => 'Publish on Lengow',
            'unpublish_on_lengow'   => 'Unpublish on Lengow',
        );
        return $actions;
    }

    /**
     * Get all products meta
     * @return array
     */
    function get_products(){
        $results = array();
        $keys =  array(
            'ID',
            'image',
            'post_title',
            'categories',
            //TODO - trad
            '_stock_status',
            '_sku',
            '_price',
            'product_type'
        );

        // filter by search box
        if (isset($_POST['s'])){
            $products = get_posts(
                array(
                    'numberposts' => -1,
                    'post_type' => 'product',
                    's' => $_POST['s']
                ));
        } else {
            $products = get_posts(
                array(
                    'numberposts' => -1,
                    'post_type' => 'product'
                ));
        }

        foreach ($keys as $key) {
            foreach ($products as $product) {
                switch ($key) :
                    case 'ID' : $products_data = $product->ID;
                        break;
                    case 'image' :
                        $products_data = get_the_post_thumbnail($product->ID, array( 40, 40));
                        break;
                    case 'post_title' : $products_data = $product->post_title;
                        break;
                    case 'categories':
                        $categories = wp_get_post_terms( $product->ID, 'product_cat', array( 'fields' => 'names' ) );
                        $products_categories= array();
                        foreach ($categories as $value) {
                            array_push($products_categories,$value);
                        }
                        $products_data = implode(",", $products_categories);
                        break;
                    case '_price':
                        $price = get_post_meta($product->ID, $key, true);
                        $products_data = $price.' '.get_woocommerce_currency_symbol();
                        break;
                    case 'product_type':
                        if (get_post_meta($product->ID, '_downloadable', true) == 'yes') {
                            //TODO - trad
                            $products_data = 'downloadable';
                        } elseif (get_post_meta($product->ID, '_virtual', true) == 'yes') {
                            //TODO - trad
                            $products_data = 'virtual';
                        } else {
                            $product_type = wc_get_product($product->ID);
                            $products_data = $product_type->product_type;
                        }
                        break;
                    default : $products_data = get_post_meta($product->ID, $key, true);
                endswitch;

                $results[$product->ID][$key] = $products_data;
            }
        }
        return $results;
    }

    /**
     * Search box
     *
     * @param $text
     * @param $input_id
     */
    function search($text, $input_id)
    {
        echo '<form id="post-filter" method="post">';
        //The hidden element is needed to load the right page
        echo '<input type="hidden" name="page" value="lengow_list" />';
        echo $this->search_box($text, $input_id);
        echo '</form>';
    }

    /**
     * Display products page
     */
    public static function html_display() {
        include_once 'views/products/html-admin-products.php';

    }
}

