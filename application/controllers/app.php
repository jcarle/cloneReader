<?php 
class App extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
	}
	
	function index() {
		// TODO: implementar seguridad ?
		
		$this->load->view('app');
	}
	

}
