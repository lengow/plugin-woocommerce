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
    public $example_data = array(
        array('ID' => 1,'booktitle' => 'Quarter Share', 'author' => 'Nathan Lowell',
            'isbn' => '978-0982514542'),
        array('ID' => 2, 'booktitle' => '7th Son: Descent','author' => 'J. C. Hutchins',
            'isbn' => '0312384378'),
        array('ID' => 3, 'booktitle' => 'Shadowmagic', 'author' => 'John Lenahan',
            'isbn' => '978-1905548927'),
        array('ID' => 4, 'booktitle' => 'The Crown Conspiracy', 'author' => 'Michael J. Sullivan',
            'isbn' => '978-0979621130'),
        array('ID' => 5, 'booktitle'     => 'Max Quick: The Pocket and the Pendant', 'author'    => 'Mark Jeffrey',
            'isbn' => '978-0061988929'),
        array('ID' => 6, 'booktitle' => 'Jack Wakes Up: A Novel', 'author' => 'Seth Harwood',
            'isbn' => '978-0307454355')
    );

    function get_columns(){
        $columns = array(
            'booktitle' => 'Title',
            'author'    => 'Author',
            'isbn'      => 'ISBN'
        );
        return $columns;
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->example_data;
    }

    function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'booktitle':
            case 'author':
            case 'isbn':
                return $item[ $column_name ];
            default:

        }
    }


    static function my_render_list_page(){
        $myListTable = new Lengow_Admin_Products();
        echo '<div class="wrap"><h2>My List Table Test</h2>';
        $myListTable->prepare_items();
        $myListTable->display();
        echo '</div>';
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'booktitle'  => array('booktitle',true),
            'author' => array('author',false),
            'isbn'   => array('isbn',false)
        );
        return $sortable_columns;
    }

    /**
     * Display products page
     */
    public static function html_display() {
        include_once 'views/products/html-admin-products.php';

    }
}

