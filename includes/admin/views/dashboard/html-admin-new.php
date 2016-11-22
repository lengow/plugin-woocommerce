<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lgw-container">
	<div class="lgw-content-section text-center">
		<div id="frame_loader">
			<i class="fa fa-circle-o-notch fa-spin" style="font-size:100px;margin-top:100px;color:white;"></i>
		</div>
		<iframe id="lengow_iframe" scrolling="no"
		        style="display: none; overflow-y: hidden;' width='580' height='400' frameborder='0' seamless='seamless'"
		        frameBorder="0"></iframe>
	</div>
</div>
<input type="hidden" id="lengow_sync_link" value="<?php echo $isSync; ?>">
<script type="text/javascript">jQuery('body').addClass('lgw-home-iframe');</script>