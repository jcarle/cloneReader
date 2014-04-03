<?php
$CI = &get_instance();

//sleep(5);

if ($CI->input->get('appType') == 'ajax') {
	return 	$this->load->view('pageJson');
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
