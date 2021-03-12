<?php
/**
 * Admin View: Connection Catalog Failed
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="lgw-content-section">
	<h2><?php echo $locale->t( 'connection.catalog.failed_title' ); ?></h2>
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
	<p><?php echo $locale->t( 'connection.catalog.failed_description_first' ); ?></p>
	<p><?php echo $locale->t( 'connection.catalog.failed_description_second' ); ?></p>
	<p>
		<?php echo $locale->t( 'connection.cms.failed_help' ); ?>
		<a href="<?php echo $locale->t( 'help.screen.knowledge_link_url' ); ?>" target="_blank">
			<?php echo $locale->t( 'connection.cms.failed_help_center' ); ?>
		</a>
		<?php echo $locale->t( 'connection.cms.failed_help_or' ); ?>
		<a href="<?php echo $locale->t( 'help.screen.link_lengow_support' ); ?>" target="_blank">
			<?php echo $locale->t( 'connection.cms.failed_help_customer_success_team' ); ?>
		</a>
	</p>
</div>
<div>
	<button class="lgw-btn lgw-btn-green js-go-to-catalog" data-retry="true">
		<?php echo $locale->t( 'connection.cms.failed_button' ); ?>
	</button>
	<a href="<?php echo admin_url( 'admin.php?page=lengow' ); ?>"
	   class="lgw-btn lgw-btn-green">
		<?php echo $locale->t( 'connection.cms.success_button' ); ?>
	</a>
</div>
