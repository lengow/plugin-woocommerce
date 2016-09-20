<?php
/**
 * Admin View: Legals
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-container">
    <?php if ( $keys['lengow_preprod_enabled'] == 1 ) : ?>
        <div id="lgw-preprod" class="adminlengowlegals">
            <?php echo $locale->t('menu.preprod_active') ?>
        </div>
    <?php endif; ?>
    <div class="lgw-box lengow_legals_wrapper">
        <h3>SAS Lengow</h3>
        <?php echo $locale->t('legals.screen.simplified_company') ?>
        <br />
        <?php echo $locale->t('legals.screen.social_capital')?>
        368 778 €
        <br />
        <?php echo $locale->t('legals.screen.cnil_declaration')?>
        1748784 v 0
        <br />
        <?php echo $locale->t('legals.screen.company_registration_number')?>
        513 381 434
        <br />
        <?php echo $locale->t('legals.screen.vat_identification_number')?>
        FR42513381434
        <h3><?php echo $locale->t('legals.screen.address')?></h3>
        6 rue René Viviani<br />
        44200 Nantes
        <h3><?php echo $locale->t('legals.screen.contact')?></h3>
        contact@lengow.com<br />
        +33 (0)2 85 52 64 14
        <h3><?php echo $locale->t('legals.screen.hosting')?></h3>
        Linkbynet<br />
        RCS Bobigny : 430 359 927<br />
        5-9 Rue, de l’Industrie – 93200 Saint-Denis<br />
        +33 (0)1 48 13 00 00
    </div>
</div>

