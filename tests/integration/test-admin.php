<?php
declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use Http\Message\RequestMatcher\RequestMatcher;
use Lengow\Sdk\Resource\Api;

/**
 * Class Test_Admin
 *
 * @package Lengow_Woocommerce
 */

class Test_Admin extends WP_UnitTestCase
{
	use Trait_Mock_Client;
	use Trait_Fixtures;

	public function test_admin_dashboard() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_dashboard';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			esc_html( ( new Lengow_Translation() )->t( 'dashboard.screen.products_text' ) ),
			esc_html( ( new Lengow_Translation() )->t( 'dashboard.screen.orders_text' ) ),
			esc_html( ( new Lengow_Translation() )->t( 'dashboard.screen.settings_text' ) ),
			esc_html( ( new Lengow_Translation() )->t( 'dashboard.screen.some_help_title' ) )
		];

		foreach ( $expected as $expr ) {
			$this->assertMatchesRegularExpression(
				'/' . preg_quote( $expr ) . '/',
				$output,
				'Output should contain message: ' . $expr
			);
		}
	}

	public function test_admin_dashboard_end_free_trial() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();

		$json = json_decode( file_get_contents(
			$this->sdk_mock_dir . 'restriction-restrictions.json'
		), true );

		$json['isFreeTrial'] = true;
		$json['isExpired'] = true;

		$this->mock_client->on(
			new RequestMatcher( Api\Restriction::API ),
			new Response( 200, [], json_encode( $json ) )
		);

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_dashboard';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			esc_html( ( new Lengow_Translation() )->t( 'status.screen.title_end_free_trial' ) ),
			esc_html( ( new Lengow_Translation() )->t( 'status.screen.subtitle_end_free_trial' ) ),
		];

		foreach ( $expected as $expr ) {
			$this->assertMatchesRegularExpression(
				'/' . preg_quote( $expr ) . '/',
				$output,
				'Output should contain message: ' . $expr
			);
		}
	}

	public function test_admin_products() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();
		$products = $this->create_default_products();

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_products';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			count( $products ) . '<\/span>[ \r\n\t]*' . preg_quote( esc_html( ( new Lengow_Translation() )->t( 'product.screen.nb_exported' ) ) ),
			count( $products ) . '<\/span>[ \r\n\t]*' . preg_quote( esc_html( ( new Lengow_Translation() )->t( 'product.screen.nb_available' ) ) )
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
