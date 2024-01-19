<?php
/**
 * Admin View: Toolbox
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-container lgw-toolbox-wrapper">
	<?php if ( Lengow_Configuration::debug_mode_is_active() ) : ?>
        <div id="lgw-debug">
			<?php echo esc_html( $locale->t( 'menu.debug_active' ) ); ?>
        </div>
	<?php endif; ?>
    <h2><i class="fa fa-rocket"></i> <?php echo esc_html( $locale->t( 'toolbox.screen.title' ) ); ?></h2>
    <div class="lgw-box">
        <div class="lgw-switch checked js-lgw-global">
            <label>
                <div>
                    <span></span>
                    <input type="checkbox" name="see_global_content" checked>
                </div>
	            <?php echo esc_html( $locale->t( 'toolbox.screen.global_information' ) ); ?>
            </label>
        </div>
        <div class="js-lgw-global-content">
            <div class="lgw-box-content">
                <h3>
                    <i class="fa fa-check"></i>
	                <?php echo esc_html( $locale->t( 'toolbox.screen.checklist_information' ) ); ?>
                </h3>
	            <?php echo wp_kses_post( $toolbox_element->get_check_list() ); ?>
            </div>
            <div class="lgw-box-content">
                <h3>
                    <i class="fa fa-cog"></i>
	                <?php echo esc_html( $locale->t( 'toolbox.screen.plugin_information' ) ); ?>
                </h3>
	            <?php echo wp_kses_post( $toolbox_element->get_global_information() ); ?>
            </div>
            <div class="lgw-box-content">
                <h3>
                    <i class="fa fa-download"></i>
	                <?php echo esc_html( $locale->t( 'toolbox.screen.synchronization_information' ) ); ?>
                </h3>
	            <?php echo wp_kses_post( $toolbox_element->get_import_information() ); ?>
            </div>
        </div>
    </div>
    <div class="lgw-box">
        <div class="lgw-switch js-lgw-export">
            <label>
                <div>
                    <span></span>
                    <input type="checkbox" name="see_export_content">
                </div>
	            <?php echo esc_html( $locale->t( 'toolbox.screen.shop_information' ) ); ?>
            </label>
        </div>
        <div class="js-lgw-export-content">
            <div class="lgw-box-content">
                <h3>
                    <i class="fa fa-upload"></i>
	                <?php echo esc_html( $locale->t( 'toolbox.screen.export_information' ) ); ?>
                </h3>
	            <?php echo wp_kses_post( $toolbox_element->get_export_information() ); ?>
            </div>
            <div class="lgw-box-content">
                <h3>
                    <i class="fa fa-list"></i>
	                <?php echo esc_html( $locale->t( 'toolbox.screen.content_folder_media' ) ); ?>
                </h3>
	            <?php echo wp_kses_post( $toolbox_element->get_file_information() ); ?>
            </div>
        </div>
    </div>
    <div class="lgw-box">
        <div class="lgw-switch js-lgw-checksum">
            <label>
                <div>
                    <span></span>
                    <input type="checkbox" name="see_checksum_content">
                </div>
	            <?php echo esc_html( $locale->t( 'toolbox.screen.checksum_integrity' ) ); ?>
            </label>
        </div>
        <div class="lgw-box-content js-lgw-checksum-content">
	        <?php echo wp_kses_post( $toolbox_element->check_file_md5() ); ?>
        </div>
    </div>
</div>

