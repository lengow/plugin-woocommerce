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
		$this->mock_basic_stuff( [ Api\Restriction::API ] );

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

	public function test_admin_order_settings() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_order_settings';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'order_setting.screen.default_shipping_method_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'order_setting.screen.order_status_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'order_setting.screen.import_setting_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'order_setting.screen.currency_conversion_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'order_setting.screen.import_b2b_without_tax_title' ) ) )
		];

		foreach ( $expected as $expr ) {
			$this->assertMatchesRegularExpression(
				'/' . $expr . '/',
				$output,
				'Output should contain message: ' . $expr
			);
		}
	}

	public function test_admin_order_settings_post_process() {
		$post_data = [
			'action' => 'process',
			'lengow_import_default_shipping_method' => 'flat_rate',
			'lengow_id_waiting_shipment' => 'wc-on-hold',
			'lengow_id_shipped' => 'wc-completed',
			'lengow_id_cancel' => 'wc-cancelled',
			'lengow_id_shipped_by_mp' => 'wc-completed',
			'lengow_import_days' => 3,
			'lengow_import_ship_mp_enabled' => 0,
			'lengow_anonymize_email' => 'on',
			'lengow_type_anonymize_email' => 0,
			'lengow_import_stock_ship_mp' => 0,
			'lengow_currency_conversion' => 'on',
			'lengow_import_b2b_without_tax' => 'on', // changed this one
		];

		foreach ( $post_data as $k => $v ) {
			$_POST[ $k ] = $v;
		}

		$val = Lengow_Configuration::get( 'lengow_import_b2b_without_tax' );
		$this->assertEquals( '', $val );

		Lengow_Admin_Order_Settings::post_process();

		foreach ( $post_data as $k => $v ) {
			unset( $_POST[ $k ] );
		}

		$val = Lengow_Configuration::get( 'lengow_import_b2b_without_tax' );
		$this->assertEquals( '1', $val );
	}

	public function test_admin_help() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_help';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'help.screen.title' ) ) ),
		];

		foreach ( $expected as $expr ) {
			$this->assertMatchesRegularExpression(
				'/' . $expr . '/',
				$output,
				'Output should contain message: ' . $expr
			);
		}
	}

	public function test_admin_settings() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_settings';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'global_setting.screen.notification_alert_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'global_setting.screen.export_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'global_setting.screen.security_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'global_setting.screen.shop_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'global_setting.screen.debug_mode_title' ) ) ),
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'global_setting.screen.log_file_title' ) ) ),
		];

		foreach ( $expected as $expr ) {
			$this->assertMatchesRegularExpression(
				'/' . $expr . '/',
				$output,
				'Output should contain message: ' . $expr
			);
		}
	}

	public function test_admin_settings_post_process() {
		$post_data = [
			'action' => 'process',
			'lengow_report_mail_enabled' => 'on',
			'lengow_report_mail_address' => '',
			'lengow_product_types' => ['simple', 'variable', 'external', 'grouped'],
			'lengow_ip_enabled' => 0,
			'lengow_authorized_ip' => '',
			'lengow_store_enabled' => 'on',
			'lengow_catalog_id' => 456, // changed
			'lengow_debug_enabled' => 0,
			'lengow_plugin_env' => 'preprod',
			'lengow_account_id' => 124, // changed
			'lengow_access_token' => '****', // changed
			'lengow_secret_token' => '****', // changed
		];

		foreach ( $post_data as $k => $v ) {
			$_POST[ $k ] = $v;
		}

		Lengow_Configuration::update_value( Lengow_Configuration::ACCESS_TOKEN, '***' );
		Lengow_Configuration::update_value( Lengow_Configuration::SECRET, '***' );
		Lengow_Configuration::update_value( Lengow_Configuration::ACCOUNT_ID, '123' );
		Lengow_Configuration::update_value( Lengow_Configuration::CATALOG_IDS, '123' );

		$val = Lengow_Configuration::get( 'lengow_catalog_id' );
		$this->assertEquals( '123', $val );
		$this->assertEquals( [ '123', '***', '***' ], Lengow_Configuration::get_access_id() );

		Lengow_Admin_Main_Settings::post_process();

		foreach ( $post_data as $k => $v ) {
			unset( $_POST[ $k ] );
		}

		$val = Lengow_Configuration::get( 'lengow_catalog_id' );
		$this->assertEquals( '456', $val );
		$this->assertEquals( [ '124', '****', '****' ], Lengow_Configuration::get_access_id() );
	}

	public function test_admin_legals() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_legals';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'legals.screen.simplified_company' ) ) ),
		];

		foreach ( $expected as $expr ) {
			$this->assertMatchesRegularExpression(
				'/' . $expr . '/',
				$output,
				'Output should contain message: ' . $expr
			);
		}
	}

	public function test_admin_toolbox() {
		$this->init_with_mock_client();
		$this->mock_on_access_token();
		$this->mock_basic_stuff();

		$admin = new Lengow_Admin();
		$admin->current_tab = 'lengow_admin_toolbox';
		ob_start();
		$admin->lengow_display();
		$output = ob_get_clean();

		$expected = [
			preg_quote( esc_html( ( new Lengow_Translation() )->t( 'toolbox.screen.plugin_version' ) ) ),
			'\<b\>' . preg_quote( $GLOBALS['lengow']->version ) . '\<\/b\>'
		];

		foreach ( $expected as $expr ) {
			$this->assertMatchesRegularExpression(
				'/' . $expr . '/',
				$output,
				'Output should contain message: ' . $expr
			);
		}

		if ( preg_match( '/Some files have been changed by the customer/', $output ) ) {
			$this->addWarning( 'checkmd5.csv file not up to date !' );
		}
	}
}
