<?php
/**
 * Admin View: Connection Cms Result
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="lgw-connection-cms-result">
	<?php if ( $cms_connected ) : ?>
        <div class="lgw-content-section">
            <h2><?php echo esc_html( $locale->t( 'connection.cms.success_title' ) ); ?></h2>
        </div>
        <div class="lgw-module-illu mod-connected">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/connected-woocommerce.png"
			     class="lgw-module-illu-module mod-connected"
			     alt="woocommerce">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/connected-lengow.png"
			     class="lgw-module-illu-lengow mod-connected"
			     alt="lengow">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/connection-module.png"
			     class="lgw-module-illu-plug mod-connected"
			     alt="connection">
		</div>
		<?php if ( $has_catalog_to_link ) : ?>
			<div class="lgw-content-section">
                <p><?php echo esc_html( $locale->t( 'connection.cms.success_description_first_catalog' ) ); ?></p>
                <p><?php echo esc_html( $locale->t( 'connection.cms.success_description_second_catalog' ) ); ?></p>
            </div>
			<div>
                <button class="lgw-btn lgw-btn-green js-go-to-catalog" data-retry="false">
                    <?php echo esc_html( $locale->t( 'connection.cms.success_button_catalog' ) ); ?>
                </button>
            </div>
		<?php else: ?>
			<div class="lgw-content-section">
                <p><?php echo esc_html( $locale->t( 'connection.cms.success_description_first' ) ); ?></p>
				<p>
                    <?php echo esc_html( $locale->t( 'connection.cms.success_description_second' ) ); ?>
                    <a href="<?php echo esc_url( 'https://my.' . Lengow_Configuration::get_lengow_url() ); ?>" target="_blank">
                        <?php echo esc_html( $locale->t( 'connection.cms.success_description_second_go_to_lengow' ) ); ?>
                    </a>
				</p>
			</div>
			<div>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_dashboard' ) ); ?>"
                   class="lgw-btn lgw-btn-green">
                    <?php echo esc_html( $locale->t( 'connection.cms.success_button' ) ); ?>
                </a>

            </div>
		<?php endif; ?>
	<?php else: ?>
        <div class="lgw-content-section">
            <h2><?php echo esc_html( $locale->t( 'connection.cms.failed_title' ) ); ?></h2>
        </div>
        <div class="lgw-module-illu mod-disconnected">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/connected-woocommerce.png"
			     class="lgw-module-illu-module mod-disconnected"
			     alt="woocommerce">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/connected-lengow.png"
			     class="lgw-module-illu-lengow mod-disconnected"
			     alt="lengow">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/unplugged.png"
			     class="lgw-module-illu-plug mod-disconnected"
			     alt="unplugged">
		</div>
		<div class="lgw-content-section">
			<?php if ( $credentials_valid ) : ?>
                <p><?php echo esc_html( $locale->t( 'connection.cms.failed_description' ) ); ?></p>
			<?php else: ?>
                <p><?php echo esc_html( $locale->t( 'connection.cms.failed_description_first_credentials' ) ); ?></p>
                <?php if ( Lengow_Configuration::get_lengow_url() === 'lengow.net' ) : ?>
                    <p><?php echo esc_html( $locale->t( 'connection.cms.failed_description_second_credentials_preprod' ) ); ?></p>
                <?php else: ?>
					<p><?php echo esc_html( $locale->t( 'connection.cms.failed_description_second_credentials_prod' ) ); ?></p>
				<?php endif; ?>
			<?php endif; ?>
			<p>
				<?php echo esc_html( $locale->t( 'connection.cms.failed_help' ) ); ?>
				<a href="<?php echo esc_url( $plugin_links[ Lengow_Sync::LINK_TYPE_HELP_CENTER ] ); ?>" target="_blank">
					<?php echo esc_html( $locale->t( 'connection.cms.failed_help_center' ) ); ?>
				</a>
				<?php echo esc_html( $locale->t( 'connection.cms.failed_help_or' ) ); ?>
				<a href="<?php echo esc_url( $plugin_links[ Lengow_Sync::LINK_TYPE_SUPPORT ] ); ?>" target="_blank">
					<?php echo esc_html( $locale->t( 'connection.cms.failed_help_customer_success_team' ) ); ?>
				</a>
			</p>
		</div>
		<div>
			<button class="lgw-btn lgw-btn-green js-go-to-credentials">
				<?php echo esc_html( $locale->t( 'connection.cms.failed_button' ) ); ?>
			</button>
		</div>
	<?php endif; ?>
</div>
