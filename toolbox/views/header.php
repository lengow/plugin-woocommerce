<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Lengow Toolbox</title>
	<link rel="stylesheet" href="/wp-content/plugins/lengow-woocommerce/assets/css/bootstrap-3.3.6.css">
	<link rel="stylesheet" href="/wp-content/plugins/lengow-woocommerce/assets/css/toolbox.css">
	<link rel="stylesheet" href="/wp-content/plugins/lengow-woocommerce/assets/css/font-awesome.css">
</head>

<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="/wp-content/plugins/lengow-woocommerce/toolbox/index.php">
				<i class="fa fa-rocket"></i> <?php echo $locale->t('toolbox.menu.lengow_toolbox'); ?>
			</a>
		</div>
		<div id="navbar" class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li>
					<a href="/wp-content/plugins/lengow-woocommerce/toolbox/checksum.php">
						<i class="fa fa-search"></i> <?php echo $locale->t('toolbox.menu.checksum'); ?>
					</a>
				</li>
				<li>
					<a href="/wp-content/plugins/lengow-woocommerce/toolbox/log.php">
						<i class="fa fa-file-text-o"></i> <?php echo $locale->t('toolbox.menu.log'); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
</nav>
