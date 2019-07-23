<?php
/**
 * Get all product data for export feed
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
 * @license        https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Product Class.
 */
class Lengow_Product {

	/**
	 * @var array default fields for export.
	 */
	public static $excludes = array(
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
		'_wc_rating_count',
		'_wc_average_rating',
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
	 * @var array API nodes containing relevant data.
	 */
	public static $product_api_nodes = array(
		'marketplace_product_id',
		'marketplace_status',
		'merchant_product_id',
		'marketplace_order_line_id',
		'quantity',
		'amount',
	);

	/**
	 * @var WC_Product WooCommerce product instance.
	 */
	public $product;

	/**
	 * @var WC_Product WooCommerce product parent instance for variation.
	 */
	private $_product_parent;

	/**
	 * @var integer product id
	 */
	private $_product_id;

	/**
	 * @var integer variation id
	 */
	private $_variation_id;

	/**
	 * @var string product type
	 */
	private $_product_type;

	/**
	 * @var array all product prices
	 */
	private $_prices;

	/**
	 * @var array all product images
	 */
	private $_images;

	/**
	 * Construct a new Lengow product.
	 *
	 * @param integer $product_id WooCommerce product id
	 *
	 * @throws Lengow_Exception Unable to find product
	 */
	public function __construct( $product_id ) {
		$this->product = self::get_product( $product_id );
		if ( $this->product === ''
		     || ! in_array( get_post_type( $product_id ), array( 'product', 'product_variation' ) )
		) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message(
					'log.export.error_unable_to_find_product',
					array( 'product_id' => $product_id )
				)
			);
		}
		$this->_product_type   = self::get_product_type( $this->product );
		$this->_product_id     = self::get_product_id( $this->product );
		$this->_variation_id   = self::get_variation_id( $this->product );
		$this->_product_parent = self::get_product_parent( $this->product, $this->_product_type );
		$this->_prices         = $this->_get_prices();
		$this->_images         = $this->_get_images();
	}

	/**
	 * Get data of product.
	 *
	 * @param string $name field name
	 *
	 * @return string the data
	 */
	public function get_data( $name ) {
		switch ( $name ) {
			case 'id':
				return $this->_product_type === 'variation'
					? $this->_product_id . '_' . $this->_variation_id
					: $this->_product_id;
			case 'sku':
				return $this->product->get_sku();
			case 'name':
				return Lengow_Main::clean_data( $this->product->get_title() );
			case 'quantity':
				return (int) $this->product->get_stock_quantity();
			case 'availability':
				return self::get_stock_status( $this->product );
			case 'available_product':
				$availability = $this->product->get_availability();

				return $availability['availability'] !== '' ? ltrim( $availability['availability'] ) : '';
			case 'is_in_stock':
				return (int) $this->product->is_in_stock();
			case 'is_virtual':
				return (int) $this->product->is_virtual();
			case 'is_downloadable':
				return (int) $this->product->is_downloadable();
			case 'is_featured':
				return (int) $this->product->is_featured();
			case 'is_on_sale':
				return (int) $this->product->is_on_sale();
			case 'average_rating':
				return $this->product->get_average_rating();
			case 'rating_count':
				return $this->product->get_rating_count();
			case 'category':
				return $this->_get_categories();
			case 'status':
				return $this->product->is_purchasable() ? 'Enabled' : 'Disabled';
			case 'url':
				return get_permalink( $this->_product_id );
			case 'price_excl_tax':
			case 'price_incl_tax':
			case 'price_before_discount_excl_tax':
			case 'price_before_discount_incl_tax':
			case 'discount_amount_excl_tax':
			case 'discount_amount_incl_tax':
			case 'discount_percent':
			case 'discount_start_date':
			case 'discount_end_date':
				return $this->_prices[ $name ];
			case 'shipping_class':
				return $this->_get_shipping_class();
			case 'price_shipping':
				return $this->_get_price_shipping();
			case 'currency':
				return get_woocommerce_currency();
			case 'image_product':
			case ( preg_match( '`image_url_([0-9]+)`', $name ) ? true : false ):
				return $this->_images[ $name ];
			case 'type':
				if ( $this->_product_type === 'variation' ) {
					return 'child';
				} elseif ( $this->_product_type === 'variable' ) {
					return 'parent';
				} else {
					return $this->_product_type;
				}
			case 'parent_id':
				return $this->_product_type === 'variation' ? $this->_product_id : '';
			case 'variation':
				return $this->_get_variation_list();
			case 'language':
				return get_locale();
			case 'description':
				return Lengow_Main::clean_html(
					Lengow_Main::clean_data(
						self::get_description( $this->product, $this->_product_parent, $this->_product_type )
					)
				);
			case 'description_html':
				return Lengow_Main::clean_data(
					self::get_description( $this->product, $this->_product_parent, $this->_product_type )
				);
			case 'description_short':
				return Lengow_Main::clean_html(
					Lengow_Main::clean_data(
						self::get_short_description( $this->product, $this->_product_parent, $this->_product_type )
					)
				);
			case 'description_short_html':
				return Lengow_Main::clean_data(
					self::get_short_description( $this->product, $this->_product_parent, $this->_product_type )
				);
			case 'tags':
				return $this->_get_tag_list();
			case 'weight':
				return $this->product->has_weight() ? $this->product->get_weight() : '';
			case 'dimensions':
				return $this->product->has_dimensions() ? $this->product->get_dimensions() : '';
			default:
				if ( in_array( $name, Lengow_Export::$attributes ) ) {
					return Lengow_Main::clean_data( $this->_get_attribute_data( $name ) );
				} elseif ( in_array( $name, Lengow_Export::$post_metas ) ) {
					return Lengow_Main::clean_data( $this->_get_post_meta_data( $name ) );
				}

				return '';
		}
	}

	/**
	 * Get product.
	 *
	 * @param integer $product_id WooCommerce product id
	 *
	 * @return WC_Product
	 */
	public static function get_product( $product_id ) {
		return Lengow_Main::get_woocommerce_version() < '3.0'
			? get_product( $product_id )
			: wc_get_product( $product_id );
	}

	/**
	 * Get product type.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 *
	 * @return string
	 */
	public static function get_product_type( $product ) {
		return Lengow_Main::get_woocommerce_version() < '3.0' ? $product->product_type : $product->get_type();
	}

	/**
	 * Get product id.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 *
	 * @return integer
	 */
	public static function get_product_id( $product ) {
		if ( Lengow_Main::get_woocommerce_version() < '3.0' ) {
			$product_id = $product->id;
		} else {
			$product_id = $product->get_type() == 'variation' ? $product->get_parent_id() : $product->get_id();
		}

		return (int) $product_id;
	}

	/**
	 * Get variation id.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 *
	 * @return integer|null
	 */
	public static function get_variation_id( $product ) {
		if ( Lengow_Main::get_woocommerce_version() < '3.0' ) {
			$variation_id = $product->product_type === 'variation' ? $product->variation_id : null;
		} else {
			$variation_id = $product->get_type() === 'variation' ? $product->get_id() : null;
		}

		return ! is_null( $variation_id ) ? (int) $variation_id : null;
	}


	/**
	 * Get parent product for variation
	 *
	 * @param WC_Product $product WooCommerce product instance
	 * @param string $product_type WooCommerce product type
	 *
	 * @return WC_Product|null
	 */
	public static function get_product_parent( $product, $product_type ) {
		return Lengow_Main::get_woocommerce_version() > '3.0' && $product_type === 'variation'
			? wc_get_product( $product->get_parent_id() )
			: null;
	}


	/**
	 * Get regular price.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 *
	 * @return string
	 */
	public static function get_regular_price( $product ) {
		return Lengow_Main::get_woocommerce_version() < '3.0'
			? $product->regular_price
			: $product->get_regular_price();
	}

	/**
	 * Get stock status.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 *
	 * @return string
	 */
	public static function get_stock_status( $product ) {
		return Lengow_Main::get_woocommerce_version() < '3.0'
			? $product->stock_status
			: $product->get_stock_status();
	}

	/**
	 * Get gallery image ids.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 * @param WC_Product|null $product_parent WooCommerce product parent instance
	 * @param string $product_type WooCommerce product type
	 *
	 * @return array
	 */
	public static function get_gallery_image_ids( $product, $product_parent, $product_type ) {
		if ( $product_type === 'variation' && $product_parent ) {
			$gallery_image_ids = Lengow_Main::get_woocommerce_version() < '3.0'
				? $product_parent->product_image_gallery
				: $product_parent->get_gallery_image_ids();
		} else {
			$gallery_image_ids = Lengow_Main::get_woocommerce_version() < '3.0'
				? $product->product_image_gallery
				: $product->get_gallery_image_ids();
		}
		$gallery_image_ids = is_array( $gallery_image_ids ) ? $gallery_image_ids : explode( ',', $gallery_image_ids );

		return $gallery_image_ids;
	}

	/**
	 * Get thumbnail id.
	 *
	 * @param integer $product_id WooCommerce product id
	 * @param integer|null $variation_id WooCommerce variation id
	 * @param string $product_type WooCommerce product type
	 *
	 * @return integer
	 */
	public static function get_thumbnail_id( $product_id, $variation_id, $product_type ) {
		$variation_thumbnail_id = false;
		$thumbnail_id           = get_post_thumbnail_id( $product_id );
		if ( $product_type === 'variation' ) {
			$variation_thumbnail_id = get_post_thumbnail_id( $variation_id );
		}

		return (int) ( $variation_thumbnail_id ? $variation_thumbnail_id : $thumbnail_id );

	}

	/**
	 * Get description.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 * @param WC_Product|null $product_parent WooCommerce product parent instance
	 * @param string $product_type WooCommerce product type
	 *
	 * @return string
	 */
	public static function get_description( $product, $product_parent, $product_type ) {
		if ( Lengow_Main::get_woocommerce_version() < '2.5' ) {
			$description = $product->post->post_content;
		} else if ( Lengow_Main::get_woocommerce_version() < '3.0' ) {
			if ( $product_type === 'variation' && $product->get_variation_description() != null ) {
				$description = $product->get_variation_description();
			} else {
				$description = $product->post->post_content;
			}
		} else {
			if ( $product_type === 'variation' && $product->get_description() == null && $product_parent ) {
				$description = $product_parent->get_description();
			} else {
				$description = $product->get_description();
			}
		}

		return $description;
	}

	/**
	 * Get short description.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 * @param WC_Product|null $product_parent WooCommerce product parent instance
	 * @param string $product_type WooCommerce product type
	 *
	 * @return string
	 */
	public static function get_short_description( $product, $product_parent, $product_type ) {
		if ( Lengow_Main::get_woocommerce_version() < '3.0' ) {
			$short_description = $product->post->post_excerpt;
		} else {
			if ( $product_type === 'variation' && $product->get_short_description() === null && $product_parent ) {
				$short_description = $product_parent->get_short_description();
			} else {
				$short_description = $product->get_short_description();
			}
		}

		return $short_description;
	}

	/**
	 * Decrease product stock.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 * @param integer $quantity quantity to reduce
	 *
	 * @return integer
	 */
	public static function reduce_product_stock( $product, $quantity ) {
		return Lengow_Main::get_woocommerce_version() < '3.0'
			? $product->reduce_stock( $quantity )
			: wc_update_product_stock( $product, $quantity, 'decrease' );
	}

	/**
	 * Get all attribute keys for export.
	 *
	 * @return array
	 */
	public static function get_attributes() {
		global $wpdb;
		$return = array();
		$sql    = 'SELECT * FROM ' . $wpdb->postmeta . ' WHERE meta_key = \'_product_attributes\'';
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
	 * Get all post meta keys for export.
	 *
	 * @return array
	 */
	public static function get_post_metas() {
		global $wpdb;
		$return = array();
		$sql    = '
			SELECT DISTINCT(meta_key)
			FROM ' . $wpdb->postmeta . ' AS pm
			INNER JOIN ' . $wpdb->posts . ' AS p ON p.id = pm.post_id
			AND p.post_type = \'product\'
		';
		foreach ( $wpdb->get_results( $sql ) as $result ) {
			if ( ! in_array( $result->meta_key, self::$excludes ) ) {
				if ( ! in_array( $result->meta_key, $return ) ) {
					$return[] = $result->meta_key;
				}
			}
		}

		return $return;
	}

	/**
	 * Extract cart data from API.
	 *
	 * @param mixed $product API product datas
	 *
	 * @return array
	 */
	public static function extract_product_data_from_api( $product ) {
		$temp = array();
		foreach ( self::$product_api_nodes as $node ) {
			$temp[ $node ] = $product->{$node};
		}
		$temp['price_unit'] = (float) $temp['amount'] / (float) $temp['quantity'];

		return $temp;
	}

	/**
	 * Match product with api datas.
	 *
	 * @param mixed $product_datas all product datas
	 * @param string $marketplace_sku Lengow id of current order
	 * @param boolean $log_output see log or not
	 *
	 * @return integer|false
	 * @throws Lengow_Exception If product is a variable
	 *
	 */
	public static function match_product( $product_datas, $marketplace_sku, $log_output ) {
		$product_id      = false;
		$api_product_ids = array(
			'merchant_product_id'    => $product_datas['merchant_product_id']->id,
			'marketplace_product_id' => $product_datas['marketplace_product_id'],
		);
		$product_field   = $product_datas['merchant_product_id']->field !== null
			? strtolower( (string) $product_datas['merchant_product_id']->field )
			: false;
		// search product foreach value.
		foreach ( $api_product_ids as $attribute_name => $attribute_value ) {
			// remove _FBA from product id.
			$attribute_value = preg_replace( '/_FBA$/', '', $attribute_value );
			if ( empty( $attribute_value ) ) {
				continue;
			}
			// search by field if exists.
			if ( $product_field ) {
				$product_id = self::search_product( $attribute_value, $product_field );
			}
			// search by id or sku.
			if ( ! $product_id ) {
				$product_id = self::search_product( $attribute_value );
				if ( ! $product_id ) {
					$product_id = self::search_product( $attribute_value, 'sku' );
				}
			}
			if ( $product_id ) {
				$lengow_product = self::get_product( $product_id );
				if ( self::get_product_type( $lengow_product ) === 'variable' ) {
					throw new Lengow_Exception(
						Lengow_Main::set_log_message(
							'lengow_log.exception.product_is_a_parent',
							array( 'product_id' => $product_id )
						)
					);
				}
				Lengow_Main::log(
					'Import',
					Lengow_Main::set_log_message(
						'log.import.product_be_found',
						array(
							'product_id'      => $product_id,
							'attribute_name'  => $attribute_name,
							'attribute_value' => $attribute_value,
						)
					),
					$log_output,
					$marketplace_sku
				);
				unset( $lengow_product );
				break;
			}
		}

		return $product_id;
	}

	/**
	 * Search product.
	 *
	 * @param string $attribute_value value for search
	 * @param string $type id to search product (id, sku or other field)
	 *
	 * @return integer|false
	 */
	public static function search_product( $attribute_value, $type = 'id' ) {
		global $wpdb;
		$product_id = false;
		switch ( $type ) {
			case 'id':
				$attribute_value = str_replace( '\_', '_', $attribute_value );
				$attribute_value = str_replace( 'X', '_', $attribute_value );
				$ids             = explode( '_', $attribute_value );
				// if a product variation -> search with product variation id.
				$id = isset( $ids[1] ) ? $ids[1] : $ids[0];
				if ( preg_match( '/^[0-9]*$/', $id ) ) {
					$product_id = $id;
				}
				break;
			case 'sku':
				$sql        = '
				  	SELECT post_id FROM ' . $wpdb->postmeta . ' 
				  	WHERE meta_key = \'_sku\' AND meta_value = \'%s\' LIMIT 1
				';
				$product_id = $wpdb->get_var( $wpdb->prepare( $sql, $attribute_value ) );
				break;
			default:
				$sql        = '
					SELECT post_id FROM ' . $wpdb->postmeta . ' 
					WHERE meta_key = \'%s\' AND meta_value = \'%s\' LIMIT 1
				';
				$product_id = $wpdb->get_var( $wpdb->prepare( $sql, array( $type, $attribute_value ) ) );
				break;
		}
		if ( $product_id ) {
			$product = self::get_product( $product_id );
			if ( $product ) {
				if ( in_array( get_post_type( $product_id ), array( 'product', 'product_variation' ) ) ) {
					if ( self::get_product_type( $product ) === 'variation' ) {
						return self::get_variation_id( $product );
					} else {
						return self::get_product_id( $product );
					}
				}
			}
		}

		return false;
	}

	/**
	 * Publish or Un-publish to Lengow.
	 *
	 * @param integer $product_id the id product
	 * @param integer $value 1 : publish, 0 : unpublish
	 *
	 * @return boolean
	 */
	public static function publish( $product_id, $value ) {
		global $wpdb;
		if ( ! $value ) {
			$wpdb->delete( $wpdb->prefix . 'lengow_product', array( 'product_id' => ( (int) $product_id ) ) );
		} else {
			$sql     = 'SELECT product_id FROM ' . $wpdb->prefix . 'lengow_product WHERE product_id = %d';
			$results = $wpdb->get_results( $wpdb->prepare( $sql, (int) $product_id ) );
			if ( count( $results ) === 0 ) {
				$wpdb->insert( $wpdb->prefix . 'lengow_product', array( 'product_id' => ( (int) $product_id ) ) );
			}
		}

		return true;
	}

	/**
	 * Get Lengow products.
	 *
	 * @return array
	 */
	public static function get_lengow_products() {
		global $wpdb;
		$sql      = 'SELECT * FROM ' . $wpdb->prefix . 'lengow_product';
		$results  = $wpdb->get_results( $sql );
		$products = array();
		foreach ( $results as $value ) {
			$products[ $value->product_id ] = $value->product_id;
		}

		return $products;
	}

	/**
	 * Is Lengow product.
	 *
	 * @param integer $product_id the id product
	 *
	 * @return boolean
	 */
	public static function is_lengow_product( $product_id ) {
		global $wpdb;
		$sql     = 'SELECT product_id FROM ' . $wpdb->prefix . 'lengow_product WHERE product_id = %d';
		$results = $wpdb->get_results( $wpdb->prepare( $sql, (int) $product_id ) );
		if ( count( $results ) > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get prices for a product.
	 *
	 * @return array
	 */
	private function _get_prices() {
		$price_excl_tax = $this->_get_price();
		$price_incl_tax = $this->_get_price( true );
		$regular_price  = self::get_regular_price( $this->product );
		if ( $regular_price ) {
			$precision                      = get_option( 'woocommerce_price_num_decimals' );
			$price_before_discount_excl_tax = $this->_get_price( false, $regular_price );
			$price_before_discount_incl_tax = $this->_get_price( true, $regular_price );
			$amount_excl_tax                = $price_before_discount_excl_tax - $price_excl_tax;
			$amount_incl_tax                = $price_before_discount_incl_tax - $price_incl_tax;
			$discount_amount_excl_tax       = round( $amount_excl_tax, $precision );
			$discount_amount_incl_tax       = round( $amount_incl_tax, $precision );
			$discount_percent               = round(
				( $amount_excl_tax * 100 ) / $price_before_discount_excl_tax,
				$precision
			);
		}
		if ( $this->product->is_on_sale() ) {
			$product_id            = $this->_product_type === 'variation' ? $this->_variation_id : $this->_product_id;
			$sale_price_dates_from = get_post_meta( $product_id, '_sale_price_dates_from', true );
			$start_date            = $sale_price_dates_from !== '' ? date( 'Y-m-d H:i:s', $sale_price_dates_from ) : '';
			$sale_price_dates_to   = get_post_meta( $product_id, '_sale_price_dates_to', true );
			$end_date              = $sale_price_dates_to !== '' ? date( 'Y-m-d H:i:s', $sale_price_dates_to ) : '';
		}
		$prices = array(
			'price_excl_tax'                 => $price_excl_tax,
			'price_incl_tax'                 => $price_incl_tax,
			'price_before_discount_excl_tax' => isset( $price_before_discount_excl_tax )
				? $price_before_discount_excl_tax
				: 0,
			'price_before_discount_incl_tax' => isset( $price_before_discount_incl_tax )
				? $price_before_discount_incl_tax
				: 0,
			'discount_amount_excl_tax'       => isset( $discount_amount_excl_tax ) ? $discount_amount_excl_tax : 0,
			'discount_amount_incl_tax'       => isset( $discount_amount_incl_tax ) ? $discount_amount_incl_tax : 0,
			'discount_percent'               => isset( $discount_percent ) ? $discount_percent : 0,
			'discount_start_date'            => isset( $start_date ) ? $start_date : '',
			'discount_end_date'              => isset( $end_date ) ? $end_date : '',
		);

		return $prices;
	}

	/**
	 * Returns the price (excluding tax).
	 *
	 * @param boolean $including_tax price including tax or not
	 * @param string|null $price to calculate, left blank to just use get_price()
	 *
	 * @return string
	 */
	private function _get_price( $including_tax = false, $price = null ) {
		if ( is_null( $price ) ) {
			$price = $this->product->get_price();
		}
		if ( $this->product->is_taxable() ) {
			$WC_tax    = new WC_Tax();
			$tax_rates = $WC_tax->get_rates( $this->product->get_tax_class() );
			if ( $including_tax && get_option( 'woocommerce_prices_include_tax' ) === 'no' ) {
				$taxes      = $WC_tax->calc_tax( $price, $tax_rates, false );
				$tax_amount = $WC_tax->get_tax_total( $taxes );
				$price      = round( $price + $tax_amount, get_option( 'woocommerce_price_num_decimals' ) );
			} elseif ( ! $including_tax && get_option( 'woocommerce_prices_include_tax' ) === 'yes' ) {
				$taxes      = $WC_tax->calc_tax( $price, $tax_rates, true );
				$tax_amount = $WC_tax->get_tax_total( $taxes );
				$price      = round( $price - $tax_amount, get_option( 'woocommerce_price_num_decimals' ) );
			}
		}

		return $price;
	}

	/**
	 * Returns the price shipping.
	 *
	 * @return string
	 */
	private function _get_price_shipping() {
		global $woocommerce;
		$price_shipping = 0;
		if ( $this->product->needs_shipping() ) {
			$woocommerce->cart->empty_cart();
			$packages                               = array();
			$packages[0]['contents'][0]             = array(
				'product_id'   => $this->_product_id,
				'variation_id' => $this->_variation_id,
				'variation'    => null,
				'quantity'     => 1,
				'line_total'   => $this->product->get_price(),
				'data'         => $this->product,
			);
			$packages[0]['contents_cost']           = $this->product->get_price();
			$packages[0]['applied_coupons']         = 0;
			$packages[0]['destination']['country']  = $woocommerce->customer->get_shipping_country();
			$packages[0]['destination']['state']    = $woocommerce->customer->get_shipping_state();
			$packages[0]['destination']['postcode'] = $woocommerce->customer->get_shipping_postcode();
			$packages                               = apply_filters(
				'woocommerce_cart_shipping_packages',
				$packages
			);
			$woocommerce->shipping->calculate_shipping( $packages );

			$price_shipping = $woocommerce->shipping->shipping_total;
		}

		return $price_shipping;
	}

	/**
	 * Returns the shipping class.
	 *
	 * @return string
	 */
	private function _get_shipping_class() {
		$shipping_class_name = '';
		$taxonomy            = 'product_shipping_class';
		// get product terms.
		$product_terms = get_the_terms( $this->_product_id, $taxonomy );
		if ( $product_terms ) {
			$shipping_class = $product_terms[0];
			if ( isset( $shipping_class->name ) ) {
				$shipping_class_name = $shipping_class->name;
			}
		}

		return $shipping_class_name;
	}

	/**
	 * Get images for a product.
	 *
	 * @return array
	 */
	private function _get_images() {
		$urls      = array();
		$imageUrls = array();
		// get thumbnail image for a product or a variation.
		$thumbnail_id               = self::get_thumbnail_id(
			$this->_product_id,
			$this->_variation_id,
			$this->_product_type
		);
		$thumbnail                  = wp_get_attachment_image_src( $thumbnail_id, 'shop_catalog_image_size' );
		$imageUrls['image_product'] = $thumbnail ? $thumbnail[0] : '';
		// get all product images for parent and variation.
		$gallery_image_ids = self::get_gallery_image_ids(
			$this->product,
			$this->_product_parent,
			$this->_product_type
		);
		if ( ! empty( $gallery_image_ids ) ) {
			foreach ( $gallery_image_ids as $image_id ) {
				$image  = wp_get_attachment_image_src( $image_id, 'shop_catalog_image_size' );
				$urls[] = $image[0];
			}
		}
		// create image urls array.
		for ( $i = 1; $i < 11; $i ++ ) {
			$imageUrls[ 'image_url_' . $i ] = '';
		}
		// Retrieves up to 10 images per product.
		$counter = 1;
		foreach ( $urls as $url ) {
			$imageUrls[ 'image_url_' . $counter ] = $url;
			if ( $counter === 10 ) {
				break;
			}
			$counter ++;
		}

		return $imageUrls;
	}

	/**
	 * Returns the category breadcrumb.
	 *
	 * @return string
	 */
	private function _get_categories() {
		$taxonomy = 'product_cat';
		// get all terms with id and name.
		$terms     = array();
		$all_terms = get_terms( $taxonomy );
		foreach ( $all_terms as $term ) {
			$childs = array();
			foreach ( $all_terms as $child ) {
				if ( $term->term_id == $child->parent ) {
					$childs[] = $child->term_id;
				}
			}
			$terms[ $term->term_id ] = array(
				'name'   => $term->name,
				'parent' => $term->parent,
				'child'  => $childs,
			);
		}
		// get product terms.
		$product_terms = get_the_terms( $this->_product_id, $taxonomy );
		if ( $product_terms && ! is_wp_error( $product_terms ) ) {
			// get product terms with only term id.
			$last_id          = false;
			$product_term_ids = array();
			foreach ( $product_terms as $product_term ) {
				$product_term_ids[] = $product_term->term_id;
			}
			// get the id at the last term.
			foreach ( $product_term_ids as $product_term_id ) {
				$term_childs = $terms[ $product_term_id ]['child'];
				if ( count( $term_childs ) > 0 ) {
					foreach ( $term_childs as $term_child ) {
						if ( ! in_array( $term_child, $product_term_ids ) ) {
							$last_id = $product_term_id;
							break;
						}
					}
				} else {
					$last_id = $product_term_id;
					break;
				}
			}
			// construct breadcrumb with all term names.
			if ( $last_id ) {
				$term_ids   = array();
				$term_ids[] = $terms[ $last_id ]['name'];
				$parent_id  = $last_id;
				do {
					$parent_id = $terms[ $parent_id ]['parent'];
					if ( $parent_id != 0 ) {
						$term_ids[] = $terms[ $parent_id ]['name'];
					}
				} while ( $parent_id != 0 );

				return join( ' > ', array_reverse( $term_ids ) );
			}
		}

		return '';
	}

	/**
	 * Returns all variation names to string.
	 *
	 * @return string
	 */
	private function _get_variation_list() {
		$variationsToString = '';
		if ( $this->_product_type === 'variable' ) {
			$variations = array();
			$attributes = $this->product->get_attributes();
			foreach ( $attributes as $attribute ) {
				if ( $attribute['is_variation'] ) {
					$variations[] = $attribute['name'];
				}
			}
			$variationsToString = implode( ', ', $variations );
		}

		return $variationsToString;
	}

	/**
	 * Returns all tags to string.
	 *
	 * @return string
	 */
	private function _get_tag_list() {
		$tagsToString = '';
		$return       = array();
		$tags         = get_the_terms( $this->_product_id, 'product_tag' );
		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				$return[] = $tag->name;
			}
			$tagsToString = implode( ', ', $return );
		}

		return $tagsToString;
	}

	/**
	 * Get data for attribute.
	 *
	 * @param string|null $name attribute name
	 *
	 * @return string
	 */
	private function _get_attribute_data( $name = null ) {
		if ( ! is_null( $name ) ) {
			if ( $this->_product_type === 'variation' ) {
				$name            = 'attribute_' . $name;
				$variation_datas = $this->product->get_variation_attributes();
				if ( array_key_exists( $name, $variation_datas ) ) {
					return $variation_datas[ $name ];
				}
			} elseif ( $this->_product_type !== 'variable' ) {
				return $this->product->get_attribute( $name );
			}
		}

		return '';
	}

	/**
	 * Get data for post metas.
	 *
	 * @param string|null $name post meta name
	 *
	 * @return string
	 */
	private function _get_post_meta_data( $name = null ) {
		if ( ! is_null( $name ) ) {
			$product_id = ! is_null( $this->_variation_id ) ? $this->_variation_id : $this->_product_id;
			$post_meta  = get_post_meta( $product_id, $name );
			if ( isset( $post_meta[0] ) ) {
				return is_array( $post_meta[0] ) ? json_encode( $post_meta[0] ) : $post_meta[0];
			}
		}

		return '';
	}
}
