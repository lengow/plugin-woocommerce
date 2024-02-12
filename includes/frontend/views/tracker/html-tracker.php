<?php
/**
 * Lengow tracker : WooCommerce confirmation order page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<!-- Tag_Lengow -->
<img src="<?php echo esc_url("https://trk.lgw.io/lead?account_id={$account_id}&order_ref={$order_ref}&amount={$amount}&currency={$currency}&payment_method={$payment_method}&cart={$cart}&cart_number={$cart_number}&newbiz={$newbiz}"); ?>"
     alt="" style="width: 1px; height: 1px; border: none;"/>
<img src="<?php echo esc_url("https://trk.lgw.io/validation?account_id={$account_id}&order_ref={$order_ref}&payment_method={$payment_method}&valid={$valid}"); ?>"
     alt="" style="width: 1px; height: 1px; border: none;"/>
<!-- /Tag_Lengow -->
