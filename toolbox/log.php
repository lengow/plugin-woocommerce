<?php
/**
 * Toolbox log page
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

$locale   = new Lengow_Translation();
$listFile = Lengow_Toolbox::get_data( Lengow_Toolbox::DATA_TYPE_LOG );

require 'views/header.php';
?>
    <div class="container">
        <h1><?php echo $locale->t( 'toolbox.log.log_files' ); ?></h1>
        <ul class="list-group">
			<?php
			foreach ( $listFile as $file ) {
				$name = $file[ Lengow_Log::LOG_DATE ]
					? date( 'l d F Y', strtotime( $file[ Lengow_Log::LOG_DATE ] ) )
					: $locale->t( 'toolbox.log.download_all' );
				echo '<li class="list-group-item"><a href="' . $file[ Lengow_Log::LOG_LINK ] . '">' . $name . '</a></li>';
			}
			?>
        </ul>
    </div><!-- /.container -->
<?php
require 'views/footer.php';
