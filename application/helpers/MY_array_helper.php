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
	$CI = &get_instance();
	
	return 'cr-page-'.$CI->router->class.($CI->router->method != 'index' ? '-'.$CI->router->method : '');
}

function formatCurrency($value, $currencyName = DEFAULT_CURRENCY_NAME) {
	$CI = &get_instance();
	 
	return $currencyName.' '.number_format($value, 2, $CI->lang->line('NUMBER_DEC_SEP'), $CI->lang->line('NUMBER_THOUSANDS_SEP'));
}
