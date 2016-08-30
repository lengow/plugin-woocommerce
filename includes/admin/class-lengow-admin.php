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
 * Lengow_Admin Class.
 */
class Lengow_Admin {

	/**
	 * Init Lengow for WooCommerce
	 * Init module administration and action
	 */
	public function __construct() {
		global $lengow, $woocommerce;
		// Add Menu item
		add_action( 'admin_menu', array( $this, 'lengow_admin_menu' ) );
	}

	/**
	 * Add Lengow admin item menu
	 */
	public function lengow_admin_menu() {
		$locale = new Lengow_Translation();
		add_menu_page(
			$locale->t('module.name'),
			$locale->t('module.name'),
			'manage_woocommerce',
			'lengow',
			array( 'Lengow_Dashboard', 'display' ),
			null,
			56
		);
	}
}

