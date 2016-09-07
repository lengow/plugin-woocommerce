<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-container" id="lengow_mainsettings_wrapper" xmlns="http://www.w3.org/1999/html">
	<?php if ( $values['lengow_preprod_enabled'] == 1 ) : ?>
		<div id="lgw-preprod" class="adminlengowmainsetting">
			<?= $locale->t( 'menu.preprod_active' ); ?>
		</div>
	<?php endif; ?>
	<form class="lengow_form" method="POST">
		<!--        <input type="hidden" name="action" value="process">-->
		<div class="lgw-box">
			<h2>Authorised IP</h2>
			<label class="control-label"><?= $keys['lengow_authorized_ip']['label'] ?></label>
			<input type="text" name="lengow_authorized_ip" class="form-control"
			       value="<?= $values['lengow_authorized_ip'] ?>"/>
			<h2><?= $locale->t( 'global_setting.screen.preprod_mode_title' ); ?></h2>
			<p><?= $locale->t( 'global_setting.screen.preprod_mode_description' ); ?></p>
			<div class="lgw-switch <?= $values['lengow_preprod_enabled'] == 1 ? 'checked' : '' ;?>">
				<label>
					<div>
						<span></span>
						<input type="hidden" name="lengow_preprod_enabled" value="0">
						<input name="lengow_preprod_enabled"
						       type="checkbox"
						<?= $values['lengow_preprod_enabled'] == 1 ? 'checked' : '' ;?> />
					</div>
					<?= $keys['lengow_preprod_enabled']['label'] ?>
				</label>
			</div>
			<div id="lengow_wrapper_preprod">
				<div class="grey-frame">
					<div class="form-group">
						<div class="lgw-switch <?= $values['lengow_store_enabled'] == 1 ? 'checked' : '' ;?>">
							<label>
								<div>
									<span></span>
									<input type="hidden" name="lengow_store_enabled" value="0">
									<input name="lengow_store_enabled"
										   type="checkbox"
										<?= $values['lengow_store_enabled'] == 1 ? 'checked' : '' ;?>/>
								</div>
								<?= $keys['lengow_store_enabled']['label'] ?>
							</label>
						</div>
					</div>
					<div class="form-group">
						<label class="control-label"><?= $keys['lengow_account_id']['label'] ?></label>
						<input type="text" name="lengow_account_id" class="form-control"
						   value="<?= $values['lengow_account_id'] ?>"/>
					</div>
					<div class="form-group">
						<label class="control-label"><?= $keys['lengow_access_token']['label'] ?></label>
						<input type="text" name="lengow_access_token" class="form-control"
						   value="<?= $values['lengow_access_token'] ?>"/>
					</div>
					<div class="form-group">
						<label class="control-label"><?= $keys['lengow_secret_token']['label'] ?></label>
						<input type="text" name="lengow_secret_token" class="form-control"
						   value="<?= $values['lengow_secret_token'] ?>"/>
					</div>
				</div>
			</div>
		</div>
		<div class="lgw-box">
			<h2><?= $locale->t('global_setting.screen.export_title'); ?></h2>
			<label class="control-label"><?= $locale->t('global_setting.screen.product_types_title'); ?></label>
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
						<option value="<?= $row ?>" <?= $selected ?>><?= $value ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<span class="legend" style="display:block;"><?= $locale->t('global_setting.screen.product_types_legend'); ?></span>
		</div>
		<div class="lgw-box">
			<h2><?= $locale->t('global_setting.screen.import_setting_title'); ?></h2>
			<p><?= $locale->t('global_setting.screen.import_setting_description'); ?></p>
			<div class="form-group">
				<div class="input-group">
					<input type="number" name="lengow_import_days" class="form-control" value="<?= $values['lengow_import_days'] ?>" min="1" max="99" />
					<div class="input-group-addon">
						<div class="unit"><?= $locale->t('global_setting.screen.nb_days'); ?></div>
					</div>
					<div class="clearfix"></div>
				</div>
				<span class="legend" style="display:block;"><?= $keys['lengow_import_days']['legend']?></span>
			</div>
			<div class="form-group">
				<div class="lgw-switch <?= $values['lengow_import_ship_mp_enabled'] == 1 ? 'checked' : '' ;?>">
					<label>
						<div>
							<span></span>
							<input type="hidden" name="lengow_import_ship_mp_enabled" value="0">
							<input name="lengow_import_ship_mp_enabled"
								   type="checkbox"
								<?= $values['lengow_import_ship_mp_enabled'] == 1 ? 'checked' : '' ;?>/>
						</div>
						<?= $keys['lengow_import_ship_mp_enabled']['label'] ?>
					</label>
				</div>
			</div>
			<div id="lengow_wrapper_import_ship_mp_enabled">
				<div class="grey-frame">
					<div class="form-group">
						<div class="lgw-switch <?= $values['lengow_import_stock_ship_mp'] == 1 ? 'checked' : '' ?>">
							<label>
								<div>
									<span></span>
									<input type="hidden" name="lengow_import_stock_ship_mp" value="0">
									<input type="checkbox" name="lengow_import_stock_ship_mp" <?= $values['lengow_import_stock_ship_mp'] == 1 ? 'checked' : '' ?> />
								</div>
								<?= $keys['lengow_import_stock_ship_mp']['label'] ?>
							</label>
						</div>
					</div>
					<span class="legend" style="display:block;"><?= $keys['lengow_import_stock_ship_mp']['legend'] ?></span>
				</div>
			</div>
		</div>
		<div class="lgw-box">
			<h2><?= $locale->t( 'global_setting.screen.log_file_title' ); ?></h2>
			<p><?= $locale->t( 'global_setting.screen.log_file_description' ); ?></p>
			<!--            TODO - select for all logs-->
			<button type="button" id="download_log" class="lgw-btn lgw-btn-white">
				<i class="fa fa-download"></i> <?= $locale->t( 'global_setting.screen.button_download_file' ); ?>
			</button>
		</div>
		<div class="form-group container">
			<div class="lengow_main_setting_block_content">
				<div class="pull-left">
					<button type="submit" class="lgw-btn lgw-btn-progression lengow_submit_main_setting">
						<div class="btn-inner">
							<div class="btn-step default">
								<?= $locale->t( 'global_setting.screen.button_save' ); ?>
							</div>
							<div class="btn-step loading">
								<?= $locale->t('global_setting.screen.setting_saving') ?>
							</div>
							<div class="btn-step done" data-success="Saved!" data-error="Error">
								<?= $locale->t('global_setting.screen.setting_saved') ?>
							</div>
						</div>
					</button>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</form>
</div>