<?php
/**
 * Admin View: Connection Catalog Failed
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="lgw-content-section">
    <h2><?php echo esc_html( $locale->t( 'connection.catalog.failed_title' ) ); ?></h2>
</div>
<div class="lgw-module-illu mod-disconnected">
	<img src="/wp-content/plugins/lengow/assets/images/connected-woocommerce.png"
	     class="lgw-module-illu-module mod-disconnected"
	     alt="woocommerce">
	<img src="/wp-content/plugins/lengow/assets/images/connected-lengow.png"
	     class="lgw-module-illu-lengow mod-disconnected"
	     alt="lengow">
	<img src="/wp-content/plugins/lengow/assets/images/unplugged.png"
	     class="lgw-module-illu-plug mod-disconnected"
	     alt="unplugged">
</div>
<div class="lgw-content-section">
    <p><?php echo esc_html( $locale->t( 'connection.catalog.failed_description_first' ) ); ?></p>
    <p><?php echo esc_html( $locale->t( 'connection.catalog.failed_description_second' ) ); ?></p>
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
    <button class="lgw-btn lgw-btn-green js-go-to-catalog" data-retry="true">
        <?php echo esc_html( $locale->t( 'connection.cms.failed_button' ) ); ?>
    </button>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_dashboard' ) ); ?>"
        class="lgw-btn lgw-btn-green">
        <?php echo esc_html( $locale->t( 'connection.cms.success_button' ) ); ?>
    </a>
</div>
