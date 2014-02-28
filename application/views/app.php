<?php
$CI = &get_instance();

$CI->load->driver('cache', array('adapter' => 'file'));

$userId = $this->session->userdata('userId');

if (!is_array($CI->cache->file->get('MENU_PROFILE_'.$userId))) {
	$CI->cache->file->save('MENU_PROFILE_'.$userId, $CI->Menu_Model->getMenu(MENU_PROFILE));
}
if (!is_array($CI->cache->file->get('MENU_PUBLIC_'.$userId))) {
	$CI->cache->file->save('MENU_PUBLIC_'.$userId, $CI->Menu_Model->getMenu(MENU_PUBLIC));  
}
if (!is_array($CI->cache->file->get('MENU_ADMIN_'.$userId))) {
	$CI->cache->file->save('MENU_ADMIN_'.$userId, $CI->Menu_Model->getMenu(MENU_ADMIN));
}

$this->load->spark('carabiner/1.5.4');

$CI->carabiner->minify_js   = true;
$CI->carabiner->minify_css  = true;

$aScripts = array();

if ($_SERVER['SERVER_NAME'] == 'jcarle.redirectme.net') {
	$CI->carabiner->minify_js   = false;
	$CI->carabiner->minify_css  = false;
	$CI->carabiner->empty_cache('both');
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
$CI->carabiner->css( config_item('siteId').'.css');

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
<?php

// TODO: pedir las traducciones y el menu por ajax
// TODO: sacar todo el codigo php de aca!


if (!isset($langs)) {
	$langs = array();
}

$langs = array_merge($langs, array_keys($CI->lang->language));

$langs  = getLangToJs($langs);
$aScripts[] = langJs($langs);


$aMenu = array(
	'MENU_PROFILE' => array(
		'items' 	=> $CI->cache->file->get('MENU_PROFILE_'.$userId), 
		'className'	=> 'menuProfile nav navbar-nav pull-right',
		'parent'	=> '.navbar-ex1-collapse'
	),
	'MENU_PUBLIC' 	=> array(
		'items' 	=> $CI->cache->file->get('MENU_PUBLIC_'.$userId), 
		'className' =>'menuPublic',
		'parent'	=> '.menu.label-primary div',
	)
); 

$aScripts[] = ' 
	var APP_MENU 	= '.json_encode($aMenu).'; 
	var PAGE_HOME 	= \'users\';
	var siteName	= \''.config_item('siteName').'\'; 
';

if (isset($aServerData)) {
	$aScripts[] = 'var SERVER_DATA = '.json_encode($aServerData).'; ';
}

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

echo implode(' ', $aScripts);
?>
	</script>
	<title><?php echo config_item('siteName'); ?> </title>
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
			<a class="navbar-brand logo" href="<?php echo base_url('app')?>"> <img alt="<?php echo config_item('siteName'); ?>" src="<?php echo base_url('assets/images/logo.png'); ?>" width="151" height="39">  </a>
		</div>
		<div class="navbar-collapse collapse navbar-ex1-collapse "></div>
	</nav>
	<nav class="menu label-primary">
		<div></div>
	</nav>
	<div class="container content">

