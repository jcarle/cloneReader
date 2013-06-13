<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" charset="utf-8" />
	<link rel="icon" href="<?php echo base_url();?>favicon.png" type="image/png">

	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.7.2.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-ui-1.8.21.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.json-2.3.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jqueryExtension.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.printf.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.topzindex.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.alert-1.0.js"></script>		
	
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


if (!isset($aJs)) {
	$aJs = array();
}

// FIXME: pensar si esto se puede resolver de un modo mas elegante
if ($view == 'includes/paginatedList') { 
	$aJs[] = 'jquery.paginatedList-1.0.js';
}
if ($view == 'includes/formValidation') {
	$aJs[] = 'jquery.formValidator.js';
	$aJs[] = 'jquery.url.js';
} 
if ($view == 'login') {
	$aJs[] = 'jquery.formValidator.js';
	$aJs[] = 'jquery.url.js';
	$aJs[] = 'loginFB.js';
} 

foreach ($aJs as $fileName) {
	echo '<script type="text/javascript" src="'.base_url().'js/'.$fileName.'"></script>';

}
?>

	<link rel="stylesheet" href="<?php echo base_url();?>css/default.css" type="text/css"  charset="utf-8" />
	<link rel="stylesheet" href="<?php echo base_url();?>css/jquery-ui-1.8.22.custom.css" type="text/css" charset="utf-8" />
	
<?php
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
	

