<?php
define( 'WP_USE_THEMES', false );
require_once( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/lengow.php');
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-configuration.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-translation.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-feed.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-export.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-check.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-main.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-file.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-log.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/lengow-woocommerce/includes/class-lengow-import.php' );
$locale = new Lengow_Translation();
$check = new Lengow_Check();
?>