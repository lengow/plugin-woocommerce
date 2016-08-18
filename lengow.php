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
if (!defined('ABSPATH')) exit;

/**
 * Check if WooCommerce is active
 **/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) :

    /**
     * Main Lengow Class.
     *
     * @class Lengow
     * @version    2.0.0
     */
    class Lengow
    {

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
         *
         */
        public function __construct()
        {
            $this->_include();
            $this->_init_hooks();
            $this->_define_constants();
        }

        /**
         * Init plugin
         *
         * @return void
         */
        public function init()
        {
            load_plugin_textdomain('lengow', false, dirname(plugin_basename(__FILE__)) . '/languages/');
            if (is_admin()) {
                $this->lengow_admin = new Lengow_Admin();
            }
        }

        /**
         * Include all dependencies
         *
         * @return void
         */
        private function _include()
        {
            if (is_admin()) {
                require_once('includes/class-lengow-install.php');
	            require_once('includes/admin/class-lengow-admin.php');
	            require_once('includes/admin/class-lengow-dashboard.php');
            }
        }

        /**
         * Define Lengow Constants.
         */
        private function _define_constants()
        {
            $this->_define('LENGOW_PATH', dirname(__FILE__));
            $this->_define('LENGOW_URL', WP_PLUGIN_URL . '/' . $this->name);
            $this->_define('LENGOW_VERSION', $this->version);
        }

        /**
         * Define constant if not already set.
         *
         * @param  string $name
         * @param  string|bool $value
         */
        private function _define($name, $value)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        private function _init_hooks()
        {
            register_activation_hook(__FILE__, array('Lengow_install', 'install'));
            add_action('init', array($this, 'init'));
        }
    }

    $GLOBALS['lengow'] = new Lengow();
    
endif;
