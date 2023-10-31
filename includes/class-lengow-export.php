<?php
/**
 * All components to generate Lengow feed
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
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Export Class.
 */
class Lengow_Export {

	/* Export GET params */
	const PARAM_TOKEN = 'token';
	const PARAM_MODE = 'mode';
	const PARAM_FORMAT = 'format';
	const PARAM_STREAM = 'stream';
	const PARAM_OFFSET = 'offset';
	const PARAM_LIMIT = 'limit';
	const PARAM_SELECTION = 'selection';
	const PARAM_OUT_OF_STOCK = 'out_of_stock';
	const PARAM_PRODUCT_IDS = 'product_ids';
	const PARAM_VARIATION = 'variation';
	const PARAM_PRODUCT_TYPES = 'product_types';
	const PARAM_LEGACY_FIELDS = 'legacy_fields';
	const PARAM_LOG_OUTPUT = 'log_output';
	const PARAM_UPDATE_EXPORT_DATE = 'update_export_date';
	const PARAM_GET_PARAMS = 'get_params';

	/* Legacy export GET params for old versions */
	const PARAM_LEGACY_SELECTION = 'all_products';
	const PARAM_LEGACY_PRODUCT_TYPES = 'product_type';

	/**
	 * @var array default fields for export.
	 */
	public static $default_field;

	/**
	 * @var array|null export product attributes.
	 */
	public static $attributes;

	/**
	 * @var array|null  export product post metas.
	 */
	public static $post_metas;

	/**
	 * @var array all available params for export.
	 */
	public static $export_params = array(
		self::PARAM_MODE,
		self::PARAM_FORMAT,
		self::PARAM_STREAM,
		self::PARAM_OFFSET,
		self::PARAM_LIMIT,
		self::PARAM_SELECTION,
		self::PARAM_OUT_OF_STOCK,
		self::PARAM_PRODUCT_IDS,
		self::PARAM_PRODUCT_TYPES,
		self::PARAM_VARIATION,
		self::PARAM_LEGACY_FIELDS,
		self::PARAM_LOG_OUTPUT,
		self::PARAM_UPDATE_EXPORT_DATE,
		self::PARAM_GET_PARAMS,
	);

	/**
	 * @var array new fields for v3.
	 */
	private $new_fields = array(
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
		'shipping_class'                 => 'shipping_class',
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
	 * @var array legacy fields for export.
	 */
	private $legacy_fields = array(
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
		'shipping_class'        => 'shipping_class',
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
	 * @var string format of exported files.
	 */
	private $format;

	/**
	 * @var boolean stream file or generate a file.
	 */
	private $stream;

	/**
	 * @var integer offset of total product.
	 */
	private $offset;

	/**
	 * @var integer limit number of exported product.
	 */
	private $limit;

	/**
	 * @var boolean export product selection.
	 */
	private $selection;

	/**
	 * @var boolean export out of stock product.
	 */
	private $out_of_stock;

	/**
	 * @var boolean use legacy fields.
	 */
	private $legacy;

	/**
	 * @var array list of product id separate with comma.
	 */
	private $product_ids = array();

	/**
	 * @var array product types separate with comma.
	 */
	private $product_types = array();

	/**
	 * @var boolean export product variation.
	 */
	private $variation;

	/**
	 * @var boolean See logs or not.
	 */
	private $log_output;

	/**
	 * @var boolean change last export date in database.
	 */
	private $update_export_date;

	/**
	 * Construct new Lengow export.
	 *
	 * @param array $params optional options
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
	 * boolean update_export_date Change last export date in database (1) or not (0)
	 */
	public function __construct( $params = array() ) {
		$this->stream             = isset( $params[ self::PARAM_STREAM ] )
			? $params[ self::PARAM_STREAM ]
			: ! (bool) Lengow_Configuration::get( Lengow_Configuration::EXPORT_FILE_ENABLED );
		$this->offset             = isset( $params[ self::PARAM_OFFSET ] )
			? $params[ self::PARAM_OFFSET ]
			: 0;
		$this->limit              = isset( $params[ self::PARAM_LIMIT ] )
			? $params[ self::PARAM_LIMIT ]
			: 0;
		$this->selection          = isset( $params[ self::PARAM_SELECTION ] )
			? $params[ self::PARAM_SELECTION ]
			: (bool) Lengow_Configuration::get( Lengow_Configuration::SELECTION_ENABLED );
		$this->out_of_stock       = isset( $params[ self::PARAM_OUT_OF_STOCK ] )
			? $params[ self::PARAM_OUT_OF_STOCK ]
			: true;
		$this->variation          = isset( $params[ self::PARAM_VARIATION ] )
			? $params[ self::PARAM_VARIATION ]
			: true;
		$this->update_export_date = ! isset( $params[ self::PARAM_UPDATE_EXPORT_DATE ] )
		                            || $params[ self::PARAM_UPDATE_EXPORT_DATE ];
		$this->legacy             = isset( $params[ self::PARAM_LEGACY_FIELDS ] )
			? $params[ self::PARAM_LEGACY_FIELDS ]
			: null;
		$this->set_format(
			isset( $params[ self::PARAM_FORMAT ] ) ? $params[ self::PARAM_FORMAT ] : false
		);
		$this->set_product_ids(
			isset( $params[ self::PARAM_PRODUCT_IDS ] ) ? $params[ self::PARAM_PRODUCT_IDS ] : false
		);
		$this->set_product_types(
			isset( $params[ self::PARAM_PRODUCT_TYPES ] ) ? $params[ self::PARAM_PRODUCT_TYPES ] : false
		);
		$this->set_log_output(
			isset( $params[ self::PARAM_LOG_OUTPUT ] ) ? $params[ self::PARAM_LOG_OUTPUT ] : true
		);
	}

	/**
	 * Execute the export.
	 */
	public function exec() {
		try {
			// clean logs.
			Lengow_Main::clean_log();
			Lengow_Main::log(
				Lengow_Log::CODE_EXPORT,
				Lengow_Main::set_log_message( 'log.export.start' ),
				$this->log_output
			);
			// set legacy fields option.
			$this->set_legacy_fields();
			// get fields to export.
			$fields = $this->get_fields();
			// get products to be exported.
			$products = $this->get_export_ids();
			Lengow_Main::log(
				Lengow_Log::CODE_EXPORT,
				Lengow_Main::set_log_message(
					'log.export.nb_product_found',
					array( 'nb_product' => count( $products ) )
				),
				$this->log_output
			);
			$exported = $this->export( $products, $fields );
			if ( $this->update_export_date ) {
				Lengow_Configuration::update_value( Lengow_Configuration::LAST_UPDATE_EXPORT, time() );
			}
			Lengow_Main::log(
				Lengow_Log::CODE_EXPORT,
				Lengow_Main::set_log_message( 'log.export.end' ),
				$this->log_output
			);
		} catch ( Lengow_Exception $e ) {
			$error_message = $e->getMessage();
		} catch ( Exception $e ) {
			$error_message = '[WooCommerce error]: "' . $e->getMessage()
			                 . '" in ' . $e->getFile() . ' on line ' . $e->getLine();
		}
		if ( isset( $error_message ) ) {
			$decoded_message = Lengow_Main::decode_log_message( $error_message, Lengow_Translation::DEFAULT_ISO_CODE );
			Lengow_Main::log(
				Lengow_Log::CODE_EXPORT,
				Lengow_Main::set_log_message(
					'log.export.export_failed',
					array( 'decoded_message' => $decoded_message )
				),
				$this->log_output
			);
		}
                if ($this->stream) {
                    echo $exported;
                    exit();
                }
	}

	/**
	 * Get Count total products.
	 *
	 * @return integer
	 */
	public function get_total_product() {
		global $wpdb;
		$query = '
			SELECT COUNT(*) AS total FROM ( (
				SELECT DISTINCT(id) AS id_product
				FROM ' . $wpdb->posts . '
    			WHERE post_status = \'publish\' AND post_type = \'product\'
    		) UNION ALL (
   				SELECT DISTINCT(id) AS id_product
   				FROM ' . $wpdb->posts . '
   				WHERE post_status = \'publish\' AND post_type = \'product_variation\' AND post_parent > 0
   			) ) AS tmp
		';

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Get Count export products.
	 *
	 * @return integer
	 */
	public function get_total_export_product() {
		global $wpdb;
		if ( $this->variation && in_array( 'variable', $this->product_types, true ) ) {
			$query = ' SELECT COUNT(*) AS total FROM ( ( ';
			$query .= $this->build_total_query();
			$query .= ' ) UNION ALL ( ';
			$query .= $this->build_total_query( true );
			$query .= ' ) ) AS tmp';
		} else {
			$query = ' SELECT COUNT(*) AS total FROM ( ' . $this->build_total_query() . ' ) AS tmp';
		}

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Get all export available parameters.
	 *
	 * @return string
	 */
	public static function get_export_params() {
		$params = array();
		foreach ( self::$export_params as $param ) {
			switch ( $param ) {
				case self::PARAM_MODE:
					$authorized_value = array( 'size', 'total' );
					$type             = 'string';
					$example          = 'size';
					break;
				case self::PARAM_FORMAT:
					$authorized_value = Lengow_Feed::$available_formats;
					$type             = 'string';
					$example          = Lengow_Feed::FORMAT_CSV;
					break;
				case self::PARAM_OFFSET:
				case self::PARAM_LIMIT:
					$authorized_value = 'all integers';
					$type             = 'integer';
					$example          = 100;
					break;
				case self::PARAM_PRODUCT_IDS:
					$authorized_value = 'all integers';
					$type             = 'string';
					$example          = '101,108,215';
					break;
				case self::PARAM_PRODUCT_TYPES:
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
				'example'           => $example,
			);
		}

		return json_encode( $params );
	}

	/**
	 * Set legacy fields or not.
	 */
	private function set_legacy_fields() {
		if ( null === $this->legacy ) {
			$merchant_status = Lengow_Sync::get_status_account();
			if ( $merchant_status && isset( $merchant_status['legacy'] ) ) {
				$this->legacy = $merchant_status['legacy'];
			} else {
				$this->legacy = false;
			}
		}
		self::$default_field = $this->legacy ? $this->legacy_fields : $this->new_fields;
	}

	/**
	 * Set format to export.
	 *
	 * @param string|false $format export format
	 */
	private function set_format( $format ) {
		if ( $format ) {
			$this->format = ! in_array( $format, Lengow_Feed::$available_formats, true ) ? null : $format;
		}
		if ( null === $this->format ) {
			$this->format = Lengow_Configuration::get( Lengow_Configuration::EXPORT_FORMAT );
		}
	}

	/**
	 * Set product ids to export.
	 *
	 * @param string|false $product_ids product ids to export
	 */
	private function set_product_ids( $product_ids ) {
		if ( $product_ids ) {
			$exported_ids = explode( ',', $product_ids );
			foreach ( $exported_ids as $id ) {
				if ( is_numeric( $id ) && $id > 0 ) {
					$this->product_ids[] = (int) $id;
				}
			}
		}
	}

	/**
	 * Set product types to export.
	 *
	 * @param string|false $product_types product types to export
	 */
	private function set_product_types( $product_types ) {
		if ( $product_types ) {
			$exported_types = explode( ',', $product_types );
			foreach ( $exported_types as $type ) {
				if ( array_key_exists( $type, Lengow_Main::$product_types ) ) {
					$this->product_types[] = $type;
				}
			}
		}
		if ( empty( $this->product_types ) ) {
			$this->product_types = Lengow_Configuration::get_product_types();
		}
	}

	/**
	 * Set Log output for export.
	 *
	 * @param boolean $log_output see logs or not
	 */
	private function set_log_output( $log_output ) {
		$this->log_output = $this->stream ? false : $log_output;
	}

	/**
	 * Export products.
	 *
	 * @param array $products list of products to be exported
	 * @param array $fields list of fields
         *
	 *
	 * @throws Lengow_Exception Export folder not writable
         *
         * @return string
	 */
	private function export( $products, $fields ) {
		$product_count = 0;
		$feed          = new Lengow_Feed( $this->stream, $this->format, $this->legacy );
		$writed = $feed->write( Lengow_Feed::HEADER, $fields );
		$is_first = true;
		// get the maximum of character for yaml format.
		$max_character = 0;
		foreach ( $fields as $field ) {
			if ( strlen( $field ) > $max_character ) {
				$max_character = strlen( $field );
			}
		}
		foreach ( $products as $p ) {
			$product_data = array();
			if ( (int) $p->id_product_attribute > 0 ) {
				if ( 0 === (int) $p->id_product ) {
					continue;
				}
				$product = new Lengow_Product( (int) $p->id_product_attribute );
			} else {
				$product = new Lengow_Product( (int) $p->id_product );
			}
			foreach ( $fields as $field ) {
				if ( isset( self::$default_field[ $field ] ) ) {
					$product_data[ $field ] = $product->get_data( self::$default_field[ $field ] );
				} else {
					$product_data[ $field ] = $product->get_data( $field );
				}
			}
			// write parent product.
			$writed .= $feed->write( Lengow_Feed::BODY, $product_data, $is_first, $max_character );
			$product_count ++;
			if ( $product_count > 0 && 0 === $product_count % 50 ) {
				Lengow_Main::log(
					Lengow_Log::CODE_EXPORT,
					Lengow_Main::set_log_message(
						'log.export.count_product',
						array( 'product_count' => $product_count )
					),
					$this->log_output
				);
			}
			// clean data for next product.
			unset( $product_data, $product );
			if ( function_exists( 'gc_collect_cycles' ) ) {
				gc_collect_cycles();
			}
			$is_first = false;
		}
                $writed .= $feed->write(Lengow_Feed::FOOTER );
		$success = $feed->end();
		if ( ! $success ) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'log.export.error_folder_not_writable' )
			);
		}
		if ( ! $this->stream ) {
			$feed_url = $feed->get_url();
			if ( $feed_url && 'cli' !== php_sapi_name() ) {
				Lengow_Main::log(
					Lengow_Log::CODE_EXPORT,
					Lengow_Main::set_log_message(
						'log.export.your_feed_available_here',
						array( 'feed_url' => $feed_url )
					),
					$this->log_output
				);
			}
		}
                return $writed;
	}

	/**
	 * Get fields to export.
	 *
	 * @return array
	 */
	private function get_fields() {
		$fields = array();
		// check field name to lower to avoid duplicates
		$formatted_fields = array();
		foreach ( self::$default_field as $key => $value ) {
			$fields[]           = $key;
			$formatted_fields[] = Lengow_Feed::format_fields( $key, $this->format, $this->legacy );
		}
		self::$attributes = Lengow_Product::get_attributes();
		foreach ( self::$attributes as $attribute ) {
			$formatted_attribute = Lengow_Feed::format_fields( $attribute, $this->format, $this->legacy );
			if ( ! in_array( $formatted_attribute, $formatted_fields, true ) ) {
				$fields[]           = $attribute;
				$formatted_fields[] = $formatted_attribute;
			}
		}
		self::$post_metas = Lengow_Product::get_post_metas();
		foreach ( self::$post_metas as $post_meta ) {
			$formatted_post_meta = Lengow_Feed::format_fields( $post_meta, $this->format, $this->legacy );
			if ( ! in_array( $formatted_post_meta, $formatted_fields, true ) ) {
				$fields[]           = $post_meta;
				$formatted_fields[] = $formatted_post_meta;
			}
		}

		return $fields;
	}

	/**
	 * Get the products to export.
	 *
	 * @return array
	 */
	private function get_export_ids() {
		global $wpdb;
		if ( $this->variation && in_array( 'variable', $this->product_types, true ) ) {
			$query = ' SELECT * FROM ( ( ';
			$query .= $this->build_total_query();
			$query .= ' ) UNION ALL ( ';
			$query .= $this->build_total_query( true );
			$query .= ' ) ) AS tmp ORDER BY id_product, id_product_attribute';
		} else {
			$query = $this->build_total_query();
		}
		if ( $this->limit > 0 ) {
			if ( $this->offset > 0 ) {
				$query .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
			} else {
				$query .= ' LIMIT 0, ' . $this->limit;
			}
		}

		return $wpdb->get_results( $query );
	}

	/**
	 * Get Count export product.
	 *
	 * @param boolean $variation count variation product
	 *
	 * @return string
	 */
	private function build_total_query( $variation = false ) {
		global $wpdb;
		if ( $variation ) {
			$query = 'SELECT DISTINCT(p.post_parent) AS id_product, p.id AS id_product_attribute ';
		} else {
			$query = 'SELECT DISTINCT(p.id) AS id_product, 0 AS id_product_attribute ';
		}
		$query .= '
			FROM ' . $wpdb->posts . ' AS p
			INNER JOIN ' . $wpdb->postmeta . ' AS pm ON p.id = pm.post_id
		';
		if ( ! $variation ) {
			$query .= '
				INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON tr.object_id = p.id
				INNER JOIN ' . $wpdb->terms . ' AS t ON t.term_id = tr.term_taxonomy_id
			';
		}
		if ( $this->selection ) {
			if ( $variation ) {
				$query .= ' INNER JOIN ' . $wpdb->prefix . 'lengow_product lp ON lp.product_id = p.post_parent ';
			} else {
				$query .= ' INNER JOIN ' . $wpdb->prefix . 'lengow_product lp ON lp.product_id = p.id ';
			}
		}
		// specific conditions.
		$where   = array();
		$where[] = 'p.post_status = \'publish\'';
		if ( $variation ) {
			$where[] = 'p.post_parent > 0';
			$where[] = 'p.post_type = \'product_variation\'';
		} else {
			$where[] = "p.post_type = 'product'";
		}
		if ( ! $this->out_of_stock ) {
			$where[] = '((
				meta_key = \'_stock_status\' AND meta_value = \'instock\'
				) OR ( meta_key = \'_manage_stock\' AND meta_value = \'yes\' AND p.id IN
					(SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key = \'_stock\' AND meta_value > 0)
				) OR (
					p.id NOT IN
					(SELECT post_id FROM ' . $wpdb->postmeta . '
						WHERE meta_key = \'_stock_status\' AND meta_value IN (\'instock\', \'outofstock\'))
					AND p.post_parent IN
						(SELECT post_id FROM ' . $wpdb->postmeta . '
							WHERE meta_key = \'_manage_stock\' AND meta_value = \'yes\')
					AND p.post_parent IN
						(SELECT post_id FROM ' . $wpdb->postmeta . '
							WHERE meta_key = \'_stock\' AND meta_value > 0)
				) OR (
					p.id NOT IN
					(SELECT post_id FROM ' . $wpdb->postmeta . '
						WHERE meta_key = \'_stock_status\' AND meta_value IN (\'instock\', \'outofstock\'))
					AND p.post_parent IN
					(SELECT post_id FROM ' . $wpdb->postmeta . '
						WHERE meta_key = \'_manage_stock\' AND meta_value = \'no\')
			))';
		}
		if ( ! empty( $this->product_types ) && ! $variation ) {
			$where[] = 't.name IN (\'' . implode( "','", $this->product_types ) . '\')';
		}
		if ( ! empty( $this->product_ids ) ) {
			if ( $variation ) {
				$where[] = 'p.post_parent IN (' . implode( ',', $this->product_ids ) . ')';
			} else {
				$where[] = 'p.id IN (' . implode( ',', $this->product_ids ) . ')';
			}
		}
		if ( ! empty( $where ) ) {
			$query .= ' WHERE ' . implode( ' AND ', $where );
		}
		$query .= ' ORDER BY id_product ASC';

		return $query;
	}
}