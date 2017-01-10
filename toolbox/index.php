<?php
/**
 * Toolbox index page
 *
 * Copyright 2017 Lengow SAS
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 * 
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category   	lengow
 * @package    	lengow-woocommerce
 * @subpackage 	toolbox
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
 */

require 'views/head.php';
require 'views/header.php';

?>
	<div class="container">
		<h1> <?php echo $locale->t( 'toolbox.menu.lengow_toolbox' ) ?></h1>
		<h3><i class="fa fa-check-square-o"></i> <?php echo $locale->t( 'toolbox.index.checklist_information' ) ?></h3>
		<?php echo $check->get_check_list(); ?>
		<h3><i class="fa fa-cog"></i> <?php echo $locale->t( 'toolbox.index.global_information' ) ?></h3>
		<?php echo $check->get_global_information(); ?>
		<h3><i class="fa fa-download"></i> <?php echo $locale->t( 'toolbox.index.import_information' ) ?></h3>
		<?php echo $check->get_import_information(); ?>
		<h3><i class="fa fa-upload"></i> <?php echo $locale->t( 'toolbox.index.export_information' ) ?></h3>
		<?php echo $check->get_information_by_store(); ?>
	</div>
<?php
require 'views/footer.php';
?>