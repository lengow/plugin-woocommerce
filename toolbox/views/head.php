<?php
define( 'WP_USE_THEMES', false );
require( dirname( dirname( dirname( dirname( dirname( $_SERVER["SCRIPT_FILENAME"] ) ) ) ) ) . '/wp-load.php' );
require_once( '../lengow.php' );
require_once( '../includes/class-lengow-configuration.php' );
require_once( '../includes/class-lengow-crud.php' );
require_once( '../includes/class-lengow-export.php' );
require_once( '../includes/class-lengow-feed.php' );
require_once( '../includes/class-lengow-file.php' );
require_once( '../includes/class-lengow-import.php' );
require_once( '../includes/class-lengow-log.php' );
require_once( '../includes/class-lengow-main.php' );
require_once( '../includes/class-lengow-toolbox.php' );
require_once( '../includes/class-lengow-toolbox-element.php' );
require_once( '../includes/class-lengow-translation.php' );

$locale = new Lengow_Translation();
$toolbox_element  = new Lengow_Toolbox_Element();
if ( ! Lengow_Main::check_ip() ) {
	$isHttps = isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] ? 'https://' : 'http://';
	$url     = $isHttps . $_SERVER['SERVER_NAME'];
	header( 'Location: ' . $url );
}
?>
