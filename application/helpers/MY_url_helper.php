<?php

/**
 * // TODO: documentar ! y mover de aca tambien!
 *
 * */
function createSefUrl($base_url, $sefsOrder, $currentFilters, $entityTypeId, $entitySef) {

	$remove = false;
	if (isset($currentFilters[$entityTypeId])) {
		if ($currentFilters[$entityTypeId]['entitySef'] == $entitySef) {
			$remove = true;
		}
	}

	$currentFilters[$entityTypeId] = array('entityTypeId' => $entityTypeId, 'entitySef' => $entitySef);
	if ($remove == true) {
		unset($currentFilters[$entityTypeId]);
		if ($entityTypeId == config_item('entityTypeCountry')) {
			unset($currentFilters[config_item('entityTypeState')]);
			unset($currentFilters[config_item('entityTypeCity')]);
			unset($currentFilters[config_item('entityTypePlace')]);
		}
		if ($entityTypeId == config_item('entityTypeState')) {
			unset($currentFilters[config_item('entityTypeCity')]);
		}
	}

	$sefs = array($base_url);
	foreach ($sefsOrder as $entityTypeId) {
		if (isset($currentFilters[$entityTypeId])) {
			$sefs[] = $currentFilters[$entityTypeId]['entitySef'];
		}
	}

	return base_url(implode('/', $sefs));
}

/**
 * Setea todos los js's y css's en carabiner
 */
function appendFilesToCarabiner() {
	$CI = &get_instance();

	$aJs = array(
		'jquery-1.7.2.js',
		//'jquery-1.11.0.js',
		'jquery.json-2.3.js',
		'jquery.printf.js',
		'jquery.url.js',
//		'jquery.dotdotdot.js',
		'jquery.actual.js',

		'moment-with-langs.js',
		'bootstrap.js',
		'crFunctions.js',
		'crMain.js',
		'crLang.js',
		'crAlert.js',
		'crMenu.js',
		'feedback.js',
		'profile.js',
		'process.js',

		'crForm.js',
		'jquery.raty.js',
		'select2.js',
		'autoNumeric.js',
		'bootstrap-datetimepicker.min.js',

		'tmpl.min.js',
		'jquery.ui.widget.js',
		'jquery.fileupload.js',
		'jquery.fileupload-ui.js',
		'jquery.fileupload-process.js',
		'jquery.ui.widget.js',
		'jquery.imgCenter.js',
		'blueimp-gallery.js',

		'crList.js',
		'bootstrap-paginator.js',
		'jquery.highlight.js',
	);

	$aCss = array(
		'bootstrap.css',
//		'bootstrap-theme.css',
		'bootstrap-social.css',
		'font-awesome.css',

		'select2.css',
		'select2-bootstrap.css',
		'bootstrap-datetimepicker.css',
		'blueimp-gallery.css',
		'jquery.fileupload-ui.css',

		'default.css',
	);

	if ($CI->session->userdata('langId') != 'en') {
		$aTmp = explode('-', $CI->session->userdata('langId'));
		if (count($aTmp) == 2) {
			$aJs[] = 'select2/select2_locale_'.$aTmp[0].'-'.strtoupper($aTmp[1]).'.js';
			$aJs[] = 'datetimepicker/bootstrap-datetimepicker.'.$aTmp[0].'-'.strtoupper($aTmp[1]).'.js';
		}
		else {
			$aJs[] = 'select2/select2_locale_'.$CI->session->userdata('langId').'.js';
			$aJs[] = 'datetimepicker/bootstrap-datetimepicker.'.$CI->session->userdata('langId').'.js';
		}
	}

	$siteAssets = config_item('siteAssets');

	$aJs  = array_merge($aJs, $siteAssets['js']);
	$aCss = array_merge($aCss, $siteAssets['css']);

	foreach ($aJs as $js) {
		$CI->carabiner->js($js);
	}
	foreach ($aCss as $css) {
		$CI->carabiner->css($css);
	}
}

function errorForbidden($forceJson = false) {
	$CI = &get_instance();
	$CI->load->library('../controllers/error');
	initLang(); // FIXME: revisar; se esta vaciando la session al hacer el redirect
	$CI->safety->initSession();
	$CI->error->forbidden($forceJson);
}

function error404($forceJson = false) {
	$CI = &get_instance();
	$CI->load->library('../controllers/error');
	$CI->error->error404($forceJson);
}


function popupLogin($onLoginUrl = null) {
	$CI = &get_instance();
	$CI->load->library('../controllers/login');

	if ($onLoginUrl == null) {
		$onLoginUrl = uri_string();
	}
	$CI->session->set_userdata(array( 'onLoginUrl'  => $onLoginUrl ));
	$CI->login->popupLogin();
}

function loadViewAjax($code, $result = null) {
	$CI = &get_instance();

	if ($result == null) {
		$result = array();
	}
	if ($code != true && is_array($result)) {
		$result['formErrors'] = validation_array_errors();
	}

	return $CI->load->view('json', array(
		'code'   => $code,
		'result' => $result,
	));
}

function getPageName() {
	$aTmp        = explode('/', uri_string());
	$controller  = $aTmp[0];
	if (trim($controller) == '') {
		$CI         = &get_instance();
		$controller = $CI->router->default_controller;
	}

	return 'cr-page-' . $controller . (count($aTmp) > 1 ? '-'.$aTmp[1] : '');
}

function getEntityUrl($entityTypeId, $entitySef) {
	$entityConfig = getEntityConfig($entityTypeId);
	if ($entityConfig == null) {
		return $entitySef;
	}

	if (is_array($entitySef)) {
		return base_url(vsprintf($entityConfig['entityUrl'], $entitySef));
	}

	return base_url(sprintf($entityConfig['entityUrl'], $entitySef));
}
