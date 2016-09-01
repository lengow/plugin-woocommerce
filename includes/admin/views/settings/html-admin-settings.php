<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$locale = new Lengow_Translation();
$keys = Lengow_Configuration::get_keys();
$values = Lengow_Configuration::get_all_values();
?>
<div class="lgw-container" id="lengow_mainsettings_wrapper" xmlns="http://www.w3.org/1999/html">
    <?php if ($keys['lengow_preprod_enabled'] == 1) : ?>
    <div id="lgw-preprod" class="adminlengowmainsetting">
       <?= $locale->t('menu.preprod_active'); ?>
    </div>
    <?php endif; ?>
    <form class="lengow_form" method="POST">
<!--        <input type="hidden" name="action" value="process">-->
        <div class="lgw-box">
            <h1>Global Settings</h1>
            <label class="control-label" ><?= $keys['lengow_authorized_ip']['label'] ?></label>
            <input type="text" name="lengow_authorized_ip" class="form-control" value="<?= $values['lengow_authorized_ip'] ?>" />
            <h2><?= $locale->t('global_setting.screen.preprod_mode_title');?></h2>
            <p><?= $locale->t('global_setting.screen.preprod_mode_description');?></p>
            <div class="lgw-switch">
                <label>
                    <div>
                        <input name="lengow_preprod_enabled" type="checkbox" <?= $values['lengow_preprod_enabled'] == 1 ? 'checked' : '' ?> />
                    </div>
                    <?= $keys['lengow_preprod_enabled']['label'] ?>
                </label>
            </div>
        </div>
            <div id="lengow_wrapper_preprod" style="display:none;">
                <div class="grey-frame">
                    <div class="lgw-switch">
                        <label>
                            <div>
                                <input name="lengow_store_enabled" type="checkbox" <?= $values['lengow_store_enabled'] == 1 ? 'checked' : '' ?> />
                            </div>
                            <?= $keys['lengow_store_enabled']['label'] ?>
                        </label>
                    </div>
                    <label class="control-label" ><?= $keys['lengow_account_id']['label'] ?></label>
                    <input type="text" name="lengow_account_id" class="form-control" value="<?= $values['lengow_account_id'] ?>" />
                    <label class="control-label"><?= $keys['lengow_access_token']['label'] ?></label>
                    <input type="text" name="lengow_access_token" class="form-control" value="<?= $values['lengow_access_token'] ?>" />
                    <label class="control-label"><?= $keys['lengow_secret_token']['label'] ?></label>
                    <input type="text" name="lengow_secret_token" class="form-control" value="<?= $values['lengow_secret_token'] ?>" />
                </div>
            </div>
        <div class="lgw-box">
            <h1>Export Settings</h1>
            <h2>Product Type</h2>
            <label class="control-label" >Product type to export</label>
            <select class="form-control lengow_select" name="lengow_product_type[]" multiple="multiple">
                <?php foreach (Lengow_Main::$PRODUCT_TYPES as $row => $value) : ?>
                $selected =  $values['lengow_product_type'] == $value ? 'selected' : '';
                <option value="<?= $row ?>" ><?= $value ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="lgw-box">
            <h1>Log Settings</h1>
            <h2><?= $locale->t('global_setting.screen.log_file_title'); ?></h2>
            <p><?= $locale->t('global_setting.screen.log_file_description'); ?></p>
<!--            TODO - select for all logs-->
            <button type="button" id="download_log" class="lgw-btn lgw-btn-white">
                <i class="fa fa-download"></i> <?= $locale->t('global_setting.screen.button_download_file');?>
            </button>
        </div>
        <div class="form-group container">
            <div class="lengow_main_setting_block_content">
                <div class="pull-left">
                    <button type="submit" class="lgw-btn lgw-btn-progression lengow_submit_main_setting">
                        <div class="btn-inner">
                            <div class="btn-step default">
                                <?= $locale->t('global_setting.screen.button_save'); ?>
                            </div>
<!--                            TODO - Ajax event save change-->
<!--                            <div class="btn-step loading">-->
<!--                                 $locale->t('global_setting.screen.setting_saving')
<!--                            </div>-->
<!--                            <div class="btn-step done" data-success="Saved!" data-error="Error">-->
<!--                                 $locale->t('global_setting.screen.setting_saved')
<!--                            </div>-->
                        </div>
                    </button>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </form>
</div>