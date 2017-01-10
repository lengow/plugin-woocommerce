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
 * the Free Software Foundation; either version 2 of the License, or
 * at your option) any later version.
 * 
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/old-licenses/gpl-2.0
 *
 * @category   	Lengow
 * @package    	lengow-woocommerce
 * @subpackage 	toolbox
 * @author     	Team module <team-module@lengow.com>
 * @copyright  	2017 Lengow SAS
 */

require 'views/head.php';

$action = isset( $_GET['action'] ) ? $_GET['action'] : null;
$file   = isset( $_GET['file'] ) ? $_GET['file'] : null;

switch ( $action ) {
	case 'download':
		Lengow_Log::download( $file );
		break;
	case 'download_all':
		Lengow_Log::download();
		break;
	default:
		break;
}

require 'views/header.php';
$locale = new Lengow_Translation();

$listFile = Lengow_Log::get_paths();

?>
	<div class="container">
		<h1><?php echo $locale->t( 'toolbox.log.log_files' ); ?></h1>
		<ul class="list-group">
			<?php
			foreach ( $listFile as $file ) {
				echo '<li class="list-group-item">';
				echo '<a href="/wp-content/plugins/lengow-woocommerce/toolbox/log.php?action=download&file='
					. urlencode( $file['short_path'] ) . '">
					<i class="fa fa-download"></i> ' . $file['name'] . '</a>';
				echo '</li>';
			}
			echo '<li class="list-group-item">';
			echo '<a href="/wp-content/plugins/lengow-woocommerce/toolbox/log.php?action=download_all">
				<i class="fa fa-download"></i> ' . $locale->t( 'toolbox.log.download_all' ) . '</a>';
			echo '</li>';
			?>
		</ul>
	</div><!-- /.container -->
<?php
require 'views/footer.php';
