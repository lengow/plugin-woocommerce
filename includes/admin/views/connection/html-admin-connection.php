<?php
/**
 * Admin View: Connection
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="lengow_connection_wrapper" class="lgw-container lgw-connection text-center">
	<div class="lgw-content-section">
		<div class="lgw-logo">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/lengow-blue.png" alt="lengow">
		</div>
	</div>
	<div id="lgw-connection-content">
		<div class="lgw-content-section">
			<p><?php echo $locale->t( 'connection.home.description_first' ); ?></p>
			<p><?php echo $locale->t( 'connection.home.description_second' ); ?></p>
			<p><?php echo $locale->t( 'connection.home.description_third' ); ?></p>
		</div>
		<div class="lgw-module-illu">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/connected-woocommerce.png"
			     class="lgw-module-illu-module"
			     alt="woocommerce">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/connected-lengow.png"
			     class="lgw-module-illu-lengow"
			     alt="lengow">
			<img src="/wp-content/plugins/lengow-woocommerce/assets/images/plug-grey.png"
			     class="lgw-module-illu-plug"
			     alt="plug">
		</div>
		<p><?php echo $locale->t( 'connection.home.description_fourth' ); ?></p>
		<div>
			<button class="lgw-btn lgw-btn-green js-go-to-credentials">
				<?php echo $locale->t( 'connection.home.button' ); ?>
			</button>
			<br/>
			<p>
				<?php echo $locale->t( 'connection.home.no_account' ); ?>
				<a href="//my.<?php echo Lengow_Connector::get_lengow_url(); ?>" target="_blank">
					<?php echo $locale->t( 'connection.home.no_account_sign_up' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
