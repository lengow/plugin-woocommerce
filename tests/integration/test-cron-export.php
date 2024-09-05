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

		$products = $this->create_default_products();

		$export = new Lengow_Export();

		$total = $export->get_total_export_product();
		$this->assertEquals( count( $products ), $total );

		$total = $export->get_total_product();
		$this->assertEquals( count( $products ), $total );

		// --- variations ---
		$export = new Lengow_Export( [
			Lengow_Export::PARAM_VARIATION => '0',
		] );

		$count = count ( array_filter( $products, function( $product ) {
			return 'variation' !== $product->get_type();
		} ) );
		$total = $export->get_total_export_product();
		$this->assertEquals( $count, $total );

		$total = $export->get_total_product();
		$this->assertEquals( count( $products ), $total );

		// --- product types ---
		$export = new Lengow_Export( [
			Lengow_Export::PARAM_PRODUCT_TYPES => 'variable',
		] );

		$count = count ( array_filter( $products, function( $product ) {
			return 'simple' !== $product->get_type();
		} ) );
		$total = $export->get_total_export_product();
		$this->assertEquals( $count, $total );

		$total = $export->get_total_product();
		$this->assertEquals( count( $products ), $total );

		// --- limit / offset ---
		$export = new Lengow_Export( [
			Lengow_Export::PARAM_LIMIT => '3',
			Lengow_Export::PARAM_OFFSET => '3'
		] );

		$total = $export->get_total_export_product();
		$this->assertEquals( count( $products ), $total );

		$total = $export->get_total_product();
		$this->assertEquals( count( $products ), $total );
	}

	public function test_export() {
		$products = $this->create_default_products();
		$export_file = __DIR__ . '/../../export/flux.csv';
		if ( file_exists( $export_file ) ) {
			unlink( $export_file );
		}

		$export = new Lengow_Export( [
			Lengow_Export::PARAM_STREAM => '0'
		] );
		ob_start();
		$export->exec();
		$exported = ob_get_clean();

		$this->assertEquals( '', $exported ); // because of stream=false
		$this->assertFileExists( __DIR__ . '/../../export/flux.csv' );

		$count = $this->test_file( $products );
		$this->assertEquals( count( $products ), $count );

		// --- variations ---
		unlink( $export_file );
		$export = new Lengow_Export( [
			Lengow_Export::PARAM_STREAM => '0',
			Lengow_Export::PARAM_VARIATION => '0',
		] );
		ob_start();
		$export->exec();
		$exported = ob_get_clean();

		$this->assertEquals( '', $exported ); // because of stream=false
		$this->assertFileExists( __DIR__ . '/../../export/flux.csv' );
		$expectedCount = count ( array_filter( $products, function( $product ) {
			return 'variation' !== $product->get_type();
		} ) );

		$count = $this->test_file( $products );
		$this->assertEquals( $expectedCount, $count );

		// --- product types ---
		unlink( $export_file );
		$export = new Lengow_Export( [
			Lengow_Export::PARAM_STREAM => '0',
			Lengow_Export::PARAM_PRODUCT_TYPES => 'variable',
		] );
		ob_start();
		$export->exec();
		$exported = ob_get_clean();

		$this->assertEquals( '', $exported ); // because of stream=false
		$this->assertFileExists( __DIR__ . '/../../export/flux.csv' );

		$expectedCount = count ( array_filter( $products, function( $product ) {
			return 'simple' !== $product->get_type();
		} ) );
		$count = $this->test_file( $products );
		$this->assertEquals( $expectedCount, $count );

		// --- limit / offset ---
		unlink( $export_file );
		$export = new Lengow_Export( [
			Lengow_Export::PARAM_STREAM => '0',
			Lengow_Export::PARAM_LIMIT => '3',
			Lengow_Export::PARAM_OFFSET => '3'
		] );
		ob_start();
		$export->exec();
		$exported = ob_get_clean();

		$this->assertEquals( '', $exported ); // because of stream=false
		$this->assertFileExists( __DIR__ . '/../../export/flux.csv' );

		$count = $this->test_file( $products );
		$this->assertEquals( 3, $count );
	}

	/**
	 * @param array $products<WC_Product> products
	 * @return int actual number of lines
	 */
	protected function test_file( array $products ): int {
		$handle = fopen( __DIR__ . '/../../export/flux.csv', 'r' );
		$header = fgetcsv( $handle, null, '|' );
		$count = 0;

		while ( false !== ( $line = fgetcsv( $handle, null, '|' ) ) ) {
			$this->assertIsArray( $line );
			$this->assertMatchesRegularExpression( '/[0-9]+(_[0-9]+)?/', $line[ array_search( 'id', $header ) ] );

			$id = $line[ array_search( 'id', $header ) ];
			$id = false !== strpos( $id, '_' ) ? substr( $id, strpos( $id, '_' ) + 1 ) : $id;
			if ( 'variable' === $products[ $id ]->get_type() ) {
				$type = 'parent';
			} elseif ( 'variation' === $products[ $id ]->get_type() ) {
				$type = 'child';
			} else {
				$type = $products[ $id ]->get_type();
			}

			$this->assertEquals( count( $header ), count( $line ) );
			$this->assertEquals( $type, $line[ array_search( 'type', $header ) ] );
			$this->assertEquals( $products[ $id ]->is_in_stock() ? 'instock' : 'outofstock', $line[ array_search( 'availability', $header ) ] );

			if ( in_array( $type, [ 'simple', 'child' ] ) ) {
				// variable has no price
				$this->assertEquals( $products[ $id ]->get_price(), $line[ array_search( 'price_incl_tax', $header ) ] );
			}

			$count++;
		}

		return $count;
	}
}
