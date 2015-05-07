<?php
/**
 * Convierte un sourde del tipo array('id' => '', 'text' => '') en un array valido para los dropdown de CI 
 */
function sourceToDropdown($source, $appendNullOption) {
	$CI    = &get_instance();
	$data  = array();
	
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
	$data = array();
	foreach ($source as $item) {
		$data[] = $item[$fieldName];
	}
	return $data;
}

/*
 * Devuelve las porperties de una entidad, se utiliza para definir el upload de archivos, folder, tamaños, etc
 */
function getEntityConfig($entityTypeId, $key = null) {
	$entityConfig = config_item('entityConfig');
	$entityConfig  = element($entityTypeId, $entityConfig);
	if ($entityConfig != null) {
		if ($key != null) {
			return $entityConfig[$key];
		}
		return $entityConfig;
	}

	return null;
}

/**
 * @return $entityTypeId 
 * 
 * */
function getEntityTypeIdByEnityTypeName($entityTypeName) {
	$entities = config_item('entityConfig');
	// TODO: pensar si conviene indexar el entityTypeName, para que no tenga que recorrerlo
	foreach ($entities as $entityTypeId => $entityConfig) {
		if ($entityConfig['entityTypeName'] == $entityTypeName) {
			return $entityTypeId;
		}
	}
	return null;
}

/**
 * Devuelve el config de una gallery, si no esta definida usa la gallery por default
 */
function getEntityGalleryConfig($entityTypeId) {
	$config   = getEntityConfig($entityTypeId);
	$gallery  = element('gallery', $config);
	if ($gallery != null) {
		return $gallery;
	}

	// Si no existe, devuelve las properties por defecto, haciendo un sprintf de los folder y del controller con el name de la entidad
	$entityConfig   = config_item('entityConfig');
	$galleryDefault = $entityConfig['default']['gallery'];
	$entityTypeName = $entityConfig[$entityTypeId]['entityTypeName'];
	
	$galleryDefault['controller']                = sprintf($galleryDefault['controller'], $entityTypeName);
	$galleryDefault['folder']                    = sprintf($galleryDefault['folder'], $entityTypeName);
	$galleryDefault['sizes']['thumb']['folder']  = sprintf($galleryDefault['sizes']['thumb']['folder'], $entityTypeName);
	$galleryDefault['sizes']['large']['folder']  = sprintf($galleryDefault['sizes']['large']['folder'], $entityTypeName);

	return $galleryDefault;
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
		array('text' => $CI->lang->line('Home'),    'href' => base_url()),
		array('text' => $meta['title'],             'active' => true ) 
	);
	
	return $breadcrumb;	
}

