<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 3600');

$path = str_replace('maintenance.php', '', $_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

	<meta name="description" content="503 - Site maintenance." />
	<meta name="robots" content="noindex,nofollow" />

	<link rel="icon" href="<?php echo $path; ?>favicon.png" type="image/png">

	<link type="text/css" rel="stylesheet" href="<?php echo $path; ?>assets/styles/bootstrap.css" media="screen" />
	<link type="text/css" rel="stylesheet" href="<?php echo $path; ?>assets/styles/default.css" media="screen" />
	<link type="text/css" rel="stylesheet" href="<?php echo $path; ?>assets/styles/cloneReader.css" media="screen" />

	<title> 503 - Site maintenance. </title>
</head>
<body>
	<nav class="navbar navbar-default" role="navigation" id="header">
		<div class="container">
			<div class="navbar-header">
				<a class="navbar-brand logo">
					<img alt="motormaniaco.com" src="<?php echo $path; ?>assets/images/logo.png" >
				</a>
			</div>
		</div>
	</nav>

	<div class="container pageContainer ">
		<div class="cr-page cr-page-maintenance">

			<div class="alert alert-info " style="margin-top:10px"> 503 - Site maintenance </div>

		</div>
	</div>
</body>
</html>
