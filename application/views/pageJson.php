<?php
$CI = &get_instance();


$result = array('pageName' => getPageName());

if (isset($hasUploadFile)) {
	$result['hasUploadFile'] = $hasUploadFile;
}
if (isset($notRefresh)) {
	$result['notRefresh'] = $notRefresh;
}
if (isset($showTitle)) {
	$result['showTitle'] = $showTitle;
}
if (isset($breadcrumb)) {
	$result['breadcrumb'] = $breadcrumb;
}
if (!isset($meta)) {
	$meta = array();
}

switch ($view) {
	case 'includes/crList':
		$result['title'] 	= element('title', $meta);
		$result['js']		= 'crList';
		$result['list']		= $list;
		break;
	case 'includes/crForm':
		$form  = appendMessagesToCrForm($form);
		$result['title']	= element('title', $meta);
		$result['js']		= 'crForm';
		$result['form'] 	= $form;
		if (hasCrUploadFile($form) == true) { 
			$result['hasUploadFile'] = true;
		}
		break;
	default: 
		$result['title']	= element('title', $meta);
		$result['html'] 	= $this->load->view($view, '', true); 
}


return 	$this->load->view('ajax', array(
	'view' 		=> null,
	'code'		=> true,
	'result'	=> $result
)); 
