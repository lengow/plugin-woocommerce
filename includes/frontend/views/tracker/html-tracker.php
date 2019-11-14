<?php
/**
 * Lengow tracker : WooCommerce confirmation order page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<!-- Tag_Lengow -->
<img src="https://trk.lgw.io/lead?account_id=<?php echo $account_id; ?>&order_ref=<?php echo $order_ref; ?>&amount=<?php echo $amount; ?>&currency=<?php echo $currency; ?>&payment_method=<?php echo $payment_method; ?>&cart=<?php echo $cart; ?>&cart_number=<?php echo $cart_number; ?>&newbiz=<?php echo $newbiz; ?>" alt="" style="width: 1px; height: 1px; border: none;" />
<img src="https://trk.lgw.io/validation?account_id=<?php echo $account_id; ?>&order_ref=<?php echo $order_ref; ?>&payment_method=<?php echo $payment_method; ?>&valid=<?php echo $valid; ?>" alt="" style="width: 1px; height: 1px; border: none;" />
<!-- /Tag_Lengow -->