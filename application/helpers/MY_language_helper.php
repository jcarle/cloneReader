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

/**
 * langs que se utilizan en js
 */
 // TODO: renombrar!
function getLangToJs($langs) {
	$langs[] = 'DATE_FORMAT';
	$langs[] = 'MOMENT_DATE_FORMAT';
	$langs[] = 'NUMBER_DEC_SEP';
	$langs[] = 'NUMBER_THOUSANDS_SEP';
	$langs[] = 'Ok';
	$langs[] = 'Cancel';
	$langs[] = 'Close';
	$langs[] = 'Add';
	$langs[] = 'Delete';
	$langs[] = 'Save';
	$langs[] = 'Retry';
	$langs[] = 'Are you sure?';
	$langs[] = 'Not connected. Please verify your network connection';
	$langs[] = 'No results';
	return $langs;	
}

function initLang() {
	$CI = &get_instance();
	
	$languages = array(
		'es'	 	=> 'spanish',
		'pt-br' 	=> 'portuguese-br',
		'en' 		=> 'english',		
		'zh-tw'		=> 'zh_tw',
	);
	
	$langId = $CI->session->userdata('langId');
	if ($langId === false) {
		$CI->load->library('user_agent');
		foreach ($languages as $key => $value) { // Trato de setear el idioma del browser
			if ($CI->agent->accept_lang($key)) {
				$langId = $key;
				break;
			}
		}
		
		if ($langId === false) {
			$langId = config_item('langId');
		} 
	}
	
	if (!in_array($langId, array_keys($languages))) {
		$langId = config_item('langId');
	}

	$langName = element($langId, $languages, config_item('langId'));

	$CI->config->set_item('language', $langName);
	$CI->config->set_item('langId', $langId);


	$CI->session->set_userdata('langId', $langId);
	$CI->lang->load('default', $langName);	
	$CI->lang->load(SITE_ID, $langName);	
}	
