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

$CI->carabiner->minify_js 	= true;
$CI->carabiner->minify_css	= true;

  
 /*
if ($_SERVER['SERVER_NAME'] == 'jcarle.redirectme.net') {
	$CI->carabiner->minify_js 	= false;
	$CI->carabiner->minify_css	= false;
	$CI->carabiner->empty_cache('both');
}*/


//$CI->carabiner->js('jquery-2.0.3.min.js');
$CI->carabiner->js('jquery-1.7.2.js');
$CI->carabiner->js('jquery.json-2.3.js');
$CI->carabiner->js('jquery.printf.js');
$CI->carabiner->js('jquery.url.js');
$CI->carabiner->js('moment-with-langs.js');
$CI->carabiner->js('bootstrap.js');
$CI->carabiner->js('jqueryExtension.js');
$CI->carabiner->js('jquery.jAlert.js');

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

if (!isset($hasForm)) {
	$hasForm = false;
}
if ($view == 'includes/jForm') {
	$hasForm = true;
}

// FIXME: pensar si esto se puede resolver de un modo mas elegante
if ($view == 'includes/paginatedList') { 
	$CI->carabiner->js('jquery.paginatedList-1.0.js');
}
if ($hasForm == true) {
	if (!isset($form)) {
		$form = array('fields' => array());	
	}
	
	$CI->carabiner->js('jquery.raty.js');
	$CI->carabiner->js('select2.js');
	$CI->carabiner->js('autoNumeric.js');
	$CI->carabiner->js('bootstrap-datetimepicker.min.js');
	
	if (hasFieldUpload($form) == true) {
		$CI->carabiner->js('jquery.ui.widget.js');
		$CI->carabiner->js('jquery.fileupload.js');
		$CI->carabiner->js('jquery.fileupload-ui.js');
		$CI->carabiner->js('jquery.fileupload-process.js');
				
		$CI->carabiner->css('jquery.fileupload-ui.css');
	}	
	
	
	if ($this->session->userdata('langId') == 'es') {
		$CI->carabiner->js('select2_locale_es.js');	
		$CI->carabiner->js('bootstrap-datetimepicker.es.js');
	}

	if (hasGallery($form) == true) {
		$CI->carabiner->js('tmpl.min.js');
		$CI->carabiner->js('jquery.ui.widget.js');
		$CI->carabiner->js('jquery.fileupload.js');
		$CI->carabiner->js('jquery.fileupload-ui.js');
		$CI->carabiner->js('jquery.fileupload-process.js');
		
		$CI->carabiner->js('jquery.imgCenter.js');
		$CI->carabiner->js('blueimp-gallery.js');
	}

		
	$CI->carabiner->js('jquery.jForm.js');
	$CI->carabiner->css('select2.css');
	$CI->carabiner->css('select2-bootstrap.css');
	$CI->carabiner->css('bootstrap-datetimepicker.css');
	
	if (hasGallery($form) == true) {
		$CI->carabiner->css('blueimp-gallery.css');
		$CI->carabiner->css('jquery.fileupload-ui.css');
	}
} 
if (isset($hasGallery) && $hasGallery == true) {
	$CI->carabiner->js('jquery.imgCenter.js');
	$CI->carabiner->js('blueimp-gallery.js');

	$CI->carabiner->css('blueimp-gallery.css');
	$CI->carabiner->css('jquery.fileupload-ui.css');

}
if ($view == 'login') {
	$CI->carabiner->js('jquery.jForm.js');
	$CI->carabiner->js('loginFB.js');
	$CI->carabiner->js('loginGoogle.js');
}


$CI->carabiner->css('default.css');
$CI->carabiner->css('cloneReader.css');

header ('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html lang="<?php echo $CI->session->userdata('langId'); ?>">
<head>
	<link rel="alternate" type="application/rss+xml" title="cloneReader Feed" href="<?php echo base_url('rss'); ?>/" />
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />	
	<link rel="icon" href="<?php echo base_url();?>favicon.png" type="image/png">
<?php
$CI->carabiner->empty_cache('both', 'yesterday');

$CI->carabiner->display('css');
$CI->carabiner->display('js');
?>	
	<script type="text/javascript">
		var base_url	= '<?php echo base_url(); ?>';
		var datetime	= '<?php echo $this->Commond_Model->getCurrentDateTime(); ?>';
		var langId		= '<?php echo $this->session->userdata('langId'); ?>';
	
<?php
if (!isset($langs)) {
	$langs = array();
}
$langs[] = 'DATE_FORMAT';
$langs[] = 'MOMENT_DATE_FORMAT';
$langs[] = 'NUMBER_DEC_SEP';
$langs[] = 'NUMBER_THOUSANDS_SEP';
$langs[] = 'Cancel';
$langs[] = 'Close';
$langs[] = 'Are you sure?';

echo langJs($langs);

$scripts = '';
if (isset($aServerData)) {
	$scripts .= 'var SERVER_DATA = '.json_encode($aServerData).'; ';
}

if (in_array($_SERVER['SERVER_NAME'], array('www.jcarle.com.ar', 'www.clonereader.com.ar'))) {
	$scripts .= "

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
if ($scripts != '') {
	echo $scripts;
}
?>
	</script>	
	<title><?php echo $title; ?> | cloneReader</title>
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
			<?php echo anchor('', '<img src="'.base_url().'assets/images/icons/16.png?v1.2" /> cReader <span class="btn btn-primary active"> <i class="icon-certificate"></i> beta</span>', array('class' => 'logo btn btn-success btn-sm active')); ?>
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
	echo '	<div class="aaaapage-header pageTitle">
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
		$icon = '';
		if ($aMenu[$i]['icon'] != null) {
			$icon = ' <i class="'.$aMenu[$i]['icon'].'" ></i> ';
		}
		
		if ($aMenu[$i]['url'] != null) {
			$sTmp .= '	<li> <a href="'.base_url().$aMenu[$i]['url'].'">'.$icon.$CI->lang->line($aMenu[$i]['label']).'</a>';
		}
		else {
			$sTmp .= '	<li> <a>'.$icon.$CI->lang->line($aMenu[$i]['label']).'</a>';
		} 	
		
		if (count($aMenu[$i]['childs']) > 0) {			
			$sTmp .= renderMenu($aMenu[$i]['childs']);
		}
		
		$sTmp .= '</li>';		
	}
	$sTmp .= '</ul>';
	return $sTmp;
}

