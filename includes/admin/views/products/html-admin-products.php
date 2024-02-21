<?php
/**
 * Admin View: Products
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="lengow_feed_wrapper" class="lgw-container">
	<?php if ( Lengow_Configuration::debug_mode_is_active() ) : ?>
		<div id="lgw-debug">
			<?php echo esc_html( $locale->t( 'menu.debug_active' ) ); ?>
		</div>
	<?php endif; ?>
	<div class="lgw-box no-margin" id="block">
		<a href="<?php echo esc_url( $shop['link'] ); ?>&stream=1&update_export_date=0"
			class="lengow_export_feed lengow_link_tooltip"
			data-original-title="<?php echo esc_attr( $locale->t( 'product.screen.button_download' ) ); ?>"
			target="_blank"><i class="fa fa-download"></i></a>
		<h2 class="text-center catalog-title">
			<span class="lengow_link_tooltip"
					data-original-title="<?php echo esc_attr( $shop['shop'] . ' (' . $shop['domain'] . ')' ); ?>">
				<?php echo esc_html( $shop['shop'] ); ?>
			</span>
		</h2>
		<div class="text-center">
			<div class="margin-standard text-center">
				<p class="products-exported">
					<span class="js-lengow_exported stats-big-value"
							id="js-lengow_exported"><?php echo esc_html( $shop['total_export_product'] ); ?></span>
					<?php echo esc_html( $locale->t( 'product.screen.nb_exported' ) ); ?>
				</p>
				<p class="products-available small light">
					<span class="js-lengow_total stats-big-value"><?php echo esc_html( $shop['total_product'] ); ?></span>
					<?php echo esc_html( $locale->t( 'product.screen.nb_available' ) ); ?>
				</p>
			</div>
			<hr>
			<div class="lgw-switch <?php echo esc_attr( $shop['option_selected'] ? 'checked' : '' ); ?>">
				<label>
					<div><span></span>
						<input
								type="checkbox"
								data-size="mini"
								data-on-text="<?php echo esc_attr( $locale->t( 'product.screen.button_yes' ) ); ?>"
								data-off-text="<?php echo esc_attr( $locale->t( 'product.screen.button_no' ) ); ?>"
								name="lengow_export_selection"
								class="js-lengow_switch_option"
								data-action="change_option_selected"
								value="1" 
								<?php
								if ( $shop['option_selected'] ) :
									?>
									checked="checked" <?php endif; ?>>
					</div> <?php echo esc_html( $locale->t( 'product.screen.include_specific_product' ) ); ?>
				</label>
			</div>
			<i class="fa fa-info-circle lengow_link_tooltip"
				title="<?php echo esc_attr( $locale->t( 'product.screen.include_specific_product_support' ) ); ?>"></i>
		</div>
	</div>

	<form id="lengow-list-table-form" method="post">
		<div id="lengow_feed_table" class="lgw-table">
			<div class="lgw-box">
				<div class="lengow_feed_block_footer">
					<div class="js-lengow_feed_block_footer_content"
						style="
						<?php
						if ( ! $shop['option_selected'] ) :
							?>
							display:none;<?php endif; ?>">
						<div class="lengow_table_top">
							<div id="lgw-product-toolbar" class="js-lengow_toolbar" style="display:none;">
								<a href="#"
									data-export-action="remove_to_export"
									data-action="export_mass_action"
									data-message="
									<?php
									echo esc_attr(
										$locale->t(
											'product.screen.remove_confirmation',
											array( 'nb' => $shop['select_all'] )
										)
									);
									?>
										"
									class="lgw-btn lgw-btn-red js-lengow_remove_from_export">
									<i class="fa fa-minus"></i>
									<?php echo esc_html( $locale->t( 'product.screen.remove_from_export' ) ); ?>
								</a>
								<a href="#"
									data-export-action="add_to_export"
									data-action="export_mass_action"
									data-message="
									<?php
									echo esc_attr(
										$locale->t(
											'product.screen.add_confirmation',
											array( 'nb' => $shop['select_all'] )
										)
									);
									?>
										"
									class="lgw-btn js-lengow_add_to_export">
									<i class="fa fa-plus"></i>
									<?php echo esc_html( $locale->t( 'product.screen.add_from_export' ) ); ?>
								</a>
								<div class="js-lengow_select_all">
									<input type="checkbox" id="js-select_all_shop">
									<span>
									<?php
									echo esc_html(
										$locale->t(
											'product.screen.select_all_products',
											array( 'nb' => $shop['select_all'] )
										)
									);
									?>
											</span>
								</div>
							</div>
						</div>
						<div id="lengow_product_grid">
							<?php Lengow_Admin_Products::render_lengow_list(); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
