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

if ($_SERVER['SERVER_NAME'] == 'jcarle.redirectme.net') {
	$CI->carabiner->minify_js 	= false;
	$CI->carabiner->minify_css	= false;
	$CI->carabiner->empty_cache('both');
}


$CI->carabiner->js('jquery-1.7.2.js');
$CI->carabiner->js('jquery.json-2.3.js');
$CI->carabiner->js('jquery.printf.js');
$CI->carabiner->js('jquery.topzindex.js');
$CI->carabiner->js('moment.min.js');
$CI->carabiner->js('bootstrap.js');
$CI->carabiner->js('jqueryExtension.js');
$CI->carabiner->js('jquery.jAlert.js');

$CI->carabiner->css('bootstrap.css');
$CI->carabiner->css('bootstrap-responsive.css');
$CI->carabiner->css('font-awesome.css');

if (isset($aJs)) {
	foreach ($aJs as $js) {
		$CI->carabiner->js($js);
	}
}

// FIXME: pensar si esto se puede resolver de un modo mas elegante
if ($view == 'includes/paginatedList') { 
	$CI->carabiner->js('jquery.paginatedList-1.0.js');
}
if ($view == 'includes/jForm') {
	$CI->carabiner->js('jquery.url.js');
	$CI->carabiner->js('jquery.raty.js');
	$CI->carabiner->js('autoNumeric.js');
	$CI->carabiner->js('bootstrap-datetimepicker.min.js');
	$CI->carabiner->js('bootstrap-datetimepicker.es.js');
	
	if (hasFieldUpload($form) == true) {
		$CI->carabiner->js('jquery.ui.widget.js');
		$CI->carabiner->js('jquery.fileupload.js');
		$CI->carabiner->js('jquery.fileupload-ui.js');
		$CI->carabiner->js('jquery.fileupload-process.js');
				
		$CI->carabiner->css('jquery.fileupload-ui.css');
	}	

	if (hasGallery($form) == true) {
		$CI->carabiner->js('tmpl.min.js');
		$CI->carabiner->js('jquery.ui.widget.js');
		$CI->carabiner->js('jquery.fileupload.js');
		$CI->carabiner->js('jquery.fileupload-ui.js');
		$CI->carabiner->js('jquery.fileupload-process.js');
		
		$CI->carabiner->js('jquery.imgCenter.js');
		$CI->carabiner->js('load-image.js');
		$CI->carabiner->js('bootstrap-image-gallery.js');
	}

		
	$CI->carabiner->js('jquery.jForm.js');		
		
	
	$CI->carabiner->css('bootstrap-datetimepicker.css');
	
	if (hasGallery($form) == true) {
		$CI->carabiner->css('bootstrap-image-gallery.css');
		$CI->carabiner->css('jquery.fileupload-ui.css');
	}
} 
if (isset($hasGallery) && $hasGallery == true) {
	$CI->carabiner->js('jquery.imgCenter.js');
	$CI->carabiner->js('load-image.js');
	$CI->carabiner->js('bootstrap-image-gallery.js');

	$CI->carabiner->css('bootstrap-image-gallery.css');
	$CI->carabiner->css('jquery.fileupload-ui.css');

}
if ($view == 'login') {
	$CI->carabiner->js('jquery.jForm.js');
	$CI->carabiner->js('jquery.url.js');
	$CI->carabiner->js('loginFB.js');
}


$CI->carabiner->css('default.css');

if (isset($aCss)) {
	foreach ($aCss as $css) {
		$CI->carabiner->css($css);
	}
}


header ('Content-type: text/html; charset=utf-8');
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="<?php echo base_url();?>favicon.png" type="image/png">
<?php
$CI->carabiner->empty_cache('both', 'yesterday');

$CI->carabiner->display('css');
$CI->carabiner->display('js');
?>	
	<title><?php echo $title; ?> - cloneReader</title>
</head>
<body class="container-fluid">
	<div id="divWaiting" class="alert alert-info navbar-fixed-top">
		<i class="icon-spinner icon-spin icon-large"></i>
		<small>procesando ...</small>
	</div>
	
	<div id="header">
		<div>
		<?php echo anchor('', 'cloneReader<span class="btn btn-primary active"> <i class="icon-certificate"></i> beta</span>', array('class' => 'logo btn btn-success btn-large active')); ?>
		
<?php
echo renderMenu($CI->cache->file->get('MENU_PROFILE_'.$userId), 'menuProfile');
?>

			<form class="search  input-prepend input-append">
				<span class="add-on">
					<i class="icon-search" ></i>
				</span>
				<input type="text" placeholder="search ..." />
				<button  class="btnSearch btn">Search</button>
			</form>
		</div>
	</div>
	<div class="menu">
		<div>
<?php echo renderMenu($CI->cache->file->get('MENU_PUBLIC_'.$userId), 'menuPublic'); ?>
<?php echo renderMenu($CI->cache->file->get('MENU_ADMIN_'.$userId), 'menuAdmin'); ?>
		</div>
	</div>	
	

<?php
function renderMenu($aMenu, $className = null){
	if (empty($aMenu)) {
		return;
	}
	
	$sTmp = '<ul '.($className != null ? ' class="'.$className.'" ' : '').'>';
	for ($i=0; $i<count($aMenu); $i++) {
		$icon = '';
		if ($aMenu[$i]['icon'] != null) {
			$icon = ' <i class="'.$aMenu[$i]['icon'].'" ></i> ';
		}
		
		if ($aMenu[$i]['url'] != null) {
			$sTmp .= '	<li> <a href="'.base_url().$aMenu[$i]['url'].'">'.$icon.$aMenu[$i]['label'].'</a>';
		}
		else {
			$sTmp .= '	<li> <a>'.$icon.$aMenu[$i]['label'].'</a>';
		} 	
		
		if (count($aMenu[$i]['childs']) > 0) {			
			$sTmp .= renderMenu($aMenu[$i]['childs']);
		}
		
		$sTmp .= '</li>';		
	}
	$sTmp .= '</ul>';
	return $sTmp;
}
?>
<div class="content row-fluid">
	<h1><?php echo $title; ?></h1>
	

