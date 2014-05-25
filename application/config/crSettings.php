<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$config['urlSecretTime'] = 180;// Cuantos MINUTOS esta vivo el link para resetear password y cambiar email
$config['pageSize'] =	15;
$config['defaultCurrencyId'] = 1;
$config['defaultCurrencyName'] = 'AR$';
$config['autocompleteSize'] =	50;
$config['siteLogo'] = array('w' => 151, 'h' => 39);

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