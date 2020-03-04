<?php
/**
 * Admin View: Order Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
Lengow_Admin_Order_Settings::post_process();
$keys             = Lengow_Configuration::get_keys();
$values           = Lengow_Configuration::get_all_values();
$order_statuses   = Lengow_Main::get_order_statuses();
$shipping_methods = Lengow_Main::get_shipping_methods();
$min_import_days  = Lengow_Import::MIN_INTERVAL_TIME / 86400;
$max_import_days  = Lengow_Import::MAX_INTERVAL_TIME / 86400;
?>
<div id="lengow_order_setting_wrapper">
    <div class="lgw-container">
		<?php if ( Lengow_Configuration::debug_mode_is_active() ) : ?>
            <div id="lgw-debug" class="adminlengowlegals">
				<?php echo $locale->t( 'menu.debug_active' ); ?>
            </div>
		<?php endif; ?>
        <form class="lengow_form" method="POST">
            <input type="hidden" name="action" value="process">
            <div class="lgw-box">
                <h2><?php echo $locale->t( 'order_setting.screen.default_shipping_method_title' ); ?></h2>
                <p><?php echo $locale->t( 'order_setting.screen.default_shipping_method_description' ); ?></p>
                <br/>
                <div class="form-group lengow_import_default_shipping_method">
                    <label><?php echo $keys['lengow_import_default_shipping_method']['label']; ?></label>
                    <select class="js-select lengow_select" name="lengow_import_default_shipping_method">
						<?php foreach ( $shipping_methods as $shipping_method => $label ) : ?>
                            <option value="<?php echo $shipping_method; ?>" <?php echo $values['lengow_import_default_shipping_method'] === $shipping_method ? 'selected' : ''; ?>>
								<?php echo $label; ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="lgw-box">
                <h2><?php echo $locale->t( 'order_setting.screen.order_status_title' ); ?></h2>
                <p><?php echo $locale->t( 'order_setting.screen.order_status_description' ); ?></p>
                <br/>
                <div class="form-group lengow_id_waiting_shipment">
                    <label><?php echo $keys['lengow_id_waiting_shipment']['label']; ?></label>
                    <select class="js-select lengow_select" name="lengow_id_waiting_shipment">
						<?php foreach ( $order_statuses as $order_status => $label ) : ?>
                            <option value="<?php echo $order_status; ?>" <?php echo $values['lengow_id_waiting_shipment'] === $order_status ? 'selected' : ''; ?>>
								<?php echo $label; ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group lengow_id_shipped">
                    <label><?php echo $keys['lengow_id_shipped']['label']; ?></label>
                    <select class="js-select lengow_select" name="lengow_id_shipped">
						<?php foreach ( $order_statuses as $order_status => $label ) : ?>
                            <option value="<?php echo $order_status; ?>" <?php echo $values['lengow_id_shipped'] === $order_status ? 'selected' : ''; ?>>
								<?php echo $label; ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group lengow_id_cancel">
                    <label><?php echo $keys['lengow_id_cancel']['label']; ?></label>
                    <select class="js-select lengow_select" name="lengow_id_cancel">
						<?php foreach ( $order_statuses as $order_status => $label ) : ?>
                            <option value="<?php echo $order_status; ?>" <?php echo $values['lengow_id_cancel'] === $order_status ? 'selected' : ''; ?>>
								<?php echo $label; ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group lengow_id_shipped_by_mp">
                    <label><?php echo $keys['lengow_id_shipped_by_mp']['label']; ?></label>
                    <select class="js-select lengow_select" name="lengow_id_shipped_by_mp">
						<?php foreach ( $order_statuses as $order_status => $label ) : ?>
                            <option value="<?php echo $order_status; ?>" <?php echo $values['lengow_id_shipped_by_mp'] === $order_status ? 'selected' : ''; ?>>
								<?php echo $label; ?>
                            </option>
						<?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="lgw-box">
                <h2><?php echo $locale->t( 'order_setting.screen.import_setting_title' ); ?></h2>
                <p><?php echo $locale->t( 'order_setting.screen.import_setting_description' ); ?></p>
                <br/>
                <div class="form-group lengow_import_days">
                    <label><?php echo $keys['lengow_import_days']['label']; ?></label>
                    <div class="input-group">
                        <input type="number" name="lengow_import_days" class="form-control"
                               value="<?php echo $values['lengow_import_days']; ?>"
                               min="<?php echo $min_import_days; ?>"
                               max="<?php echo $max_import_days; ?>"/>
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
