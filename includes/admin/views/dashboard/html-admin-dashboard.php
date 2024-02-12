<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="lengow_home_wrapper" class="lgw-container">
	<?php if ( Lengow_Configuration::debug_mode_is_active() ) : ?>
        <div id="lgw-debug">
			<?php echo esc_html( $locale->t( 'menu.debug_active' ) ); ?>
        </div>
	<?php endif; ?>
    <div class="lgw-row">
        <div class="text-left lgw-col-6" id="alert-plugin-available">
			<?php if ( $plugin_data && version_compare( LENGOW_VERSION, $plugin_data['version'], '<' ) ) : ?>
				<?php echo esc_html( $locale->t( 'menu.new_version_available', array( 'version' => $plugin_data['version'] ) ) ); ?>
                <button class="btn-link mod-inline js-upgrade-plugin-modal-open">
					<?php echo esc_html( $locale->t( 'menu.download_plugin' ) ); ?>
                </button>
			<?php endif; ?>
        </div>
        <div class="text-right lgw-col-6" id="alert-counter-trial">
			<?php if ( 'free_trial' === $merchant_status['type'] && ! $merchant_status['expired'] ) : ?>
				<?php echo esc_html( $locale->t( 'menu.counter', array( 'counter' => $merchant_status['day'] ) ) ); ?>
                <a href="<?php echo esc_url( '//my.' . Lengow_Configuration::get_lengow_url() ); ?>" target="_blank">
                    <?php echo esc_html( $locale->t( 'menu.upgrade_account' ) ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="lgw-box lgw-home-header text-center">
        <img src="/wp-content/plugins/lengow-woocommerce/assets/images/lengow-white-big.png" alt="lengow">
        <h1><?php echo esc_html( $locale->t( 'dashboard.screen.welcome_back' ) ); ?></h1>
        <a href="<?php echo esc_url( '//my.' . Lengow_Configuration::get_lengow_url() ); ?>" class="lgw-btn" target="_blank">
            <?php echo esc_html( $locale->t( 'dashboard.screen.go_to_lengow' ) ); ?>
        </a>
    </div>
    <div class="lgw-row lgw-home-menu text-center">
        <div class="lgw-col-4">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_products' )); ?>"
               class="lgw-box-link">
                <div class="lgw-box">
                    <img src="/wp-content/plugins/lengow-woocommerce/assets/images/home-products.png"
                         class="img-responsive">
                    <h2><?php echo esc_html( $locale->t( 'dashboard.screen.products_title' ) ); ?></h2>
                    <p><?php echo esc_html( $locale->t( 'dashboard.screen.products_text' ) ); ?></p>
                </div>
            </a>
        </div>
        <div class="lgw-col-4">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_orders' ) ); ?>"
               class="lgw-box-link">
                <div class="lgw-box">
                    <img src="/wp-content/plugins/lengow-woocommerce/assets/images/home-orders.png"
                         class="img-responsive">
                    <h2>
						<?php echo esc_html( $locale->t( 'dashboard.screen.orders_title' ) ); ?>
						<?php if ( $total_pending_order > 0 ) : ?>
                            <span class="lgw-label lgw-label red"><?php echo esc_html( $total_pending_order ); ?></span>
						<?php endif; ?>
                    </h2>
                    <p><?php echo esc_html( $locale->t( 'dashboard.screen.orders_text' ) ); ?></p>
                </div>
            </a>
        </div>
        <div class="lgw-col-4">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ) ); ?>"
               class="lgw-box-link">
                <div class="lgw-box">
                    <img src="/wp-content/plugins/lengow-woocommerce/assets/images/home-settings.png"
                         class="img-responsive">
                    <h2><?php echo esc_html( $locale->t( 'dashboard.screen.settings_title' ) ); ?></h2>
                    <p><?php echo esc_html( $locale->t( 'dashboard.screen.settings_text' ) ); ?></p>
                </div>
            </a>
        </div>
    </div>
    <div class="lgw-box">
        <h2><?php echo esc_html( $locale->t( 'dashboard.screen.some_help_title' ) ); ?></h2>
        <p>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_help' ) ); ?>">
				<?php echo esc_html( $locale->t( 'dashboard.screen.get_in_touch' ) ); ?>
            </a>
        </p>
        <p>
            <a href="<?php echo esc_url( $plugin_links[ Lengow_Sync::LINK_TYPE_HELP_CENTER ] ); ?>" target="_blank">
				<?php echo esc_html( $locale->t( 'dashboard.screen.visit_help_center' ) ); ?></a>
			<?php echo esc_html( $locale->t( 'dashboard.screen.configure_plugin' ) ); ?>
        </p>
    </div>
</div>
