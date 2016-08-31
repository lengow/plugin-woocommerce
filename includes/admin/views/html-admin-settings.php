<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
Lengow_Settings::post_process();
?>
<h2>Settings</h2>

<form action="" method="post">
    <input type="text" name="lengow_import_days" value="<?= get_option('lengow_import_days') ?>">
    <input type="submit" value="Save">
</form>