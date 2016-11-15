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
 * Lengow_Admin_Orders Class.
 */
class Lengow_Admin_Orders {
	/**
	 * Process Post Parameters
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
	 * Generate message array (new, update and errors)
	 *
	 * @param array $return
	 *
	 * @return array
	 */
	public function load_message( $return ) {
		$locale  = new Lengow_Translation();
		$message = array();
		if ( Lengow_Import::is_in_process() ) {
			$message[] = $locale->t(
				'lengow_log.error.rest_time_to_import',
				array( 'rest_time' => Lengow_Import::rest_time_to_import() )
			);
		}
		if ( isset( $return['order_new'] ) && $return['order_new'] > 0 ) {
			$message[] = $locale->t(
				'lengow_log.error.nb_order_imported',
				array( 'nb_order' => (int) $return['order_new'] )
			);
		}
		if ( isset( $return['order_error'] ) && $return['order_error'] > 0 ) {
			$message[] = $locale->t(
				'lengow_log.error.nb_order_with_error',
				array( 'nb_order' => (int) $return['order_error'] )
			);
			$message[] = $locale->t(
				'lengow_log.error.check_logs',
				array( 'link' => admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ) )
			);
		}
		if ( isset( $return['error'] ) && $return['error'] != false ) {
			$message[] = Lengow_Main::decode_log_message( $return['error'] );
		}
		if ( count( $message ) == 0 ) {
			$message[] = $locale->t( 'lengow_log.error.no_notification' );
		}

		return $message;
	}

	/**
	 * Get warning messages
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
		if ( count( $warning_messages ) > 0 ) {
			$warning_message = join( '<br/>', $warning_messages );
		} else {
			$warning_message = false;
		}

		return $warning_message;
	}

	/**
	 * Get all last importation information
	 */
	public function assign_last_importation_infos() {
		$last_import      = Lengow_Main::get_last_import();
		$order_collection = array(
			'last_import_date' => $last_import['timestamp'] != 'none'
				? strftime( '%A %d %B %Y @ %X', $last_import['timestamp'] )
				: '',
			'last_import_type' => $last_import['type']
		);

		return $order_collection;
	}

	/**
	 * Display admin orders page
	 */
	public static function display() {
		$lengow_admin_orders = new Lengow_Admin_Orders();
		$warning_message     = $lengow_admin_orders->assign_warning_messages();
		$order_collection    = $lengow_admin_orders->assign_last_importation_infos();
		$locale              = new Lengow_Translation();
		include_once 'views/orders/html-admin-orders.php';
	}

}