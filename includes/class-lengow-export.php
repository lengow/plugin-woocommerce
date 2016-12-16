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
	 * Default fields for export
	 */
	public static $default_field;

	/**
	 * Export product attributes
	 */
	public static $attributes = null;

	/**
	 * Export product post metas
	 */
	public static $post_metas = null;

	/**
	 * All available params for export
	 */
	public static $export_params = array(
		'mode',
		'format',
		'stream',
		'offset',
		'limit',
		'selection',
		'out_of_stock',
		'product_ids',
		'product_types',
		'variation',
		'legacy_fields',
		'log_output',
		'update_export_date',
		'get_params'
	);

	/**
	 * New fields for v3
	 */
	private $_new_fields = array(
		'id'                             => 'id',
		'sku'                            => 'sku',
		'name'                           => 'name',
		'quantity'                       => 'quantity',
		'availability'                   => 'availability',
		'is_virtual'                     => 'is_virtual',
		'is_downloadable'                => 'is_downloadable',
		'is_featured'                    => 'is_featured',
		'is_on_sale'                     => 'is_on_sale',
		'average_rating'                 => 'average_rating',
		'rating_count'                   => 'rating_count',
		'category'                       => 'category',
		'status'                         => 'status',
		'url'                            => 'url',
		'price_excl_tax'                 => 'price_excl_tax',
		'price_incl_tax'                 => 'price_incl_tax',
		'price_before_discount_excl_tax' => 'price_before_discount_excl_tax',
		'price_before_discount_incl_tax' => 'price_before_discount_incl_tax',
		'discount_amount_excl_tax'       => 'discount_amount_excl_tax',
		'discount_amount_incl_tax'       => 'discount_amount_incl_tax',
		'discount_percent'               => 'discount_percent',
		'discount_start_date'            => 'discount_start_date',
		'discount_end_date'              => 'discount_end_date',
		'shipping_cost'                  => 'price_shipping',
		'currency'                       => 'currency',
		'image_product'                  => 'image_product',
		'image_url_1'                    => 'image_url_1',
		'image_url_2'                    => 'image_url_2',
		'image_url_3'                    => 'image_url_3',
		'image_url_4'                    => 'image_url_4',
		'image_url_5'                    => 'image_url_5',
		'image_url_6'                    => 'image_url_6',
		'image_url_7'                    => 'image_url_7',
		'image_url_8'                    => 'image_url_8',
		'image_url_9'                    => 'image_url_9',
		'image_url_10'                   => 'image_url_10',
		'type'                           => 'type',
		'parent_id'                      => 'parent_id',
		'variation'                      => 'variation',
		'language'                       => 'language',
		'description'                    => 'description',
		'description_html'               => 'description_html',
		'description_short'              => 'description_short',
		'description_short_html'         => 'description_short_html',
		'tags'                           => 'tags',
		'weight'                         => 'weight',
		'dimensions'                     => 'dimensions',
	);

	/**
	 * Legacy fields for export
	 */
	private $_legacy_fields = array(
		'id_product'            => 'id',
		'name_product'          => 'name',
		'sku_product'           => 'sku',
		'is_virtual'            => 'is_virtual',
		'is_downloadable'       => 'is_downloadable',
		'price'                 => 'price_excl_tax',
		'price_wt'              => 'price_incl_tax',
		'price_before_discount' => 'price_before_discount_excl_tax',
		'discount_amount'       => 'discount_amount_excl_tax',
		'discount_percent'      => 'discount_percent',
		'in_stock'              => 'is_in_stock',
		'weight'                => 'weight',
		'dimensions'            => 'dimensions',
		'short_description'     => 'description_short',
		'description'           => 'description_html',
		'url_product'           => 'url',
		'image_product'         => 'image_product',
		'category'              => 'category',
		'tags'                  => 'tags',
		'available_product'     => 'available_product',
		'quantity'              => 'quantity',
		'shipping_price'        => 'shipping_cost',
		'id_parent'             => 'parent_id',
		'is_featured'           => 'is_featured',
		'is_on_sale'            => 'is_on_sale',
		'average_rating'        => 'average_rating',
		'rating_count'          => 'rating_count',
		'product_variation'     => 'variation',
		'image_1'               => 'image_url_1',
		'image_2'               => 'image_url_2',
		'image_3'               => 'image_url_3',
		'image_4'               => 'image_url_4',
		'image_5'               => 'image_url_5',
		'image_6'               => 'image_url_6',
		'image_7'               => 'image_url_7',
		'image_8'               => 'image_url_8',
		'image_9'               => 'image_url_9',
		'image_10'              => 'image_url_10',
	);

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
	 * @var boolean Use legacy fields
	 */
	private $_legacy;

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
		$this->_stream             = isset( $params['stream'] ) ? $params['stream'] : true;
		$this->_offset             = isset( $params['offset'] ) ? $params['offset'] : 0;
		$this->_limit              = isset( $params['limit'] ) ? $params['limit'] : 0;
		$this->_selection          = isset( $params['selection'] )
			? $params['selection']
			: (bool) Lengow_Configuration::get( 'lengow_selection_enabled' );
		$this->_out_of_stock       = isset( $params['out_of_stock'] ) ? $params['out_of_stock'] : true;
		$this->_variation          = isset( $params['variation'] ) ? $params['variation'] : true;
		$this->_update_export_date = isset( $params['update_export_date'] )
			? (bool) $params['update_export_date']
			: true;
		$this->_legacy             = isset( $params['legacy_fields'] ) ? $params['legacy_fields'] : null;
		$this->_set_format( isset( $params['format'] ) ? $params['format'] : 'csv' );
		$this->_set_product_ids( isset( $params['product_ids'] ) ? $params['product_ids'] : false );
		$this->_set_product_types( isset( $params['product_types'] ) ? $params['product_types'] : false );
		$this->_set_log_output( isset( $params['log_output'] ) ? $params['log_output'] : true );
	}

	/**
	 * Set legacy fields or not
	 */
	private function _set_legacy_fields() {
		if ( is_null( $this->_legacy ) ) {
			$result = Lengow_Connector::query_api( 'get', '/v3.0/subscriptions' );
			if ( isset( $result->legacy ) ) {
				$this->_legacy = (bool) $result->legacy;
			} else {
				$this->_legacy = false;
			}
		}
		self::$default_field = $this->_legacy ? $this->_legacy_fields : $this->_new_fields;
	}

	/**
	 * Set format to export
	 *
	 * @param string $format The export format
	 */
	private function _set_format( $format ) {
		$this->_format = ! in_array( $format, Lengow_Feed::$available_formats ) ? 'csv' : $format;
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
				if ( array_key_exists( $type, Lengow_Main::$product_types ) ) {
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
		try {
			// clean logs.
			Lengow_Main::clean_log();
			Lengow_Main::log( 'Export', Lengow_Main::set_log_message( 'log.export.start' ), $this->_log_output );
			// set legacy fields option.
			$this->_set_legacy_fields();
			// get fields to export
			$fields = $this->_get_fields();
			// get products to be exported.
			$products = $this->_get_export_ids();
			Lengow_Main::log(
				'Export',
				Lengow_Main::set_log_message(
					'log.export.nb_product_found',
					array( 'nb_product' => count( $products ) )
				),
				$this->_log_output
			);
			$this->_export( $products, $fields );
			if ( $this->_update_export_date ) {
				Lengow_Configuration::update_value( 'lengow_last_export', time() );
			}
			Lengow_Main::log(
				'Export',
				Lengow_Main::set_log_message( 'log.export.end' ),
				$this->_log_output
			);
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[Wordpress error] "' . $e->getMessage() . '" ' . $e->getFile() . ' | ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			$decoded_message = Lengow_Main::decode_log_message( $error_message, 'en_GB' );
			Lengow_Main::log(
				'Export',
				Lengow_Main::set_log_message(
					'log.export.export_failed',
					array( 'decoded_message' => $decoded_message )
				),
				$this->_log_output
			);
		}
	}

	/**
	 * Export products
	 *
	 * @param array $products list of products to be exported
	 * @param array $fields list of fields
	 *
	 * @throws Lengow_Exception Export folder not writable
	 */
	private function _export( $products, $fields ) {
		$product_count = 0;
		$feed          = new Lengow_Feed( $this->_stream, $this->_format, $this->_legacy );
		$feed->write( 'header', $fields );
		$is_first = true;
		// Get the maximum of character for yaml format.
		$max_character = 0;
		foreach ( $fields as $field ) {
			if ( strlen( $field ) > $max_character ) {
				$max_character = strlen( $field );
			}
		}
		foreach ( $products as $p ) {
			$product_data = array();
			if ( (int) $p->id_product_attribute > 0 ) {
				$product = new Lengow_Product( (int) $p->id_product_attribute );
			} else {
				$product = new Lengow_Product( (int) $p->id_product );
			}
			foreach ( $fields as $field ) {
				if ( isset( Lengow_Export::$default_field[ $field ] ) ) {
					$product_data[ $field ] = $product->get_data( Lengow_Export::$default_field[ $field ] );
				} else {
					$product_data[ $field ] = $product->get_data( $field );
				}
			}
			// write parent product.
			$feed->write( 'body', $product_data, $is_first, $max_character );
			$product_count ++;
			if ( $product_count > 0 && $product_count % 50 == 0 ) {
				Lengow_Main::log(
					'Export',
					Lengow_Main::set_log_message(
						'log.export.count_product',
						array( 'product_count' => $product_count )
					),
					$this->_log_output
				);
			}
			// clean data for next product.
			unset( $product_data, $product );
			if ( function_exists( 'gc_collect_cycles' ) ) {
				gc_collect_cycles();
			}
			$is_first = false;
		}
		$success = $feed->end();
		if ( ! $success ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'log.export.error_folder_not_writable' )
			);
		}
		if ( ! $this->_stream ) {
			$feed_url = $feed->get_url();
			if ( $feed_url && php_sapi_name() != "cli" ) {
				Lengow_Main::log(
					'Export',
					Lengow_Main::set_log_message( 'log.export.your_feed_available_here', array( 'feed_url' => $feed_url ) ),
					$this->_log_output
				);
			}
		}
	}

	/**
	 * Get fields to export
	 *
	 * @return array
	 */
	private function _get_fields() {
		$fields = array();
		foreach ( self::$default_field as $key => $value ) {
			$fields[] = $key;
		}
		self::$attributes = Lengow_Product::get_attributes();
		foreach ( self::$attributes as $attribute ) {
			$fields[] = $attribute;
		}
		self::$post_metas = Lengow_Product::get_post_metas();
		foreach ( self::$post_metas as $post_meta ) {
			$fields[] = $post_meta;
		}

		return $fields;
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
		if ( $this->_variation && in_array( 'variable', $this->_product_types ) ) {
			$query = " SELECT COUNT(*) AS total FROM ( ( ";
			$query .= $this->build_total_query();
			$query .= " ) UNION ( ";
			$query .= $this->build_total_query( true );
			$query .= " ) ) AS tmp";
		} else {
			$query = " SELECT COUNT(*) AS total FROM ( " . $this->build_total_query() . " ) AS tmp";
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Get the products to export
	 *
	 * @return array IDs product
	 */
	private function _get_export_ids() {
		global $wpdb;
		if ( $this->_variation && in_array( 'variable', $this->_product_types ) ) {
			$query = " SELECT * FROM ( ( ";
			$query .= $this->build_total_query();
			$query .= " ) UNION ( ";
			$query .= $this->build_total_query( true );
			$query .= " ) ) AS tmp ORDER BY id_product, id_product_attribute";
		} else {
			$query = $this->build_total_query();
		}
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
		if ( $variation ) {
			$query = "
                SELECT p.post_parent AS id_product, p.id AS id_product_attribute
            ";
		} else {
			$query = "
                SELECT DISTINCT(p.id) AS id_product, 0 AS id_product_attribute
            ";
		}
		$query .= "
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
		// Specific conditions.
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
		if ( count( $this->_product_ids ) > 0 ) {
			if ( $variation ) {
				$where[] = "p.post_parent IN (" . join( ',', $this->_product_ids ) . ")";
			} else {
				$where[] = "p.id IN (" . join( ',', $this->_product_ids ) . ")";
			}
		}
		if ( count( $where ) > 0 ) {
			$query .= " WHERE " . join( ' AND ', $where );
		}
		$query .= " ORDER BY id_product ASC";

		return $query;
	}

	/**
	 * Get all export available parameters
	 *
	 * @return string
	 */
	public static function get_export_params() {
		$params = array();
		foreach ( self::$export_params as $param ) {
			switch ( $param ) {
				case 'mode':
					$authorized_value = array( 'size', 'total' );
					$type             = 'string';
					$example          = 'size';
					break;
				case 'format':
					$authorized_value = Lengow_Feed::$available_formats;
					$type             = 'string';
					$example          = 'csv';
					break;
				case 'offset':
				case 'limit':
					$authorized_value = 'all integers';
					$type             = 'integer';
					$example          = 100;
					break;
				case 'product_ids':
					$authorized_value = 'all integers';
					$type             = 'string';
					$example          = '101,108,215';
					break;
				case 'product_types':
					$types = array();
					foreach ( Lengow_Main::$product_types as $key => $value ) {
						$types[] = $key;
					}
					$authorized_value = $types;
					$type             = 'string';
					$example          = 'simple,variable,external,grouped';
					break;
				default:
					$authorized_value = array( 0, 1 );
					$type             = 'integer';
					$example          = 1;
					break;
			}
			$params[ $param ] = array(
				'authorized_values' => $authorized_value,
				'type'              => $type,
				'example'           => $example
			);
		}

		return json_encode( $params );
	}
}

