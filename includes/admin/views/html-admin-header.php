<?php
/**
 * Admin View: Header
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( isset( $_GET['tab'] ) ) {
	$current_page = sanitize_text_field($_GET['tab']);
} ?>
<ul class="nav nav-pills lengow-nav lengow-nav-top">
    <li role="presentation" id="lengow_logo">
        <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_dashboard' ); ?>">
            <img src="/wp-content/plugins/lengow-woocommerce/assets/images/lengow-white.png" alt="lengow">
        </a>
    </li>
    <li role="presentation"
        class="<?php echo ( isset( $current_page ) && 'lengow_admin_products' === $current_page ) ? 'active' : ''; ?>">
        <a href="
			<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_products' ); ?>">
			<?php echo $locale->t( 'menu.product' ); ?>
        </a>
    </li>
    <li role="presentation"
        class="<?php echo ( isset( $current_page ) && 'lengow_admin_orders' === $current_page ) ? 'active' : ''; ?>"
        id="js-menugotoimport">
        <a href="
			<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_orders' ); ?>">
			<?php echo $locale->t( 'menu.order' ); ?>
			<?php if ( $total_pending_order > 0 ) : ?>
                <span class="lengow-nav-notif"><?php echo $total_pending_order; ?></span>
			<?php endif; ?>
        </a>
    </li>
    <li class="lengow_float_right <?php echo ( isset( $current_page ) && 'lengow_admin_settings' === $current_page ) ? 'active' : ''; ?>"
        id="menugotosetting">
        <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ); ?>"
           class="lengow_link_tooltip"
           data-placement="bottom" data-original-title="<?php echo $locale->t( 'menu.global_parameter' ); ?>">
            <i class="fa fa-cog fa-2x"></i>
        </a>
    </li>
    <li class="lengow_float_right <?php echo ( isset( $current_page ) && 'lengow_admin_help' === $current_page ) ? 'active' : ''; ?>"
        id="menugotohelp">
        <a href="<?php echo admin_url( 'admin.php?page=lengow&tab=lengow_admin_help' ); ?>"
           class="lengow_link_tooltip"
           data-placement="bottom" data-original-title="<?php echo $locale->t( 'menu.help' ); ?>">
            <i class="fa fa-life-ring fa-2x"></i>
        </a>
    </li>
    <li class="lengow_float_right" id="menugotosolution">
        <a href="//my.<?php echo Lengow_Connector::LENGOW_URL; ?>" target="_blank">
			<?php echo $locale->t( 'menu.jump_to_lengow' ); ?>
        </a>
    </li>
	<?php if ( 'free_trial' === $merchant_status['type'] && ! $merchant_status['expired'] ) : ?>
        <li class="lengow_float_right" id="menucountertrial">
            <div class="lgw-block">
				<?php echo $locale->t( 'menu.counter', array( 'counter' => $merchant_status['day'] ) ); ?>
                <a href="//my.<?php echo Lengow_Connector::LENGOW_URL; ?>" target="_blank">
					<?php echo $locale->t( 'menu.upgrade_account' ); ?>
                </a>
            </div>
        </li>
	<?php endif; ?>
	<?php if ( ! $plugin_is_up_to_date ) : ?>
        <li class="lengow_float_right" id="menupluginavailable">
            <div class="lgw-block">
				<?php echo $locale->t( 'menu.new_version_available', array( 'version' => $plugin_data['version'] ) ); ?>
                <button class="btn-link mod-inline js-upgrade-plugin-modal-open">
					<?php echo $locale->t( 'menu.download_plugin' ); ?>
                </button>
            </div>
        </li>
	<?php endif; ?>
</ul>