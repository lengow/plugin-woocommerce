<?php
declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;
use Lengow\Sdk\Resource\Api;

class Test_Cron extends WP_UnitTestCase
{
	use Trait_Mock_Client;
	use Trait_Fixtures;

	protected array $order;

	protected function setUp(): void {
		parent::setUp();
		Lengow_Configuration::update_value( Lengow_Configuration::SHOP_ACTIVE, '1' );
		Lengow_Configuration::update_value( Lengow_Configuration::CATALOG_IDS, '123' );
	}

	protected function mock_marketplace() {
		$this->mock_client->on(
			new RequestMatcher( Api\Marketplace::API, null, [ 'GET' ] ),
			new Response( 200, [], file_get_contents(
				$this->mock_dir . 'marketplace-sample.json'
			) )
		);
	}

	/**
	 * @throws Exception
	 */
	public function test_import_order() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();
		$this->mock_marketplace();
		$this->mock_order_list();

		$updated = 0;
		$updatedBody = null;
		$this->mock_client->on(
			new RequestMatcher( Api\Order::API_MOI, null, [ 'PATCH' ] ),
			function ( $request ) use ( &$updated, &$updatedBody ) {
				$updatedBody = json_decode( (string) $request->getBody(), true );
				$updated++;
				return new Response( 200, [], '{}' );
			}
		);

		if ( is_null( WC()->session ) || ! function_exists( 'wc_get_chosen_shipping_method_ids' ) ) {
			WC()->frontend_includes();
			WC()->init();
			WC()->initialize_session();
		}
		$import = new Lengow_Import( [
			Lengow_Import::PARAM_TYPE => Lengow_Import::TYPE_CRON,
			Lengow_Import::PARAM_LOG_OUTPUT => true,
		] );
		$results = $import->exec();
		$this->assertCount( 1, $results['orders_created'] );
		$lwg_order = new Lengow_Order( $results['orders_created'][0]['lengow_order_id'] );
		$wc_order = new WC_Order( $lwg_order->order_id );

		$this->assertEquals( 'Testd', $wc_order->get_shipping_first_name() );
	    $this->assertEquals( 'Testd', $wc_order->get_shipping_last_name() );
	    $this->assertEquals( 'testd', $wc_order->get_shipping_address_1() );
	    $this->assertEquals( 'Testd', $wc_order->get_shipping_city() );
	    $this->assertEquals( '12345', $wc_order->get_shipping_postcode() );
	    $this->assertEquals( 'FR', $wc_order->get_shipping_country() );

		$this->assertEquals( 'Testb', $wc_order->get_billing_first_name() );
		$this->assertEquals( 'Testb', $wc_order->get_billing_last_name() );
		$this->assertEquals( 'testb', $wc_order->get_billing_address_1() );
		$this->assertEquals( 'Testb', $wc_order->get_billing_city() );
		$this->assertEquals( '12346', $wc_order->get_billing_postcode() );
		$this->assertEquals( 'FR', $wc_order->get_billing_country() );

		$this->assertEquals( (float)$this->order['total_order'], (float)$wc_order->get_total() );
		$this->assertEquals( (float)$this->order['total_tax'], (float)$wc_order->get_total_tax() );
		$this->assertEquals( (float)$this->order['shipping'], (float)$wc_order->get_shipping_total() );
		$this->assertEquals( 'on-hold', $wc_order->get_status() );

		$this->assertEquals( 123, $updatedBody['account_id'] );
		$this->assertEquals( $this->order['marketplace_order_id'], $updatedBody['marketplace_order_id'] );
		$this->assertEquals( $this->order['marketplace'], $updatedBody['marketplace'] );
		$this->assertEquals( $wc_order->get_id(), $updatedBody['merchant_order_id'][0] );

		$this->assertEquals( 1, $updated, 'Order should be updated once on lengow' );

		// testing order page
		// simpler to test here while the order exists

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_orders';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			preg_quote( '4UT0-000001' ) // Order ID in the mock order list
		];

		foreach ( $expected as $expr ) {
			$this->assertMatchesRegularExpression(
				'/' . $expr . '/',
				$output,
				'Output should contain message: ' . $expr
			);
		}
	}
}
