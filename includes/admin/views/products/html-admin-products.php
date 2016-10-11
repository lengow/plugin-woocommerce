<?php
/**
 * Admin View: Products
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="lgw-container" id="lengow_feed_wrapper">
    <?php if ( $keys['lengow_preprod_enabled'] == 1 ) : ?>
        <div id="lgw-preprod" class="adminlengowlegals">
            <?php echo $locale->t('menu.preprod_active') ?>
        </div>
    <?php endif; ?>
    <div class="lgw-box no-margin" id="block">
        <div class="lengow_shop_status">
            <a href="#" class="lengow_check_shop lengow_check_shop_no_sync lengow_link_tooltip"
               data-original-title="">
            </a>
            <label class="lengow_shop_status_label">
            </label>
        </div>
        <a href="<?php echo $shop['link']?>?stream=1&update_export_date=0"
           class="lengow_export_feed lengow_link_tooltip"
           data-original-title="<?php echo $locale->t('product.screen.button_download')?>"
           target="_blank"><i class="fa fa-download"></i></a>
        <h2 class="text-center catalog-title lengow_link_tooltip"
            data-original-title="<?php echo $shop['shop'].' ('.$shop['domain'].')'; ?>">
            <?php echo $shop['shop']; ?>
        </h2>
        <div class="text-center">
            <div class="margin-standard text-center">
                <p class="products-exported">
                    <span class="js-lengow_exported stats-big-value" id="js-lengow_exported"><?php echo $shop['total_export_product'] ?></span>
                    <?php echo $locale->t('product.screen.nb_exported') ?>
                </p>
                <p class="products-available small light">
                    <span class="js-lengow_total stats-big-value"><?php echo $shop['total_product'] ?></span>
                    <?php echo $locale->t('product.screen.nb_available') ?>
                </p>
            </div>
            <hr>
            <div class="lgw-switch <?php echo $shop['option_variation'] == 1 ? 'checked' : '' ;?>">
                <label>
                    <div><span></span>
                        <input
                            type="checkbox"
                            data-size="mini"
                            data-on-text="<?php echo $locale->t('product.screen.button_yes')?>"
                            data-off-text="<?php echo $locale->t('product.screen.button_no')?>"
                            name="lengow_export_selection"
                            class="js-lengow_switch_option"
                            data-action="change_option_product_variation"
                            value="1" <?php if ($shop['option_variation'] == 1) : ?> checked="checked" <?php endif; ?>>
                    </div> <?php echo $locale->t('product.screen.include_variation')?>
                </label>
            </div>
            <i
                class="fa fa-info-circle lengow_link_tooltip"
                title="<?php echo $locale->t('product.screen.include_variation_support')?>"></i><br>
            <div class="lgw-switch <?php echo $shop['option_product_out_of_stock'] == 1 ? 'checked' : '' ;?>">
                <label>
                    <div><span></span>
                        <input
                            type="checkbox"
                            data-size="mini"
                            data-on-text="<?php echo $locale->t('product.screen.button_yes') ?>"
                            data-off-text="<?php echo $locale->t('product.screen.button_no')?>"
                            name="lengow_export_out_of_stock"
                            class="js-lengow_switch_option"
                            data-action="change_option_product_out_of_stock"
                            value="1" <?php if ($shop['option_product_out_of_stock'] == 1) : ?> checked="checked" <?php endif; ?>>
                    </div> <?php echo $locale->t('product.screen.include_out_of_stock') ?>
                </label>
            </div>
            <i class="fa fa-info-circle lengow_link_tooltip"
               title="<?php echo $locale->t('product.screen.include_out_of_stock_support')?>"></i><br>
            <div class="lgw-switch <?php echo $shop['option_selected'] == 1 ? 'checked' : '' ;?>">
                <label>
                    <div><span></span>
                        <input
                            type="checkbox"
                            data-size="mini"
                            data-on-text="<?php echo $locale->t('product.screen.button_yes') ?>"
                            data-off-text="<?php echo $locale->t('product.screen.button_no')?>"
                            name="lengow_export_selection"
                            class="js-lengow_switch_option"
                            data-action="change_option_selected"
                            value="1" <?php if ($shop['option_selected'] == 1) : ?> checked="checked" <?php endif; ?>>
                    </div> <?php echo $locale->t('product.screen.include_specific_product') ?>
                </label>
            </div>
            <i class="fa fa-info-circle lengow_link_tooltip"
               title="<?php echo $locale->t('product.screen.include_specific_product_support')?>"></i>
        </div>
    </div>
</div>
<form id="lengow-list-table-form" method="post">
<div class="lgw-table">
    <div class="lgw-box">
        <div class="lengow_feed_block_footer">
            <div class="js-lengow_feed_block_footer_content" style="<?php if (!$shop['option_selected']): ?>display:none;<?php endif;?>">
                <div class="lengow_table_top">
                    <div class="js-lengow_toolbar">
                        <a href="#" style="display:none;"
                           data-message="<?php echo $locale->t('product.screen.remove_confirmation', array(
                            'nb' => $shop['select_all']
                            )) ?>"
                           class="lgw-btn lgw-btn-red js-lengow_remove_from_export">
                            <i class="fa fa-minus"></i><?php echo $locale->t('product.screen.remove_from_export')?>
                        </a>
                        <a href="#" style="display:none;"
                           data-message="<?php echo $locale->t('product.screen.add_confirmation', array(
                            'nb' => $shop['select_all']
                            )) ?>"
                           class="lgw-btn js-lengow_add_to_export">
                            <i class="fa fa-plus"></i><?php echo $locale->t('product.screen.add_from_export')?>
                        </a>
                        <div class="js-lengow_select_all lgw-container" style="display:none;">
                            <input type="checkbox" id="js-select_all_shop">
                            <span><?php echo $locale->t('product.screen.select_all_products', array(
                                'nb' => $shop['select_all']
                                ));?></span>
                        </div>
                    </div>
                </div>
                <?php Lengow_Admin_Products::render_lengow_list();?>
            </div>
        </div>
    </div>
</div>
</form>
