<?php
/**
 * Toolbox checksum page
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
		<h1><?php echo $locale->t( 'toolbox.checksum.checksum_integrity' ); ?></h1>
		<?php echo $toolbox_element->check_file_md5(); ?>
	</div>
<?php
require 'views/footer.php';
?>