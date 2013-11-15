<?php 
class Langs extends CI_Controller {
	function __construct() {
		parent::__construct();	

	}  
	
	function index() { }
	
	function change($langId) {
		// TODO: guardar el idioma en la db
		$this->session->set_userdata('langId', $langId);

		$this->load->library('user_agent');
		
		// TODO: mejorar esto, para que redirija a un controles, no a una url completa; ej: http://jcarle.redirectme.net/dev/jcarle/cloneReader/login
		if ($this->agent->is_referral()) {
			redirect($this->agent->referrer());
		}
		else {
			redirect('');
		}
	}
}
