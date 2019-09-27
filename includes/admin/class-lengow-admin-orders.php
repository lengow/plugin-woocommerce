<?php
/**
 * Admin order page
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
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Admin_Orders Class.
 */
class Lengow_Admin_Orders {

	/**
	 * Display admin orders page.
	 */
	public static function display() {
		$lengow_admin_orders = new Lengow_Admin_Orders();
		$warning_message     = $lengow_admin_orders->assign_warning_messages();
		$order_collection    = $lengow_admin_orders->assign_last_importation_infos();
		$locale              = new Lengow_Translation();
		include_once 'views/html-admin-header-order.php';
		include_once 'views/orders/html-admin-orders.php';
	}

	/**
	 * Process Post Parameters.
	 */
	public function post_process() {
		$lengow_admin_orders = new Lengow_Admin_Orders();
		$locale              = new Lengow_Translation();
		$action              = isset( $_POST['do_action'] ) ? $_POST['do_action'] : false;
		if ( $action ) {
			switch ( $action ) {
				case 'import_all':
					$import                   = new Lengow_Import(
						array( 'log_output' => false )
					);
					$return                   = $import->exec();
					$message                  = $lengow_admin_orders->load_message( $return );
					$order_collection         = $lengow_admin_orders->assign_last_importation_infos();
					$data                     = array();
					$data['message']          = '<div class="lengow_alert">' . join( '<br/>', $message ) . '</div>';
					$data['import_orders']    = $locale->t( 'order.screen.button_update_orders' );
					$data['last_importation'] = $order_collection['last_import_date'];
					echo json_encode( $data );
					break;
			}
			exit();
		}
	}

	/**
	 * Generate message array (new, update and errors).
	 *
	 * @param array $return import informations
	 *
	 * @return array
	 */
	public function load_message( $return ) {
		$locale   = new Lengow_Translation();
		$messages = array();
		if ( isset( $return['error'] ) && $return['error'] != false ) {
			$messages[] = Lengow_Main::decode_log_message( $return['error'] );

			return $messages;
		}
		if ( isset( $return['order_new'] ) && $return['order_new'] > 0 ) {
			$messages[] = $locale->t(
				'lengow_log.error.nb_order_imported',
				array( 'nb_order' => (int) $return['order_new'] )
			);
		}
		if ( isset( $return['order_error'] ) && $return['order_error'] > 0 ) {
			$messages[] = $locale->t(
				'lengow_log.error.nb_order_with_error',
				array( 'nb_order' => (int) $return['order_error'] )
			);
			$messages[] = $locale->t(
				'lengow_log.error.check_logs',
				array( 'link' => admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ) )
			);
		}
		if ( empty( $messages ) ) {
			$messages[] = $locale->t( 'lengow_log.error.no_notification' );
		}

		return $messages;
	}

	/**
	 * Get warning messages.
	 */
	public function assign_warning_messages() {
		$locale           = new Lengow_Translation();
		$warning_messages = array();
		if ( Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) {
			$warning_messages[] = $locale->t(
				'order.screen.preprod_warning_message',
				array( 'url' => admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ) )
			);
		}
		if ( ! empty( $warning_messages ) ) {
			$warning_message = join( '<br/>', $warning_messages );
		} else {
			$warning_message = false;
		}

		return $warning_message;
	}

	/**
	 * Get all last importation information.
	 */
	public function assign_last_importation_infos() {
		$last_import      = Lengow_Main::get_last_import();
		$order_collection = array(
			'last_import_date' => $last_import['timestamp'] != 'none'
				? strftime( '%A %d %B %Y @ %X', $last_import['timestamp'] )
				: '',
			'last_import_type' => $last_import['type'],
		);

		return $order_collection;
	}
}
