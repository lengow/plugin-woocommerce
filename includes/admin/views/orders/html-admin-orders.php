<?php
/**
 * Admin View: Orders
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="lengow_order_wrapper" class="lgw-container">
	<?php if ( (bool) Lengow_Configuration::get( 'lengow_preprod_enabled' ) ) : ?>
        <div id="lgw-preprod">
			<?php echo $locale->t( 'menu.preprod_active' ); ?>
        </div>
	<?php endif; ?>
    <div class="lgw-box row">
		<?php if ( $warning_message ) : ?>
            <p class="blue-frame" style="line-height: 20px;">
				<?php echo $warning_message; ?>
            </p>
		<?php endif; ?>
        <div id="lengow_order_header">
            <div class="lgw-col-8" style="padding:0;">
                <div id="lengow_last_importation">
                    <p>
						<?php if ( 'none' !== $order_collection['last_import_type'] ) : ?>
							<?php echo $locale->t( 'order.screen.last_order_importation' ); ?>
                            :
                            <b><span id="lengow_last_import_date"><?php echo $order_collection['last_import_date']; ?></span></b>
						<?php else: ?>
							<?php echo $locale->t( 'order.screen.no_order_importation' ); ?>
						<?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="pull-right text-right lgw-col-3">
                <a id="lengow_import_orders" class="lgw-btn btn no-margin-top">
					<?php echo $locale->t( 'order.screen.button_update_orders' ); ?>
                </a>
            </div>
        </div>
        <!-- UPDATE ORDERS -->
        <div id="lengow_charge_import_order" style="display:none;">
            <div class="lgw-ajax-loading mod-synchronise-order">
                <div class="lgw-ajax-loading-ball1"></div>
                <div class="lgw-ajax-loading-ball2"></div>
            </div>
            <p id="lengow_charge_lign1"><?php echo $locale->t( 'order.screen.import_charge_first' ); ?></p>
            <p id="lengow_charge_lign2"><?php echo $locale->t( 'order.screen.import_charge_second' ); ?></p>
        </div>
        <!-- /UPDATE ORDERS -->
        <div id="lengow_wrapper_messages" class="blue-frame" style="display:none;"></div>
        <!-- ORDERS GRID -->
        <div id="container_lengow_grid">
            <div id="lengow_order_grid">
				<?php
				if ( Lengow_Admin_Orders::count_orders() > 0 ) {
					Lengow_Admin_Orders::render_lengow_list();
				} else { ?>
                    <div id="lengow_no_order_block">
                        <div id="lengow_no_order_message" class="text-center">
                            <h2 class="no-margin"><?php echo $locale->t( 'order.screen.no_order_title' ); ?></h2>
                            <p><?php echo $locale->t( 'order.screen.no_order_description' ); ?></p>
                        </div>
                    </div>
				<?php }
				?>
            </div>
        </div>
        <!-- /ORDERS GRID -->
    </div>
</div>
