<?php
declare(strict_types=1);

class Test_Cron_Export extends WP_UnitTestCase
{
	use Trait_Fixtures;

	protected function setUp(): void {
		parent::setUp();
		Lengow_Configuration::update_value( Lengow_Configuration::SHOP_ACTIVE, '1' );
		Lengow_Configuration::update_value( Lengow_Configuration::CATALOG_IDS, '123' );
	}

	public function test_export_params() {
		$params = Lengow_Export::get_export_params();
		$this->assertNotEmpty( json_decode( $params ) );
	}

	public function test_size() {
		$export = new Lengow_Export();

		$total = $export->get_total_export_product();
		$this->assertEquals( 0, $total );

		$total = $export->get_total_product();
		$this->assertEquals( 0, $total );

		$this->create_products( [ 'simple' => 10 ] );

		$export = new Lengow_Export();

		$total = $export->get_total_export_product();
		$this->assertEquals( 10, $total );

		$total = $export->get_total_product();
		$this->assertEquals( 10, $total );
		// TODO test variations
		// TODO test filters
	}

	public function test_export() {
		$products = $this->create_products( [ 'simple' => 10 ] );

		$export = new Lengow_Export( [ 'stream' => false ] );
		ob_start();
		$export->exec();
		$exported = ob_get_clean();

		$this->assertEquals( '', $exported ); // because of stream=false
		$this->assertFileExists( __DIR__ . '/../../export/flux.csv' );

		$handle = fopen( __DIR__ . '/../../export/flux.csv', 'r' );
		$header = fgetcsv( $handle, null, '|' );
		$count = 0;

		while ( false !== ( $line = fgetcsv( $handle, null, '|' ) ) ) {
			$this->assertIsArray( $line );
			$this->assertIsNumeric( $line[ array_search( 'id', $header ) ] );

			$id = $line[ array_search( 'id', $header ) ];
			$this->assertEquals( count( $header ), count( $line ) );
			$this->assertEquals( $products[ $id ]->get_type(), $line[ array_search( 'type', $header ) ] );
			$this->assertEquals( $products[ $id ]->is_in_stock() ? 'instock' : 'outofstock', $line[ array_search( 'availability', $header ) ] );
			$this->assertEquals( $products[ $id ]->get_price(), $line[ array_search( 'price_incl_tax', $header ) ] );
			$count++;
		}

		$this->assertEquals( 10, $count );
	}
}
