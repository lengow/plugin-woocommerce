<?php
declare(strict_types=1);

use Lengow\Sdk\Resource\Api;

/**
 * Class Test_Connect_Cms
 *
 * @package Lengow_Woocommerce
 */

class Test_Connect_Cms extends WP_UnitTestCase
{
	use Trait_Mock_Client;

	public function test_will_connect() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();

		$post_data = [
			'do_action' => 'connect_cms',
			'access_token' => '***',
			'secret' => '***',
		];

		foreach ( $post_data as $k => $v ) {
			$_POST[ $k ] = $v;
		}

		ob_start();
		Lengow_Admin_Connection::post_process();
		$output = ob_get_clean();

		foreach ( $post_data as $k => $v ) {
			unset( $_POST[ $k ] );
		}

		$this->assertEquals(
			[ 123, '***', '***' ],
			Lengow_Configuration::get_access_id()
		);

		$expected = esc_html( ( new Lengow_Translation() )->t( 'connection.cms.success_title' ) );
		$this->assertMatchesRegularExpression(
			'/' . preg_quote( $expected ) . '/',
			$output,
			'Output should contain a success message'
		);
	}

	public function test_will_fail() {
		$this->init_with_mock_client( true, true );
		$this->mock_on_access_token_fail();
		$this->mock_basic_stuff( [], [ Api\Plugin::API ] );

		$post_data = [
			'do_action' => 'connect_cms',
			'access_token' => '***',
			'secret' => '***',
		];

		foreach ( $post_data as $k => $v ) {
			$_POST[ $k ] = $v;
		}

		ob_start();
		Lengow_Admin_Connection::post_process();
		$output = ob_get_clean();

		foreach ( $post_data as $k => $v ) {
			unset( $_POST[ $k ] );
		}

		$this->assertEquals(
			[ null, null, null ],
			Lengow_Configuration::get_access_id()
		);

		$expected = esc_html( ( new Lengow_Translation() )->t( 'connection.cms.failed_title' ) );
		$this->assertMatchesRegularExpression(
			'/' . preg_quote( $expected ) . '/',
			$output,
			'Output should contain a fail message'
		);
	}
}
