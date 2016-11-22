<?php

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-11-09 at 15:08:39.
 */
class Lengow_ProductTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var Lengow_Product
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$fixture = New Fixture();
		$fixture->loadProducts( 'products3.yml' );
		$fixture->loadCategories( 'categories1.yml' );
		$this->object = new Lengow_Product( 1 );
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
	}

	/**
	 * @expectedException Lengow_Exception
	 * @covers Lengow_Product::__construct
	 */
	/*public function test__construct()
	{
		$this->assertEquals(new Lengow_Product(1), $this->object);
		$this->expectException(new Lengow_Product(6666));
	}*/

	/**
	 * @covers Lengow_Product::get_data
	 * @todo   Implement testGet_data().
	 */
	public function testGet_data() {
		$this->assertEquals( '1', $this->object->get_data( 'id' ) );
		$variation = new Lengow_Product( 4 );
		$this->assertEquals( '4', $variation->get_data( 'id' ) );
		$this->assertEquals( 'sku1', $this->object->get_data( 'sku' ) );
		$this->assertEquals( 'mon nom', $this->object->get_data( 'name' ) );
		$this->assertEquals( 102, $this->object->get_data( 'quantity' ) );
		$this->assertEquals( 'instock', $this->object->get_data( 'availability' ) );
		$this->assertEquals( '102 in stock', $this->object->get_data( 'available_product' ) );
		//$this->assertEquals('0', $this->object->get_data('is_in_stock'));
		$this->assertEquals( '0', $this->object->get_data( 'is_virtual' ) );
		$this->assertEquals( '1', $this->object->get_data( 'is_downloadable' ) );
		$this->assertEquals( '1', $this->object->get_data( 'is_featured' ) );
		//$this->assertEquals('1', $this->object->get_data('is_on_sale'));
		//$this->assertEquals('1', $this->object->get_data('average_rating'));
		//$this->assertEquals('1', $this->object->get_data('rating_count'));
		//$this->assertEquals('1', $this->object->get_data('category'));
		//$this->assertEquals('1', $this->object->get_data('status'));
		$this->assertEquals( 'http://example.org/?product=mon-nom', $this->object->get_data( 'url' ) );
		$this->assertEquals( '15.00', $this->object->get_data( 'price_excl_tax' ) );
		$this->assertEquals( '15.00', $this->object->get_data( 'price_incl_tax' ) );
		$this->assertEquals( '21.00', $this->object->get_data( 'price_before_discount_excl_tax' ) );
		$this->assertEquals( '21.00', $this->object->get_data( 'price_before_discount_incl_tax' ) );
		$this->assertEquals( '6.0', $this->object->get_data( 'discount_amount_excl_tax' ) );
		$this->assertEquals( '28.57', $this->object->get_data( 'discount_percent' ) );
		$this->assertEquals( '2016-10-07 00:00:00', $this->object->get_data( 'discount_start_date' ) );
		$this->assertEquals( '2017-10-07 00:00:00', $this->object->get_data( 'discount_end_date' ) );
		//$this->assertEquals('1', $this->object->get_data('price_shipping'));
		$this->assertEquals( 'GBP', $this->object->get_data( 'currency' ) );
		$this->assertEquals( '', $this->object->get_data( 'image_product' ) );
		$this->assertEquals( '', $this->object->get_data( 'image_url_10' ) );
		$this->assertEquals( 'simple', $this->object->get_data( 'type' ) );
		$this->assertEquals( '0', $this->object->get_data( 'parent_id' ) );
		$this->assertEquals( '', $this->object->get_data( 'variation' ) );
		$this->assertEquals( 'en_US', $this->object->get_data( 'language' ) );
		$this->assertEquals( 'ma description', $this->object->get_data( 'description' ) );
		$this->assertEquals( '<p>ma description</p>', $this->object->get_data( 'description_html' ) );
		$this->assertEquals( 'ma short description', $this->object->get_data( 'description_short' ) );
		$this->assertEquals( '<p>ma short description</p>', $this->object->get_data( 'description_short_html' ) );
		//$this->assertEquals('1', $this->object->get_data('tags'));
		//$this->assertEquals('1', $this->object->get_data('weight'));
		//$this->assertEquals('1', $this->object->get_data('dimensions'));
	}

	/**
	 * @covers Lengow_Product::_get_price()
	 */
	public function test_get_price() {
		$fixture = New Fixture();
		$this->assertEquals( '15.00', $fixture->invokeMethod( $this->object, "_get_price" ) );
	}

	/**
	 * @covers Lengow_Product::_get_price_shipping()
	 */
	/*public function test_get_price_shipping()
	{
		$fixture = New Fixture();
		$this->assertEquals('0', $fixture->invokeMethod($this->object, "_get_price_shipping"));
	}*/

	/**
	 * @covers Lengow_Product::_get_categories()
	 */
	public function test_get_categories() {
		$fixture = New Fixture();
		$this->assertEquals( 'cat 1', $fixture->invokeMethod( $this->object, "_get_categories" ) );
	}

	/**
	 * @covers Lengow_Product::_get_attribute_data()
	 */
	public function test_get_attribute_data() {
		$fixture = New Fixture();
		$this->assertEquals( '', $fixture->invokeMethod( $this->object, "_get_attribute_data" ) );
	}

	/**
	 * @covers Lengow_Product::_get_post_meta_data()
	 */
	public function test_get_post_meta_data() {
		$fixture = New Fixture();
		$this->assertEquals( '', $fixture->invokeMethod( $this->object, "_get_post_meta_data" ) );
		//$this->assertEquals('', $fixture->invokeMethod($this->object, "_get_post_meta_data"), array("total_sales"));
		//$this->assertEquals('', $fixture->invokeMethod($this->object, "_get_post_meta_data"), array("sku"));
	}

	/**
	 * @covers Lengow_Product::get_attributes
	 */
	public function testGet_attributes() {
		$this->assertEquals( array(), $this->object->get_attributes() );
	}

	/**
	 * @covers Lengow_Product::get_post_metas
	 */
	public function testGet_post_metas() {
		$this->assertEquals( array( 0 => 'total_sales', 1 => '_backorders' ), $this->object->get_post_metas() );
	}

	/**
	 * @covers Lengow_Product::extract_product_data_from_api
	 */
	/*public function testExtract_product_data_from_api()
	{
		$this->assertEquals('111', $this->object->extract_product_data_from_api($this->object));
	}*/

	/**
	 * @covers Lengow_Product::search_product
	 */
	public function testSearch_product() {
		$this->assertEquals( false, $this->object->search_product( "weight" ) );
		$this->assertEquals( false, $this->object->search_product( "weight", "sku" ) );
		$this->assertEquals( false, $this->object->search_product( "weight", "plop" ) );
		$this->assertEquals( 2, $this->object->search_product( "sku2", "sku" ) );
	}

	/**
	 * @covers Lengow_Product::publish
	 */
	public function testPublish() {
		global $wpdb;
		$this->object->publish( 1, 0 );
		$sql = "SELECT product_id FROM {$wpdb->prefix}lengow_product WHERE product_id = 1";
		$this->assertEquals( 0, count( $wpdb->get_results( $sql ) ) );

		$this->object->publish( 2, 1 );
		$sql = "SELECT product_id FROM {$wpdb->prefix}lengow_product WHERE product_id = 2";
		$this->assertEquals( 1, count( $wpdb->get_results( $sql ) ) );

		$this->object->publish( 666, 1 );
		$sql = "SELECT product_id FROM {$wpdb->prefix}lengow_product WHERE product_id = 666";
		$this->assertEquals( 1, count( $wpdb->get_results( $sql ) ) );
	}

	/**
	 * @covers Lengow_Product::get_lengow_products
	 */
	public function testGet_lengow_products() {
		$this->assertEquals( 4, count( $this->object->get_lengow_products() ) );
	}
}
