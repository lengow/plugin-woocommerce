<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
Lengow_Admin_Main_Settings::post_process();
$keys      = Lengow_Configuration::get_keys();
$values    = Lengow_Configuration::get_all_values();
$list_file = Lengow_Log::get_paths();
?>
<div class="lgw-container" id="lengow_mainsettings_wrapper" xmlns="http://www.w3.org/1999/html">
	<?php if ( (bool) $values[ Lengow_Configuration::DEBUG_MODE_ENABLED ] ) : ?>
		<div id="lgw-debug" class="adminlengowmainsetting">
			<?php echo esc_html( $locale->t( 'menu.debug_active' ) ); ?>
		</div>
	<?php endif; ?>
	<form class="lengow_form" method="POST">
		<input type="hidden" name="action" value="process">
		<div class="lgw-box">
			<h2><?php echo esc_html( $locale->t( 'global_setting.screen.notification_alert_title' ) ); ?></h2>
			<div class="form-group">
				<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::REPORT_MAIL_ENABLED ] ? 'checked' : '' ); ?>">
					<label>
						<div>
							<span></span>
							<input type="hidden" name="lengow_report_mail_enabled" value="0">
							<input name="lengow_report_mail_enabled"
									type="checkbox"
								<?php echo esc_attr( $values[ Lengow_Configuration::REPORT_MAIL_ENABLED ] ? 'checked' : '' ); ?> >
						</div>
						<?php echo esc_html( $keys[ Lengow_Configuration::REPORT_MAIL_ENABLED ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
				</div>
			</div>
			<div id="lengow_wrapper_report_mail_address"
				<?php echo esc_attr( $values['lengow_report_mail_enabled'] ? '' : 'hidden' ); ?>>
				<div class="form-group">
					<input type="text" name="lengow_report_mail_address" class="form-control"
							placeholder="<?php echo esc_attr( $keys[ Lengow_Configuration::REPORT_MAILS ][ Lengow_Configuration::PARAM_PLACEHOLDER ] ); ?>"
							value="<?php echo esc_attr( $values[ Lengow_Configuration::REPORT_MAILS ] ); ?>">
					<span class="legend blue-frame" style="display:block;">
						<?php echo esc_html( $keys[ Lengow_Configuration::REPORT_MAILS ][ Lengow_Configuration::PARAM_LEGEND ] ); ?>
					</span>
				</div>
			</div>
		</div>
		<div class="lgw-box">
			<h2><?php echo esc_html( $locale->t( 'global_setting.screen.export_title' ) ); ?></h2>
			<label class="control-label">
				<?php echo esc_html( $keys[ Lengow_Configuration::EXPORT_PRODUCT_TYPES ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
			</label>
			<div class="form-group">
				<select class="form-control js-multiple-select" name="lengow_product_types[]" multiple>
					<?php
					foreach ( Lengow_Main::$product_types as $row => $value ) :
						$selected = false;
						foreach ( $values[ Lengow_Configuration::EXPORT_PRODUCT_TYPES ] as $key => $type ) {
							if ( $type === $row ) {
								$selected = 'selected';
								continue;
							}
						}
						?>
						<option value="<?php echo esc_attr( $row ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="legend blue-frame" style="display:block;">
					<?php echo esc_html( $keys[ Lengow_Configuration::EXPORT_PRODUCT_TYPES ][ Lengow_Configuration::PARAM_LEGEND ] ); ?>
				</span>
			</div>
		</div>
		<div class="lgw-box">
			<h2><?php echo esc_html( $locale->t( 'global_setting.screen.security_title' ) ); ?></h2>
			<div class="form-group">
				<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::AUTHORIZED_IP_ENABLED ] ? 'checked' : '' ); ?>">
					<label>
						<div>
							<span></span>
							<input type="hidden" name="lengow_ip_enabled" value="0">
							<input name="lengow_ip_enabled"
									type="checkbox"
								<?php echo esc_attr( $values[ Lengow_Configuration::AUTHORIZED_IP_ENABLED ] ? 'checked' : '' ); ?>>
						</div>
						<?php echo esc_html( $keys[ Lengow_Configuration::AUTHORIZED_IP_ENABLED ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
				</div>
				<span class="legend blue-frame" style="display:block;">
					<?php echo esc_html( $keys[ Lengow_Configuration::AUTHORIZED_IP_ENABLED ][ Lengow_Configuration::PARAM_LEGEND ] ); ?>
				</span>
			</div>
			<div id="lengow_wrapper_authorized_ip"
				<?php echo esc_attr( $values[ Lengow_Configuration::AUTHORIZED_IP_ENABLED ] ? '' : 'hidden' ); ?>>
				<div class="grey-frame">
					<div class="form-group">
						<label class="control-label">
							<?php echo esc_html( $keys[ Lengow_Configuration::AUTHORIZED_IPS ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
						<input type="text" name="lengow_authorized_ip" class="form-control"
								value="<?php echo esc_attr( $values[ Lengow_Configuration::AUTHORIZED_IPS ] ); ?>">
						<span class="legend blue-frame" style="display:block;">
							<?php echo esc_html( $keys[ Lengow_Configuration::AUTHORIZED_IPS ][ Lengow_Configuration::PARAM_LEGEND ] ); ?>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="lgw-box">
			<h2><?php echo esc_html( $locale->t( 'global_setting.screen.shop_title' ) ); ?></h2>
			<p><?php echo esc_html( $locale->t( 'global_setting.screen.shop_description' ) ); ?></p>
			<div class="form-group">
				<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::SHOP_ACTIVE ] ? 'checked' : '' ); ?>">
					<label>
						<div>
							<span></span>
							<input type="hidden" name="lengow_store_enabled" value="0">
							<input name="lengow_store_enabled"
									type="checkbox"
								<?php echo esc_attr( $values[ Lengow_Configuration::SHOP_ACTIVE ] ? 'checked' : '' ); ?>>
						</div>
						<?php echo esc_html( $keys[ Lengow_Configuration::SHOP_ACTIVE ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
				</div>
			</div>
			<div id="lengow_wrapper_catalog_id"
				<?php echo esc_attr( $values[ Lengow_Configuration::SHOP_ACTIVE ] ? '' : 'hidden' ); ?>>
				<div class="grey-frame">
					<div class="form-group">
						<label class="control-label">
							<?php echo esc_html( $keys[ Lengow_Configuration::CATALOG_IDS ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
						<input type="text"
								name="lengow_catalog_id"
								class="form-control"
								value="<?php echo esc_attr( $values[ Lengow_Configuration::CATALOG_IDS ] ); ?>"/>
						<span class="legend blue-frame" style="display:block;">
							<?php echo esc_html( $keys[ Lengow_Configuration::CATALOG_IDS ][ Lengow_Configuration::PARAM_LEGEND ] ); ?>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="lgw-box">
			<h2 class="margin-s"><?php echo esc_html( $locale->t( 'global_setting.screen.debug_mode_title' ) ); ?></h2>
			<p><?php echo esc_html( $locale->t( 'global_setting.screen.debug_mode_description' ) ); ?></p>
			<div class="form-group">
				<div class="lgw-switch <?php echo esc_attr( $values[ Lengow_Configuration::DEBUG_MODE_ENABLED ] ? 'checked' : '' ); ?>">
					<label>
						<div>
							<span></span>
							<input type="hidden" name="lengow_debug_enabled" value="0">
							<input name="lengow_debug_enabled"
									type="checkbox"
								<?php echo esc_attr( $values[ Lengow_Configuration::DEBUG_MODE_ENABLED ] ? 'checked' : '' ); ?> />
						</div>
						<?php echo esc_html( $keys[ Lengow_Configuration::DEBUG_MODE_ENABLED ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
					</label>
				</div>
			</div>
			<div id="lengow_wrapper_debug"
				<?php echo esc_attr( (bool) $values[ Lengow_Configuration::DEBUG_MODE_ENABLED ] ? '' : 'hidden' ); ?>>
				<div class="grey-frame">
					<div class="form-group">
						<label class="control-label">
							<?php echo esc_html( $keys[ Lengow_Configuration::PLUGIN_ENV ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
						<select class="form-control " name="lengow_plugin_env" style="font-size: 13px; text-transform: uppercase;">
							<?php foreach ( Lengow_Configuration::ENVIRONMENTS as $env ) : ?>
							<option value="<?php echo esc_attr( $env ); ?>"
								<?php
								if ( isset( $values[ Lengow_Configuration::PLUGIN_ENV ] ) && $env === $values[ Lengow_Configuration::PLUGIN_ENV ] ) :
									?>
									selected="selected"<?php endif ?>>
									<?php echo esc_html( $env ); ?>
							</option>
							<?php endforeach; ?>
						</select>
						<span class="legend blue-frame" style="display:block;">
							<?php echo esc_html( $keys[ Lengow_Configuration::PLUGIN_ENV ][ Lengow_Configuration::PARAM_LEGEND ] ); ?>
						</span>
					</div>
					<div class="form-group">
						<label class="control-label">
							<?php echo esc_html( $keys[ Lengow_Configuration::ACCOUNT_ID ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
						<input type="text" name="lengow_account_id" class="form-control"
								value="<?php echo esc_attr( $values[ Lengow_Configuration::ACCOUNT_ID ] ); ?>"/>
					</div>
					<div class="form-group">
						<label class="control-label">
							<?php echo esc_html( $keys[ Lengow_Configuration::ACCESS_TOKEN ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
						<input type="text" name="lengow_access_token" class="form-control"
								value="<?php echo esc_attr( $values[ Lengow_Configuration::ACCESS_TOKEN ] ); ?>">
					</div>
					<div class="form-group">
						<label class="control-label">
							<?php echo esc_html( $keys[ Lengow_Configuration::SECRET ][ Lengow_Configuration::PARAM_LABEL ] ); ?>
						</label>
						<input type="text" name="lengow_secret_token" class="form-control"
								value="<?php echo esc_attr( $values[ Lengow_Configuration::SECRET ] ); ?>">
					</div>
				</div>
			</div>
		</div>
		<div class="lgw-box">
			<h2><?php echo esc_html( $locale->t( 'global_setting.screen.log_file_title' ) ); ?></h2>
			<p><?php echo esc_html( $locale->t( 'global_setting.screen.log_file_description' ) ); ?></p>
			<select class="js-log-select js-select lengow_select">
				<option value="" disabled selected>
					<?php echo esc_html( $locale->t( 'global_setting.screen.please_choose_log' ) ); ?>
				</option>
				<?php foreach ( $list_file as $file ) : ?>
					<option value="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_settings' ) ); ?>&action=download&date=<?php echo esc_attr( $file[ Lengow_Log::LOG_DATE ] ); ?>">
						<?php echo esc_html( date_format( date_create( $file[ Lengow_Log::LOG_DATE ] ), 'd F Y' ) ); ?></option>
				<?php endforeach; ?>
				<?php if ( ! empty( $list_file ) ) : ?>
					<option
							value="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_settings' ) ); ?>&action=download_all">
						<?php echo esc_html( $locale->t( 'global_setting.screen.download_all_files' ) ); ?>
					</option>
				<?php endif; ?>
			</select>
			<button type="button" class="js-log-btn-download lgw-btn lgw-btn-white" style="display: none;">
				<i class="fa fa-download"></i> <?php echo esc_html( $locale->t( 'global_setting.screen.button_download_file' ) ); ?>
			</button>
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
