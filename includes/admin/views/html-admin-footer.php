<?php
/**
 * Admin View: Footer
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="lgw-container lgw-footer clear">
    <div class="lgw-content-section text-center">
        <div id="lgw-footer">
            <p>
                <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ); ?>"
                   class="sub-link"
                   title="<?php echo $locale->t( 'setting.setting' ); ?>">
                    <?php echo $locale->t( 'footer.setting' ); ?>
                </a>
                | <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_toolbox' ); ?>"
                   class="sub-link"
                   title="<?php echo $locale->t( 'footer.toolbox' ); ?>">
                    <?php echo $locale->t( 'footer.toolbox' ); ?>
                </a>
                | <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_legals' ); ?>"
                     class="sub-link"
                     title="<?php echo $locale->t( 'footer.legals' ); ?>">
                    <?php echo $locale->t( 'footer.legals' ); ?>
                </a>
                | <?php echo $locale->t( 'footer.plugin_lengow' ) ?> - v.<?php echo LENGOW_VERSION; ?>
				<?php if ( 'lengow.net' === Lengow_Configuration::get_lengow_url() ): ?>
                    <span class="lgw-label-preprod">preprod</span>
				<?php endif; ?>
                | copyright © <?php echo date( 'Y' ); ?> <a href="http://www.lengow.com" target="_blank"
                                                            class="sub-link" title="Lengow.com">Lengow</a>
            </p>
        </div>
    </div>
	<?php if ( ! $plugin_is_up_to_date ): ?>
        <!-- Modal Update plugin -->
        <div id="upgrade-plugin"
             class="lgw-modalbox mod-size-medium <?php if ( $show_plugin_upgrade_modal ): ?>is-open<?php endif; ?>">
            <div class="lgw-modalbox-content">
                <span class="lgw-modalbox-close js-upgrade-plugin-modal-close"></span>
                <div class="lgw-modalbox-body">
                    <div class="lgw-row flexbox-vertical-center">
                        <div class="lgw-col-5 text-center">
                            <img src="/wp-content/plugins/lengow/assets/images/plugin-update.png" alt="">
                        </div>
                        <div class="lgw-col-7">
                            <h1><?php echo $locale->t( 'update.version_available' ); ?></h1>
                            <p>
								<?php echo $locale->t( 'update.start_now' ); ?>
                                <a href="<?php echo $plugin_links[ Lengow_Sync::LINK_TYPE_CHANGELOG ]; ?>"
                                   target="_blank">
									<?php echo $locale->t( 'update.link_changelog' ); ?>
                                </a>
                            </p>
                            <div class="lgw-content-section mod-small">
                                <h2><?php echo $locale->t( 'update.step_one' ); ?></h2>
                                <p class="no-margin-bottom">
									<?php echo $locale->t( 'update.download_last_version' ); ?>
                                </p>
                                <p class="text-lesser text-italic">
									<?php echo $locale->t(
										'update.plugin_compatibility',
										array(
											'cms_min_version' => $plugin_data['cms_min_version'],
											'cms_max_version' => $plugin_data['cms_max_version'],
										)
									); ?>
									<?php foreach ( $plugin_data['extensions'] as $extension ): ?>
                                        <br/>
										<?php echo $locale->t(
											'update.extension_required',
											array(
												'name'        => $extension['name'],
												'min_version' => $extension['min_version'],
												'max_version' => $extension['max_version'],
											)
										); ?>
									<?php endforeach; ?>
                                </p>
                            </div>
                            <div class="lgw-content-section mod-small">
                                <h2><?php echo $locale->t( 'update.step_two' ); ?></h2>
                                <p class="no-margin-bottom">
                                    <a href="<?php echo $plugin_links[ Lengow_Sync::LINK_TYPE_UPDATE_GUIDE ]; ?>"
                                       target="_blank"><?php echo $locale->t( 'update.link_follow' ); ?></a>
									<?php echo $locale->t( 'update.update_procedure' ); ?>
                                </p>
                                <p class="text-lesser text-italic">
									<?php echo $locale->t( 'update.not_working' ); ?>
                                    <a href="<?php echo $plugin_links[ Lengow_Sync::LINK_TYPE_SUPPORT ]; ?>"
                                       target="_blank"><?php echo $locale->t( 'update.customer_success_team' ); ?></a>
                                </p>
                            </div>
                            <div class="flexbox-vertical-center margin-standard">
                                <a class="lgw-btn lgw-modal-download no-margin-top"
                                   href="//my.<?php echo Lengow_Connector::LENGOW_URL . $plugin_data['download_link'] ?>"
                                   target="_blank">
									<?php echo $locale->t(
										'update.button_download_version',
										array( 'version' => $plugin_data['version'] )
									); ?>
                                </a>
								<?php if ( $show_plugin_upgrade_modal ): ?>
                                    <button class="btn-link sub-link no-margin-top text-small js-upgrade-plugin-modal-remind-me">
										<?php echo $locale->t( 'update.button_remind_me_later' ); ?>
                                    </button>
								<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<?php endif; ?>
</div>
