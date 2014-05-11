<?php
function array_to_select() {
	$CI = &get_instance();

	$args = func_get_args();

	$return = array();

	if ($CI->input->get('pageJson') == true) {
		switch(count($args)) {
			case 3 :
				foreach ($args[0] as $itteration) {
					$return[] = array('key' => $itteration[$args[1]], 'value' => $itteration[$args[2]]);
				}
				return $return;
			case 2 :
				foreach ($args[0] as $itteration) {
					$return[] = $itteration[$args[1]];
				}
				return $return;
		}
	}	
	
	switch(count($args)) :

		case 3 :
			foreach ($args[0] as $itteration) :
				if (is_object($itteration))
					$itteration = (array)$itteration;
				$return[$itteration[$args[1]]] = $itteration[$args[2]];
			endforeach;
			break;

		case 2 :
			foreach ($args[0] as $key => $itteration) :
				if (is_object($itteration))
					$itteration = (array)$itteration;
				$return[$key] = $itteration[$args[1]];
			endforeach;
			break;

		case 1 :
			foreach ($args[0] as $itteration) :
				$return[$itteration] = $itteration;
			endforeach;
			break;

		default :
			return FALSE;
			break;
	endswitch;

	return $return;
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
