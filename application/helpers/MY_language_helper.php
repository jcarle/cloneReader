<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');


function createLangJs() {
	$CI       = & get_instance();
	$fileName = "./assets/cache/".sprintf("language_%s.js", $CI->session->userdata('langId'));

	if (realpath($fileName) !== false) {
		$filemtime = filemtime($fileName);
		foreach ($CI->lang->is_loaded as $fileLang) {
			if ($filemtime < filemtime('./application/language/'.config_item('language').'/'.$fileLang)) {
				@unlink($fileName);
				break;
			}
		}
	}

	if (realpath($fileName) !== false) {
		return;
	}

	$lines  = array_keys($CI->lang->language);
	$aLangs = array();
	foreach ((array)$lines as $line) {
		$aLangs[$line] = lang($line);
	}

	file_put_contents( $fileName, ' crLang.aLangs = '.json_encode($aLangs).'; ' );
}

function initLang() {
	$CI = &get_instance();

	$CI->lang->is_loaded = array();
	$CI->lang->language = array();

	$languages = array(
		'es'      => 'spanish',
		'pt-br'   => 'portuguese-br',
		'en'      => 'english',
		'zh-cn'   => 'zh-CN',
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
	$CI->lang->load(config_item('siteId'), $langName);
}
