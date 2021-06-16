<?php
/**
 * Admin View: Help
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-container">
	<?php if ( Lengow_Configuration::debug_mode_is_active() ) : ?>
        <div id="lgw-debug">
			<?php echo $locale->t( 'menu.debug_active' ); ?>
        </div>
	<?php endif; ?>
    <div class="lgw-box lengow_help_wrapper text-center">
        <h2><?php echo $locale->t( 'help.screen.title' ); ?></h2>
        <p>
			<?php echo $locale->t( 'help.screen.contain_text_support' ); ?>
            <a href="<?php echo $plugin_links[ Lengow_Sync::LINK_TYPE_SUPPORT ]; ?>"
               target="_blank"
               title="Support Lengow">
				<?php echo $locale->t( 'help.screen.title_lengow_support' ); ?>
            </a>
        </p>
        <p><?php echo $locale->t( 'help.screen.contain_text_support_hour' ); ?></p>
        <p>
			<?php echo $locale->t( 'help.screen.find_answer' ); ?>
            <a href="<?php echo $plugin_links[ Lengow_Sync::LINK_TYPE_HELP_CENTER ]; ?>"
               target="_blank"
               title="Help Center">
				<?php echo $locale->t( 'help.screen.link_woocommerce_guide' ); ?>
            </a>
        </p>
    </div>
</div>
