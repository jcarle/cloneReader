<?php 
class Logout extends CI_Controller {

    function __construct() {
        parent::__construct();	
		
	}
	
	function index() {
		$this->session->sess_destroy();
				
		redirect('');
	}
}
