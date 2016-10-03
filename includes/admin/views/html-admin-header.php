<?php
/**
 * Admin View: Header
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (isset($_GET['tab'])) {
	$current_page = $_GET['tab'];
} ?>
<ul class="nav nav-pills lengow-nav lengow-nav-top">
	<li role="presentation" id="lengow_logo">
		<a href="<?php echo admin_url('admin.php?page=lengow'); ?>">
			<img src="<?php echo LENGOW_PLUGIN_URL.'/assets/images/lengow-white.png'; ?>" alt="lengow">
		</a>
	</li>
	<li role="presentation" class="<?php echo (isset($current_page) && $current_page == 'lengow_admin_products') ? "active" : "" ?>"><a href="
            <?php echo admin_url('admin.php?page=lengow&tab=lengow_admin_products'); ?>">
			<?php echo $locale->t('menu.product') ?>
		</a>
	</li>
	<li class="lengow_float_right <?php echo (isset($current_page) && $current_page == 'lengow_admin_settings') ? "active" : "" ?>" id="menugotosetting">
		<a href="<?php echo admin_url('admin.php?page=lengow&tab=lengow_admin_settings'); ?>"
		   class="lengow_link_tooltip"
		   data-placement="bottom" data-original-title="<?php echo $locale->t('menu.global_parameter') ?>">
			<i class="fa fa-cog fa-2x"></i>
		</a>
	</li>
	<li class="lengow_float_right <?php echo (isset($current_page) && $current_page == 'lengow_admin_help') ? "active" : "" ?>" id="menugotohelp">
		<a href="<?php echo admin_url('admin.php?page=lengow&tab=lengow_admin_help'); ?>"
		   class="lengow_link_tooltip"
		   data-placement="bottom" data-original-title="<?php echo $locale->t('menu.help') ?>">
			<i class="fa fa-life-ring fa-2x"></i>
		</a>
	</li>
	<li class="lengow_float_right" id="menugotosolution">
		<a href="http://solution.lengow.com" target="_blank">
			<?php echo $locale->t('menu.jump_to_lengow') ?>
		</a>
	</li>
	<?php if ($merchant_status['type'] == 'free_trial' && $merchant_status['day'] != 0) : ?>
	<li class="lengow_float_right" id="menucountertrial">
		<div class="lgw-block">
			<?php echo $locale->t('menu.counter', ['counter' => $merchant_status['day']]) ?>
			<a href="http://my.lengow.io/" target="_blank">
				<?php echo $locale->t('menu.upgrade_account') ?>
			</a>
		</div>
	</li>
	<?php endif; ?>
</ul>