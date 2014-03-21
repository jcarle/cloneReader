<?php 
class Logout extends CI_Controller {

	function __construct() {
		parent::__construct();	
	}
	
	function index() {
		$this->session->sess_destroy();
		
		if ($this->input->get('appType') == 'ajax') {
			return loadViewAjax(true, array('goToUrl' => base_url('login')));
		}
				
		redirect('login');
	}
}
