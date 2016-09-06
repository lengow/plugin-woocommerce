<?php
/**
 * Connector to use Lengow API
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
 * Lengow_Connector Class.
 */
class Lengow_Connector {
    /**
     * Get result for a query Api
     *
     * @param string  $type   (GET / POST / PUT / PATCH)
     * @param string  $url
     * @param integer $id_shop
     * @param array   $params
     * @param string  $body
     *
     * @return api result as array
     */
    public static function query_api()
    {
        return true;
    }

}

