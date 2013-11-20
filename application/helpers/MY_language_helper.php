<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Save all lines into a json data for use in javascript
 *
 * @param array $lines array('line1, 'line2', ..., 'lineN')
 * @author porquero
 * @link porquero.blogspot.com
 *
 * @return string
 */
function langJs($lines)
{
	$CI = & get_instance();
	$json = array();

	foreach ((array)$lines as $line) {
		$json[$line] = $CI->lang->line($line);
	}

	$json = json_encode($json);
	$script = 'var _msg = ' . $json . ';' . "\n";

	return $script;

}

function initLang() {
	$CI = &get_instance();
	
	$languages = array(
		'en' => 'english',
		'es' => 'spanish',
		'de' => 'deutsch'
	);
	
	$langId = $CI->session->userdata('langId');
	if ($langId === false) {
		$langId = config_item('langId'); 
	}
	
	$langName = element($langId, $languages, config_item('langId'));
	$CI->config->set_item('language', $langName);

	$CI->session->set_userdata('langId', $langId);
	$CI->lang->load('default', $langName);	
	$CI->lang->load(config_item('siteId'), $langName);	
}	
