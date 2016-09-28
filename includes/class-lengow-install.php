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
 * Lengow_Install Class.
 */
class Lengow_Install {

    /**
     * installation status
     */
    public static $installationStatus;

	/**
	 * Installation of module
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function install() {
        Lengow_Install::update();
	}

    /**
     * Update process from previous versions
     * @return boolean Result of update process
     */
    public static function update() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        global $wpdb;
        self::set_installation_status(true);
        $upgrade_files = array_diff(scandir(LENGOW_PLUGIN_PATH . '/upgrade'), array('..', '.'));
        foreach ($upgrade_files as $file) {
            $number_version = preg_replace('/update_|\.php$/', '', $file);
            if ( version_compare( get_option('lengow_version'), $number_version, '>=' ) ) continue;
            include LENGOW_PLUGIN_PATH . '/upgrade/' . $file;
        }
        update_option('lengow_version', $number_version);
        self::set_installation_status(false);
        return true;
    }

    /**
     * Checks if a field exists in BDD
     *
     * @param string $table
     * @param string $field
     *
     * @return boolean
     */
    public static function check_field_exists($table, $field)
    {
        global $wpdb;
        $sql = 'SHOW COLUMNS FROM '.$wpdb->prefix.$table.' LIKE \''.$field.'\'';
        $result = $wpdb->get_results($sql);
        $exists = count($result) > 0 ? true : false;
        return $exists;
    }

    /**
     * Checks if a field exists in BDD and Dropped It
     *
     * @param string $table
     * @param string $field
     *
     * @return boolean
     */
    public static function check_field_and_drop($table, $field)
    {
        global $wpdb;
        if (self::check_field_exists($table, $field)) {
            $wpdb->query(
                'ALTER TABLE '.$wpdb->prefix.$table.' DROP COLUMN `'.$field.'`'
            );
        }
    }

    /**
     * Rename configuration key
     *
     * @param string $oldName
     * @param string $newName
     */
    public static function rename_configuration_key($oldName, $newName)
    {
        $tempValue = Lengow_Configuration::get($oldName);
        Lengow_Configuration::update_value($newName, $tempValue);
        Lengow_Configuration::delete($oldName);
    }

    /**
     * Set Installation Status
     *
     * @param boolean $status Installation Status
     */
    public static function set_installation_status($status)
    {
        self::$installationStatus = $status;
    }

    /**
     * Is Installation In Progress
     *
     * @return boolean
     */
    public static function is_installation_in_progress()
    {
        return self::$installationStatus;
    }
}
