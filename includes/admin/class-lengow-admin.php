<?php
/**
 * Installation related functions and actions.
 *
 * @author   Lengow
 * @category Admin
 * @package  Lengow/Classes
 * @version  2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lengow_Admin Class.
 */
class Lengow_Admin
{
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
    public function __construct()
    {
        global $lengow, $woocommerce;
        $this->current_tab = (empty($_GET['tab'])) ? $this->_default_tab : sanitize_text_field(urldecode($_GET['tab']));
        add_action('admin_menu', array($this, 'lengow_admin_menu'));
    }

    /**
     * Add Lengow admin item menu
     */
    public function lengow_admin_menu()
    {

        $locale = new Lengow_Translation();

        add_menu_page(
            $locale->t('module.name'),
            $locale->t('module.name'),
            'manage_woocommerce',
            'lengow',
            array($this, 'lengow_display'),
            null,
            56
        );
    }

    /**
     * Routing
     */
    public function lengow_display()
    {
        $locale = new Lengow_Translation();
        $merchant_status = Lengow_Sync::get_status_account();
        $is_new_merchant = Lengow_Main::is_new_merchant();
        if ($this->current_tab != $this->_default_tab &&
            !($merchant_status['type'] == 'free_trial' && $merchant_status['day'] <= 0)
            && $merchant_status['type'] != 'bad_payer'
            && !$is_new_merchant
        ) {
            include_once 'views/html-admin-header.php';
        }
        switch ($this->current_tab) {
            case 'lengow_admin_products':
                Lengow_Admin_Products::html_display();
                break;
            case 'lengow_admin_orders':
                Lengow_Admin_Orders::display();
                break;
            case 'lengow_admin_help':
                Lengow_Admin_Help::display();
                break;
            case 'lengow_admin_settings':
                Lengow_Admin_Settings::display();
                break;
            case 'lengow_admin_legals':
                Lengow_Admin_Legals::display();
                break;
            default:
                Lengow_Admin_Dashboard::display();
                break;
        }
        include_once 'views/html-admin-footer.php';
    }

}
