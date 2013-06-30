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
$CI->carabiner->js('jquery-ui-1.8.21.custom.min.js');
$CI->carabiner->js('jquery.json-2.3.js');
$CI->carabiner->js('jqueryExtension.js');
$CI->carabiner->js('jquery.printf.js');
$CI->carabiner->js('jquery.topzindex.js');
$CI->carabiner->js('jquery.alert-1.0.js');		

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
	$CI->carabiner->js('jquery.jForm.js');
	$CI->carabiner->js('jquery.url.js');
	$CI->carabiner->js('jquery.raty.js');
} 
if ($view == 'login') {
	$CI->carabiner->js('jquery.jForm.js');
	$CI->carabiner->js('jquery.url.js');
	$CI->carabiner->js('loginFB.js');
}



$CI->carabiner->css('default.css');
$CI->carabiner->css('jquery-ui-1.8.22.custom.css');
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
	<link rel="icon" href="<?php echo base_url();?>favicon.png" type="image/png">
<?php
$CI->carabiner->empty_cache('both', 'yesterday');

$CI->carabiner->display('css');
$CI->carabiner->display('js');
?>	
	<title><?php echo $title; ?> - cloneReader</title>
</head>
<body>
	<div id="header">
		<div>
		<?php echo anchor('home', 'cloneReader<span/>', array('class' => 'logo')); ?>
		
<?php
echo renderMenu($CI->cache->file->get('MENU_PROFILE_'.$userId), 'menuProfile');
?>

			<form class="search">
				<span ></span>
				<input type="text" placeholder="search ..." />
				<input type="submit" value="Search" class="btnSearch"/>
			</form>
		</div>
	</div>
	<div class="menu">
		<div>
<?php echo renderMenu($CI->cache->file->get('MENU_PUBLIC_'.$userId)); ?>
<?php echo renderMenu($CI->cache->file->get('MENU_ADMIN_'.$userId), 'menuAdmin'); ?>
		</div>
	</div>	
	

<?php
function renderMenu($aMenu, $className = null){
	$sTmp = '<ul '.($className != null ? ' class="'.$className.'" ' : '').'>';
	for ($i=0; $i<count($aMenu); $i++) {
		if ($aMenu[$i]['url'] != null) {
			$sTmp .= '	<li>'.anchor(str_replace('::', '/', $aMenu[$i]['url']), $aMenu[$i]['label']);
		}
		else {
			$sTmp .= '	<li><a>'.$aMenu[$i]['label'].'</a>';
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
<div class="content">
	<h1><?php echo $title; ?></h1>
	

