<?php
declare( strict_types=1 );

use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;
use Lengow\Sdk\Resource\Api;

trait Trait_Fixtures
{
	protected static int $n = 0;

	public function create_simple_product(): WC_Product_Simple {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test product ' . ++self::$n );
		$product->set_slug( 'test-product-' . self::$n );
		$product->set_regular_price( rand( 10, 100 ) );
		$product->set_short_description( '<p>Custom description</p>' );
		$product->save();

		return $product;
	}

	public function create_variation_product(): array {
		$product = new WC_Product_Variable();
		$product->set_name( 'Test product ' . ++self::$n );
		$product->set_slug( 'test-product-' . self::$n );
		$product->set_regular_price( rand(10, 100) );
		$product->set_short_description( '<p>Custom description</p>' );
		$product->save();

		$variations = [ $product ];
		$count = 3; // rand(1, 20);
		for ( $i = 0; $i < $count; $i++ ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product->get_id() );
			$variation->set_regular_price( rand( 10, 100 ) );
			$variation->set_attributes( [ 'size' => 'M' ] );
			$variation->save();
			$variations[] = $variation;
		}

		return $variations;
	}

	/**
	 * @param array<string, int> $what
	 *
	 * @return array<WC_Product>
	 */
	public function create_products( array $what = [] ): array {
		$products = [];
		foreach ( $what as $type => $nb ) {
			for ( $i = 0; $i < $nb; $i++ ) {
				if ( 'simple' === $type ) {
					$product = $this->create_simple_product();
					$products[ $product->get_id() ] = $product;
				} elseif ( 'variable' === $type ) {
					$variables = $this->create_variation_product();
					foreach ( $variables as $product ) {
						$products[ $product->get_id() ] = $product;
					}
				}
			}
		}

		return $products;
	}

	/**
	 * @return array<WC_Product>
	 */
	public function create_default_products(): array {
		return $this->create_products( [ 'simple' => 2, 'variable' => 2 ] );
	}
}
