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
				<?php echo esc_html( $locale->t( 'menu.debug_active' ) ); ?>
			</div>
		<?php endif; ?>
		<form class="lengow_form" method="POST">
			<input type="hidden" name="action" value="process">
			<div class="lgw-box">
				<h2><?php echo esc_html( $locale->t( 'order_setting.screen.default_shipping_method_title' ) ); ?></h2>
				<p><?php echo esc_html( $locale->t( 'order_setting.screen.default_shipping_method_description' ) ); ?></p>
				<br/>
				<div class="form-group lengow_import_default_shipping_method">
					<label>
						<?php echo esc_html( $keys[ Lengow_Configuration::DEFAULT_IMPORT_CARRIER_ID ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
					<select class="js-select lengow_select" name="lengow_import_default_shipping_method">
						<?php foreach ( $shipping_methods as $shipping_method => $label ) : ?>
							<option value="<?php echo esc_attr( $shipping_method ); ?>"
								<?php echo esc_attr( $values[ Lengow_Configuration::DEFAULT_IMPORT_CARRIER_ID ] === $shipping_method ? 'selected' : '' ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="lgw-box">
				<h2><?php echo esc_html( $locale->t( 'order_setting.screen.order_status_title' ) ); ?></h2>
				<p><?php echo esc_html( $locale->t( 'order_setting.screen.order_status_description' ) ); ?></p>
				<br/>
				<div class="form-group lengow_id_waiting_shipment">
					<label>
						<?php echo esc_html( $keys[ Lengow_Configuration::WAITING_SHIPMENT_ORDER_ID ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
					<select class="js-select lengow_select" name="lengow_id_waiting_shipment">
						<?php foreach ( $order_statuses as $order_status => $label ) : ?>
							<option value="<?php echo esc_attr( $order_status ); ?>"
								<?php echo esc_attr( $values[ Lengow_Configuration::WAITING_SHIPMENT_ORDER_ID ] === $order_status ? 'selected' : '' ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group lengow_id_shipped">
					<label>
						<?php echo esc_html( $keys[ Lengow_Configuration::SHIPPED_ORDER_ID ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
					<select class="js-select lengow_select" name="lengow_id_shipped">
						<?php foreach ( $order_statuses as $order_status => $label ) : ?>
							<option value="<?php echo esc_attr( $order_status ); ?>"
								<?php echo esc_attr( $values[ Lengow_Configuration::SHIPPED_ORDER_ID ] === $order_status ? 'selected' : '' ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group lengow_id_cancel">
					<label>
						<?php echo esc_html( $keys[ Lengow_Configuration::CANCELED_ORDER_ID ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
					<select class="js-select lengow_select" name="lengow_id_cancel">
						<?php foreach ( $order_statuses as $order_status => $label ) : ?>
							<option value="<?php echo esc_attr( $order_status ); ?>"
								<?php echo esc_attr( $values[ Lengow_Configuration::CANCELED_ORDER_ID ] === $order_status ? 'selected' : '' ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group lengow_id_shipped_by_mp">
					<label>
						<?php echo esc_html( $keys[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_ORDER_ID ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
					<select class="js-select lengow_select" name="lengow_id_shipped_by_mp">
						<?php foreach ( $order_statuses as $order_status => $label ) : ?>
							<option value="<?php echo esc_attr( $order_status ); ?>"
								<?php echo esc_attr( $values[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_ORDER_ID ] === $order_status ? 'selected' : '' ); ?>>
								<?php echo esc_attr( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="lgw-box">
				<h2><?php echo esc_html( $locale->t( 'order_setting.screen.import_setting_title' ) ); ?></h2>
				<p><?php echo esc_html( $locale->t( 'order_setting.screen.import_setting_description' ) ); ?></p>
				<br/>
				<div class="form-group lengow_import_days">
					<label>
						<?php echo esc_html( $keys[ Lengow_Configuration::SYNCHRONIZATION_DAY_INTERVAL ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
					<div class="input-group">
						<input type="number" name="lengow_import_days" class="form-control"
								value="<?php echo esc_attr( $values[ Lengow_Configuration::SYNCHRONIZATION_DAY_INTERVAL ] ); ?>"
								min="<?php echo esc_attr( $min_import_days ); ?>"
								max="<?php echo esc_attr( $max_import_days ); ?>"/>
						<div class="input-group-addon">
							<div class="unit"><?php echo esc_html( $locale->t( 'order_setting.screen.nb_days' ) ); ?></div>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
				<div class="form-group lengow_import_ship_mp_enabled">
					<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_ENABLED ] ? 'checked' : '' ); ?>">
						<label>
							<div>
								<span></span>
								<input type="hidden" name="lengow_import_ship_mp_enabled" value="0">
								<input name="lengow_import_ship_mp_enabled"
										type="checkbox"
									<?php echo esc_attr( $values[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_ENABLED ] ? 'checked' : '' ); ?>/>
							</div>
							<?php echo esc_html( $keys[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_ENABLED ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
					</div>
				</div>
				<div class="form-group lengow_anonymize_email">
					<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::ANONYMIZE_EMAIL ] ? 'checked' : '' ); ?>">
						<label>
							<div>
								<span></span>
								<input type="hidden" name="lengow_anonymize_email" value="0">
								<input name="lengow_anonymize_email"
										type="checkbox"
									<?php echo esc_attr( $values[ Lengow_Configuration::ANONYMIZE_EMAIL ] ? 'checked' : '' ); ?>/>
							</div>
							<?php echo esc_html( $keys[ Lengow_Configuration::ANONYMIZE_EMAIL ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
					</div>
				</div>
				<div class="form-group lengow_type_anonymize_email">
					<label>
						<?php echo esc_html( $keys[ Lengow_Configuration::TYPE_ANONYMIZE_EMAIL ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
					<select class="js-select lengow_select" name="lengow_lengow_type_anonymize_email">
						<option value="0"
							<?php echo esc_attr( $values[ Lengow_Configuration::TYPE_ANONYMIZE_EMAIL ] === 0 ? 'selected' : '' ); ?>>
							<?php echo esc_html( $locale->t( 'order_setting.screen.anonymize_email_type_encrypted' ) ); ?>
						<option value="1"
							<?php echo esc_attr( $values[ Lengow_Configuration::TYPE_ANONYMIZE_EMAIL ] === 1 ? 'selected' : '' ); ?>>
							<?php echo esc_html( $locale->t( 'order_setting.screen.anonymize_email_type_not_encrypted' ) ); ?>
					</select>
				</div>
				<div class="form-group lengow_import_stock_ship_mp" <?php echo esc_attr( $values[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_ENABLED ] ? '' : 'hidden' ); ?>>
					<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED ] ? 'checked' : '' ); ?>">
						<label>
							<div>
								<span></span>
								<input type="hidden" name="lengow_import_stock_ship_mp" value="0">
								<input name="lengow_import_stock_ship_mp"
										type="checkbox"
									<?php echo esc_attr( $values[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED ] ? 'checked' : '' ); ?>/>
							</div>
							<?php echo esc_html( $keys[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
					</div>
					<span class="legend blue-frame"
							style="display:block;"><?php echo esc_html( $keys[ Lengow_Configuration::SHIPPED_BY_MARKETPLACE_STOCK_ENABLED ][ Lengow_Configuration::PARAM_LEGEND ] ); ?></span>
				</div>
			</div>
			<div class="lgw-box">
				<h2><?php echo esc_html( $locale->t( 'order_setting.screen.currency_conversion_title' ) ); ?></h2>
				<p><?php echo esc_html( $locale->t( 'order_setting.screen.currency_conversion_description' ) ); ?></p>
				<br/>
				<div class="form-group lengow_currency_conversion">
					<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::CURRENCY_CONVERSION_ENABLED ] ? 'checked' : '' ); ?>">
						<label>
							<div>
								<span></span>
								<input type="hidden" name="lengow_currency_conversion" value="0">
								<input name="lengow_currency_conversion"
										type="checkbox"
									<?php echo esc_attr( $values[ Lengow_Configuration::CURRENCY_CONVERSION_ENABLED ] ? 'checked' : '' ); ?>/>
							</div>
							<?php echo esc_html( $keys[ Lengow_Configuration::CURRENCY_CONVERSION_ENABLED ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
					</div>
				</div>

			</div>
			<div class="lgw-box">
				<h2><?php echo esc_html( $locale->t( 'order_setting.screen.import_b2b_without_tax_title' ) ); ?></h2>
				<p><?php echo esc_html( $locale->t( 'order_setting.screen.import_b2b_without_tax_description' ) ); ?></p>
				<br/>
				<div class="form-group lengow_import_b2b_without_tax">
					<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::B2B_WITHOUT_TAX_ENABLED ] ? 'checked' : '' ); ?>">
						<label>
							<div>
								<span></span>
								<input type="hidden" name="lengow_import_b2b_without_tax" value="0">
								<input name="lengow_import_b2b_without_tax"
										type="checkbox"
									<?php echo esc_attr( $values[ Lengow_Configuration::B2B_WITHOUT_TAX_ENABLED ] ? 'checked' : '' ); ?>/>
							</div>
							<?php echo esc_html( $keys[ Lengow_Configuration::B2B_WITHOUT_TAX_ENABLED ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
					</div>
				</div>
			</div>
			<div class="form-group container">
				<div class="lengow_main_setting_block_content">
					<div class="pull-left">
						<button type="submit" class="lgw-btn lgw-btn-progression lengow_submit_main_setting">
							<div class="btn-inner">
								<div class="btn-step default">
									<?php echo esc_html( $locale->t( 'global_setting.screen.button_save' ) ); ?>
								</div>
								<div class="btn-step loading">
									<?php echo esc_html( $locale->t( 'global_setting.screen.setting_saving' ) ); ?>
								</div>
								<div class="btn-step done" data-success="Saved!" data-error="Error">
									<?php echo esc_html( $locale->t( 'global_setting.screen.setting_saved' ) ); ?>
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
