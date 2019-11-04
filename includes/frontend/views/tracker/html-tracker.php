<?php
/**
 * Lengow tracker : WooCommerce confirmation order page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<!-- Tag_Lengow -->
<img src="https://trk.lgw.io/lead?account_id=><?php echo $tracker['account_id']; ?>&order_ref=<?php echo $tracker['order_ref']; ?>&amount=<?php echo $tracker['amount']; ?>&currency=<?php echo $tracker['currency']; ?>&payment_method=<?php echo $tracker['payment_method']; ?>&cart=<?php echo $tracker['cart']; ?>&cart_number=<?php echo $tracker['cart_number']; ?>&newbiz=<?php echo $tracker['newbiz']; ?>" alt="" style="width: 1px; height: 1px; border: none;" />
<img src="https://trk.lgw.io/validation?account_id=<?php echo $tracker['account_id']; ?>&order_ref=<?php echo $tracker['order_ref']; ?>&payment_method=<?php echo $tracker['payment_method']; ?>&valid=<?php echo $tracker['valid']; ?>" alt="" style="width: 1px; height: 1px; border: none;" />
<!-- /Tag_Lengow -->