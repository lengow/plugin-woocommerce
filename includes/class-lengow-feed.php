<?php
/**
 * All options to create a specific format
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
 * Lengow_Feed Class.
 */
class Lengow_Feed {

	/**
	 * @var array formats available for export
	 */
	public static $AVAILABLE_FORMATS = array(
		'csv',
		'yaml',
		'xml',
		'json',
	);
}

