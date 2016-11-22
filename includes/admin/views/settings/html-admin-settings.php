<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
Lengow_Admin_Settings::post_process();
$keys      = Lengow_Configuration::get_keys();
$values    = Lengow_Configuration::get_all_values();
$list_file = Lengow_Log::get_paths();
?>
<div class="lgw-container" id="lengow_mainsettings_wrapper" xmlns="http://www.w3.org/1999/html">
	<?php if ( $values['lengow_preprod_enabled'] == 1 ) : ?>
		<div id="lgw-preprod" class="adminlengowmainsetting">
			<?php echo $locale->t( 'menu.preprod_active' ); ?>
		</div>
	<?php endif; ?>
	<form class="lengow_form" method="POST">
		<input type="hidden" name="action" value="process">
		<div class="lgw-box">
			<h2><?php echo $locale->t( 'global_setting.screen.main_setting_title' ); ?></h2>
			<label class="control-label"><?php echo $keys['lengow_authorized_ip']['label'] ?></label>
			<input type="text" name="lengow_authorized_ip" class="form-control"
			       value="<?php echo $values['lengow_authorized_ip'] ?>"/>
			<span class="legend" style="display:block;"><?= $keys['lengow_authorized_ip']['legend'] ?></span>
			<br/>
			<h2 class="margin-s"><?php echo $locale->t( 'global_setting.screen.preprod_mode_title' ); ?></h2>
			<p><?php echo $locale->t( 'global_setting.screen.preprod_mode_description' ); ?></p>
			<div class="lgw-switch <?= $values['lengow_preprod_enabled'] == 1 ? 'checked' : ''; ?>">
				<label>
					<div>
						<span></span>
						<input type="hidden" name="lengow_preprod_enabled" value="0">
						<input name="lengow_preprod_enabled"
						       type="checkbox"
							<?php echo $values['lengow_preprod_enabled'] == 1 ? 'checked' : ''; ?> />
					</div>
					<?php echo $keys['lengow_preprod_enabled']['label'] ?>
				</label>
			</div>
			<div id="lengow_wrapper_preprod">
				<div class="grey-frame">
					<div class="form-group">
						<div class="lgw-switch <?php echo $values['lengow_store_enabled'] == 1 ? 'checked' : ''; ?>">
							<label>
								<div>
									<span></span>
									<input type="hidden" name="lengow_store_enabled" value="0">
									<input name="lengow_store_enabled"
									       type="checkbox"
										<?php echo $values['lengow_store_enabled'] == 1 ? 'checked' : ''; ?>/>
								</div>
								<?php echo $keys['lengow_store_enabled']['label'] ?>
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label"><?php echo $keys['lengow_account_id']['label'] ?></label>
						<input type="text" name="lengow_account_id" class="form-control"
						       value="<?php echo $values['lengow_account_id'] ?>"/>
					</div>
					<div class="form-group">
						<label class="control-label"><?php echo $keys['lengow_access_token']['label'] ?></label>
						<input type="text" name="lengow_access_token" class="form-control"
						       value="<?php echo $values['lengow_access_token'] ?>"/>
					</div>
					<div class="form-group">
						<label class="control-label"><?php echo $keys['lengow_secret_token']['label'] ?></label>
						<input type="text" name="lengow_secret_token" class="form-control"
						       value="<?php echo $values['lengow_secret_token'] ?>"/>
					</div>
				</div>
			</div>
		</div>
		<div class="lgw-box">
			<h2><?php echo $locale->t( 'global_setting.screen.export_title' ); ?></h2>
			<label class="control-label"><?php echo $keys['lengow_product_types']['label'] ?></label>
			<div class="form-group">
				<select class="form-control js-multiple-select" name="lengow_product_types[]" multiple>
					<?php foreach ( Lengow_Main::$PRODUCT_TYPES as $row => $value ) :
						$selected = false;
						foreach ( $values['lengow_product_types'] as $key => $type ) {
							if ( $type == $row ) {
								$selected = 'selected';
								continue;
							}
						}
						?>
						<option value="<?php echo $row ?>" <?php echo $selected ?>><?php echo $value ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<span class="legend" style="display:block;"><?php echo $keys['lengow_product_types']['legend'] ?></span>
		</div>
		<div class="lgw-box">
			<h2><?php echo $locale->t( 'global_setting.screen.import_setting_title' ); ?></h2>
			<p><?php echo $locale->t( 'global_setting.screen.import_setting_description' ); ?></p>
			<div class="form-group">
				<div class="lgw-switch <?php echo $values['lengow_import_enabled'] == 1 ? 'checked' : ''; ?>">
					<label>
						<div>
							<span></span>
							<input type="hidden" name="lengow_import_enabled" value="0">
							<input name="lengow_import_enabled"
							       type="checkbox"
								<?php echo $values['lengow_import_enabled'] == 1 ? 'checked' : ''; ?> />
						</div>
						<?php echo $keys['lengow_import_enabled']['label'] ?>
					</label>
				</div>
				<span class="legend"
				      style="display:block;"><?php echo $keys['lengow_import_enabled']['legend'] ?></span>
			</div>
			<div id="lengow_wrapper_import">
				<div class="grey-frame">
					<div class="form-group">
						<div class="input-group">
							<input type="number" name="lengow_import_days" class="form-control"
							       value="<?php echo $values['lengow_import_days'] ?>" min="1" max="99"/>
							<div class="input-group-addon">
								<div class="unit"><?php echo $locale->t( 'global_setting.screen.nb_days' ); ?></div>
							</div>
							<div class="clearfix"></div>
						</div>
						<span class="legend"
						      style="display:block;"><?php echo $keys['lengow_import_days']['legend'] ?></span>
					</div>
					<div class="form-group">
						<div
							class="lgw-switch <?php echo $values['lengow_import_ship_mp_enabled'] == 1 ? 'checked' : ''; ?>">
							<label>
								<div>
									<span></span>
									<input type="hidden" name="lengow_import_ship_mp_enabled" value="0">
									<input name="lengow_import_ship_mp_enabled"
									       type="checkbox"
										<?php echo $values['lengow_import_ship_mp_enabled'] == 1 ? 'checked' : ''; ?>/>
								</div>
								<?php echo $keys['lengow_import_ship_mp_enabled']['label'] ?>
							</label>
						</div>
						<span class="legend"
						      style="display:block;"><?php echo $keys['lengow_import_ship_mp_enabled']['legend'] ?></span>
					</div>
				</div>
			</div>
		</div>
		<div class="lgw-box">
			<h2><?php echo $locale->t( 'global_setting.screen.log_file_title' ); ?></h2>
			<p><?php echo $locale->t( 'global_setting.screen.log_file_description' ); ?></p>
			<select class="js-log-select js-select lengow_select">
				<option value="" disabled selected>
					<?php echo $locale->t( 'global_setting.screen.please_choose_log' ) ?>
				</option>
				<?php foreach ( $list_file as $file ) : ?>
					<option
						value="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_settings' ); ?>&action=download&file=<?php echo $file['short_path'] ?>">
						<?php $file_name = explode( ".", $file['name'] ); ?>
						<?php echo date_format( date_create( $file_name[0] ), "d F Y" ); ?></option>
				<?php endforeach; ?>
				<option
					value="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_settings' ); ?>&action=download_all">
					<?php echo $locale->t( 'global_setting.screen.download_all_files' ) ?>
				</option>
			</select>
			<button type="button" class="js-log-btn-download lgw-btn lgw-btn-white" style="display: none;">
				<i class="fa fa-download"></i> <?php echo $locale->t( 'global_setting.screen.button_download_file' ); ?>
			</button>
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
								<?php echo $locale->t( 'global_setting.screen.setting_saving' ) ?>
							</div>
							<div class="btn-step done" data-success="Saved!" data-error="Error">
								<?php echo $locale->t( 'global_setting.screen.setting_saved' ) ?>
							</div>
						</div>
					</button>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</form>
</div>