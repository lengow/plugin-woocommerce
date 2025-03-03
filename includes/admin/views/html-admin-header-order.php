<?php
/**
 * Admin View: Header Order
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( isset( $_GET['tab'] ) ) {
	$current_page = sanitize_text_field( $_GET['tab'] );
} ?>

<ul class="nav nav-pills lengow-nav lengow-nav-bottom">
	<li role="presentation"
		class="<?php echo ( isset( $current_page ) && 'lengow_admin_orders' === $current_page ) ? 'active' : ''; ?>">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_orders' ) ); ?>">
			<?php echo esc_html( $locale->t( 'menu.order_overview' ) ); ?>
		</a>
	</li>
	<li role="presentation"
		class="<?php echo ( isset( $current_page ) && 'lengow_admin_order_settings' === $current_page ) ? 'active' : ''; ?>">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_order_settings' ) ); ?>">
			<?php echo esc_html( $locale->t( 'menu.order_parameter' ) ); ?>
		</a>
	</li>
    <li role="presentation"
        class="<?php echo ( isset( $current_page ) && 'lengow_admin_carrier_settings' === $current_page ) ? 'active' : ''; ?>">
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=lengow&tab=lengow_admin_carrier_settings' ) ); ?>">
			<?php echo esc_html( $locale->t( 'menu.carrier_parameter' ) ); ?>
        </a>
    </li>
</ul>
