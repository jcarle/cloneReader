<?php
// TODO: renombrar esta vista para que tenga un name mas claro; 
// 			se encarga de cargar la vista correspondiente segun el tipo de page [appAjax, appMobile, webPage]

$CI = &get_instance();


//sleep(5);
/*
if ($CI->input->is_ajax_request()) {
	switch ($view) {
		case 'includes/crList':
			return $this->load->view('ajax', array(
				'view' 		=> null,
				'code'		=> true, 
				'result' 	=> array_merge($list, 
					array(
						'title'		=> $title,
						'js'		=> 'crList',
					)
				)
			));
		case 'includes/crForm':
			return $this->load->view('ajax', array(
				'view' 		=> null,
				'code'		=> true, 
				'result' 	=> array_merge($form, 
					array(
						'title'		=> $title,
						'js'		=> 'crForm',
					)
				)
			));
	}
}

$this->load->view('app');
*/

$this->load->view('includes/header');
$this->load->view($view);
$this->load->view('includes/footer');
