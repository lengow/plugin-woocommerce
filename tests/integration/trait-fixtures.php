<?php
declare( strict_types=1 );

trait Trait_Fixtures
{
	public string $simpleId;

	public function create_simple_product(  ): int {
		$product = new WC_Product_Simple();
		$product->set_name( 'Test product' );
		$product->set_slug( 'test-product' );
		$product->set_regular_price( 50.00 );
		$product->set_short_description( '<p>Custom description</p>' );
		return $product->save();
	}

	public function load_products(): void {
		$xml = simplexml_load_file( dirname( __FILE__, 4 ) . '/woocommerce/sample-data/sample_products.xml' );
//		$namespaces = $xml->getNamespaces(true);
//		$xml->registerXPathNamespace('wp', $namespaces['wp']);
		foreach ($xml->channel->item as $item) {
			$post_title = (string) $item->title;
			$post_content = (string) $item->children('content', true)->encoded;
			$post_excerpt = (string) $item->children('excerpt', true)->encoded;
			$sku = (string) ($item->xpath("wp:postmeta[wp:meta_key='_sku']/wp:meta_value")[0] ?? '');
			$price = (string) ($item->xpath("wp:postmeta[wp:meta_key='_price']/wp:meta_value")[0] ?? '');
			$regular_price = (string) ($item->xpath("wp:postmeta[wp:meta_key='_regular_price']/wp:meta_value")[0] ?? '');
			$sale_price = (string) ($item->xpath("wp:postmeta[wp:meta_key='_sale_price']/wp:meta_value")[0] ?? '');

			// Create product post
			$product_id = wp_insert_post(array(
				'post_title'   => $post_title,
				'post_content' => $post_content,
				'post_excerpt' => $post_excerpt,
				'post_status'  => 'publish',
				'post_type'    => 'product',
			));

			if (is_wp_error($product_id)) {
				echo "error importing product: " . $product_id->get_error_message() . PHP_EOL;
				continue;
			}

			// Set product metadata
			update_post_meta($product_id, '_sku', $sku);
			update_post_meta($product_id, '_price', $price);
			update_post_meta($product_id, '_regular_price', $regular_price);
			update_post_meta($product_id, '_sale_price', $sale_price);

			// Set additional metadata, like stock status, attributes, categories, etc.
			$stock_status = (string) ($item->xpath("wp:postmeta[wp:meta_key='_stock_status']/wp:meta_value")[0] ?? '');
			update_post_meta($product_id, '_stock_status', $stock_status);
			$product_type = (string) ($item->xpath("category[@domain='product_type']")[0] ?? '');
			wp_set_object_terms($product_id, 'Tshirts', 'product_cat'); // Replace 'Tshirts' with relevant category names
			wp_set_object_terms($product_id, $product_type, 'product_type'); // Replace 'Tshirts' with relevant category names
		}
	}
}
