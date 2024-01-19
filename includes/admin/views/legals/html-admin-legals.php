<?php
/**
 * Admin View: Legals
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-container">
	<?php if ( Lengow_Configuration::debug_mode_is_active() ) : ?>
        <div id="lgw-debug">
			<?php echo esc_html( $locale->t( 'menu.debug_active' ) ); ?>
        </div>
	<?php endif; ?>
    <div class="lgw-box lengow_legals_wrapper">
        <h3>SAS Lengow</h3>
		<?php echo esc_html( $locale->t( 'legals.screen.simplified_company' ) ); ?>
        <br/>
		<?php echo esc_html( $locale->t( 'legals.screen.social_capital' ) ); ?>
        368 778 €
        <br/>
		<?php echo esc_html( $locale->t( 'legals.screen.cnil_declaration' ) ); ?>
        1748784 v 0
        <br/>
		<?php echo esc_html( $locale->t( 'legals.screen.company_registration_number' ) ); ?>
        513 381 434
        <br/>
		<?php echo esc_html( $locale->t( 'legals.screen.vat_identification_number' ) ); ?>
        FR42513381434
        <h3><?php echo esc_html( $locale->t( 'legals.screen.address' ) ); ?></h3>
        6 rue René Viviani<br/>
        44200 Nantes
        <h3><?php echo esc_html( $locale->t( 'legals.screen.contact' ) ); ?></h3>
        contact@lengow.com<br/>
        +33 (0)2 85 52 64 14
        <h3><?php echo esc_html( $locale->t( 'legals.screen.hosting' ) ); ?></h3>
        OXALIDE<br/>
        RCS Paris : 803 816 529<br/>
        25 Boulevard de Strasbourg – 75010 Paris<br/>
        +33 (0)1 75 77 16 66
    </div>
</div>

