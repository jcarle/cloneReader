<?php 
class Error extends CI_Controller {
    function __construct() {
        parent::__construct();	
	}  
	
	function index() {
		$this->error404();
	}
	
	function forbidden() {
		if ($this->input->is_ajax_request()) { 
			return $this->load->view('ajax', array(
				'code'		=> false, 
				'result' 	=> 'No tiene permisos para la acción que desea realizar' 
			));
		}		
		
		$this->load->view('includes/template', array(
			'view'			=> 'error', 
			'title'			=> 'Error 403',
			'message'		=> 'No tiene permisos para la acción que desea realizar'
		));
	}	
	
	function error404() {
		if ($this->input->is_ajax_request()) { 
			return $this->load->view('ajax', array(
				'code'		=> false, 
				'result' 	=> 'La página solicitada no existe' 
			));
		}		
		
		$this->load->view('includes/template', array(
			'view'			=> 'error', 
			'title'			=> 'Error 404',
			'message'		=> 'La página solicitada no existe'
		));
	}		
}
