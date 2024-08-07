<?php
/**
 * Class TestLengowFactory
 *
 * @package Lengow_Woocommerce
 */

/**
 * Sample test case.
 */
class TestLengowFactory extends WP_UnitTestCase
{

	/**
	 * @throws Exception
	 */
	function test_make_instance() {
		$factory = Lengow_Factory::instance();
		$factory->bind('Lengow_Main', 'Lengow_Main' );

		$main = $factory->get('Lengow_Main');
		$this->assertInstanceOf('Lengow_Main', $main);

		$main2 = $factory->get('Lengow_Main');
		$this->assertTrue($main === $main2, 'Instances should be the same');

		$main3 = $factory->make('Lengow_Main');
		$this->assertFalse($main === $main3, 'Instances should not be the same');

		$this->assertTrue(Lengow_Factory::instance() === $factory, 'Instances should be the same');
	}

	function test_make_instance_will_fail() {
		$factory = Lengow_Factory::instance();
		$factory->bind('Lengow_Main2', 'Lengow_Main' );

		$this->expectException(Exception::class);
		$factory->get('Class_Unknown');

		$this->expectException(Exception::class);
		$factory->make('Class_Unknown');
	}

	/**
	 * @throws Exception
	 */
	function test_factory() {
		$factory = Lengow_Factory::instance();
		$factory->bind('Test_Factory', function () {
			return new Lengow_Main();
		} );

		$main = $factory->get('Test_Factory');
		$this->assertInstanceOf('Lengow_Main', $main);

		$main_cp = $factory->get('Test_Factory');
		$this->assertTrue($main === $main_cp, 'Instances should be the same');

		$factory->bind('Test_Factory', function () {
			return new Lengow_Main();
		} );

		$main_cp = $factory->get('Test_Factory');
		$this->assertTrue($main === $main_cp, 'Instances should be the same');

		$factory->bind('Test_Factory', function () {
			return new Lengow_Main();
		}, true );

		$main_cp = $factory->get('Test_Factory');
		$this->assertFalse($main === $main_cp, 'Instances should not be the same');
	}
}
