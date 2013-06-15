<?php
$CI = &get_instance();

if (!is_array($CI->session->userdata('MENU_PROFILE'))) {
	$CI->session->set_userdata('MENU_PROFILE', $CI->Menu_Model->getMenu(MENU_PROFILE));
}
if (!is_array($CI->session->userdata('MENU_PUBLIC'))) {
	$CI->session->set_userdata('MENU_PUBLIC', $CI->Menu_Model->getMenu(MENU_PUBLIC));
}
if (!is_array($CI->session->userdata('MENU_ADMIN'))) {
	$CI->session->set_userdata('MENU_ADMIN', $CI->Menu_Model->getMenu(MENU_ADMIN));
}


$this->load->spark('carabiner/1.5.4');

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
if ($view == 'includes/formValidation') {
	$CI->carabiner->js('jquery.formValidator.js');
	$CI->carabiner->js('jquery.url.js');
} 
if ($view == 'login') {
	$CI->carabiner->js('jquery.formValidator.js');
	$CI->carabiner->js('jquery.url.js');
	$CI->carabiner->js('loginFB.js');
}




/*
$this->load->spark('carabiner/1.5.4');

$this->carabiner->js('jquery-1.7.2.js');*/

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" charset="utf-8" />
	<link rel="icon" href="<?php echo base_url();?>favicon.png" type="image/png">

<?php

?>

	<link rel="stylesheet" href="<?php echo base_url();?>css/default.css" type="text/css"  charset="utf-8" />
	<link rel="stylesheet" href="<?php echo base_url();?>css/jquery-ui-1.8.22.custom.css" type="text/css" charset="utf-8" />
	
<?php
$CI->carabiner->empty_cache('js');
$CI->carabiner->display('js');



if (!isset($aCss)) {
	$aCss = array();
}
foreach ($aCss as $fileName) {
	echo '<link rel="stylesheet" href="'.base_url().'css/'.$fileName.'" type="text/css" charset="utf-8" />';
}
?>	

	<title><?php echo $title; ?> - cloneReader</title>
</head>
<body>
	<div id="header">
		<div>
		<?php echo anchor('home', 'cloneReader<span/>', array('class' => 'logo')); ?>
		
<?php
echo renderMenu($CI->session->userdata('MENU_PROFILE'), 'menuProfile');
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
<?php echo renderMenu($CI->session->userdata('MENU_PUBLIC')); ?>
<?php echo renderMenu($CI->session->userdata('MENU_ADMIN'), 'menuAdmin'); ?>
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
	

