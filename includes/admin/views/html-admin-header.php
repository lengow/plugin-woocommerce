<?php
/**
 * Admin View: Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//TODO - Check if new merchant
//if (!$is_new_merchant) : ?>
<ul class="nav nav-pills lengow-nav lengow-nav-top">
	<li role="presentation" id="lengow_logo">
		<a href="<?= admin_url('admin.php?page=lengow'); ?>">
			<img src="<?= LENGOW_PLUGIN_URL.'/assets/images/lengow-white.png'; ?>" alt="lengow">
		</a>
	</li>
	<li role="presentation" class="<?= ($current_page == 'lengow_product') ? "active" : "" ?>"><a href="
            <?= admin_url('admin.php?page=lengow&tab=lengow_product'); ?>">
			<?= $locale->t('menu.product') ?>
		</a>
	</li>
	<li class="lengow_float_right <?= ($current_page == 'lengow_settings') ? "active" : "" ?>" id="menugotosetting">
		<a href="<?= admin_url('admin.php?page=lengow&tab=lengow_settings'); ?>"
		   class="lengow_link_tooltip"
		   data-placement="bottom" data-original-title="Paramètres principaux">
			<i class="fa fa-cog fa-2x"></i>
		</a>
	</li>
	<li class="lengow_float_right <?= ($current_page == 'lengow_help') ? "active" : "" ?>" id="menugotohelp">
		<a href="<?= admin_url('admin.php?page=lengow&tab=lengow_help'); ?>"
		   class="lengow_link_tooltip"
		   data-placement="bottom" data-original-title="Aide">
			<i class="fa fa-life-ring fa-2x"></i>
		</a>
	</li>
	<li class="lengow_float_right" id="menugotosolution">
		<a href="http://solution.lengow.com" target="_blank">
			<?= $locale->t('menu.jump_to_lengow') ?>
		</a>
	</li>
</ul>
<? //endif; ?>