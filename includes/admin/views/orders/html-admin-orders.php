<?php
/**
 * Admin View: Orders
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="lgw-container">
    <?php if ( $keys['lengow_preprod_enabled'] == 1 ) : ?>
        <div id="lgw-preprod" class="adminlengowlegals">
            <?php echo $locale->t('menu.preprod_active') ?>
        </div>
    <?php endif; ?>
    <div class="lgw-box" id="lengow_order_wrapper">
        <?php if ( $warning_message) : ?>
            <p class="blue-frame" style="line-height: 20px;">
                <?php echo $warning_message ?>
            </p>
        <?php endif; ?>
        <div class="lgw-col-8" style="padding:0;">
            <div id="lengow_last_importation">
                <p>
                    <?php if ($order_collection['last_import_type'] != 'none') : ?>
                        <?php echo $locale->t('order.screen.last_order_importation'); ?>
                        : <b><?php echo $order_collection['last_import_date']; ?></b>
                        <?php else: ?>
                        <?php echo $locale->t('order.screen.no_order_importation'); ?>
                    <?php endif; ?>
                </p>
            </div>
            <div id="lengow_wrapper_messages" class="blue-frame" style="display:none;"></div>
        </div>
    </div>
    <div class="pull-right text-right lgw-col-3">
        <a id="lengow_import_orders" class="lgw-btn btn no-margin-top">
            <?php echo $locale->t('order.screen.button_update_orders') ?>
        </a>
    </div>
</div>
