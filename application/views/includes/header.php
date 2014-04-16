<?php
$CI = &get_instance();

$userId = $this->session->userdata('userId');

$CI->load->driver('cache', array('adapter' => 'file'));
$CI->Menu_Model->createMenuCache($userId);


$this->load->spark('carabiner/1.5.4');

$CI->carabiner->minify_js 	= true;
$CI->carabiner->minify_css	= true;

$aScripts = array();

if ($_SERVER['SERVER_NAME'] == 'jcarle.redirectme.net') {
	$CI->carabiner->minify_js 	= false;
	$CI->carabiner->minify_css	= false;
//	$CI->carabiner->empty_cache('both');
}

$CI->carabiner->js('jquery-1.7.2.js');
//$CI->carabiner->js('jquery-1.11.0.js');
$CI->carabiner->js('jquery.json-2.3.js');
$CI->carabiner->js('jquery.printf.js');
$CI->carabiner->js('jquery.url.js');
$CI->carabiner->js('moment-with-langs.js');
$CI->carabiner->js('bootstrap.js');
$CI->carabiner->js('crFunctions.js');
$CI->carabiner->js('crMain.js');
$CI->carabiner->js('crAlert.js');
$CI->carabiner->js('crMenu.js');
$CI->carabiner->js('feedback.js');
$CI->carabiner->js('profile.js');


$CI->carabiner->css('bootstrap.css');
$CI->carabiner->css('bootstrap-theme.css');
$CI->carabiner->css('font-awesome.css');

$aScripts = appendCrFormJsAndCss($aScripts); 
$aScripts = appendCrListJsAndCss($aScripts);


$siteAssets = config_item('siteAssets');
foreach ($siteAssets['js'] as $js) {
	$CI->carabiner->js($js);
}
foreach ($siteAssets['css'] as $css) {
	$CI->carabiner->css($css);
}



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
$CI->carabiner->display('css');
$CI->carabiner->display('js');
?>	
	<script type="text/javascript">
		var base_url				= '<?php echo base_url(); ?>';
		var datetime				= '<?php echo $this->Commond_Model->getCurrentDateTime(); ?>';
		var langId					= '<?php echo $this->session->userdata('langId'); ?>';
		var PAGE_SIZE				= <?php echo PAGE_SIZE; ?>;	
		var PAGE_HOME			 	= '<?php echo $this->router->default_controller; ?>'; 
		var SITE_NAME				= '<?php echo SITE_NAME; ?>';
		var DEFAULT_CURRENCY_ID  	= <?php echo DEFAULT_CURRENCY_ID; ?>;
		var DEFAULT_CURRENCY_NAME	= '<?php echo DEFAULT_CURRENCY_NAME; ?>';
		var _msg			 		= {};
<?php
if (!isset($langs)) {
	$langs = array();
}
$langs  = getLangToJs($langs);
$aScripts[] = langJs($langs);


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
	<title><?php echo $title.' | '.SITE_NAME; ?> </title>
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
			<a class="navbar-brand logo" href="<?php echo base_url()?>"> <img alt="<?php echo SITE_NAME; ?>" src="<?php echo base_url('assets/images/logo.png'); ?>" width="151" height="39">  </a>
		</div>

		<div class="navbar-collapse collapse navbar-ex1-collapse ">
	
			<form class="navbar-form navbar-left" role="search" style="display:none"> <!--  TODO: implementar el buscador!-->
				<div class="form-group" >
					<div class="input-group">
						<span class="input-group-addon">
							<i class="icon-search" ></i>
						</span>
						<input type="text" class="form-control" placeholder="search ..." />
						<span class="input-group-btn">
							<button  class="btnSearch btn btn-default">Search</button>
						</span>
					</div>
				</div>
			</form>
<?php
echo renderMenu($CI->cache->file->get('MENU_PROFILE_'.$userId), 'menuProfile nav navbar-nav pull-right');
?>
		</div>
	</nav>

	<nav class="menu label-primary">
		<div>
<?php echo renderMenu($CI->cache->file->get('MENU_PUBLIC_'.$userId), 'menuPublic'); ?>
		</div>
	</nav>	
	<div class="container content">
		<div class="page <?php echo getPageName(); ?>" >
<?php
if (isset($breadcrumb)) {
	echo '<ol class="breadcrumb">';
	foreach ($breadcrumb as $link) {
		if (element('active', $link) == true) {
			echo '<li class="active"> '.$link['text'].'</li>';			
		}
		else {
			echo '<li><a href="'.$link['href'].'">'.$link['text'].'</a></li>';
		} 
	}
	echo '</ol>';  
}

if (!isset($showTitle)) {
	$showTitle = true;
}
if ($showTitle == true) {
	echo '	<div class="pageTitle">
				<h2>'. $title .' <small> </small></h2>
			</div>';
}


function renderMenu($aMenu, $className = null){
	if (empty($aMenu)) {
		return;
	}
	
	$CI	= &get_instance();
	
	$sTmp = '<ul '.($className != null ? ' class="'.$className.'" ' : '').'>';
	for ($i=0; $i<count($aMenu); $i++) {
		$icon 		= '';
		$hasChilds 	= count($aMenu[$i]['childs']) > 0;
		$attr		= '';
		$label 		= $CI->lang->line($aMenu[$i]['label']);
		if ($label == '') {
			$label = $aMenu[$i]['label'];
		}
		if ($aMenu[$i]['icon'] != null) {
			$icon = ' <i class="'.$aMenu[$i]['icon'].'" ></i> ';
		}
		if ($hasChilds == true) {
			$attr = ' class="dropdown-toggle" data-toggle="dropdown" ';
		}		
		
		if ($aMenu[$i]['url'] != null) {
			$sTmp .= '	<li> <a title="'.$label.'" href="'.base_url().$aMenu[$i]['url'].'" '.$attr.'>'.$icon.$label.'</a>';
		}
		else {
			$sTmp .= '	<li> <a title="'.$label.'" '.$attr.'>'.$icon.$label.'</a>';
		} 	
		
		if ($hasChilds == true) {			
			$sTmp .= renderMenu($aMenu[$i]['childs']);
		}
		
		$sTmp .= '</li>';		
	}
	$sTmp .= '</ul>';
	return $sTmp;
}

