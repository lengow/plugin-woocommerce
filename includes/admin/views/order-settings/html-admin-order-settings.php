<?php
/**
 * Admin View: Order Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
Lengow_Admin_Order_Settings::post_process();
$keys   = Lengow_Configuration::get_keys();
$values = Lengow_Configuration::get_all_values();
?>
<div id="lengow_order_setting_wrapper">
    <div class="lgw-container">
		<?php if ( (bool) Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) : ?>
            <div id="lgw-preprod" class="adminlengowlegals">
				<?php echo $locale->t( 'menu.preprod_active' ); ?>
            </div>
		<?php endif; ?>
        <form class="lengow_form" method="POST">
            <input type="hidden" name="action" value="process">
            <div class="lgw-box">
                <h2><?php echo $locale->t( 'order_setting.screen.import_setting_title' ); ?></h2>
                <p><?php echo $locale->t( 'order_setting.screen.import_setting_description' ); ?></p>
                <div class="form-group lengow_import_days">
                    <label><?php echo $keys['lengow_import_days']['label']; ?></label>
                    <div class="input-group">
                        <input type="number" name="lengow_import_days" class="form-control"
                               value="<?php echo $values['lengow_import_days']; ?>" min="1" max="10"/>
                        <div class="input-group-addon">
                            <div class="unit"><?php echo $locale->t( 'order_setting.screen.nb_days' ); ?></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="form-group lengow_import_ship_mp_enabled">
                    <div class="lgw-switch <?php echo (bool) $values['lengow_import_ship_mp_enabled'] ? 'checked' : ''; ?>">
                        <label>
                            <div>
                                <span></span>
                                <input type="hidden" name="lengow_import_ship_mp_enabled" value="0">
                                <input name="lengow_import_ship_mp_enabled"
                                       type="checkbox"
									<?php echo (bool) $values['lengow_import_ship_mp_enabled'] ? 'checked' : ''; ?>/>
                            </div>
							<?php echo $keys['lengow_import_ship_mp_enabled']['label']; ?>
                        </label>
                    </div>
                </div>
                <div class="form-group lengow_import_stock_ship_mp" <?php echo (bool) $values['lengow_import_ship_mp_enabled'] ? '' : 'style="display:none;"'; ?>>
                    <div class="lgw-switch <?php echo (bool) $values['lengow_import_stock_ship_mp'] ? 'checked' : ''; ?>">
                        <label>
                            <div>
                                <span></span>
                                <input type="hidden" name="lengow_import_stock_ship_mp" value="0">
                                <input name="lengow_import_stock_ship_mp"
                                       type="checkbox"
									<?php echo (bool) $values['lengow_import_stock_ship_mp'] ? 'checked' : ''; ?>/>
                            </div>
							<?php echo $keys['lengow_import_stock_ship_mp']['label']; ?>
                        </label>
                    </div>
                    <span class="legend blue-frame"
                          style="display:block;"><?php echo $keys['lengow_import_stock_ship_mp']['legend']; ?></span>
                </div>
            </div>
            <div class="form-group container">
                <div class="lengow_main_setting_block_content">
                    <div class="pull-left">
                        <button type="submit" class="lgw-btn lgw-btn-progression lengow_submit_main_setting">
                            <div class="btn-inner">
                                <div class="btn-step default">
									<?php echo $locale->t( 'global_setting.screen.button_save' ); ?>
                                </div>
                                <div class="btn-step loading">
									<?php echo $locale->t( 'global_setting.screen.setting_saving' ); ?>
                                </div>
                                <div class="btn-step done" data-success="Saved!" data-error="Error">
									<?php echo $locale->t( 'global_setting.screen.setting_saved' ); ?>
                                </div>
                            </div>
                        </button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </form>
    </div>
</div>
