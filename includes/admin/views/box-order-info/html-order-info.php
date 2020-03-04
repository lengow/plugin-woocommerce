<?php
/**
 * Infos view : WooCommerce order.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style>
    <?php include WP_PLUGIN_DIR . '/lengow-woocommerce/assets/css/lengow-box-order.css'  ?>
</style>
<div id="lgw-box-order-info">
    <ul>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.marketplace_sku' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->marketplace_sku; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.marketplace' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->marketplace_name; ?><span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.currency' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->currency; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.total' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->total_paid; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.commission' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->commission; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.address_id' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->delivery_address_id; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.customer_name' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->customer_name; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.customer_email' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->customer_email; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.message' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->message; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.imported_date' ); ?></span>
            <span class="lgw-order-label">
                <?php echo Lengow_Main::get_date_in_correct_format( strtotime( $order_lengow->created_at ) ); ?>
            </span>
        </li>
    </ul>
    <ul>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.carrier' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->carrier; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.carrier_method' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->carrier_method; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.relay_id' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->carrier_id_relay; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.tracking_number' ); ?></span>
            <span class="lgw-order-label"><?php echo $order_lengow->carrier_tracking; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'meta_box.order_info.shipped_by_marketplace' ); ?></span>
            <span class="lgw-order-label"><?php
				if ( $order_lengow->sent_marketplace ) {
					echo $locale->t( 'meta_box.order_info.boolean_yes' );
				} else {
					echo $locale->t( 'meta_box.order_info.boolean_no' );
				}
				?>
            </span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"
                  id="lgw-order-title-json"><?php echo $locale->t( 'meta_box.order_info.json_format' ); ?></span><br>
            <textarea readonly><?php echo $order_lengow->extra; ?></textarea>
        </li>
    </ul>
</div>

<!-- ACTION BUTTONS -->
<?php if ( ! $debug_mode ) : ?>
    <div id="lgw-box-order-buttons">
		<?php if ( $can_send_action ) : ?>
            <button id="lgw-order-resend"
                    class="button-primary"
                    data-message="<?php echo $locale->t( 'order.screen.check_resend_action', array(
				        'action' => $action_type
			        ) ); ?>"
                    data-success="<?php echo $locale->t( 'order.screen.resend_action_success' ); ?>"
                    data-error="<?php echo $locale->t( 'order.screen.resend_action_error' ); ?>"
                    data-action="resend_<?php echo $action_type ?>"
                    data-id="<?php echo $order_lengow->id ?>"
                    type="button">
				<?php echo $locale->t( 'order.screen.resend_action' ); ?>
            </button>
		<?php endif ?>
        <button id="lgw-order-synchronize"
                class="button-primary"
                data-message="<?php echo $locale->t( 'order.screen.check_synchronize' ); ?>"
                data-success="<?php echo $locale->t( 'order.screen.synchronize_action_success' ); ?>"
                data-error="<?php echo $locale->t( 'order.screen.synchronize_action_error' ); ?>"
                data-action="synchronize"
                data-id="<?php echo $order_lengow->id ?>"
                type="button">
			<?php echo $locale->t( 'order.screen.synchronize_id' ); ?>
        </button>
        <button id="lgw-order-reimport"
                class="button-primary"
                data-message="<?php echo $locale->t( 'order.screen.check_cancel_and_reimport' ); ?>"
                data-error="<?php echo $locale->t( 'order.screen.cancel_and_reimport_action_error' ); ?>"
                data-action="reimport"
                data-id="<?php echo $order_lengow->id ?>"
                type="button">
			<?php echo $locale->t( 'order.screen.cancel_and_reimport' ); ?>
        </button>
    </div>
<?php endif ?>

<script><?php include WP_PLUGIN_DIR . '/lengow-woocommerce/assets/js/lengow/order_box.js' ?></script>