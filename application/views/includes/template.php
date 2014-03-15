<?php
// TODO: renombrar esta vista para que tenga un name mas claro; 
// 			se encarga de cargar la vista correspondiente segun el tipo de page [appAjax, appMobile, webSite]

$CI = &get_instance();

//sleep(5);

if ($CI->input->get('appType') == 'ajax') {
	$result = array();
	
	if (isset($aJs)) {
		$result['aJs'] = $aJs;
	}
	 
	switch ($view) {
		case 'includes/crList':
			$result['title'] 	= $title;
			$result['js']		= 'crList';
			$result 			= array_merge($list, $result);
			break;
		case 'includes/crForm':
			$result['title']	= $title;
			$result['js']		= 'crForm';
			$result 			= array_merge($form, $result);
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
}



$this->load->view('includes/header');
$this->load->view($view);
$this->load->view('includes/footer');
