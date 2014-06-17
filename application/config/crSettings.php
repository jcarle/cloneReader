<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config['urlSecretTime'] = 180;// Cuantos MINUTOS esta vivo el link para resetear password y cambiar email
$config['pageSize'] =	15;
$config['defaultCurrencyId'] = 1;
$config['defaultCurrencyName'] = 'AR$';
$config['autocompleteSize'] =	50;
$config['siteLogo'] = array('w' => 151, 'h' => 39);
$config['hasRss'] = true;

$config['siteName'] = 'cloneReader BETA';
$config['siteId'] = 'cloneReader';
$config['emailFrom'] = 'clonereader@gmail.com';
$config['google-analytics-Account'] = 'UA-41589815-1';

$config['entityTypeTesting'] = 1;

$config['entitiesType'] = array(
	$config['entityTypeTesting'] => 'testing',
);

/**
 * Default gallery config 
 */
$config['gallery']['default'] = array(
	'controller'    => '%s/edit',
	'folder'        => '/assets/images/%s/original/',
	'allowed_types' => 'gif|jpg|png',
	'max_size'      => 1024 * 8,
	'sizes'         => array(
		'thumb' => array( 'width' => 150,  'height' => 100, 'folder' => '/assets/images/%s/thumb/' ),
		'large' => array( 'width' => 1024, 'height' => 660, 'folder' => '/assets/images/%s/large/' ),
	)
);

// upload test picture
$config['testPicture'] = array(
	'folder'        => '/assets/images/testing/logos/original/',
	'allowed_types' => 'gif|jpg|png',
	'max_size'      => 1024 * 8,
	'sizes'         => array(
		'thumb' => array( 'width' => 150,  'height' => 150, 'folder' => '/assets/images/testing/logos/thumb/' ),
	)
);

// upload test doc
$config['testDoc'] = array(
	'folder'        => '/assets/files/testing/',
	'allowed_types' => 'txt|pdf',
	'max_size'      => 1024 * 8,
);

/**
 * Js y Css adicionales para que sea compilado con carabiner
 */
$config['siteAssets'] = array(
	'js' => array(
		'feeds.js',
		'cloneReader.js', 
		'jquery.visible.min.js',
	),
	'css' => array()
);

/*
*	Metas por default, sino existe el meta en la vista
*	busca en este array por controller/method y carga ese texto como default
*   Los textos que se carguen en este config no hace falta traduccion,
*   luego el header se va a encargar de pasarlo por traduccion.
*/

$config['meta'] = array(
	'home/index' => array(
		'description'  => 'Clone Reader. Clone of google reader. Reader of feeds, rss news. Open source',
		'keyword'      => 'cReader cloneReader news feeds rss reader open source',	
	)
);

$config['tagAll'] = 1;
$config['tagStar'] = 2;
$config['tagHome'] = 3;
$config['tagBrowse'] = 4;

$config['feedMaxCount'] = 1000;

$config['feedCloneReader'] = 1633; 	// 	id del feed propio, se muestra en el filtro 'home'
$config['feedStatusPending'] = 0; 	
$config['feedStatusApproved'] = 1;
$config['feedStatusInvalidFormat"'] = 3;
$config['feedStatusNotFound'] = 404;

$config['feedMaxRetries'] = 10; // maxima cantidad de reintentos si un feed tiene algun error 

$config['feedTimeScan'] = 180; 	// 	Cada cuanto MINUTOS busca nuevos feeds
$config['feedTimeSave'] = 10; 	// 	Cada cuanto SEGUNDOS guardan los datos
$config['feedTimeReload'] = 9999;  //  Cada cuanto MINUTOS recarga el menu con feeds
$config['entriesPageSize'] = 30;

