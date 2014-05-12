<?php 
class About extends CI_Controller {

	function __construct() {
		parent::__construct();	
	}
	
	function index() {
		if (! $this->safety->allowByControllerName('about') ) { return errorForbidden(); }

		$this->load->view('pageHtml', array(
			'view'			=> 'about',
			'title'			=> $this->lang->line('About of cloneReader'),
			'code'			=> true
		));
	}
}
