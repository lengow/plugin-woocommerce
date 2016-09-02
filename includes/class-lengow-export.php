<?php
/**
 * All components to generate Lengow feed
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
 * Lengow_Export Class.
 */
class Lengow_Export {

	/**
	 * @var string Format of exported files
	 */
	private $_format;

	/**
	 * @var boolean Stream file or generate a file
	 */
	private $_stream;

	/**
	 * @var integer Offset of total product
	 */
	private $_offset;

	/**
	 * @var integer Limit number of exported product
	 */
	private $_limit;

	/**
	 * @var boolean Export product selection
	 */
	private $_selection;

	/**
	 * @var boolean Export out of stock product
	 */
	private $_out_of_stock;

	/**
	 * @var array List of product id separate with comma
	 */
	private $_product_ids;

	/**
	 * @var boolean Product type separate with comma
	 */
	private $_product_type;

	/**
	 * @var boolean Export unpublished products
	 */
	private $_inactive;

	/**
	 * @var boolean Export product Variation
	 */
	private $_variation;

	/**
	 * @var boolean Export feed with v2 fields
	 */
	private $_legacy_fields;

	/**
	 * @var boolean See logs
	 */
	private $_log_output;

	/**
	 * @var boolean Change last export date in data base
	 */
	private $_update_export_date;

	/**
	 * Construct new Lengow export
	 *
	 * @param array params optional options
	 * string  format             Format of exported files ('csv','yaml','xml','json')
	 * boolean stream             Stream file (1) or generate a file on server (0)
	 * integer offset             Offset of total product
	 * integer limit              Limit number of exported product
	 * boolean selection          Export product selection (1) or all products (0)
	 * boolean out_of_stock       Export out of stock product (1) Export only product in stock (0)
	 * string  product_ids        List of product id separate with comma (1,2,3)
	 * string  product_type       Product type separate with comma (external,grouped,simple,variable)
	 * boolean inactive           Export unpublished products (1) or only published product (0)
	 * boolean variation          Export product Variation (1) Export parent product only (0)
	 * boolean legacy_fields      Export feed with v2 fields (1) or v3 fields (0)
	 * boolean log_output         See logs (1) or not (0)
	 * boolean update_export_date Change last export date in data base (1) or not (0)
	 */
	public function __construct( $params = array() ) {
		$this->_stream = ! is_null( $params['stream'] ) ? $params['stream'] : false;
		$this->_offset = ! is_null( $params['offset'] ) ? $params['offset'] : 0;
		$this->_limit  = ! is_null( $params['limit'] ) ? $params['limit'] : 0;
		// TODO get Lengow Configuration for selection and out of stock options
		$this->_selection          = ! is_null( $params['selection'] ) ? $params['selection'] : false;
		$this->_out_of_stock       = ! is_null( $params['out_of_stock'] ) ? $params['out_of_stock'] : false;
		$this->_product_ids        = ! is_null( $params['product_ids'] ) ? $params['product_ids'] : false;
		$this->_inactive           = ! is_null( $params['inactive'] ) ? $params['inactive'] : false;
		$this->_variation          = ! is_null( $params['variation'] ) ? $params['variation'] : true;
		$this->_legacy_fields      = ! is_null( $params['legacy_fields'] ) ? $params['legacy_fields'] : false;
		$this->_update_export_date = ! is_null( $params['update_export_date'] )
			? (bool) $params['update_export_date']
			: true;
		$this->_set_format( ! is_null( $params['format'] ) ? $params['format'] : 'csv' );
		$this->_set_product_type( ! is_null( $params['product_type'] ) ? $params['product_type'] : false );
		$this->_set_log_output( ! is_null( $params['log_output'] ) ? $params['log_output'] : true );
	}

	/**
	 * Set format to export
	 *
	 * @param string $format The export format
	 */
	private function _set_format( $format ) {
		$this->_format = ! in_array( $format, Lengow_Feed::$AVAILABLE_FORMATS ) ? 'csv' : $format;
	}

	/**
	 * Set product types to export
	 *
	 * @param string $product_type The product types to export
	 */
	private function _set_product_type( $product_type ) {
		// TODO verify and get product types
		$this->_product_type = $product_type;
	}

	/**
	 * Set Log output for export
	 *
	 * @param boolean $log_output See logs or not
	 */
	private function _set_log_output( $log_output ) {
		$this->_log_output = $this->_stream ? false : $log_output;
	}

	/**
	 * Execute the export
	 */
	public function exec()
	{

	}

	/**
	 * Get Count export product
	 *
	 * @return integer
	 */
	public function get_total_export_product()
	{

	}
}

