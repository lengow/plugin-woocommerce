<?php
/**
 * Shipping view : WooCommerce order.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/** @var Lengow_Marketplace $marketplace */
$locale                = new Lengow_Translation();
$carriers              = $marketplace->carriers;
$marketplace_arguments = $marketplace->get_marketplace_arguments( Lengow_Action::TYPE_SHIP );
$accept_custom_carrier = $marketplace->accept_custom_carrier();
$order_lengow_carrier  = false;
$wc_order              = new WC_Order( $order_lengow->order_id );
// recovery of the carrier code returned by the marketplace.
// this argument has priority over all others.
if ( null !== $order_lengow->carrier && strlen( $order_lengow->carrier ) > 0 ) {
	$order_lengow_carrier = (string) $order_lengow->carrier;
}
$carrier                = (string) $wc_order->get_meta( '_lengow_carrier', true );
$carrier                = $order_lengow_carrier ?: $carrier;
$custom_carrier         = (string) $wc_order->get_meta( '_lengow_custom_carrier', true );
$custom_carrier         = $order_lengow_carrier ?: $custom_carrier;
$tracking_number        = (string) $wc_order->get_meta( '_lengow_tracking_number', true );
$tracking_number        = strlen( $tracking_number ) > 0 ? (string) $tracking_number : (string) $order_lengow->carrier_tracking;
$tracking_url           = (string) $wc_order->get_meta( '_lengow_tracking_url', true );
$return_carrier         = (string) $wc_order->get_meta( '_lengow_return_carrier', true );
$return_tracking_number = (string) $wc_order->get_meta( '_lengow_return_tracking_number', true );
$order_shipping_methods = $wc_order->get_shipping_methods();
$order_shipping_method  = current( $order_shipping_methods );

if ( empty( $carrier ) && $order_shipping_method ) {
	$marketplace_carriers = Lengow_Configuration::get( Lengow_Configuration::SHIPPING_METHOD_CARRIERS );
	if ( !empty( $marketplace_carriers[ $marketplace->name ][ $order_shipping_method->get_method_id() ] ) ) {
		$carrier = $marketplace_carriers[ $marketplace->name ][ $order_shipping_method->get_method_id() ];
	} elseif ( !empty( $marketplace_carriers[ $marketplace->name ]['__default'] ) ) {
		$carrier = $marketplace_carriers[ $marketplace->name ]['__default'];
	}
}
?>

<style>
	<?php require WP_PLUGIN_DIR . '/lengow-woocommerce/assets/css/lengow-box-order.css'; ?>
</style>
<div id="lgw-box-order-shipping">
	<ul class="order_shipping submitbox">
		<?php if ( array_key_exists( Lengow_Action::ARG_TRACKING_NUMBER, $marketplace_arguments ) ) : ?>
			<li>
				<label for="lengow_tracking_number">
					<?php echo esc_html( $locale->t( 'meta_box.order_shipping.tracking_number' ) ); ?>
					<?php if ( $marketplace->argument_is_required( 'tracking_number' ) ) : ?>
						<span class="required">(<?php echo esc_html( $locale->t( 'meta_box.order_shipping.required' ) ); ?>)</span>
					<?php endif; ?>
					:
				</label>
				<input type="text" name="lengow_tracking_number" id="lengow_tracking_number"
						value="<?php echo esc_attr( $tracking_number ); ?>"/>
			</li>
		<?php endif; ?>
		<?php if ( ! empty( $carriers ) ) : ?>
			<li>
				<label for="lengow_carrier">
					<?php echo esc_html( $locale->t( 'meta_box.order_shipping.carrier' ) ); ?>
					<?php if ( $marketplace->argument_is_required( Lengow_Action::ARG_CARRIER ) ) : ?>
						<span class="required">(<?php echo esc_html( $locale->t( 'meta_box.order_shipping.required' ) ); ?>)</span>
					<?php endif; ?>
					:
				</label>
				<select name="lengow_carrier" id="lengow_carrier">
					<option value="">
						<?php echo esc_html( $locale->t( 'meta_box.order_shipping.choose_a_carrier' ) ); ?>
					</option>
					<?php foreach ( $carriers as $code => $label ) : ?>
						<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $carrier, $code ); ?>>
							<?php echo esc_html( $label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</li>
		<?php endif; ?>
		<?php if ( ! empty( $carriers ) && $accept_custom_carrier ) : ?>
			<span class="or-use"><?php echo esc_html( $locale->t( 'meta_box.order_shipping.or_use' ) ); ?></span>
		<?php endif; ?>
		<?php if ( $accept_custom_carrier ) : ?>
			<li>
				<label for="lengow_custom_carrier">
					<?php echo esc_html( $locale->t( 'meta_box.order_shipping.custom_carrier' ) ); ?>
					<?php if ( $marketplace->custom_carrier_is_required() ) : ?>
						<span class="required">(<?php echo esc_html( $locale->t( 'meta_box.order_shipping.required' ) ); ?>)</span>
					<?php endif; ?>:
				</label>
				<input type="text" name="lengow_custom_carrier" id="lengow_custom_carrier"
						value="<?php echo esc_attr( $custom_carrier ); ?>"/>
			</li>
		<?php endif; ?>
		<?php if ( array_key_exists( Lengow_Action::ARG_TRACKING_URL, $marketplace_arguments ) ) : ?>
			<li>
				<label for="lengow_tracking_url">
					<?php echo esc_html( $locale->t( 'meta_box.order_shipping.tracking_url' ) ); ?>
					<?php if ( $marketplace->argument_is_required( 'tracking_url' ) ) : ?>
						<span class="required">(<?php echo esc_html( $locale->t( 'meta_box.order_shipping.required' ) ); ?>)</span>
					<?php endif; ?>:
				</label>
				<input type="text" name="lengow_tracking_url" id="lengow_tracking_url"
						value="<?php echo esc_attr( $tracking_url ); ?>"/>
			</li>
		<?php endif; ?>

		<?php if ( array_key_exists( Lengow_Action::ARG_RETURN_TRACKING_NUMBER, $marketplace_arguments ) ) : ?>
			<li>
				<label for="lengow_return_tracking_number">
					<?php echo esc_html( $locale->t( 'meta_box.order_shipping.return_tracking_number' ) ); ?>
					<?php if ( $marketplace->argument_is_required( Lengow_Action::ARG_RETURN_TRACKING_NUMBER ) ) : ?>
						<span class="required">(<?php echo esc_html( $locale->t( 'meta_box.order_shipping.required' ) ); ?>)</span>
					<?php endif; ?>
					:
				</label>
				<input type="text" name="lengow_return_tracking_number" id="lengow_return_tracking_number"
						value="<?php echo esc_attr( $return_tracking_number ); ?>"/>
			</li>
		<?php endif; ?>
		<?php if ( ! empty( $carriers ) && array_key_exists( Lengow_Action::ARG_RETURN_CARRIER, $marketplace_arguments ) ) : ?>
			<li>
				<label for="lengow_return_carrier">
					<?php echo esc_html( $locale->t( 'meta_box.order_shipping.return_carrier' ) ); ?>
					<?php if ( $marketplace->argument_is_required( Lengow_Action::ARG_RETURN_CARRIER ) ) : ?>
						<span class="required">(<?php echo esc_html( $locale->t( 'meta_box.order_shipping.required' ) ); ?>)</span>
					<?php endif; ?>
					:
				</label>
                <input type="text" name="lengow_return_carrier" id="lengow_return_carrier"
                       value="<?php echo esc_attr( $return_carrier ); ?>"/>
			</li>
            <?php if ( empty( $return_carrier ) ) : ?>
            <script type="text/javascript">
                (function ($) {
                    $(document).ready(function () {
                        $('#lengow_carrier, #lengow_custom_carrier').on('change, input', function () {
                            let el = $('#lengow_return_carrier');
                            el.val($(this).val());
                        });
                    });
                })(jQuery);
            </script>
            <?php endif; ?>
		<?php endif; ?>
	</ul>
</div>
