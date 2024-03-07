<?php
/**
 * Infos view : WooCommerce order.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<style>
	<?php require WP_PLUGIN_DIR . '/lengow-woocommerce/assets/css/lengow-box-order.css'; ?>
</style>
<div id="lgw-box-order-info">
	<ul>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.marketplace_sku' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->marketplace_sku ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.marketplace' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->marketplace_name ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.currency' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->currency ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.total' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->total_paid ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.commission' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->commission ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.address_id' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->delivery_address_id ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.customer_name' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->customer_name ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.customer_email' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->customer_email ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.is_express' ) ); ?></span>
			<span class="lgw-order-label">
			<?php
			if ( $order_lengow->is_express() ) {
				echo esc_html( $locale->t( 'meta_box.order_info.boolean_yes' ) );
			} else {
				echo esc_html( $locale->t( 'meta_box.order_info.boolean_no' ) );
			}
			?>
			</span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title">
				<?php echo esc_html( $locale->t( 'meta_box.order_info.is_delivered_by_marketplace' ) ); ?>
			</span>
			<span class="lgw-order-label">
			<?php
			if ( $order_lengow->is_delivered_by_marketplace() ) {
				echo esc_html( $locale->t( 'meta_box.order_info.boolean_yes' ) );
			} else {
				echo esc_html( $locale->t( 'meta_box.order_info.boolean_no' ) );
			}
			?>
			</span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.is_business' ) ); ?></span>
			<span class="lgw-order-label">
			<?php
			if ( $order_lengow->is_business() ) {
				echo esc_html( $locale->t( 'meta_box.order_info.boolean_yes' ) );
			} else {
				echo esc_html( $locale->t( 'meta_box.order_info.boolean_no' ) );
			}
			?>
			</span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.vat_number' ) ); ?></span>
			<span class="lgw-order-label">
			<?php
			if ( $order_lengow->customer_vat_number ) {
				echo esc_html( $order_lengow->customer_vat_number );
			} else {
				echo esc_html( $locale->t( 'meta_box.order_info.no_vat_number' ) );
			}
			?>
			</span>
		</li>
	</ul>
	<ul>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.carrier' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->carrier ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.carrier_method' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->carrier_method ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.relay_id' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->carrier_id_relay ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.tracking_number' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->carrier_tracking ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.message' ) ); ?></span>
			<span class="lgw-order-label"><?php echo esc_html( $order_lengow->message ); ?></span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"><?php echo esc_html( $locale->t( 'meta_box.order_info.imported_date' ) ); ?></span>
			<span class="lgw-order-label">
				<?php echo esc_html( Lengow_Main::get_date_in_correct_format( strtotime( $imported_date ) ) ); ?>
			</span>
		</li>
		<hr>
		<li>
			<span class="lgw-order-title"
					id="lgw-order-title-json"><?php echo esc_html( $locale->t( 'meta_box.order_info.json_format' ) ); ?></span><br>
			<textarea readonly><?php echo esc_html( $order_lengow->extra ); ?></textarea>
		</li>
	</ul>
</div>

<!-- ACTION BUTTONS -->
<?php if ( ! $debug_mode ) : ?>
	<div id="lgw-box-order-buttons">
		<?php if ( $can_send_action ) : ?>
			<button id="lgw-order-resend"
					class="button-primary"
					data-message="
					<?php
					echo esc_attr(
						$locale->t(
							'order.screen.check_resend_action',
							array( 'action' => $action_type )
						)
					);
					?>
						"
					data-success="<?php echo esc_attr( $locale->t( 'order.screen.resend_action_success' ) ); ?>"
					data-error="<?php echo esc_attr( $locale->t( 'order.screen.resend_action_error' ) ); ?>"
					data-action="resend_<?php echo esc_attr( $action_type ); ?>"
					data-id="<?php echo esc_attr( $order_lengow->id ); ?>"
					type="button">
				<?php echo esc_html( $locale->t( 'order.screen.resend_action' ) ); ?>
			</button>

		<?php endif ?>
		<button id="lgw-order-synchronize"
				class="button-primary"
				data-message="<?php echo esc_attr( $locale->t( 'order.screen.check_synchronize' ) ); ?>"
				data-success="<?php echo esc_attr( $locale->t( 'order.screen.synchronize_action_success' ) ); ?>"
				data-error="<?php echo esc_attr( $locale->t( 'order.screen.synchronize_action_error' ) ); ?>"
				data-action="synchronize"
				data-id="<?php echo esc_attr( $order_lengow->id ); ?>"
				type="button">
			<?php echo esc_html( $locale->t( 'order.screen.synchronize_id' ) ); ?>
		</button>
		<button id="lgw-order-reimport"
				class="button-primary"
				data-message="<?php echo esc_attr( $locale->t( 'order.screen.check_cancel_and_reimport' ) ); ?>"
				data-error="<?php echo esc_attr( $locale->t( 'order.screen.cancel_and_reimport_action_error' ) ); ?>"
				data-action="reimport"
				data-id="<?php echo esc_attr( $order_lengow->id ); ?>"
				type="button">
			<?php echo esc_html( $locale->t( 'order.screen.cancel_and_reimport' ) ); ?>
		</button>
	</div>
<?php endif ?>

<script><?php require WP_PLUGIN_DIR . '/lengow-woocommerce/assets/js/lengow/order_box.js'; ?></script>
