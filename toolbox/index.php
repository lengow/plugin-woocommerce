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
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @category    Lengow
 * @package     lengow-woocommerce
 * @subpackage  toolbox
 * @author      Team Connector <team-connector@lengow.com>
 * @copyright   2017 Lengow SAS
 */

require 'views/head.php';
require 'views/header.php';

?>
	<div class="container">
		<h1> <?php echo $locale->t( 'toolbox.menu.lengow_toolbox' ) ?></h1>
		<h3><i class="fa fa-check-square-o"></i> <?php echo $locale->t( 'toolbox.index.checklist_information' ) ?></h3>
		<?php echo $toolbox_element->get_check_list(); ?>
		<h3><i class="fa fa-cog"></i> <?php echo $locale->t( 'toolbox.index.global_information' ) ?></h3>
		<?php echo $toolbox_element->get_global_information(); ?>
		<h3><i class="fa fa-download"></i> <?php echo $locale->t( 'toolbox.index.import_information' ) ?></h3>
		<?php echo $toolbox_element->get_import_information(); ?>
		<h3><i class="fa fa-upload"></i> <?php echo $locale->t( 'toolbox.index.export_information' ) ?></h3>
		<?php echo $toolbox_element->get_export_information(); ?>
	</div>
<?php
require 'views/footer.php';
?>