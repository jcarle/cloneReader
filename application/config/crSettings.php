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