<?php
/**
 * Convierte un sourde del tipo array('id' => '', 'text' => '') en un array valido para los dropdown de CI 
 */
function sourceToDropdown($source, $appendNullOption) {
	$CI 	= &get_instance();
	$data 	= array();
	
	if ($appendNullOption == true) {
		$data[''] = '-- '.$CI->lang->line('Choose').' --';
	}

	foreach ($source as $item) {
		$data[$item['id']] = $item['text'];
	}
	return $data;
}

/**
 * Devuelve un array con los values de la key pasada como argumento
 */
function sourceToArray($source, $fieldName) {
	$data 	= array();
	foreach ($source as $item) {
		$data[] = $item[$fieldName];
	}
	return $data;	
}

// TODO: mover esto de aca!
function pr($value) {
	print_r($value);
}

function vd($value) {
	var_dump($value);
}

function errorForbidden() {
	$CI = &get_instance();
	$CI->load->library('../controllers/error');
	$CI->error->forbidden();	
}

function error404() {
	$CI = &get_instance();
	$CI->load->library('../controllers/error');
	$CI->error->error404();	
}

function loadViewAjax($code, $result = null) {
	$CI = &get_instance();
	
	return $CI->load->view('ajax', array(
		'code'		=> $code, 
		'result' 	=> $result != null ? $result : validation_errors() 
	));	
}

function getPageName() {
	$aTmp        = explode('/', uri_string());
	$controller  = $aTmp[0];
	if (trim($controller) == '') {
		$CI         = &get_instance();
		$controller = $CI->router->default_controller;
	}

	return 'cr-page-' . $controller . (count($aTmp) > 1 ? '-'.$aTmp[1] : '');
}

function formatCurrency($value, $currencyName = null) {
	$CI = &get_instance();
	
	if ($currencyName == null) {
		$currencyName = config_item('defaultCurrencyName');
	}
	 
	return $currencyName.' '.number_format($value, 2, $CI->lang->line('NUMBER_DEC_SEP'), $CI->lang->line('NUMBER_THOUSANDS_SEP'));
}

/*
 * Devuelve las porperties de una entidad, se utiliza para definir el upload de archivos, folder, tamaños, etc
 */
function getEntityConfig($entityTypeId) {
	$gallery = config_item('gallery');
	$config  = element($entityTypeId, $gallery);
	if ($config != null) {
		return $config;
	}

	// Si no existe, devuelve las properties por defecto, haciendo un sprintf de los folder y del controller con el name de la entidad
	$entitiesType = config_item('entitiesType');
	$name         = $entitiesType[$entityTypeId];
	$config       = element('default', $gallery);
	
	$config['controller']                = sprintf($config['controller'], $name);
	$config['folder']                    = sprintf($config['folder'], $name);
	$config['sizes']['thumb']['folder']  = sprintf($config['sizes']['thumb']['folder'], $name);
	$config['sizes']['large']['folder']  = sprintf($config['sizes']['large']['folder'], $name);

	return $config;
}


/*
 * Armado de los title, h1, metaDescription y metaKeywords
 * Los meta se setean en los controller de las vistas
 * Ej: $meta = array(
 *			'title'         => 'Home',
 *			'h1'            => 'Home',
 *			'description'   => 'description',
 *			'keywords'      => 'keywords',
 *		);
 * En el config existe un array por defecto que sea ROUTER/METHOD
 * Si el array de la vista esta incompleto se completa con el array por default
 * 
 * Tiene mas peso siempre los textos seteados en los controllers, luego los de crSettings, y luego el meta default
 * Los textos que vienen desde el controller no se traducen, los que demas si.
 * Si falta h1 y existe title, asigno ese valor
 */
function getMetaByController($meta = null, $controller = null) {
	$CI = &get_instance();
	
//	$metaDefault = array();
	if (!is_array($meta)) {
		$meta = array();
	}
	if ($controller == null) {
		$controller = $CI->router->class.($CI->router->method != 'index' ? '/'.$CI->router->method : '');
	}

	//Meta por Default seteados en el config crSettings.php
	$metaConfig     = config_item('meta');
	if (!isset($metaConfig[$controller])) {
		$metaConfig[$controller] = array();
	}
	
	//Busco los textos que le faltan al array de la vista
	foreach($metaConfig['default'] as $key => $value) {
		if(empty($meta[$key])){
			if (isset($metaConfig[$controller][$key])) {
				$meta[$key] = $CI->lang->line($metaConfig[$controller][$key]);
			}
			else {
				if ($key == 'h1' && isset($meta['title'])) { // Si falta h1 y existe title, asigno ese valor
					$meta['h1'] = $meta['title'];
				}
				else {
					$meta[$key] = $CI->lang->line($value);
				}
			}
		}
	}

	return $meta;
}
