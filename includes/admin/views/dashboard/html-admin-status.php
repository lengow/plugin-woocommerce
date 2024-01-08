<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-container">
    <div class="lgw-box">
        <div class="lgw-row">
            <div class="lgw-col-6 display-inline-block">
                <h2 class="text-center"><?php echo $locale->t( 'status.screen.title_end_free_trial' ); ?></h2>
                <h3 class="text-center"><?php echo $locale->t( 'status.screen.subtitle_end_free_trial' ); ?></h3>
                <p class="text-center"><?php echo $locale->t( 'status.screen.first_description_end_free_trial' ); ?></p>
                <p class="text-center"><?php echo $locale->t( 'status.screen.second_description_end_free_trial' ); ?></p>
                <p class="text-center"><?php echo $locale->t( 'status.screen.third_description_end_free_trial' ); ?></p>
                <div class="text-center">
                    <a href="//my.<?php echo Lengow_Configuration::get_lengow_url(); ?>" class="lgw-btn" target="_blank">
						<?php echo $locale->t( 'status.screen.upgrade_account_button' ); ?>
                    </a>
                </div>
                <div class="text-center">
                    <a href="<?php echo $refresh_status; ?>"
                       class="lgw-box-link">
						<?php echo $locale->t( 'status.screen.refresh_action' ); ?>
                    </a>
                </div>
            </div>
            <div class="lgw-col-6">
                <div class="vertical-center">
                    <img src="/wp-content/plugins/lengow-woocommerce/assets/images/logo-blue.png"
                         class="center-block" alt="lengow"/>
                </div>
            </div>
        </div>
    </div>
</div>
