<?php
/**
 * Plugin Name: Lengow for Woocommerce 2.x
 * Plugin URI: http://www.lengow.com
 * Description: Export your catalog and synchronize your stock
 * Version: 2.0.0
 * Author: Lengow
 * Author URI: http://www.lengow.com
 * Requires at least: 3.5
 * Tested up to: 4.6
 *
 * Text Domain: lengow
 * Domain Path: /languages
 *
 * @package Lengow
 * @author Lengow
 */

/**
 * Prevent direct access
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	/**
	 * Main Lengow Class.
	 *
	 * @class Lengow
	 * @version 2.0.0
	 */
	class Lengow {
		/**
		 * Current version of plugin
		 * @var string
		 */
		public $version = '2.0.0';

		/**
		 * The plugin name
		 * @var string
		 */
		public $name = 'lengow-woocommerce';

		/**
		 * Instance of Lengow Admin
		 * @var Lengow Admin Object
		 */
		public $lengow_admin;

		/**
		 * Construct module Lengow for WooCommerce
		 */
		public function __construct() {
			$this->_define_constants();
			$this->includes();
			$this->_init_hooks();
		}

		/**
		 * Hook into actions
		 */
		private function _init_hooks() {
			register_activation_hook( __FILE__, array( 'Lengow_Install', 'install' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
			add_action( 'init', array( $this, 'init' ) );
		}

		/**
		 * Define Lengow Constants
		 */
		private function _define_constants() {
			$this->_define( 'LENGOW_PLUGIN_FILE', __FILE__ );
			$this->_define( 'LENGOW_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			$this->_define( 'LENGOW_PLUGIN_URL', untrailingslashit( WP_PLUGIN_URL . '/' . $this->name ) );
			$this->_define( 'LENGOW_VERSION', $this->version );
		}

		/**
		 * Define constant if not already set
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function _define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include all dependencies
		 */
		public function includes() {
			if ( is_admin() ) {
				include_once( 'includes/class-lengow-check.php' );
				include_once( 'includes/class-lengow-configuration.php' );
				include_once( 'includes/class-lengow-connector.php' );
				include_once( 'includes/class-lengow-exception.php' );
				include_once( 'includes/class-lengow-export.php' );
				include_once( 'includes/class-lengow-feed.php' );
				include_once( 'includes/class-lengow-file.php' );
				include_once( 'includes/class-lengow-import.php' );
				include_once( 'includes/class-lengow-import-order.php' );
				include_once( 'includes/class-lengow-install.php' );
				include_once( 'includes/class-lengow-log.php' );
				include_once( 'includes/class-lengow-main.php' );
				include_once( 'includes/class-lengow-marketplace.php' );
				include_once( 'includes/class-lengow-order.php' );
				include_once( 'includes/class-lengow-product.php' );
				include_once( 'includes/class-lengow-sync.php' );
				include_once( 'includes/class-lengow-translation.php' );
				include_once( 'includes/admin/class-lengow-admin.php' );
				include_once( 'includes/admin/class-lengow-dashboard.php' );
				include_once( 'includes/admin/class-lengow-settings.php' );
				include_once( 'includes/admin/class-lengow-help.php' );
				include_once('includes/admin/class-lengow-legals.php');
			}
		}

		/**
		 * Init Lengow when WordPress Initialises
		 */
		public function init() {
			if ( is_admin() ) {
				$this->lengow_admin = new Lengow_Admin();
			}
		}

		public function add_scripts() {
			wp_register_style( 'lengow_component_css', plugins_url( '/assets/css/lengow-components.css', __FILE__) );
			wp_register_style( 'lengow_admin_css', plugins_url( '/assets/css/lengow-layout.css', __FILE__), array('lengow_component_css') );
			wp_enqueue_style( 'lengow_admin_css' );

			wp_register_script( 'lengow_boostrap_js', plugins_url( '/assets/js/bootstrap.min.js', __FILE__) );
			wp_register_script( 'lengow_settings_js', plugins_url( '/assets/js/lengow/main_setting.js', __FILE__) );
			wp_register_script( 'lengow_admin_js', plugins_url( '/assets/js/lengow/admin.js', __FILE__), array('jquery', 'lengow_boostrap_js', 'lengow_settings_js') );
			wp_enqueue_script('lengow_admin_js');
		}
	}

	// Start module
	$GLOBALS['lengow'] = new Lengow();
}

