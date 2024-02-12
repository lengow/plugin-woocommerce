<?php
/**
 * Admin View: Connection Catalog
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="lgw-content-section">
    <h2><?php echo esc_html( $locale->t( 'connection.catalog.link_title' ) ); ?></h2>
    <p><?php echo esc_html( $locale->t( 'connection.catalog.link_description' ) ); ?></p>
    <p>
        <span><?php echo esc_html( count( $catalog_list ) ); ?></span>
        <?php echo esc_html( $locale->t( 'connection.catalog.link_catalog_available' ) ); ?>
    </p>
</div>
<div>
	<div class="lgw-catalog-select">
        <label class="control-label" for="select_catalog">
            <?php echo esc_html( Lengow_Configuration::get( 'blogname' ) ); ?>
        </label>
        <select class="form-control lengow_select js-catalog-linked"
                id="select_catalog"
                name="catalog_ids"
                multiple="multiple"
                data-placeholder="<?php echo esc_html( $locale->t( 'connection.catalog.link_placeholder_catalog' ) ); ?>"
                data-allow-clear="true">
            <?php foreach ( $catalog_list as $catalog ) : ?>
                <option value="<?php echo esc_attr( $catalog['value'] ); ?>">
                    <?php echo esc_html( $catalog['label'] ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div>
	<button class="lgw-btn lgw-btn-green lgw-btn-progression js-link-catalog">
		<div class="btn-inner">
            <div class="btn-step default">
                <?php echo esc_html( $locale->t( 'connection.catalog.link_button' ) ); ?>
            </div>
            <div class="btn-step loading">
                <?php echo esc_html( $locale->t( 'global_setting.screen.setting_saving' ) ); ?>
            </div>
        </div>
	</button>
</div>
