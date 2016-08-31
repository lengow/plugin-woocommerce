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
     * Current tab
     */
    public $current_tab = false;

    /**
     * Default tab
     */
    private $_default_tab = 'lengow';

	/**
	 * Init Lengow for WooCommerce
	 * Init module administration and action
	 */
	public function __construct() {
		global $lengow, $woocommerce;
        $this->current_tab = ( empty( $_GET['tab'] ) ) ? $this->_default_tab : sanitize_text_field( urldecode( $_GET['tab'] ) );
        add_action( 'admin_menu', array( $this, 'lengow_admin_menu' ) );
	}

	/**
	 * Add Lengow admin item menu
	 */
	public function lengow_admin_menu() {
		add_menu_page(
			'Lengow',
			'Lengow',
			'manage_woocommerce',
			'lengow',
			array( $this, 'lengow_display' ),
			null,
			56
		);

	}

    /**
     * Routing
     */
    public function lengow_display() {
        //TODO Add condition for dashboard
        //if ($this->current_tab != $this->_default_tab) {
            include_once 'views/html-admin-header.php';
        //}

        switch ($this->current_tab) {
            case 'lengow_product':
                Lengow_Product::display();
                break;
            case 'lengow_help':
                Lengow_Help::display();
                break;
            case 'lengow_settings':
                Lengow_Settings::display();
                break;
            case 'lengow_legals':
                Lengow_Legals::display();
                break;
            default:
                Lengow_Dashboard::display();
                break;
        }
        include_once 'views/html-admin-footer.php';
    }

}
