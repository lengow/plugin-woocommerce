<?php
/**
 * All components to generate Lengow feed
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Export Class.
 */
class Lengow_Export {

	/**
	 * @var string Format of exported files
	 */
	private $_format;

	/**
	 * @var boolean Stream file or generate a file
	 */
	private $_stream;

	/**
	 * @var integer Offset of total product
	 */
	private $_offset;

	/**
	 * @var integer Limit number of exported product
	 */
	private $_limit;

	/**
	 * @var boolean Export product selection
	 */
	private $_selection;

	/**
	 * @var boolean Export out of stock product
	 */
	private $_out_of_stock;

	/**
	 * @var array List of product id separate with comma
	 */
	private $_product_ids = array();

	/**
	 * @var array Product types separate with comma
	 */
	private $_product_types = array();

	/**
	 * @var boolean Export product Variation
	 */
	private $_variation;

	/**
	 * @var boolean Export feed with v2 fields
	 */
	private $_legacy_fields;

	/**
	 * @var boolean See logs
	 */
	private $_log_output;

	/**
	 * @var boolean Change last export date in data base
	 */
	private $_update_export_date;

	/**
	 * Construct new Lengow export
	 *
	 * @param array params optional options
	 * string  format             Format of exported files ('csv','yaml','xml','json')
	 * boolean stream             Stream file (1) or generate a file on server (0)
	 * integer offset             Offset of total product
	 * integer limit              Limit number of exported product
	 * boolean selection          Export product selection (1) or all products (0)
	 * boolean out_of_stock       Export out of stock product (1) Export only product in stock (0)
	 * string  product_ids        List of product ids separate with comma (1,2,3)
	 * string  product_types      Product types separate with comma (external,grouped,simple,variable)
	 * boolean variation          Export product Variation (1) Export parent product only (0)
	 * boolean legacy_fields      Export feed with v2 fields (1) or v3 fields (0)
	 * boolean log_output         See logs (1) or not (0)
	 * boolean update_export_date Change last export date in data base (1) or not (0)
	 */
	public function __construct( $params = array() ) {
		$this->_stream             = ! is_null( $params['stream'] ) ? $params['stream'] : true;
		$this->_offset             = ! is_null( $params['offset'] ) ? $params['offset'] : 0;
		$this->_limit              = ! is_null( $params['limit'] ) ? $params['limit'] : 0;
		$this->_selection          = ! is_null( $params['selection'] )
			? $params['selection']
			: (bool) Lengow_Configuration::get( 'lengow_selection_enabled' );
		$this->_out_of_stock       = ! is_null( $params['out_of_stock'] )
			? $params['out_of_stock']
			: (bool) Lengow_Configuration::get( 'lengow_out_stock' );
		$this->_variation          = ! is_null( $params['variation'] ) ? $params['variation'] : true;
		$this->_legacy_fields      = ! is_null( $params['legacy_fields'] ) ? $params['legacy_fields'] : false;
		$this->_update_export_date = ! is_null( $params['update_export_date'] )
			? (bool) $params['update_export_date']
			: true;
		$this->_set_format( ! is_null( $params['format'] ) ? $params['format'] : 'csv' );
		$this->_set_product_ids( ! is_null( $params['product_ids'] ) ? $params['product_ids'] : false );
		$this->_set_product_types( ! is_null( $params['product_types'] ) ? $params['product_types'] : false );
		$this->_set_log_output( ! is_null( $params['log_output'] ) ? $params['log_output'] : true );
	}

	/**
	 * Set format to export
	 *
	 * @param string $format The export format
	 */
	private function _set_format( $format ) {
		$this->_format = ! in_array( $format, Lengow_Feed::$AVAILABLE_FORMATS ) ? 'csv' : $format;
	}

	/**
	 * Set product ids to export
	 *
	 * @param string $product_ids The product ids to export
	 */
	private function _set_product_ids( $product_ids ) {
		if ( $product_ids ) {
			$exported_ids = explode( ',', $product_ids );
			foreach ( $exported_ids as $id ) {
				if ( is_numeric( $id ) && $id > 0 ) {
					$this->_product_ids[] = (int) $id;
				}
			}
		}
	}

	/**
	 * Set product types to export
	 *
	 * @param string $product_types The product types to export
	 */
	private function _set_product_types( $product_types ) {
		if ( $product_types ) {
			$exported_types = explode( ',', $product_types );
			foreach ( $exported_types as $type ) {
				if ( array_key_exists( $type, Lengow_Main::$PRODUCT_TYPES ) ) {
					$this->_product_types[] = $type;
				}
			}
		}
		if ( count( $this->_product_types ) == 0 ) {
			$this->_product_types = Lengow_Configuration::get( 'lengow_product_types' );
		}
	}

	/**
	 * Set Log output for export
	 *
	 * @param boolean $log_output See logs or not
	 */
	private function _set_log_output( $log_output ) {
		$this->_log_output = $this->_stream ? false : $log_output;
	}

	/**
	 * Execute the export
	 */
	public function exec() {

	}

	/**
	 * Get Count total products
	 *
	 * @return integer
	 */
	public function get_total_product() {
		global $wpdb;
		$query = "
			SELECT COUNT(DISTINCT(id)) as total
			FROM {$wpdb->posts}
			WHERE post_type IN ('product', 'product_variation')
			AND post_status = 'publish' 
		";

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Get Count export products
	 *
	 * @return integer
	 */
	public function get_total_export_product() {
		global $wpdb;
		$query               = $this->build_total_query();
		$exported_products   = $wpdb->get_results( $query );
		$nb_exported_product = count( $exported_products );
		if ( $this->_variation && in_array( 'variable', $this->_product_types ) ) {
			$variation_query     = $this->build_total_query( true );
			$exported_variations = $wpdb->get_results( $variation_query );
			$nb_exported_product += count( $exported_variations );
		}

		return $nb_exported_product;
	}

	/**
	 * Get the products to export
	 *
	 * @return array IDs product
	 */
	private function _get_export_ids() {
		global $wpdb;
		$query = $this->build_total_query();
		if ( $this->_limit > 0 ) {
			if ( $this->_offset > 0 ) {
				$query .= " LIMIT " . $this->_offset . ", " . $this->_limit;
			} else {
				$query .= " LIMIT 0, " . $this->_limit;
			}
		}

		return $wpdb->get_results( $query );
	}

	/**
	 * Get Count export product
	 *
	 * @param boolean $variation count variation product
	 *
	 * @return string
	 */
	public function build_total_query( $variation = false ) {
		global $wpdb;
		$query = "
            SELECT DISTINCT(id)
			FROM {$wpdb->posts} AS p
			INNER JOIN {$wpdb->postmeta} AS pm ON p.id = pm.post_id
		";
		if ( ! $variation ) {
			$query .= "
                INNER JOIN {$wpdb->term_relationships} AS tr ON tr.object_id = p.id 
			    INNER JOIN {$wpdb->terms} AS t ON t.term_id = tr.term_taxonomy_id
			";
		}
		if ( $this->_selection ) {
			if ( $variation ) {
				$query .= " INNER JOIN " . $wpdb->prefix . "lengow_product lp ON lp.product_id = p.post_parent ";
			} else {
				$query .= " INNER JOIN " . $wpdb->prefix . "lengow_product lp ON lp.product_id = p.id ";
			}
		}
		// Specific conditions
		$where   = array();
		$where[] = "p.post_status = 'publish'";
		if ( $variation ) {
			$where[] = "p.post_type = 'product_variation'";
		} else {
			$where[] = "p.post_type = 'product'";
		}
		if ( ! $this->_out_of_stock ) {
			$where[] = "((
                meta_key = '_stock_status' AND meta_value = 'instock'
                ) OR ( meta_key = '_manage_stock' AND meta_value = 'yes' AND p.id IN
                    (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_stock' AND meta_value > 0)
                ) OR (   
                    p.id NOT IN 
                    (SELECT post_id FROM {$wpdb->postmeta}
                        WHERE meta_key = '_stock_status' AND meta_value IN ('instock', 'outofstock'))
                    AND p.post_parent IN
                    (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_manage_stock' AND meta_value = 'yes')
                    AND p.post_parent IN
                    (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_stock' AND meta_value > 0)
                ) OR (   
                    p.id NOT IN 
                    (SELECT post_id FROM {$wpdb->postmeta} 
                        WHERE meta_key = '_stock_status' AND meta_value IN ('instock', 'outofstock'))
                    AND p.post_parent IN
                    (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_manage_stock' AND meta_value = 'no')  
            ))";
		}
		if ( count( $this->_product_types ) > 0 && ! $variation ) {
			$where[] = "t.name IN ('" . join( "','", $this->_product_types ) . "')";
		}
		if ( count( $this->_product_ids ) > 0 && ! $variation ) {
			$where[] = "p.id IN (" . join( ',', $this->_product_ids ) . ")";
		}
		if ( count( $where ) > 0 ) {
			$query .= " WHERE " . join( ' AND ', $where );
		}
		$query .= " ORDER BY id ASC";

		return $query;
	}
}

