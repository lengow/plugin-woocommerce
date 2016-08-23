<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$locale = new Lengow_Translation();
?>

<h2><?php echo $locale->t('dashboard.hello_world'); ?></h2>
<b><?php echo $locale->t('dashboard.new_friend', array('nb' => 100)); ?></b>
