<?php
/**
 * Class TestConnector
 *
 * @package Lengow_Woocommerce
 */

/**
 * Sample test case.
 */
class TestConnector extends WP_UnitTestCase {


	/**
	 * @return void
	 * @throws Lengow_Exception
	 */
	function test_plugin_using_mocked_object() {
		$mock_connector = $this->getMockBuilder( 'Lengow_Connector' )
			->onlyMethods( array( 'connect', 'make_request' ) )
			->disableOriginalConstructor()
			->getMock();

		$mock_connector->expects( $this->any() )
			->method( 'connect' )
			->willReturn( 'token' );

		$mock_connector->expects( $this->any() )
			->method( 'make_request' )
			->willReturn( '{}' );

		$result = $mock_connector->get( '/v3.0/plans' );

		$this->assertEquals( array(), $result );
	}
}
