<?php
$CI = &get_instance();

$groups = $this->session->userdata('groups');

$CI->load->driver('cache', array('adapter' => 'file'));
if (!is_array($CI->cache->file->get('MENU_PROFILE_'.json_encode($groups)))) {
	$CI->load->model('Menu_Model');
	$CI->Menu_Model->createMenuCache($groups);
}

if (!isset($meta)) {
	$meta = array();
}
$meta = getMetaByController($meta);
if (!isset($langs)) {
	$langs = array();
}
$langs      = getLangToJs($langs);
$this->my_js->add(langJs($langs));
$this->my_js->add(  '  $(\'.'.getPageName().'\').data(\'meta\', '.json_encode($meta).'); ');

if (ENVIRONMENT == 'production' && config_item('google-analytics-Account') != '') {
	$this->my_js->add( "
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	ga('create', '".config_item('google-analytics-Account')."', 'auto');
	ga('send', 'pageview');
	" );
}

if (!isset($breadcrumb)) {
	$breadcrumb = array();
}
$breadcrumb = getBreadcrumb($breadcrumb, $meta, isset($skipBreadcrumb) ? $skipBreadcrumb : false);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html lang="<?php echo $CI->session->userdata('langId'); ?>">
<head>
<?php
if (config_item('hasRss') == true) {
	echo ' <link rel="alternate" type="application/rss+xml" title="Feed | '.config_item('siteName').'" href="'. base_url('rss').'" />';
}	
?>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
	
	<meta name="description" content="<?php echo element('description', $meta); ?>" />
	<meta name="keywords" content="<?php echo element('keywords', $meta); ?>" />
	<meta name="robots" content="<?php echo element('robots', $meta); ?>" />

	<link rel="icon" href="<?php echo base_url('favicon.png');?>" type="image/png">
<?php
$this->load->spark('carabiner/1.5.4');

$CI->carabiner->minify_js  = true;
$CI->carabiner->minify_css = true;
if (ENVIRONMENT == 'development') {
	$CI->carabiner->minify_js  = false;
	$CI->carabiner->minify_css = false;
}

appendFilesToCarabiner();

$CI->carabiner->display('css');
//$CI->carabiner->display('js');

$siteLogo = config_item('siteLogo');
?>
	<title><?php echo element('title', $meta). (config_item('addTitleSiteName') == true ? ' | '.config_item('siteName') : ''); ?> </title>
</head>
<body>
	<div id="divWaiting" class="alert alert-warning navbar-fixed-top">
		<i class="fa fa-spinner fa-spin fa-lg"></i>
		<small> <?php echo $this->lang->line('loading ...'); ?></small>
	</div>
	
	<nav class="navbar navbar-default" role="navigation" id="header">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand logo" href="<?php echo base_url(); ?>"> 
					<img alt="<?php echo config_item('siteName'); ?>" src="<?php echo base_url('assets/images/logo.png'); ?>" width="<?php echo $siteLogo['w']; ?>" height="<?php echo $siteLogo['h']; ?>">  
				</a>
			</div>

			<div class="navbar-collapse collapse navbar-ex1-collapse ">
<?php
echo getHtmlFormSearch();
echo renderMenu($CI->cache->file->get('MENU_PROFILE_'.json_encode($groups)), 'menuProfile nav navbar-nav navbar-right');
?>
			</div>
		</div>
	</nav>

	<nav class="menu label-primary">
		<div>
<?php echo renderMenu($CI->cache->file->get('MENU_PUBLIC_'.json_encode($groups)), 'menuPublic'); ?>
		</div>
	</nav>	
	<div class="container pageContainer ">
		<div class="cr-page <?php echo getPageName(); ?>">
<?php
if (!empty($breadcrumb)) {
	echo '<ol class="breadcrumb">';
	foreach ($breadcrumb as $link) {
		if (element('active', $link) == true) {
			echo '<li class="active">'.$link['text'].'</li>';
		}
		else {
			echo '<li><a title="'.$link['text'].'" href="'.$link['href'].'">'.$link['text'].'</a></li>';
		} 
	}
	echo '</ol>';  
}

if (!isset($showTitle)) {
	$showTitle = true;
}
if ($showTitle == true) {
	echo '	<div class="page-header">
				<h1>'. element('h1', $meta).' <small> </small></h1>
			</div>';
}

function renderMenu($aMenu, $className = null, $depth = 0){
	if (empty($aMenu)) {
		return;
	}
	
	$CI = &get_instance();
	
	if ($depth == 0) {
		$className .= ' crMenu ';
	}
	
	$sTmp = '<ul '.($className != null ? ' class="'.$className.'" ' : '').'>';
	for ($i=0; $i<count($aMenu); $i++) {
		$item       = $aMenu[$i];
		$icon       = '';
		$hasChilds  = count($item['childs']) > 0;
		$attr       = '';
		$label      = $item['menuTranslate'] == true ? $CI->lang->line($item['label']) : $item['label'];
		$aClassName = array($item['menuClassName']);
		if ($item['icon'] != null) {
			$icon = ' <i class="'.$item['icon'].'" ></i> ';
		}
		if ($hasChilds == true) {
			$attr = ' class="dropdown-toggle" data-toggle="dropdown" ';
		}
		
		if ($depth >= 1 && $hasChilds == true) {
			$aClassName[] = 'dropdown-submenu dropdown-submenu-left';
		}

		if ($item['menuDividerBefore'] == true) {
			$sTmp .= ' <li role="presentation" class="divider"></li> ';
		}

		if ($item['url'] != null) {
			$sTmp .= ' <li class="'.implode(' ', $aClassName).'"> <a title="'.$label.'" href="'.base_url($item['url']).'" '.$attr.'>'.$icon. '<span>'.$label.'</span></a>';
		}
		else {
			$sTmp .= ' <li class="'.implode(' ', $aClassName).'"> <a title="'.$label.'" '.$attr.'>'.$icon.'<span>'.$label.'</span></a>';
		}	
		
		if ($hasChilds == true) {
			$sTmp .= renderMenu($item['childs'], ($hasChilds == true ? 'dropdown-menu' : null), $depth + 1 );
		}

		$sTmp .= '</li>';
		
		if ($item['menuDividerAfter'] == true) {
			$sTmp .= ' <li role="presentation" class="divider"></li> ';
		}
	}
	$sTmp .= '</ul>';
	return $sTmp;
}

