<?php
/**
 * Admin View: Orders
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-container">
	<?php if ( Lengow_Configuration::get( 'lengow_import_enabled' ) == 1 ) : ?>
		<?php if ( Lengow_Configuration::get( 'lengow_preprod_enabled' ) == 1 ) : ?>
			<div id="lgw-preprod" class="adminlengowlegals">
				<?php echo $locale->t( 'menu.preprod_active' ) ?>
			</div>
		<?php endif; ?>
		<div class="lgw-box row" id="lengow_order_wrapper">
			<?php if ( $warning_message ) : ?>
				<p class="blue-frame" style="line-height: 20px;">
					<?php echo $warning_message ?>
				</p>
			<?php endif; ?>
			<div class="lgw-col-8" style="padding:0;">
				<div id="lengow_last_importation">
					<p>
						<?php if ( $order_collection['last_import_type'] != 'none' ) : ?>
							<?php echo $locale->t( 'order.screen.last_order_importation' ); ?>
							: <b><span
									id="lengow_last_import_date"><?php echo $order_collection['last_import_date'] ?></span></b>
						<?php else: ?>
							<?php echo $locale->t( 'order.screen.no_order_importation' ); ?>
						<?php endif; ?>
					</p>
				</div>
				<div id="lengow_wrapper_messages" class="blue-frame" style="display:none;"></div>
			</div>
			<div class="pull-right text-right lgw-col-3">
				<a id="lengow_import_orders" class="lgw-btn btn no-margin-top">
					<?php echo $locale->t( 'order.screen.button_update_orders' ) ?>
				</a>
			</div>
		</div>
		<!-- UPDATE ORDERS -->
		<div id="lengow_charge_import_order" style="display:none;">
			<p id="lengow_charge_lign1"><?php echo $locale->t( 'order.screen.import_charge_first' ) ?></p>
			<p id="lengow_charge_lign2"><?php echo $locale->t( 'order.screen.import_charge_second' ) ?></p>
		</div>
		<!-- /UPDATE ORDERS -->
	<?php else: ?>
		<div class="lgw-box">
			<?php echo $locale->t( 'order.screen.order_synchronize_disabled', array(
				'url' => admin_url( 'admin.php?page=lengow&tab=lengow_admin_settings' )
			) ); ?>
		</div>
	<?php endif; ?>
</div>
