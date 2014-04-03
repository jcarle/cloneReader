<?php
// TODO: renombrar esta vista para que tenga un name mas claro; 
// 			se encarga de cargar la vista correspondiente segun el tipo de page [appAjax, appMobile, webSite]

$CI = &get_instance();

// TODO: hacer dos templates que es llamen a jsonPage y htmlPage

//sleep(5);

if ($CI->input->get('appType') == 'ajax') {
	
	return 	$this->load->view('pageJson');
	
	/*
	$result = array('pageName' => getPageName());
	
	if (isset($hasGallery)) {
		$result['hasGallery'] = $hasGallery;
	}
	
	if (isset($notRefresh)) {
		$result['notRefresh'] = $notRefresh;
	}
	
	switch ($view) {
		case 'includes/crList':
			$result['title'] 	= $title;
			$result['js']		= 'crList';
			$result['list']		= $list;
			break;
		case 'includes/crForm':
			$form  = appendMessagesToCrForm($form);
			$result['title']	= $title;
			$result['js']		= 'crForm';
			$result['form'] 	= $form;
			if (getCrFieldGallery($form) != null) { 
				$result['hasGallery'] = true;
			}
			break;
		default: 
			$result['title']	= $title;
			$result['html'] 	= $this->load->view($view, '', true); 
	}
	return 	$this->load->view('ajax', array(
		'view' 		=> null,
		'code'		=> true,
		'result'	=> $result
	));
	 * 
	 */ 
}


if (!isset($hasGallery)) {
	$hasGallery = false;
}
if ($hasGallery == false && isset($form)) {	
	$hasGallery = (getCrFieldGallery($form) != null);
}
		


$this->load->view('includes/header');
$this->load->view($view);

if ($hasGallery == true) {
	$this->load->view('includes/uploadfile');
}

$this->load->view('includes/footer');
