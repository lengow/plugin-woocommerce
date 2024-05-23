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
 * Lengow_Product Class.
 */
class Lengow_Product {

	/**
	 * @var string Lengow product table name
	 */
	const TABLE_PRODUCT = 'lengow_product';

	/* Product fields */
	const FIELD_ID         = 'id';
	const FIELD_PRODUCT_ID = 'product_id';

	const TAX_CATEGORY = 'product_cat';

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
	 * @var array non-persistent cache for categories
	 */
	private static $categories;

	/**
	 * @var WC_Product WooCommerce product instance.
	 */
	public $product;

	/**
	 * @var WC_Product WooCommerce product parent instance for variation.
	 */
	private $product_parent;

	/**
	 * @var integer product id.
	 */
	private $product_id;

	/**
	 * @var integer variation id.
	 */
	private $variation_id;

	/**
	 * @var string product type.
	 */
	private $product_type;

	/**
	 * @var array all product prices.
	 */
	private $prices;

	/**
	 * @var array all product images.
	 */
	private $images;

	/**
	 * Construct a new Lengow product.
	 *
	 * @param integer $product_id WooCommerce product id
	 *
	 * @throws Lengow_Exception Unable to find product
	 */
	public function __construct( $product_id ) {
		$this->product = wc_get_product( $product_id );
		if ( '' === $this->product
			|| ! in_array( get_post_type( $product_id ), array( 'product', 'product_variation' ) )
		) {
			throw new Lengow_Exception(
				Lengow_Main::set_log_message(
					'log.export.error_unable_to_find_product',
					array( 'product_id' => $product_id )
				)
			);
		}
		$this->product_type   = $this->product->get_type();
		$this->product_id     = self::get_product_id( $this->product );
		$this->variation_id   = self::get_variation_id( $this->product );
		$this->product_parent = self::get_product_parent( $this->product, $this->product_type );
		$this->prices         = $this->get_prices();
		$this->images         = $this->get_images();
	}

	/**
	 * Get Lengow product.
	 *
	 * @param array   $where a named array of WHERE clauses
	 * @param boolean $single get a single result or not
	 *
	 * @return false|object[]|object
	 */
	public static function get( $where = array(), $single = true ) {
		return Lengow_Crud::read( self::TABLE_PRODUCT, $where, $single );
	}

	/**
	 * Create Lengow product.
	 *
	 * @param array $data Lengow order data
	 *
	 * @return boolean
	 */
	public static function create( $data = array() ) {
		return Lengow_Crud::create( self::TABLE_PRODUCT, $data );
	}

	/**
	 * Delete Lengow product.
	 *
	 * @param array $where a named array of WHERE clauses
	 *
	 * @return boolean
	 */
	public static function delete( $where = array() ) {
		return Lengow_Crud::delete( self::TABLE_PRODUCT, $where );
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
				return 'variation' === $this->product_type
					? $this->product_id . '_' . $this->variation_id
					: $this->product_id;
			case 'sku':
				return $this->product->get_sku();
			case 'name':
				return Lengow_Main::clean_data( $this->product->get_title() );
			case 'quantity':
				return (int) $this->product->get_stock_quantity();
			case 'availability':
				return $this->product->get_stock_status();
			case 'available_product':
				$availability = $this->product->get_availability();

				return '' !== $availability['availability'] ? ltrim( $availability['availability'] ) : '';
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
				return $this->get_categories();
			case 'status':
				return $this->product->is_purchasable() ? 'Enabled' : 'Disabled';
			case 'url':
				return get_permalink( $this->product_id );
			case 'price_excl_tax':
			case 'price_incl_tax':
			case 'price_before_discount_excl_tax':
			case 'price_before_discount_incl_tax':
			case 'discount_amount_excl_tax':
			case 'discount_amount_incl_tax':
			case 'discount_percent':
			case 'discount_start_date':
			case 'discount_end_date':
				return $this->prices[ $name ];
			case 'shipping_class':
				return $this->get_shipping_class();
			case 'price_shipping':
				return $this->get_price_shipping();
			case 'currency':
				return get_woocommerce_currency();
			case 'image_product':
			case (bool) preg_match( '`image_url_([0-9]+)`', $name ):
				return $this->images[ $name ];
			case 'type':
				if ( 'variation' === $this->product_type ) {
					return 'child';
				}
				if ( 'variable' === $this->product_type ) {
					return 'parent';
				}

				return $this->product_type;
			case 'parent_id':
				return 'variation' === $this->product_type ? $this->product_id : '';
			case 'variation':
				return $this->get_variation_list();
			case 'language':
				return get_locale();
			case 'description':
				return Lengow_Main::clean_html(
					Lengow_Main::clean_data(
						self::get_description( $this->product, $this->product_parent, $this->product_type )
					)
				);
			case 'description_html':
				return Lengow_Main::clean_data(
					self::get_description( $this->product, $this->product_parent, $this->product_type )
				);
			case 'description_short':
				return Lengow_Main::clean_html(
					Lengow_Main::clean_data(
						self::get_short_description( $this->product, $this->product_parent, $this->product_type )
					)
				);
			case 'description_short_html':
				return Lengow_Main::clean_data(
					self::get_short_description( $this->product, $this->product_parent, $this->product_type )
				);
			case 'tags':
				return $this->get_tag_list();
			case 'weight':
				return $this->product->has_weight() ? $this->product->get_weight() : '';
			case 'dimensions':
				return $this->product->has_dimensions() ? $this->product->get_dimensions() : '';
			default:
				if ( in_array( $name, Lengow_Export::$attributes, true ) ) {
					return Lengow_Main::clean_data( $this->get_attribute_data( $name ) );
				}
				if ( in_array( $name, Lengow_Export::$post_metas, true ) ) {
					return Lengow_Main::clean_data( $this->get_post_meta_data( $name ) );
				}

				return '';
		}
	}

	/**
	 * Get product id.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 *
	 * @return integer
	 */
	public static function get_product_id( $product ) {
		return (int) ( 'variation' === $product->get_type() ? $product->get_parent_id() : $product->get_id() );
	}

	/**
	 * Get variation id.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 *
	 * @return integer|null
	 */
	public static function get_variation_id( $product ) {
		$variation_id = 'variation' === $product->get_type() ? $product->get_id() : null;

		return null !== $variation_id ? (int) $variation_id : null;
	}


	/**
	 * Get parent product for variation.
	 *
	 * @param WC_Product $product WooCommerce product instance
	 * @param string     $product_type WooCommerce product type
	 *
	 * @return WC_Product|null
	 */
	public static function get_product_parent( $product, $product_type ) {
		return 'variation' === $product_type ? wc_get_product( $product->get_parent_id() ) : null;
	}

	/**
	 * Get gallery image ids.
	 *
	 * @param WC_Product      $product WooCommerce product instance
	 * @param WC_Product|null $product_parent WooCommerce product parent instance
	 * @param string          $product_type WooCommerce product type
	 *
	 * @return array
	 */
	public static function get_gallery_image_ids( $product, $product_parent, $product_type ) {
		if ( 'variation' === $product_type && $product_parent ) {
			$gallery_image_ids = $product_parent->get_gallery_image_ids();
		} else {
			$gallery_image_ids = $product->get_gallery_image_ids();
		}

		return is_array( $gallery_image_ids ) ? $gallery_image_ids : explode( ',', $gallery_image_ids );
	}

	/**
	 * Get thumbnail id.
	 *
	 * @param integer      $product_id WooCommerce product id
	 * @param integer|null $variation_id WooCommerce's variation id
	 * @param string       $product_type WooCommerce product type
	 *
	 * @return integer
	 */
	public static function get_thumbnail_id( $product_id, $variation_id, $product_type ) {
		$variation_thumbnail_id = false;
		$thumbnail_id           = get_post_thumbnail_id( $product_id );
		if ( 'variation' === $product_type ) {
			$variation_thumbnail_id = get_post_thumbnail_id( $variation_id );
		}

		return (int) ( $variation_thumbnail_id ?: $thumbnail_id );
	}

	/**
	 * Get description.
	 *
	 * @param WC_Product      $product WooCommerce product instance
	 * @param WC_Product|null $product_parent WooCommerce product parent instance
	 * @param string          $product_type WooCommerce product type
	 *
	 * @return string
	 */
	public static function get_description( $product, $product_parent, $product_type ) {
		$description = $product->get_description();
		if ( 'variation' === $product_type && $product_parent && empty( $description ) ) {
			$description = $product_parent->get_description();
		}

		return $description;
	}

	/**
	 * Get short description.
	 *
	 * @param WC_Product      $product WooCommerce product instance
	 * @param WC_Product|null $product_parent WooCommerce product parent instance
	 * @param string          $product_type WooCommerce product type
	 *
	 * @return string
	 */
	public static function get_short_description( $product, $product_parent, $product_type ) {
		$short_description = $product->get_short_description();
		if ( 'variation' === $product_type && $product_parent && empty( $short_description ) ) {
			$short_description = $product_parent->get_short_description();
		}

		return $short_description;
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
					if ( ! in_array( $key, $return, true ) ) {
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
			WHERE p.post_type = \'product\' OR p.post_type = \'product_variation\'
		';
		foreach ( $wpdb->get_results( $sql ) as $result ) {
			if ( ! in_array( $result->meta_key, self::$excludes, true )
				&& ! in_array( $result->meta_key, $return, true )
			) {
				$return[] = $result->meta_key;
			}
		}

		return $return;
	}

	/**
	 * Extract cart data from API.
	 *
	 * @param mixed $product API product data
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
	 * Match product with API data.
	 *
	 * @param mixed   $product_data all product data
	 * @param string  $marketplace_sku Lengow id of current order
	 * @param boolean $log_output see log or not
	 *
	 * @return WC_Product|false
	 * @throws Lengow_Exception If product is a variable
	 */
	public static function match_product( $product_data, $marketplace_sku, $log_output ) {
		$product         = false;
		$api_product_ids = array(
			'merchant_product_id'    => $product_data['merchant_product_id']->id,
			'marketplace_product_id' => $product_data['marketplace_product_id'],
		);
		$product_field   = null !== $product_data['merchant_product_id']->field
			? strtolower( (string) $product_data['merchant_product_id']->field )
			: false;
		// search product foreach value.
		foreach ( $api_product_ids as $attribute_name => $attribute_value ) {
			// remove _FBA from product id.
			$attribute_value = preg_replace( '/_FBA$/', '', $attribute_value );
			if ( empty( $attribute_value ) ) {
				continue;
			}
			$product_id = false;
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
				$product = wc_get_product( $product_id );
				if ( 'variable' === $product->get_type() ) {
					throw new Lengow_Exception(
						Lengow_Main::set_log_message(
							'lengow_log.exception.product_is_a_parent',
							array( 'product_id' => $product_id )
						)
					);
				}
				Lengow_Main::log(
					Lengow_Log::CODE_IMPORT,
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
				break;
			}
		}

		return $product;
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
				$attribute_value = str_replace( array( '\_', 'X' ), '_', $attribute_value );
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
			$product = wc_get_product( $product_id );
			if ( $product && in_array( get_post_type( $product_id ), array( 'product', 'product_variation' ) ) ) {
				if ( 'variation' === $product->get_type() ) {
					return self::get_variation_id( $product );
				}

				return self::get_product_id( $product );
			}
		}

		return false;
	}

	/**
	 * Publish or Un-publish to Lengow.
	 *
	 * @param integer $product_id the id product
	 * @param integer $value 1 : publish, 0 : unpublished
	 *
	 * @return boolean
	 */
	public static function publish( $product_id, $value ) {
		if ( ! $value ) {
			self::delete( array( self::FIELD_PRODUCT_ID => ( (int) $product_id ) ) );
		} else {
			$result = self::get( array( self::FIELD_PRODUCT_ID => ( (int) $product_id ) ) );
			if ( ! $result ) {
				self::create( array( self::FIELD_PRODUCT_ID => ( (int) $product_id ) ) );
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
		$results  = self::get( array(), false );
		$products = array();
		foreach ( $results as $value ) {
			$products[ $value->{self::FIELD_PRODUCT_ID} ] = $value->{self::FIELD_PRODUCT_ID};
		}

		return $products;
	}

	/**
	 * Get prices for a product.
	 *
	 * @return array
	 */
	private function get_prices() {
		$price_excl_tax = $this->get_price();
		$price_incl_tax = $this->get_price( true );
		$regular_price  = (float) $this->product->get_regular_price();
		if ( $regular_price ) {
			$precision                      = (int) get_option( 'woocommerce_price_num_decimals' );
			$price_before_discount_excl_tax = $this->get_price( false, $regular_price );
			$price_before_discount_incl_tax = $this->get_price( true, $regular_price );
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
			$product_id            = 'variation' === $this->product_type ? $this->variation_id : $this->product_id;
			$sale_price_dates_from = get_post_meta( $product_id, '_sale_price_dates_from', true );
			$start_date            = '' !== $sale_price_dates_from
				? get_date_from_gmt( date( Lengow_Main::DATE_FULL, $sale_price_dates_from ) )
				: '';
			$sale_price_dates_to   = get_post_meta( $product_id, '_sale_price_dates_to', true );
			$end_date              = '' !== $sale_price_dates_to
				? get_date_from_gmt( date( Lengow_Main::DATE_FULL, $sale_price_dates_to ) )
				: '';
		}

		return array(
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
	}

	/**
	 * Returns the price (excluding tax).
	 *
	 * @param boolean    $including_tax price including tax or not
	 * @param float|null $price to calculate, left blank to just use get_price()
	 *
	 * @return float
	 */
	private function get_price( $including_tax = false, $price = null ) {
		if ( null === $price ) {
			$price = (float) $this->product->get_price();
		}
		if ( $this->product->is_taxable() ) {
			$tax_rates = WC_Tax::get_rates( $this->product->get_tax_class() );
			if ( $including_tax && 'no' === get_option( 'woocommerce_prices_include_tax' ) ) {
				$taxes      = WC_Tax::calc_tax( $price, $tax_rates );
				$tax_amount = (float) WC_Tax::get_tax_total( $taxes );
				$price      = round( $price + $tax_amount, (int) get_option( 'woocommerce_price_num_decimals' ) );
			} elseif ( ! $including_tax && 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
				$taxes      = WC_Tax::calc_tax( $price, $tax_rates, true );
				$tax_amount = (float) WC_Tax::get_tax_total( $taxes );
				$price      = round( $price - $tax_amount, (int) get_option( 'woocommerce_price_num_decimals' ) );
			}
		}

		return $price;
	}

	/**
	 * Returns the price shipping.
	 *
	 * @return float
	 */
	private function get_price_shipping() {
		global $woocommerce;
		$price_shipping = 0;
		if ( $this->product->needs_shipping() ) {
			if ( is_null( $woocommerce->cart ) ) {
				$woocommerce->initialize_session();
				$woocommerce->initialize_cart();
			}
			$woocommerce->cart->empty_cart();
			$packages                               = array();
			$packages[0]['contents'][0]             = array(
				'product_id'   => $this->product_id,
				'variation_id' => $this->variation_id,
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

		return (float) $price_shipping;
	}

	/**
	 * Returns the shipping class.
	 *
	 * @return string
	 */
	private function get_shipping_class() {
		$shipping_class_name = '';
		$taxonomy            = 'product_shipping_class';
		// get product terms.
		$product_terms = get_the_terms( $this->product_id, $taxonomy );
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
	private function get_images() {
		$urls      = array();
		$imageUrls = array();
		// get thumbnail image for a product or a variation.
		$thumbnail_id               = self::get_thumbnail_id(
			$this->product_id,
			$this->variation_id,
			$this->product_type
		);
		$thumbnail                  = wp_get_attachment_image_src( $thumbnail_id, 'shop_catalog_image_size' );
		$imageUrls['image_product'] = $thumbnail ? $thumbnail[0] : '';
		// get all product images for parent and variation.
		$gallery_image_ids = self::get_gallery_image_ids(
			$this->product,
			$this->product_parent,
			$this->product_type
		);
		if ( ! empty( $gallery_image_ids ) ) {
			foreach ( $gallery_image_ids as $image_id ) {
				$image = wp_get_attachment_image_src( $image_id, 'shop_catalog_image_size' );
				if ( isset( $image[0] ) ) {
					$urls[] = $image[0];
				}
			}
		}
		// create image urls array.
		for ( $i = 1; $i < 11; $i++ ) {
			$imageUrls[ 'image_url_' . $i ] = '';
		}
		// clean $urls array to remove NULL entry
		$urls = array_values( array_filter( $urls ) );
		// Retrieves up to 10 images per product.
		$counter = 1;
		foreach ( $urls as $url ) {
			$imageUrls[ 'image_url_' . $counter ] = $url;
			if ( 10 === $counter ) {
				break;
			}
			++$counter;
		}

		return $imageUrls;
	}

	/**
	 * returns product_cat categories
	 *
	 * @return array
	 */
	private function get_all_categories() {
		if ( isset( self::$categories ) ) {
			return self::$categories;
		}

		$terms     = array();
		$all_terms = get_terms( self::TAX_CATEGORY );
		foreach ( $all_terms as $term ) {
			$children = array();
			foreach ( $all_terms as $child ) {
				if ( $term->term_id === $child->parent ) {
					$children[] = $child->term_id;
				}
			}
			$terms[ $term->term_id ] = array(
				'name'   => $term->name,
				'parent' => $term->parent,
				'child'  => $children,
			);
		}

		self::$categories = $terms;
		return self::$categories;
	}

	/**
	 * Returns the category breadcrumb.
	 *
	 * @return string
	 */
	private function get_categories() {
		// get all terms with id and name.
		$terms    = $this->get_all_categories();
		$nb_terms = count( $terms );
		// get product terms.
		$product_terms = get_the_terms( $this->product_id, self::TAX_CATEGORY );
		if ( $product_terms && ! is_wp_error( $product_terms ) ) {
			// get product terms with only term id.
			$last_id          = false;
			$product_term_ids = array();
			foreach ( $product_terms as $product_term ) {
				$product_term_ids[] = $product_term->term_id;
			}
			// get the id at the last term.
			foreach ( $product_term_ids as $product_term_id ) {
				$term_children = $terms[ $product_term_id ]['child'];
				if ( ! empty( $term_children ) ) {
					foreach ( $term_children as $term_child ) {
						if ( ! in_array( $term_child, $product_term_ids, true ) ) {
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
				$parent_id  = (int) $last_id;
				$iteration  = 0;
				do {
					$parent_id = $terms[ $parent_id ]['parent'];
					if ( $parent_id !== 0 ) {
						if ( empty( $terms[ $parent_id ]['name'] ) ) {
							continue;
						}

						$term_ids[] = $terms[ $parent_id ]['name'];
					}
				} while ( $parent_id !== 0 && ++$iteration < $nb_terms );

				return implode( ' > ', array_reverse( $term_ids ) );
			}
		}

		return '';
	}

	/**
	 * Returns all variation names to string.
	 *
	 * @return string
	 */
	private function get_variation_list() {
		$variationsToString = '';
		if ( 'variable' === $this->product_type ) {
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
	private function get_tag_list() {
		$tagsToString = '';
		$return       = array();
		$tags         = get_the_terms( $this->product_id, 'product_tag' );
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
	private function get_attribute_data( $name = null ) {
		if ( null !== $name ) {
			if ( 'variation' === $this->product_type ) {
				$name           = 'attribute_' . $name;
				$variation_data = $this->product->get_variation_attributes();
				if ( array_key_exists( $name, $variation_data ) ) {
					return $variation_data[ $name ];
				}
			} elseif ( 'variable' !== $this->product_type ) {
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
	private function get_post_meta_data( $name = null ) {
		if ( null !== $name ) {
			$product_id = null !== $this->variation_id ? $this->variation_id : $this->product_id;
			$post_meta  = get_post_meta( $product_id, $name );
			if ( isset( $post_meta[0] ) ) {
				// if post_meta[0] is an object, it mean it surely came from another plugin and not woocommerce itself
				// we cannot know what is in the object, so we return empty string
				if ( is_object( $post_meta[0] ) ) {
					return '';
				}

				return is_array( $post_meta[0] ) ? wp_json_encode( $post_meta[0] ) : $post_meta[0];
			}
		}

		return '';
	}
}
