<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Lengow_Woocommerce
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if(!defined('WP_ADMIN')) define('WP_ADMIN', true); // pour la fonction includes() dans lengow.php


if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}


// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

//$_wp_dir = '/tmp/wordpress';
//require_once $_wp_dir . '/wp-config.php';

function run_activate_plugin( $plugin ) {
    $current = get_option( 'active_plugins' );
    $plugin = plugin_basename( trim( $plugin ) );

    if ( !in_array( $plugin, $current ) ) {
        $current[] = $plugin;
        sort( $current );
        do_action( 'activate_plugin', trim( $plugin ) );
        update_option( 'active_plugins', $current );
        do_action( 'activate_' . trim( $plugin ) );
        do_action( 'activated_plugin', trim( $plugin) );
    }

    return null;
}
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    $_SERVER['WP_TEST_UNIT'] = true;
    $_wp_dir = '/tmp/wordpress';
    require $_wp_dir . '/wp-content/plugins/woocommerce/woocommerce.php';
	require dirname( dirname( __FILE__ ) ) . '/lengow.php';
    /*require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-check.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-configuration.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-connector.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-exception.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-export.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-feed.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-file.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-import.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-import-order.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-install.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-log.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-main.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-marketplace.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-order.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-product.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-sync.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/class-lengow-translation.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/admin/class-lengow-admin.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/admin/class-lengow-admin-dashboard.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/admin/class-lengow-admin-settings.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/admin/class-lengow-admin-help.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/admin/class-lengow-admin-legals.php';
    require dirname( dirname( __FILE__ ) ) . '/includes/admin/class-lengow-admin-products.php';*/
    require dirname( dirname( __FILE__ ) ) . '/tests/Fixture.php';

}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';


