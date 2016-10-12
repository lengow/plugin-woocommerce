<?php
define( 'WP_USE_THEMES', false );
require(dirname(dirname(dirname(dirname(dirname($_SERVER["SCRIPT_FILENAME"]))))). '/wp-load.php');
require_once( '../lengow.php');
require_once( '../includes/class-lengow-configuration.php' );
require_once( '../includes/class-lengow-translation.php' );
require_once( '../includes/class-lengow-feed.php' );
require_once( '../includes/class-lengow-export.php' );
require_once( '../includes/class-lengow-check.php' );
require_once( '../includes/class-lengow-main.php' );
require_once( '../includes/class-lengow-file.php' );
require_once( '../includes/class-lengow-log.php' );
require_once( '../includes/class-lengow-import.php' );
$locale = new Lengow_Translation();
$check = new Lengow_Check();
?>