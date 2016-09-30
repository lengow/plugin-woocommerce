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
	 * Instance of WC_Product_Simple, WC_Product_External, WC_Product_Grouped, WC_Product_Variable
	 */
	public $product;

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
				Lengow_Main::set_log_message( 'log.export.error_unable_to_find_product', array(
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
		switch ( $name ) {
			case 'id':
				if ( $this->product->product_type === 'variation' ) {
					return $this->product->id . '_' . $this->product->variation_id;
				} else {
					return $this->product->id;
				}
			case 'sku':
				return $this->product->get_sku();
			case 'name':
				return Lengow_Main::clean_data( $this->product->get_title() );
			case 'quantity':
				return (int) $this->product->get_stock_quantity();
			case 'availability':
				return $this->product->stock_status;
			case 'available_product':
				$availability = $this->product->get_availability();
				if ( $availability['availability'] != '' ) {
					return ltrim( $availability['availability'] );
				}

				return '';
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
				return get_permalink( $this->product->id );
			case 'price_excl_tax':
				return $this->_get_price();
			case 'price_incl_tax':
				return $this->_get_price( true );
			case 'price_before_discount_excl_tax':
				if ( $this->product->regular_price ) {
					return $this->_get_price( false, $this->product->regular_price );
				}

				return 0;
			case 'price_before_discount_incl_tax':
				if ( $this->product->regular_price ) {
					return $this->_get_price( true, $this->product->regular_price );
				}

				return 0;
			case 'discount_amount_excl_tax':
				if ( $this->product->regular_price ) {
					return round(
						$this->_get_price( false, $this->product->regular_price ) - $this->_get_price(),
						get_option( 'woocommerce_price_num_decimals' )
					);
				}

				return 0;
			case 'discount_amount_incl_tax':
				if ( $this->product->regular_price ) {
					return round(
						$this->_get_price( true, $this->product->regular_price ) - $this->_get_price( true ),
						get_option( 'woocommerce_price_num_decimals' )
					);
				}

				return 0;
			case 'discount_percent':
				if ( $this->product->regular_price ) {
					$amount = $this->_get_price( false, $this->product->regular_price ) - $this->_get_price();

					return round(
						( $amount * 100 ) / $this->_get_price( false, $this->product->regular_price ),
						get_option( 'woocommerce_price_num_decimals' )
					);
				}

				return 0;
			case 'discount_start_date':
				if ( $this->product->is_on_sale() ) {
					$product_id = $this->product->product_type === 'variation'
						? $this->product->variation_id
						: $this->product->id;
					$start_date = get_post_meta( $product_id, '_sale_price_dates_from', true );

					return $start_date != '' ? date( 'Y-m-d H:i:s', $start_date ) : '';
				}

				return '';
			case 'discount_end_date':
				if ( $this->product->is_on_sale() ) {
					$product_id = $this->product->product_type === 'variation'
						? $this->product->variation_id
						: $this->product->id;
					$end_date   = get_post_meta( $product_id, '_sale_price_dates_to', true );

					return $end_date != '' ? date( 'Y-m-d H:i:s', $end_date ) : '';
				}

				return '';
			case 'price_shipping':
				return $this->_get_price_shipping();
			case 'currency':
				return get_woocommerce_currency();
			case 'image_product':
				$variation_thumbnail_id = false;
				$thumbnail_id           = get_post_thumbnail_id( $this->product->id );
				if ( $this->product->product_type === 'variation' ) {
					$variation_thumbnail_id = get_post_thumbnail_id( $this->product->variation_id );
				}
				if ( $variation_thumbnail_id ) {
					$variation_thumbnail = wp_get_attachment_image_src(
						$variation_thumbnail_id,
						'shop_catalog_image_size'
					);
					if ( $variation_thumbnail ) {
						return $variation_thumbnail[0];
					}
				}
				if ( $thumbnail_id ) {
					$thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'shop_catalog_image_size' );
					if ( $thumbnail ) {
						return $thumbnail[0];
					}
				}

				return '';
			//speed up export
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
				//speed up export
				switch ( $name ) {
					case 'image_url_1':
						$id_image = 0;
						break;
					case 'image_url_2':
						$id_image = 1;
						break;
					case 'image_url_3':
						$id_image = 2;
						break;
					case 'image_url_4':
						$id_image = 3;
						break;
					case 'image_url_5':
						$id_image = 4;
						break;
					case 'image_url_6':
						$id_image = 5;
						break;
					case 'image_url_7':
						$id_image = 6;
						break;
					case 'image_url_8':
						$id_image = 7;
						break;
					case 'image_url_9':
						$id_image = 8;
						break;
					case 'image_url_10':
						$id_image = 9;
						break;
				}
				$image_ids = explode( ',', $this->product->product_image_gallery );
				if ( count( $image_ids ) > 0 && isset( $image_ids[ $id_image ] ) && $image_ids[ $id_image ] ) {
					$image = wp_get_attachment_image_src( $image_ids[ $id_image ], 'shop_catalog_image_size' );

					return $image[0];
				}

				return '';
			case 'type':
				if ( $this->product->product_type === 'variation' ) {
					return 'child';
				} elseif ( $this->product->product_type === 'variable' ) {
					return 'parent';
				} else {
					return $this->product->product_type;
				}
			case 'parent_id':
				if ( $this->product->product_type === 'variation' ) {
					return $this->product->parent->id;
				} else {
					return $this->product->get_parent();
				}
			case 'variation':
				if ( $this->product->product_type === 'variable' ) {
					$variations = array();
					$attributes = $this->product->get_attributes();
					foreach ( $attributes as $attribute ) {
						if ( $attribute['is_variation'] ) {
							$variations[] = $attribute['name'];
						}
					}

					return implode( ',', $variations );
				}

				return '';
			case 'language':
				return get_locale();
			case 'description':
				return Lengow_Main::clean_html( Lengow_Main::clean_data( $this->product->post->post_content ) );
			case 'description_html':
				return Lengow_Main::clean_data( $this->product->post->post_content );
			case 'description_short':
				return Lengow_Main::clean_html( Lengow_Main::clean_data( $this->product->post->post_excerpt ) );
			case 'description_short_html':
				return Lengow_Main::clean_data( $this->product->post->post_excerpt );
			case 'tags':
				$return = array();
				$tags   = get_the_terms( $this->product->id, 'product_tag' );
				if ( ! empty( $tags ) ) {
					foreach ( $tags as $tag ) {
						$return[] = $tag->name;
					}

					return implode( ',', $return );
				}

				return '';
			case 'weight':
				if ( $this->product->has_weight() ) {
					return $this->product->get_weight();
				}

				return '';
			case 'dimensions':
				if ( $this->product->has_dimensions() ) {
					return $this->product->get_dimensions();
				}

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
	 * Returns the price (excluding tax)
	 *
	 * @param  string $price to calculate, left blank to just use get_price()
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
	 * Returns the price shipping
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
				'product_id'   => $this->product->id,
				'variation_id' => isset( $this->product->variation_id ) ? $this->product->variation_id : null,
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
	 * Returns the category breadcrum
	 *
	 * @return string
	 */
	private function _get_categories() {
		$taxonomy = 'product_cat';
		// get all terms with id and name
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
		// get product terms
		$product_terms = get_the_terms( $this->product->id, $taxonomy );
		if ( $product_terms && ! is_wp_error( $product_terms ) ) {
			// get product terms with only term id
			$last_id          = false;
			$product_term_ids = array();
			foreach ( $product_terms as $product_term ) {
				$product_term_ids[] = $product_term->term_id;
			}
			// get the id at the last term
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
			// construct breadcrum with all term names
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
     * Publish or Un-publish to Lengow.
     *
     * @param integer $product_id the id product
     * @param integer $value     1 : publish, 0 : unpublish
     *
     * @return boolean.
     */
    public static function publish($product_id, $value)
    {
        global $wpdb;
        if (!$value) {
            $wpdb->delete($wpdb->prefix.'lengow_product', array('product_id' => ((int)$product_id)));
        } else {
            $sql = "
            SELECT product_id FROM {$wpdb->prefix}lengow_product
            WHERE product_id = ".(int)$product_id;
            $results = $wpdb->get_results($sql);
            if (count($results) == 0) {
                $wpdb->insert($wpdb->prefix . 'lengow_product', array('product_id' => ((int)$product_id)));
            }
        }
        return true;
    }

    /**
     * Get Lengow products.
     *
     * @return array.
     */
    public static function get_lengow_products()
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}lengow_product";
        $results = $wpdb->get_results($sql);
        $products = array();
        foreach ($results as  $value) {
            $products[$value->product_id] = $value->product_id;
        }
        return $products;
    }

}

