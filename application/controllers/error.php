<?php 
class Error extends CI_Controller {
	function __construct() {
		parent::__construct();	
	}
	
	function index() {
		$this->error404();
	}
	
	function forbidden() {
		if ($this->input->is_ajax_request() && $this->input->get('pageJson') != true) {
			return $this->load->view('ajax', array(
				'code'			=> false, 
				'result' 		=> $this->lang->line('Not authorized for the action to take'),
				'status_code'	=> 403
			));
		}		
		
		$this->load->view('pageHtml', array(
			'view'			=> 'error', 
			'meta'          => array(
				'title'			=> 'Error 403',
				'h1'			=> $this->lang->line('Error 403'),
			),
			'message'		=> $this->lang->line('Not authorized for the action to take'),
			'status_code'	=> 403
		));
	}	
	
	function error404() {
		if ($this->input->is_ajax_request() && $this->input->get('pageJson') != true) {
			return $this->load->view('ajax', array(
				'code'			=> false, 
				'result'	 	=> $this->lang->line('The page you requested does not exist'),
				'status_code'	=> 404 
			));
		}		
		
		$this->load->view('pageHtml', array(
			'view'			=> 'error', 
			'meta'			=> array(
				'title'			=> $this->lang->line('Error 404'),
				'h1'			=> $this->lang->line('Error 404'),
			),
			'message'		=> $this->lang->line('The page you requested does not exist'),
			'status_code'	=> 404
		));
	}
}
