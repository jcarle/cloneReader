<?php
$CI = &get_instance();


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
