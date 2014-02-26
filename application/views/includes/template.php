<?php
$CI = &get_instance();


//sleep(5);

if ($CI->input->is_ajax_request()) {
	if ($view == 'includes/crList') {
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
	}
}

$this->load->view('app');

/*
$this->load->view('includes/header');
$this->load->view($view);
$this->load->view('includes/footer');
*/