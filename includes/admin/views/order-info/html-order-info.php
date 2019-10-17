<?php
/**
 * Infos view : woocommerce order.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$locale = new Lengow_Translation();
?>

<style>
    <?php include WP_PLUGIN_DIR . '/lengow-woocommerce/assets/css/lengow-infos-order.css'  ?>
</style>
<div id="lgw-box-order-infos">
    <ul>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.marketplace_sku' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->marketplace_sku; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.marketplace' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->marketplace_name; ?><span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.currency' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->currency; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.total' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->total_paid; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.commission' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->commission; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.address_id' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->delivery_address_id; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.customer_name' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->customer_name; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.customer_email' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->customer_email; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.message' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->message; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.imported_date' ); ?></span>
            <span class="lgw-order-label"><?php echo get_date_from_gmt($lengow_order->created_at); ?></span>
        </li>
    </ul>
    <ul>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.shipped_by_marketplace' ); ?></span>
            <span class="lgw-order-label"><?php
				if ( $lengow_order->sent_marketplace ) {
					echo $locale->t( 'order_infos.boolean_yes' );
				} else {
					echo $locale->t( 'order_infos.boolean_no' );
				}
				?>
            </span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.relay_id' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->carrier_id_relay; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.carrier_method' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->carrier_method; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"><?php echo $locale->t( 'order_infos.tracking_number' ); ?></span>
            <span class="lgw-order-label"><?php echo $lengow_order->carrier_tracking; ?></span>
        </li>
        <hr>
        <li>
            <span class="lgw-order-title"
                  id="lgw-order-title-json"><?php echo $locale->t( 'order_infos.json_format' ); ?></span><br>
            <textarea readonly><?php echo $lengow_order->extra; ?></textarea>
        </li>
    </ul>
</div>