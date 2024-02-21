<?php
/**
 * Admin View: Orders
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="lengow_order_wrapper" class="lgw-container">
	<?php if ( Lengow_Configuration::debug_mode_is_active() ) : ?>
		<div id="lgw-debug">
			<?php echo esc_html( $locale->t( 'menu.debug_active' ) ); ?>
		</div>
	<?php endif; ?>
	<div class="lgw-box row">
		<?php if ( $warning_message ) : ?>
			<p class="blue-frame" style="line-height: 20px;">
				<?php echo wp_kses_post( $warning_message ); ?>
			</p>
		<?php endif; ?>
		<div id="lengow_order_header">
			<div class="lgw-col-8" style="padding:0;">
				<div id="lengow_order_with_error">
					<p>
						<?php
						echo esc_html(
							$locale->t(
								'order.screen.order_with_error',
								array( 'nb_order' => Lengow_Order::count_order_with_error() )
							)
						)
						?>
					</p>
				</div>
				<div id="lengow_order_to_be_sent">
					<p>
						<?php
						echo esc_html(
							$locale->t(
								'order.screen.order_to_be_sent',
								array( 'nb_order' => Lengow_Order::count_order_to_be_sent() )
							)
						)
						?>
					</p>
				</div>
				<div id="lengow_last_importation">
					<p>
						<?php if ( 'none' !== $order_collection['last_import_type'] ) : ?>
							<?php echo esc_html( $locale->t( 'order.screen.last_order_importation' ) ); ?>
							:
							<b>
								<span id="lengow_last_import_date">
									<?php echo esc_html( $order_collection['last_import_date'] ); ?>
								</span>
							</b>
						<?php else : ?>
							<?php echo esc_html( $locale->t( 'order.screen.no_order_importation' ) ); ?>
						<?php endif; ?>
					</p>
				</div>
				<p>
					<?php
					if ( Lengow_Configuration::get( Lengow_Configuration::REPORT_MAIL_ENABLED ) ) {
						echo esc_html( $locale->t( 'order.screen.all_order_will_be_sent_to' ) . ' ' . $report_emails );
					} else {
						echo wp_kses_post(
							$locale->t( 'order.screen.no_order_will_be_sent' ) .
							' (<a href="' . admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' ) .
							'">' . $locale->t( 'order.screen.change_this' ) . '</a>)'
						);
					}
					?>
				<p>
			</div>
			<div class="pull-right text-right lgw-col-3">
				<a id="lengow_import_orders" class="lgw-btn btn no-margin-top">
					<?php echo esc_html( $locale->t( 'order.screen.button_update_orders' ) ); ?>
				</a>
			</div>
		</div>
		<!-- /UPDATE ORDERS -->
		<div id="lengow_wrapper_messages" class="blue-frame" style="display:none;"></div>
		<div id="lgw-order-toolbar" class="js-lengow_toolbar" style="display: none;">
			<a href="#"
				data-action="reimport_mass_action"
				class="lgw-btn js-lengow_reimport_mass_action">
				<i class="fa fa-download"></i> <?php echo esc_html( $locale->t( 'order.screen.button_reimport_order' ) ); ?>
			</a>
			<a href="#"
				data-action="resend_mass_action"
				class="lgw-btn js-lengow_resend_mass_action">
				<i class="fa fa-arrow-right"></i> <?php echo esc_html( $locale->t( 'order.screen.button_resend_order' ) ); ?>
			</a>
		</div>
		<!-- ORDERS GRID -->
		<div id="container_lengow_grid">
			<div id="lengow_order_grid">
				<?php
				if ( Lengow_Admin_Orders::count_orders() > 0 ) {
					Lengow_Admin_Orders::render_lengow_list();
				} else {
					?>
					<div id="lengow_no_order_block">
						<div id="lengow_no_order_message" class="text-center">
							<h2 class="no-margin"><?php echo esc_html( $locale->t( 'order.screen.no_order_title' ) ); ?></h2>
							<p><?php echo esc_html( $locale->t( 'order.screen.no_order_description' ) ); ?></p>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<!-- /ORDERS GRID -->
		<!-- UPDATE ORDERS -->
		<div id="lengow_charge_import_order" style="display:none;">
			<div class="lgw-ajax-loading mod-synchronise-order">
				<div class="lgw-ajax-loading-ball1"></div>
				<div class="lgw-ajax-loading-ball2"></div>
			</div>
			<p id="lengow_charge_lign1"><?php echo esc_html( $locale->t( 'order.screen.import_charge_first' ) ); ?></p>
			<p id="lengow_charge_lign2"><?php echo esc_html( $locale->t( 'order.screen.import_charge_second' ) ); ?></p>
		</div>
		<!-- /UPDATE ORDERS -->
	</div>
</div>
