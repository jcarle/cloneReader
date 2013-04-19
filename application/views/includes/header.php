<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" charset="utf-8" />

	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-1.7.2.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-ui-1.8.21.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.ui.datepicker-es.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery-ui-timepicker-addon"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jqueryExtension.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.printf.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.topzindex.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.url.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.popupWindow-1.0.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.alert-1.0.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.paginatedList-1.0.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.formValidator.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.imgCenter.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>js/jquery.json-2.3.js"></script>
	

<?php
if (isset($aJs)) {
	foreach ($aJs as $fileName) {
		echo '<script type="text/javascript" src="'.base_url().'js/'.$fileName.'"></script>';
	}
}
?>

	<link rel="stylesheet" href="<?php echo base_url();?>css/default.css" type="text/css"  charset="utf-8" />
	<link rel="stylesheet" href="<?php echo base_url();?>css/jquery-ui-1.8.22.custom.css" type="text/css" charset="utf-8" />
	
<?php
if (isset($aCss)) {
	foreach ($aCss as $fileName) {
		echo '<link rel="stylesheet" href="'.base_url().'css/'.$fileName.'" type="text/css" charset="utf-8" />';
	}
}
?>	
	
	<link rel="alternate" type="application/atom+xml" title="Master Atom feed" href="" />
	<link rel="alternate" type="application/rss+xml" title="Master RSS feed" href="" />

	<title><?php echo $title; ?> - cloneReader</title>
</head>
<body>
	<div id="header">
		<div>
		<?php echo anchor('home', 'cloneReader', array('class' => 'logo')); ?>
		
<?php
$CI = &get_instance();
echo renderMenu($CI->Menu_Model->getMenu(MENU_PROFILE), 'menuProfile');
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
<?php echo renderMenu($CI->Menu_Model->getMenu(MENU_PUBLIC)); ?>
<?php echo renderMenu($CI->Menu_Model->getMenu(MENU_ADMIN), 'menuAdmin'); ?>
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
