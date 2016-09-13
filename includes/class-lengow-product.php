<?php
/**
 * Get all product data for export feed
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
 * Lengow_Product Class.
 */
class Lengow_Product {

	/**
	 * Instance of WC_Product_Simple, WC_Product_External, WC_Product_Grouped, WC_Product_Variable
	 */
	public $product;

	/**
	 * Default fields for export
	 */
	public static $EXCLUDES = array(
		'_thumbnail_id',
		'_stock_status',
		'_downloadable',
		'_virtual',
		'_product_image_gallery',
		'_regular_price',
		'_sale_price',
		'_tax_status',
		'_tax_class',
		'_purchase_note',
		'_featured',
		'_weight',
		'_sku',
		'_product_attributes',
		'_sale_price_dates_from',
		'_sale_price_dates_to',
		'_price',
		'_stock',
		'_manage_stock',
		'_upsell_ids',
		'_default_attributes',
		'_wp_old_slug',
		'_edit_lock',
		'_min_price_variation_id',
		'_max_price_variation_id',
		'_min_regular_price_variation_id',
		'_max_regular_price_variation_id',
		'_min_sale_price_variation_id',
		'_max_sale_price_variation_id',
		'_edit_last',
		'_crosssell_ids',
	);

	/**
	 * Construct a new Lengow Product
	 *
	 * @param integer $product_id WooCommerce product id
	 *
	 * @throws Lengow_Exception Unable to find product
	 */
	public function __construct( $product_id ) {
		$this->product = get_product( $product_id );
		if ( $this->product == ''
		     || ! in_array( $this->product->post->post_type, array( 'product', 'product_variation' ) )
		) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message( 'log.export.unable_to_find_product', array(
					'product_id' => $product_id
				) )
			);
		}
	}

	/**
	 * Get data of product
	 *
	 * @param string $name
	 *
	 * @return string the data
	 */
	public function get_data( $name ) {
		global $woocommerce;
		switch ( $name ) {
			case 'id':
				return '';
			case 'sku':
				return '';
			case 'name':
				return '';
			case 'quantity':
				return '';
			case 'availability':
				return '';
			case 'is_virtual':
				return '';
			case 'is_downloadable':
				return '';
			case 'is_featured':
				return '';
			case 'is_on_sale':
				return '';
			case 'average_rating':
				return '';
			case 'category':
				return '';
			case 'status':
				return '';
			case 'url':
				return '';
			case 'price_excl_tax':
				return '';
			case 'price_incl_tax':
				return '';
			case 'price_before_discount_excl_tax':
				return '';
			case 'price_before_discount_incl_tax':
				return '';
			case 'discount_amount':
				return '';
			case 'discount_percent':
				return '';
			case 'discount_start_date':
				return '';
			case 'discount_end_date':
				return '';
			case 'in_stock':
				return '';
			case 'price_shipping':
				return '';
			case 'currency':
				return '';
			case 'image_product':
				return '';
			case 'image_url_1':
			case 'image_url_2':
			case 'image_url_3':
			case 'image_url_4':
			case 'image_url_5':
			case 'image_url_6':
			case 'image_url_7':
			case 'image_url_8':
			case 'image_url_9':
			case 'image_url_10':
				return '';
			case 'type':
				return '';
			case 'parent_id':
				return '';
			case 'variation':
				return '';
			case 'language':
				return '';
			case 'description':
				return '';
			case 'description_html':
				return '';
			case 'description_short':
				return '';
			case 'description_short_html':
				return '';
			case 'tags':
				return '';
			case 'weight':
				return '';
			case 'dimensions':
				return '';
			default:
				if ( in_array( $name, Lengow_Export::$ATTRIBUTES ) ) {
					return Lengow_Main::clean_data( $this->_get_attribute_data( $name ) );
				} elseif ( in_array( $name, Lengow_Export::$POST_METAS ) ) {
					return Lengow_Main::clean_data( $this->_get_post_meta_data( $name ) );
				}

				return '';
		}
	}

	/**
	 * Get all attribute keys for export
	 *
	 * @return array
	 */
	public static function get_attributes() {
		global $wpdb;
		$return = array();
		$sql    = "
            SELECT *
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_product_attributes'
        ";
		foreach ( $wpdb->get_results( $sql ) as $result ) {
			$attributes = unserialize( $result->meta_value );
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $key => $attr ) {
					if ( ! in_array( $key, $return ) ) {
						$return[] = $key;
					}
				}
			}
		}

		return $return;
	}

	/**
	 * Get all post meta keys for export
	 *
	 * @return array
	 */
	public static function get_post_metas() {
		global $wpdb;
		$return = array();
		$sql    = "
            SELECT DISTINCT(meta_key)
            FROM {$wpdb->postmeta} AS pm
            INNER JOIN {$wpdb->posts} AS p ON p.id = pm.post_id
            AND p.post_type = 'product'
        ";
		foreach ( $wpdb->get_results( $sql ) as $result ) {
			if ( ! in_array( $result->meta_key, self::$EXCLUDES ) ) {
				if ( ! in_array( $result->meta_key, $return ) ) {
					$return[] = $result->meta_key;
				}
			}
		}

		return $return;
	}

	/**
	 * Get data for attribute
	 *
	 * @param string $name attribute name
	 *
	 * @return string
	 */
	private function _get_attribute_data( $name = null ) {
		if ( $name == null ) {
			return '';
		}
		if ( $this->product->product_type == 'variation' ) {
			$name            = 'attribute_' . $name;
			$variation_datas = $this->product->get_variation_attributes();
			if ( array_key_exists( $name, $variation_datas ) ) {
				return $variation_datas[ $name ];
			}
		} elseif ( $this->product->product_type != 'variable' ) {
			return $this->product->get_attribute( $name );
		}
	}

	/**
	 * Get data for post metas
	 *
	 * @param string $name post meta name
	 *
	 * @return string
	 */
	private function _get_post_meta_data( $name = null ) {
		if ( $name == null ) {
			return '';
		}
		if ( $this->product->variation_id != '' ) {
			$post_meta = get_post_meta( $this->product->variation_id, $name );
		} else {
			$post_meta = get_post_meta( $this->product->id, $name );
		}
		if ( isset( $post_meta[0] ) ) {
			return is_array( $post_meta[0] ) ? implode( ",", $post_meta[0] ) : $post_meta[0];
		} else {
			return '';
		}
	}
}

