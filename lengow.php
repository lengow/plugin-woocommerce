<?php
/**
 * Lengow
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category  Lengow
 * @package   lengow-woocommerce
 * @author    Team module <team-module@lengow.com>
 * @copyright 2017 Lengow SAS
 * @license   https://www.gnu.org/licenses/old-licenses/gpl-2.0 GNU General Public License
 */

/**
 *
 * Plugin Name: Lengow for WooCommerce
 * Plugin URI: http://www.lengow.com
 * Description: Lengow allows you to easily export your product catalogue from your WooCommerce store and sell on Amazon, Cdiscount, Google Shopping, Criteo, LeGuide.com, Ebay, Bing,... Choose from our 1,800 available marketing channels!
 * Version: 2.2.0
 * Author: Lengow
 * Author URI: http://www.lengow.com
 * Requires at least: 3.5
 * Tested up to: 4.6
 *
 * Text Domain: lengow
 * Domain Path: /languages
 */

// prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wp_version;

// check if WooCommerce is active.
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	/**
	 * Main Lengow Class
	 */
	class Lengow {

		/**
		 * @var string current version of plugin.
		 */
		public $version = '2.2.0';

		/**
		 * @var string plugin name.
		 */
		public $name = 'lengow-woocommerce';

		/**
		 * @var Lengow_Admin Lengow Admin Instance.
		 */
		public $lengow_admin;

		/**
		 * Construct module Lengow for WooCommerce.
		 */
		public function __construct() {
			$this->_define_constants();
			$this->includes();
			$this->_init_hooks();
		}

		/**
		 * Hook into actions.
		 */
		private function _init_hooks() {
			register_activation_hook( __FILE__, array( 'Lengow_Install', 'install' ) );
			add_action( 'init', array( $this, 'init' ) );

			if ( isset( $_GET['page'] ) && $_GET['page'] === 'lengow' ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
				add_filter( 'pre_site_transient_update_core', array( $this, 'remove_core_updates' ) );
				add_filter( 'pre_site_transient_update_plugins', array( $this, 'remove_core_updates' ) );
				add_filter( 'pre_site_transient_update_themes', array( $this, 'remove_core_updates' ) );
			}
		}

		/**
		 * Define Lengow Constants.
		 */
		private function _define_constants() {
			$this->_define( 'LENGOW_PLUGIN_FILE', __FILE__ );
			$this->_define( 'LENGOW_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			$this->_define( 'LENGOW_PLUGIN_URL', untrailingslashit( WP_PLUGIN_URL . '/' . $this->name ) );
			$this->_define( 'LENGOW_VERSION', $this->version );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param string $name constant name
		 * @param string|boolean $value constant value
		 */
		private function _define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include all dependencies.
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
				include_once( 'includes/admin/class-lengow-admin-dashboard.php' );
				include_once( 'includes/admin/class-lengow-admin-help.php' );
				include_once( 'includes/admin/class-lengow-admin-legals.php' );
				include_once( 'includes/admin/class-lengow-admin-order-settings.php' );
				include_once( 'includes/admin/class-lengow-admin-orders.php' );
				include_once( 'includes/admin/class-lengow-admin-products.php' );
				include_once( 'includes/admin/class-lengow-admin-main-settings.php' );
			}
		}

		/**
		 * Init Lengow when WordPress Initialises.
		 */
		public function init() {
			if ( is_admin() ) {
				// init ajax actions.
				add_action( 'wp_ajax_post_process', array( 'Lengow_Admin_Products', 'post_process' ) );
				add_action( 'admin_action_dashboard_get_process', array( 'Lengow_Admin_Dashboard', 'get_process' ) );
				add_action( 'wp_ajax_post_process_dashboard', array( 'Lengow_Admin_Dashboard', 'post_process' ) );
				add_action( 'wp_ajax_post_process_orders', array( 'Lengow_Admin_Orders', 'post_process' ) );

				// check logs download to prevent the occurrence of the wordpress html header.
				$download = null;
				if ( isset( $_GET['action'] ) ) {
					$download = $_GET['action'];
				}
				switch ( $download ) {
					case 'download':
						$file = isset( $_GET['file'] ) ? $_GET['file'] : null;
						Lengow_Log::download( $file );
						break;
					case 'download_all':
						Lengow_Log::download();
						break;
				}
				$this->lengow_admin = new Lengow_Admin();
			}
		}

		/**
		 * Add CSS and JS.
		 */
		public function add_scripts() {
			wp_register_style( 'lengow_font_awesome', plugins_url( '/assets/css/font-awesome.css', __FILE__ ) );
			wp_register_style( 'lengow_select2_css', plugins_url( '/assets/css/select2.css', __FILE__ ) );
			wp_register_style( 'lengow_layout_css', plugins_url( '/assets/css/lengow-layout.css', __FILE__ ) );
			wp_register_style( 'lengow_components_css', plugins_url( '/assets/css/lengow-components.css', __FILE__ ) );
			wp_register_style(
				'lengow_admin_css',
				plugins_url( '/assets/css/lengow-pages.css', __FILE__ ),
				array(
					'lengow_font_awesome',
					'lengow_select2_css',
					'lengow_layout_css',
					'lengow_components_css',
				)
			);
			wp_enqueue_style( 'lengow_admin_css' );

			if ( intval( get_bloginfo( 'version' ) ) >= 4 ) {
				wp_register_script( 'lengow_boostrap_js', plugins_url( '/assets/js/bootstrap_v3.min.js', __FILE__ ) );
			} else {
				wp_register_script( 'lengow_boostrap_js', plugins_url( '/assets/js/bootstrap.min.js', __FILE__ ) );
			}
			wp_register_script( 'lengow_main_settings', plugins_url( '/assets/js/lengow/main_setting.js', __FILE__ ) );
			wp_register_script(
				'lengow_order_settings',
				plugins_url( '/assets/js/lengow/order_setting.js', __FILE__ )
			);
			wp_register_script( 'lengow_select2', plugins_url( '/assets/js/select2.js', __FILE__ ) );
			wp_register_script( 'lengow_products', plugins_url( '/assets/js/lengow/products.js', __FILE__ ) );
			wp_register_script( 'lengow_home', plugins_url( '/assets/js/lengow/home.js', __FILE__ ) );
			wp_register_script( 'lengow_orders', plugins_url( '/assets/js/lengow/orders.js', __FILE__ ) );
			wp_register_script(
				'lengow_admin_js',
				plugins_url( '/assets/js/lengow/admin.js', __FILE__ ),
				array(
					'jquery',
					'lengow_boostrap_js',
					'lengow_products',
					'lengow_select2',
					'lengow_home',
					'lengow_orders',
					'lengow_main_settings',
					'lengow_order_settings',
				)
			);
			wp_enqueue_script( 'lengow_admin_js' );
			// must be added to instantiate admin-ajax.php.
			wp_localize_script( 'lengow_admin_js', 'ajaxurl', admin_url( 'admin-ajax.php' ) );
		}

		/**
		 * Remove Wordpress's updates messages.
		 *
		 * @return object
		 */
		public function remove_core_updates() {
			global $wp_version;

			return (object) array(
				'last_checked'    => time(),
				'version_checked' => $wp_version,
			);
		}
	}

	// start module.
	$GLOBALS['lengow'] = new Lengow();
	if ( $wp_version <= '4.0.0' ) {
		$GLOBALS['hook_suffix'] = 'toplevel_page_lengow';
	}
}
