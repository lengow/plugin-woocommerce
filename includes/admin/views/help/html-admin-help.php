<?php
/**
 * Admin View: Help
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="lgw-container">
    <?php if ( $keys['lengow_preprod_enabled'] == 1 ) : ?>
    <div id="lgw-preprod" class="adminlengowhelp">
        <?php echo $locale->t('menu.preprod_active') ?>
    </div>
    <?php endif; ?>
    <div class="lgw-box lengow_help_wrapper text-center">
        <!--<img src="/modules/lengow/views/img/cosmo-yoga.png" class="img-circle" alt="lengow">-->
        <h2><?php echo $locale->t('help.screen.title')?></h2>
        <p>
            <?php echo $locale->t('help.screen.contain_text_support');
            echo $mail_to; ?>

        </p>
        <p><?php echo $locale->t('help.screen.contain_text_support_hour')?></p>
        <p>
            <?php echo $locale->t('help.screen.find_answer')?>
            <a href="https://en.knowledgeowl.com/help/woocommerce-plugin"
               target="_blank"
               title="Help Center">
                <?php echo $locale->t('help.screen.link_woocommerce_guide')?>
            </a>
        </p>
    </div>
</div>
