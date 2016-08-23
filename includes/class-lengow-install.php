<?php
/**
 * Installation related functions and actions.
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Install Class.
 */
class Lengow_Install {

	/**
	 * Installation of module
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function install() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$table_name = $wpdb->prefix . 'lengow_product';
		$sql        = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' ('
		              . ' `product_id` bigint(20) NOT NULL,'
		              . ' UNIQUE KEY `product_id` (`product_id`));';
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'lengow_orders';
		$sql        = 'CREATE TABLE IF NOT EXISTS ' . $table_name . ' ('
		              . ' `id_order` INTEGER(10) UNSIGNED NOT NULL,'
		              . ' `id_order_lengow` VARCHAR(50),'
		              . ' `id_flux` INTEGER(11) UNSIGNED NOT NULL,'
		              . ' `marketplace` VARCHAR(100),'
		              . ' `message` TEXT,'
		              . ' `total_paid` DECIMAL(17,2) NOT NULL,'
		              . ' `carrier` VARCHAR(100),'
		              . ' `tracking` VARCHAR(100),'
		              . ' `extra` TEXT,'
		              . ' `date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,'
		              . ' PRIMARY KEY(id_order),'
		              . ' INDEX (`id_order_lengow`),'
		              . ' INDEX (`id_flux`),'
		              . ' INDEX (`marketplace`),'
		              . ' INDEX (`date_add`))';
		dbDelta( $sql );

		add_option( 'lengow_version', LENGOW_VERSION );

	}
}

