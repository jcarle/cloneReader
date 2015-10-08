<?php
/**
 * Convierte un sourde del tipo array('id' => '', 'text' => '') en un array valido para los dropdown de CI
 */
function sourceToDropdown($source, $appendNullOption) {
	$CI    = &get_instance();
	$data  = array();

	if ($appendNullOption == true) {
		$data[''] = '-- '.lang('Choose').' --';
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
	$data = array();
	foreach ($source as $item) {
		$data[] = $item[$fieldName];
	}
	return $data;
}


/*
 * Armado de los title, h1, metaDescription y metaKeywords
 * Los meta se setean en los controller
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
				$meta[$key] = lang($metaConfig[$controller][$key]);
			}
			else {
				if ($key == 'h1' && isset($meta['title'])) { // Si falta h1 y existe title, asigno ese valor
					$meta['h1'] = $meta['title'];
				}
				else {
					$meta[$key] = lang($value);
				}
			}
		}
	}

	return $meta;
}

/**
 * Revisa las variables de la vista, y si no existe devuelve un breadcrumb por defecto
 * 		array(
 *			array('text' => 'Home',  'href' => base_url()),
 *			array('text' => 'About', 'active' => true )
 *		);
 *
 * @param $breadcrumb
 * @param $meta              array meta, se utiliza el title para el breadcrumb por defecto
 * @param $skipBreadcrumb    para forzar que no muestre el breadcrumb
 * @return array
 */
function getBreadcrumb(array $breadcrumb, array $meta, $skipBreadcrumb = false) {
	$CI = &get_instance();

	if ($skipBreadcrumb == true) {
		return array();
	}
	if (!empty($breadcrumb)) {
		return $breadcrumb;
	}

	$breadcrumb = array(
		array('text' => lang('Home'),    'href' => base_url()),
		array('text' => $meta['title'],  'active' => true )
	);

	return $breadcrumb;
}

function array_diff_recursive($array1, $array2) {
	$difference=array();
	foreach($array1 as $key => $value) {
		if (is_numeric($key)){
			if (!in_array($value, $array2)) {
				$difference[] = $value;
			}
		}
		else {
			if( is_array($value) ) {
				if( !isset($array2[$key]) || !is_array($array2[$key]) ) {
					$difference[$key] = $value;
				} else {
					$new_diff = array_diff_recursive($value, $array2[$key]);
					if( !empty($new_diff) )
					$difference[$key] = $new_diff;
				}
			} else if( !array_key_exists($key,$array2) || $array2[$key] !== $value ) {
				$difference[$key] = $value;
			}
		}
	}
	return $difference;
}
