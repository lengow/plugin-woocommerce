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
		$product->set_regular_price( rand(10, 100) );
		$product->set_short_description( '<p>Custom description</p>' );
		$product->save();

		return $product;
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
				}
			}
		}

		return $products;
	}

	/**
	 * @return array<WC_Product>
	 */
	public function create_default_products(): array {
		return $this->create_products( [ 'simple' => 10 ] );
	}

	public function mock_order_list() {
		$json = json_decode( file_get_contents(
			$this->mock_dir . 'order-sample.json'
		), true );

		$json['results'][0]['marketplace_order_date'] = date( 'Y-m-d\TH:i:s\Z' );
		$json['results'][0]['packages'][0]['cart'][0]['merchant_product_id']['id'] = $this->create_simple_product()->get_id();

		$this->order = $json['results'][0];
		$this->mock_client->on(
			new RequestMatcher( Api\Order::API, null, [ 'GET' ] ),
			new Response( 200, [], json_encode( $json ) )
		);
	}
}
