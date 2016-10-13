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
 * Lengow_Admin_Orders Class.
 */
class Lengow_Admin_Orders
{
    /**
     * Get warning messages
     */
    public function assign_warning_messages()
    {
        $locale = new Lengow_Translation();
        $warning_messages = array();
        if (Lengow_Configuration::get('lengow_preprod_enabled')) {
            $warning_messages[] = $locale->t(
                'order.screen.preprod_warning_message',
                array('url' => admin_url('admin.php?page=lengow&tab=lengow_admin_settings'))
            );
        }
        if (count($warning_messages) > 0) {
            $warning_message = join('<br/>', $warning_messages);
        } else {
            $warning_message = false;
        }
        return $warning_message;
    }

    /**
     * Get all last importation informations
     */
    public function assign_last_importation_infos()
    {
        $last_import =  Lengow_Main::get_last_import();
        $order_collection = array(
            'last_import_date'  => $last_import['timestamp'],
            'last_import_type'  => $last_import['type']
        );

        return $order_collection;
    }

    /**
     * Display admin orders page
     */
    public static function display() {
        $lengow_admin_orders = new Lengow_Admin_Orders();
        $warning_message = $lengow_admin_orders->assign_warning_messages();
        $order_collection = $lengow_admin_orders->assign_last_importation_infos();
        $locale = new Lengow_Translation();
        $keys   = Lengow_Configuration::get_keys();
        include_once 'views/orders/html-admin-orders.php';

    }

}