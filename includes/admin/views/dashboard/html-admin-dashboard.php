<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="lengow_home_wrapper" class="lgw-container">
	<?php if ( (bool) Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) : ?>
        <div id="lgw-preprod">
			<?php echo $locale->t( 'menu.preprod_active' ); ?>
        </div>
	<?php endif; ?>
	<?php if ( 'free_trial' === $merchant_status['type'] && ! $merchant_status['expired'] ) : ?>
        <p class="text-right" id="menucountertrial">
			<?php echo $locale->t( 'menu.counter', array( 'counter' => $merchant_status['day'] ) ); ?>
            <a href="http://my.lengow.io/" target="_blank">
				<?php echo $locale->t( 'menu.upgrade_account' ); ?>
            </a>
        </p>
	<?php endif; ?>
    <div class="lgw-box lgw-home-header text-center">
        <img src="/wp-content/plugins/lengow-woocommerce/assets/images/lengow-white-big.png" alt="lengow">
        <h1><?php echo $locale->t( 'dashboard.screen.welcome_back' ); ?></h1>
        <a href="http://my.lengow.io/" class="lgw-btn" target="_blank">
			<?php echo $locale->t( 'dashboard.screen.go_to_lengow' ); ?>
        </a>
    </div>
    <div class="lgw-row lgw-home-menu text-center">
        <div class="lgw-col-4">
            <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_products' ); ?>"
               class="lgw-box-link">
                <div class="lgw-box">
                    <img src="/wp-content/plugins/lengow-woocommerce/assets/images/home-products.png"
                         class="img-responsive">
                    <h2><?php echo $locale->t( 'dashboard.screen.products_title' ); ?></h2>
                    <p><?php echo $locale->t( 'dashboard.screen.products_text' ); ?></p>
                </div>
            </a>
        </div>
        <div class="lgw-col-4">
            <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_orders' ); ?>"
               class="lgw-box-link">
                <div class="lgw-box">
                    <img src="/wp-content/plugins/lengow-woocommerce/assets/images/home-orders.png"
                         class="img-responsive">
                    <h2>
						<?php echo $locale->t( 'dashboard.screen.orders_title' ); ?>
						<?php if ( $total_pending_order > 0 ) : ?>
                            <span class="lgw-label lgw-label red"><?php echo $total_pending_order; ?></span>
						<?php endif; ?>
                    </h2>
                    <p><?php echo $locale->t( 'dashboard.screen.orders_text' ); ?></p>
                </div>
            </a>
        </div>
        <div class="lgw-col-4">
            <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ); ?>"
               class="lgw-box-link">
                <div class="lgw-box">
                    <img src="/wp-content/plugins/lengow-woocommerce/assets/images/home-settings.png"
                         class="img-responsive">
                    <h2><?php echo $locale->t( 'dashboard.screen.settings_title' ); ?></h2>
                    <p><?php echo $locale->t( 'dashboard.screen.settings_text' ); ?></p>
                </div>
            </a>
        </div>
    </div>
	<?php if ( $stats['available'] ) : ?>
        <div class="lgw-box text-center">
            <div class="lgw-col-12 center-block">
                <img src="/wp-content/plugins/lengow-woocommerce/assets/images/picto-stats.png" class="img-responsive">
            </div>
            <h2><?php echo $locale->t( 'dashboard.screen.partner_business' ); ?></h2>
            <div class="lgw-row lgw-home-stats">
                <div class="lgw-col-4 lgw-col-offset-2">
                    <h5><?php echo $locale->t( 'dashboard.screen.stat_turnover' ); ?></h5>
                    <span class="stats-big-value"><?php echo $stats['total_order']; ?></span>
                </div>
                <div class="lgw-col-4">
                    <h5><?php echo $locale->t( 'dashboard.screen.stat_nb_orders' ); ?></h5>
                    <span class="stats-big-value"><?php echo $stats['nb_order']; ?></span>
                </div>
            </div>
            <p>
                <a href="http://my.lengow.io/" target="_blank" class="lgw-btn lgw-btn-white">
					<?php echo $locale->t( 'dashboard.screen.stat_more_stats' ); ?>
                </a>
            </p>
        </div>
	<?php endif; ?>
    <div class="lgw-box">
        <h2><?php echo $locale->t( 'dashboard.screen.some_help_title' ); ?></h2>
        <p>
            <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_help' ); ?>">
				<?php echo $locale->t( 'dashboard.screen.get_in_touch' ); ?>
            </a>
        </p>
        <p>
            <a href="<?php echo $locale->t( 'help.screen.knowledge_link_url' ); ?>" target="_blank">
				<?php echo $locale->t( 'dashboard.screen.visit_help_center' ); ?></a>
			<?php echo $locale->t( 'dashboard.screen.configure_plugin' ); ?>
        </p>
    </div>
</div>