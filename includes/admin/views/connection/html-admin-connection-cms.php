<?php
/**
 * Admin View: Connection Cms
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="lgw-connection-cms">
    <div class="lgw-content-section">
        <h2><?php echo $locale->t( 'connection.cms.credentials_title' ); ?></h2>
    </div>
    <div class="lgw-content-input">
        <input type="text"
               name="lgwAccessToken"
               class="js-credentials-input"
               placeholder="<?php echo $locale->t( 'connection.cms.credentials_placeholder_access_token' ); ?>">
        <input type="text"
               name="lgwSecret"
               class="js-credentials-input"
               placeholder="<?php echo $locale->t( 'connection.cms.credentials_placeholder_secret' ); ?>">
    </div>
    <div class="lgw-content-section">
        <p><?php echo $locale->t( 'connection.cms.credentials_description' ); ?></p>
        <p>
	        <?php echo $locale->t( 'connection.cms.credentials_help' ); ?>
            <a href="<?php echo $locale->t( 'connection.cms.credentials_help_center_url' ); ?>" target="_blank">
	            <?php echo $locale->t( 'connection.cms.credentials_help_center' ); ?>
            </a>
        </p>
    </div>
    <div>
        <button class="lgw-btn lgw-btn-progression lgw-btn-disabled js-connect-cms">
            <div class="btn-inner">
                <div class="btn-step default">
	                <?php echo $locale->t( 'connection.cms.credentials_button' ); ?>
                </div>
                <div class="btn-step loading">
	                <?php echo $locale->t( 'connection.cms.credentials_button_loading' ); ?>
                </div>
            </div>
        </button>
    </div>
</div>
