<?php
/**
 * All components for toolbox
 *
 * Copyright 2021 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  includes
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2021 Lengow SAS
 * @license     https://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lengow_Toolbox_Element Class.
 */
class Lengow_Toolbox_Element {

	/* Array data for toolbox content creation */
	const DATA_HEADER = 'header';
	const DATA_TITLE = 'title';
	const DATA_STATE = 'state';
	const DATA_MESSAGE = 'message';
	const DATA_HELP = 'help';
	const DATA_HELP_LINK = 'help_link';
	const DATA_HELP_LABEL = 'help_label';

	/**
	 * @var Lengow_Translation Lengow translation instance.
	 */
	private $_locale;

	/**
	 * Construct new Lengow check.
	 */
	public function __construct() {
		$this->_locale                      = new Lengow_Translation();
		Lengow_Translation::$force_iso_code = Lengow_Translation::DEFAULT_ISO_CODE;
	}

	/**
	 * Get array of requirements and their status.
	 *
	 * @return string
	 */
	public function get_check_list() {
		$checklist_data = Lengow_Toolbox::get_data( Lengow_Toolbox::DATA_TYPE_CHECKLIST );
		$checklist      = array(
			array(
				self::DATA_TITLE      => $this->_locale->t( 'toolbox.index.curl_message' ),
				self::DATA_HELP       => $this->_locale->t( 'toolbox.index.curl_help' ),
				self::DATA_HELP_LINK  => $this->_locale->t( 'toolbox.index.curl_help_link' ),
				self::DATA_HELP_LABEL => $this->_locale->t( 'toolbox.index.curl_help_label' ),
				self::DATA_STATE      => (int) $checklist_data[ Lengow_Toolbox::CHECKLIST_CURL_ACTIVATED ],
			),
			array(
				self::DATA_TITLE      => $this->_locale->t( 'toolbox.index.simple_xml_message' ),
				self::DATA_HELP       => $this->_locale->t( 'toolbox.index.simple_xml_help' ),
				self::DATA_HELP_LINK  => $this->_locale->t( 'toolbox.index.simple_xml_help_link' ),
				self::DATA_HELP_LABEL => $this->_locale->t( 'toolbox.index.simple_xml_help_label' ),
				self::DATA_STATE      => (int) $checklist_data[ Lengow_Toolbox::CHECKLIST_SIMPLE_XML_ACTIVATED ],
			),
			array(
				self::DATA_TITLE      => $this->_locale->t( 'toolbox.index.json_php_message' ),
				self::DATA_HELP       => $this->_locale->t( 'toolbox.index.json_php_help' ),
				self::DATA_HELP_LINK  => $this->_locale->t( 'toolbox.index.json_php_help_link' ),
				self::DATA_HELP_LABEL => $this->_locale->t( 'toolbox.index.json_php_help_label' ),
				self::DATA_STATE      => (int) $checklist_data[ Lengow_Toolbox::CHECKLIST_JSON_ACTIVATED ],
			),
			array(
				self::DATA_TITLE      => $this->_locale->t( 'toolbox.index.checksum_message' ),
				self::DATA_HELP       => $this->_locale->t( 'toolbox.index.checksum_help' ),
				self::DATA_HELP_LINK  => '/wp-content/plugins/lengow-woocommerce/toolbox/checksum.php',
				self::DATA_HELP_LABEL => $this->_locale->t( 'toolbox.index.checksum_help_label' ),
				self::DATA_STATE      => (int) $checklist_data[ Lengow_Toolbox::CHECKLIST_MD5_SUCCESS ],
			),
		);

		return $this->get_admin_content( $checklist );
	}

	/**
	 * Get array of requirements and their status.
	 *
	 * @return string
	 */
	public function get_global_information() {
		global $woocommerce;
		$plugin_data = Lengow_Toolbox::get_data( Lengow_Toolbox::DATA_TYPE_PLUGIN );
		$checklist   = array(
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.wordpress_version' ),
				self::DATA_MESSAGE => $plugin_data[ Lengow_Toolbox::PLUGIN_CMS_VERSION ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.woocommerce_version' ),
				self::DATA_MESSAGE => $woocommerce->version,
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.plugin_version' ),
				self::DATA_MESSAGE => $plugin_data[ Lengow_Toolbox::PLUGIN_VERSION ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.ip_server' ),
				self::DATA_MESSAGE => $plugin_data[ Lengow_Toolbox::PLUGIN_SERVER_IP ],
			),
			array(
				self::DATA_TITLE => $this->_locale->t( 'toolbox.index.ip_enabled' ),
				self::DATA_STATE => (int) $plugin_data[ Lengow_Toolbox::PLUGIN_AUTHORIZED_IP_ENABLE ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.ip_authorized' ),
				self::DATA_MESSAGE => implode( ', ', $plugin_data[ Lengow_Toolbox::PLUGIN_AUTHORIZED_IPS ] ),
			),
			array(
				self::DATA_TITLE => $this->_locale->t( 'toolbox.index.debug_disabled' ),
				self::DATA_STATE => (int) $plugin_data[ Lengow_Toolbox::PLUGIN_DEBUG_MODE_DISABLE ],
			),
			array(
				self::DATA_TITLE => $this->_locale->t( 'toolbox.index.write_permission' ),
				self::DATA_STATE => (int) $plugin_data[ Lengow_Toolbox::PLUGIN_WRITE_PERMISSION ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.toolbox_url' ),
				self::DATA_MESSAGE => $plugin_data[ Lengow_Toolbox::PLUGIN_TOOLBOX_URL ],
			),
		);

		return $this->get_admin_content( $checklist );
	}

	/**
	 * Get array of requirements and their status.
	 *
	 * @return string
	 */
	public function get_import_information() {
		$synchronization_data = Lengow_Toolbox::get_data( Lengow_Toolbox::DATA_TYPE_SYNCHRONIZATION );
		$last_synchronization = $synchronization_data[ Lengow_Toolbox::SYNCHRONIZATION_LAST_SYNCHRONIZATION ];
		if ( 0 === $last_synchronization ) {
			$last_import_date = $this->_locale->t( 'toolbox.index.last_import_none' );
			$last_import_type = $this->_locale->t( 'toolbox.index.last_import_none' );
		} else {
			$last_import_date          = Lengow_Main::get_date_in_correct_format( $last_synchronization, true );
			$last_synchronization_type = $synchronization_data[ Lengow_Toolbox::SYNCHRONIZATION_LAST_SYNCHRONIZATION_TYPE ];
			if ( Lengow_Import::TYPE_CRON === $last_synchronization_type ) {
				$last_import_type = $this->_locale->t( 'toolbox.index.last_import_cron' );
			} else {
				$last_import_type = $this->_locale->t( 'toolbox.index.last_import_manual' );
			}
		}
		if ( $synchronization_data[ Lengow_Toolbox::SYNCHRONIZATION_SYNCHRONIZATION_IN_PROGRESS ] ) {
			$import_in_progress = Lengow_Main::decode_log_message(
				'toolbox.index.rest_time_to_import',
				null,
				array( 'rest_time' => Lengow_Import::rest_time_to_import() )
			);
		} else {
			$import_in_progress = $this->_locale->t( 'toolbox.index.no_import' );
		}
		$checklist = array(
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.global_token' ),
				self::DATA_MESSAGE => $synchronization_data[ Lengow_Toolbox::SYNCHRONIZATION_CMS_TOKEN ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.url_import' ),
				self::DATA_MESSAGE => $synchronization_data[ Lengow_Toolbox::SYNCHRONIZATION_CRON_URL ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.nb_order_imported' ),
				self::DATA_MESSAGE => $synchronization_data[ Lengow_Toolbox::SYNCHRONIZATION_NUMBER_ORDERS_IMPORTED ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.nb_order_to_be_sent' ),
				self::DATA_MESSAGE => $synchronization_data[
					Lengow_Toolbox::SYNCHRONIZATION_NUMBER_ORDERS_WAITING_SHIPMENT
				],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.nb_order_with_error' ),
				self::DATA_MESSAGE => $synchronization_data[ Lengow_Toolbox::SYNCHRONIZATION_NUMBER_ORDERS_IN_ERROR ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.import_in_progress' ),
				self::DATA_MESSAGE => $import_in_progress,
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.shop_last_import' ),
				self::DATA_MESSAGE => $last_import_date,
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.shop_type_import' ),
				self::DATA_MESSAGE => $last_import_type,
			),
		);

		return $this->get_admin_content( $checklist );
	}

	/**
	 * Get array of requirements and their status, no multi-store on wordpress.
	 *
	 * @return string
	 */
	public function get_export_information() {
		$export_data = Lengow_Toolbox::get_data( Lengow_Toolbox::DATA_TYPE_SHOP );
		$data        = $export_data[0];
		if ( 0 === $data[ Lengow_Toolbox::SHOP_LAST_EXPORT ] ) {
			$last_export = $this->_locale->t( 'toolbox.index.last_import_none' );
		} else {
			$last_export = Lengow_Main::get_date_in_correct_format( $data['last_export'], true );
		}
		$shop_options          = $data[ Lengow_Toolbox::SHOP_OPTIONS ];
		$selection_enabled_key = Lengow_Configuration::$generic_param_keys[ Lengow_Configuration::SELECTION_ENABLED ];
		$product_type_key      = Lengow_Configuration::$generic_param_keys[ Lengow_Configuration::EXPORT_PRODUCT_TYPES ];
		$checklist             = array(
			array(
				self::DATA_HEADER => $data[ Lengow_Toolbox::SHOP_NAME ]
				                     . ' (' . $data[ Lengow_Toolbox::SHOP_ID ] . ')'
				                     . ' - ' . $data[ Lengow_Toolbox::SHOP_DOMAIN_URL ],
			),
			array(
				self::DATA_TITLE => $this->_locale->t( 'toolbox.index.shop_active' ),
				self::DATA_STATE => (int) $data[ Lengow_Toolbox::SHOP_ENABLED ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.shop_catalogs_id' ),
				self::DATA_MESSAGE => implode( ', ', $data[ Lengow_Toolbox::SHOP_CATALOG_IDS ] ),
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.shop_product_total' ),
				self::DATA_MESSAGE => $data[ Lengow_Toolbox::SHOP_NUMBER_PRODUCTS_AVAILABLE ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.shop_product_exported' ),
				self::DATA_MESSAGE => $data[ Lengow_Toolbox::SHOP_NUMBER_PRODUCTS_EXPORTED ],
			),
			array(
				self::DATA_TITLE => $this->_locale->t( 'toolbox.index.export_selection_enabled' ),
				self::DATA_STATE => (int) $shop_options[ $selection_enabled_key ],
			),
			array(
				self::DATA_TITLE => $this->_locale->t( 'toolbox.index.export_variation_enabled' ),
				self::DATA_STATE => 1,
			),
			array(
				self::DATA_TITLE => $this->_locale->t( 'toolbox.index.export_out_stock_enabled' ),
				self::DATA_STATE => 1,
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.export_product_types' ),
				self::DATA_MESSAGE => implode( ', ', $shop_options[ $product_type_key ] ),
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.shop_export_token' ),
				self::DATA_MESSAGE => $data[ Lengow_Toolbox::SHOP_TOKEN ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.url_export' ),
				self::DATA_MESSAGE => $data[ Lengow_Toolbox::SHOP_FEED_URL ],
			),
			array(
				self::DATA_TITLE   => $this->_locale->t( 'toolbox.index.shop_last_export' ),
				self::DATA_MESSAGE => $last_export,
			),
		);

		return $this->get_admin_content( $checklist );
	}

	/**
	 * Get files checksum.
	 *
	 * @return string
	 */
	public function check_file_md5() {
		$checklist      = array();
		$checksum_data  = Lengow_Toolbox::get_data( Lengow_Toolbox::DATA_TYPE_CHECKSUM );
		$checksum_title = $this->_locale->t( 'toolbox.checksum.summary' );
		$html           = '<h3><i class="fa fa-commenting"></i> ' . $checksum_title . '</h3>';
		if ( $checksum_data[ Lengow_Toolbox::CHECKSUM_AVAILABLE ] ) {
			$checklist[] = array(
				self::DATA_TITLE => $this->_locale->t(
					'toolbox.checksum.file_checked',
					array( 'nb_file' => $checksum_data[ Lengow_Toolbox::CHECKSUM_NUMBER_FILES_CHECKED ] )
				),
				self::DATA_STATE => 1,
			);
			$checklist[] = array(
				self::DATA_TITLE => $this->_locale->t(
					'toolbox.checksum.file_modified',
					array( 'nb_file' => $checksum_data[ Lengow_Toolbox::CHECKSUM_NUMBER_FILES_MODIFIED ] )
				),
				self::DATA_STATE => (int) ( $checksum_data[ Lengow_Toolbox::CHECKSUM_NUMBER_FILES_MODIFIED ] === 0 ),
			);
			$checklist[] = array(
				self::DATA_TITLE => $this->_locale->t(
					'toolbox.checksum.file_deleted',
					array( 'nb_file' => $checksum_data[ Lengow_Toolbox::CHECKSUM_NUMBER_FILES_DELETED ] )
				),
				self::DATA_STATE => (int) ( $checksum_data[ Lengow_Toolbox::CHECKSUM_NUMBER_FILES_DELETED ] === 0 ),
			);
			$html        .= $this->get_admin_content( $checklist );
			if ( ! empty( $checksum_data[ Lengow_Toolbox::CHECKSUM_FILE_MODIFIED ] ) ) {
				$file_modified = array();
				foreach ( $checksum_data[ Lengow_Toolbox::CHECKSUM_FILE_MODIFIED ] as $file ) {
					$file_modified[] = array(
						self::DATA_TITLE => $file,
						self::DATA_STATE => 0,
					);
				}
				$html .= '<h3><i class="fa fa-list"></i> '
				         . $this->_locale->t( 'toolbox.checksum.list_modified_file' ) . '</h3>';
				$html .= $this->get_admin_content( $file_modified );
			}
			if ( ! empty( $checksum_data[ Lengow_Toolbox::CHECKSUM_FILE_DELETED ] ) ) {
				$file_deleted = array();
				foreach ( $checksum_data[ Lengow_Toolbox::CHECKSUM_FILE_DELETED ] as $file ) {
					$file_deleted[] = array(
						self::DATA_TITLE => $file,
						self::DATA_STATE => 0,
					);
				}
				$html .= '<h3><i class="fa fa-list"></i> '
				         . $this->_locale->t( 'toolbox.checksum.list_deleted_file' ) . '</h3>';
				$html .= $this->get_admin_content( $file_deleted );
			}
		} else {
			$checklist[] = array(
				self::DATA_TITLE => $this->_locale->t( 'toolbox.checksum.file_not_exists' ),
				self::DATA_STATE => 0,
			);
			$html        .= $this->get_admin_content( $checklist );
		}

		return $html;
	}

	/**
	 * Get HTML Table content of checklist.
	 *
	 * @param array $checklist
	 *
	 * @return string
	 */
	private function get_admin_content( $checklist = array() ) {
		if ( empty( $checklist ) ) {
			return null;
		}
		$out = '<table class="table" cellpadding="0" cellspacing="0">';
		foreach ( $checklist as $check ) {
			$out .= '<tr>';
			if ( isset( $check[ self::DATA_HEADER ] ) ) {
				$out .= '<td colspan="2" align="center" style="border:0"><h4>'
				        . $check[ self::DATA_HEADER ] . '</h4></td>';
			} else {
				$out .= '<td><b>' . $check[ self::DATA_TITLE ] . '</b></td>';
				if ( isset( $check[ self::DATA_STATE ] ) ) {
					if ( 1 === $check[ self::DATA_STATE ] ) {
						$out .= '<td align="right"><i class="fa fa-check lengow-green"></i></td>';
					} else {
						$out .= '<td align="right"><i class="fa fa-times lengow-red"></i></td>';
					}
					if ( ( 0 === $check[ self::DATA_STATE ] )
					     && isset(
						     $check[ self::DATA_HELP ],
						     $check[ self::DATA_HELP_LINK ],
						     $check[ self::DATA_HELP_LABEL ]
					     )
					) {
						$out .= '<tr><td colspan="2"><p>' . $check[ self::DATA_HELP ];
						if ( array_key_exists( self::DATA_HELP_LINK, $check )
						     && '' !== $check[ self::DATA_HELP_LINK ]
						) {
							$out .= '<br /><a target="_blank" href="'
							        . $check[ self::DATA_HELP_LINK ] . '">' . $check[ self::DATA_HELP_LABEL ] . '</a>';
						}
						$out .= '</p></td></tr>';
					}
				} else {
					$out .= '<td align="right"><b>' . $check[ self::DATA_MESSAGE ] . '</b></td>';
				}
			}
			$out .= '</tr>';
		}
		$out .= '</table>';

		return $out;
	}
}
