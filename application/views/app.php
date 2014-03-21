<?php
$CI = &get_instance();

$userId = $this->session->userdata('userId');

$this->load->spark('carabiner/1.5.4');

$CI->carabiner->minify_js   = true;
$CI->carabiner->minify_css  = true;

$aScripts = array();

if ($_SERVER['SERVER_NAME'] == 'jcarle.redirectme.net') {
	$CI->carabiner->minify_js   = false;
	$CI->carabiner->minify_css  = false;
//	$CI->carabiner->empty_cache('both');
}


//$CI->carabiner->js('jquery-2.0.3.min.js');
$CI->carabiner->js('jquery-1.7.2.js');
$CI->carabiner->js('jquery.json-2.3.js');
$CI->carabiner->js('jquery.printf.js');
$CI->carabiner->js('jquery.url.js');
$CI->carabiner->js('moment-with-langs.js');
$CI->carabiner->js('bootstrap.js');
$CI->carabiner->js('crMain.js');
$CI->carabiner->js('crFunctions.js');
$CI->carabiner->js('crAlert.js');
$CI->carabiner->js('crMenu.js');
$CI->carabiner->js('crList.js');
$CI->carabiner->js('bootstrap-paginator.js');

$CI->carabiner->css('bootstrap.css');
$CI->carabiner->css('bootstrap-theme.css');
$CI->carabiner->css('font-awesome.css');

if (isset($aJs)) {
	foreach ($aJs as $js) {
		$CI->carabiner->js($js);
	}
}
if (isset($aCss)) {
	foreach ($aCss as $css) {
		$CI->carabiner->css($css);
	}
}

$aScripts = appendCrFormJsAndCss(null, null, true, true, $aScripts); 
$aScripts = appendCrListJsAndCss(null, true, $aScripts);


if (!isset($meta)) {
	$meta = array();
}

$CI->carabiner->css('default.css');
$CI->carabiner->css( SITE_ID.'.css');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html lang="<?php echo $CI->session->userdata('langId'); ?>">
<head>
	<link rel="alternate" type="application/rss+xml" title="cloneReader Feed" href="<?php echo base_url('rss'); ?>/" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />

	<meta name="description" content="<?php echo element('description', $meta); ?>" />
	<meta name="keywords" content="<?php echo element('keywords', $meta); ?>" />
	
	<link rel="icon" href="<?php echo base_url();?>favicon.png" type="image/png">
<?php
//$CI->carabiner->empty_cache('both', 'yesterday');
$CI->carabiner->display('css');
$CI->carabiner->display('js');
?>  
	<script type="text/javascript">
		var base_url	= '<?php echo base_url(); ?>';
		var datetime	= '<?php echo $this->Commond_Model->getCurrentDateTime(); ?>';
		var langId		= '<?php echo $this->session->userdata('langId'); ?>';
		var PAGE_SIZE	= <?php echo PAGE_SIZE; ?>;
		var PAGE_HOME 	= 'users'; // TODO: harckodeta! 
		var SITE_NAME	= '<?php echo SITE_NAME; ?>';
		var _msg 		= {};
		
		$(document).ready(function() {
			$.appType = 'appAjax';
		});
<?php

// TODO: sacar todo el codigo php de aca!

if (in_array($_SERVER['SERVER_NAME'], array('www.jcarle.com.ar', 'www.clonereader.com.ar'))) {
	$aScripts[] = "

	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-41589815-1']);
	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	";
}

// TODO: 
//echo implode(' ', $aScripts);
?>
	</script>
	<title><?php echo SITE_NAME; ?> </title>
</head>
<body>
	<div id="divWaiting" class="alert alert-info navbar-fixed-top">
		<i class="icon-spinner icon-spin icon-large"></i>
		<small> <?php echo $this->lang->line('loading ...'); ?></small>
	</div>
	<nav class="navbar navbar-default" role="navigation" id="header">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand logo" > <img alt="<?php echo SITE_NAME; ?>" src="<?php echo base_url('assets/images/logo.png'); ?>" width="151" height="39">  </a>
		</div>
		<div class="navbar-collapse collapse navbar-ex1-collapse "></div>
	</nav>
	<nav class="menu label-primary">
		<div></div>
	</nav>
	<div class="container content">
		
<?php $this->load->view('includes/uploadfile'); ?>		

