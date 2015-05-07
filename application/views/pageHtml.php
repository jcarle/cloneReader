<?php
$CI = &get_instance();

//sleep(5);

if ($CI->input->get('pageJson') == true) {
	return $this->load->view('pageJson');
}

$this->load->view('includes/header');
$this->load->view($view);
$this->load->view('includes/footer');
