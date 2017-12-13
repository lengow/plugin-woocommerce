<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-content-section text-center">
	<div id="frame_loader">
		<i class="fa fa-circle-o-notch fa-spin" style="font-size:100px;margin-top:100px;color:white;"></i>
	</div>
	<iframe id="lengow_iframe" scrolling="yes" style="display: none; overflow-y: hidden;' width='580' height='400' frameborder='0' seamless='seamless'" frameBorder="0"></iframe>
</div>
<input type="hidden" id="lengow_sync_link" value="<?php echo $is_sync; ?>">
<input type="hidden" id="lengow_lang_iso" value="<?php echo $locale_iso_code; ?>">
<script type="text/javascript">jQuery('body').addClass('lgw-home-iframe');</script>