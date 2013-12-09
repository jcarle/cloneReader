<?php 
class Langs extends CI_Controller {
	function __construct() {
		parent::__construct();	

	}
	
	function index() { }
	
	function change($langId) {
		$this->load->model('Users_Model');
				

		$this->session->set_userdata('langId', $langId);
		
		// No guardo el idioma del usuario anonimo
		if ($this->session->userdata('userId') !== USER_ANONYMOUS) {
			$this->Users_Model->updateLangIdByUserId($langId, $this->session->userdata('userId'));
		}

		$this->load->library('user_agent');
		
		// TODO: mejorar esto, para que redirija a un controler, no a una url completa; ej: http://jcarle.redirectme.net/dev/jcarle/cloneReader/login
		if ($this->agent->is_referral()) {
			redirect($this->agent->referrer());
		}
		else {
			redirect('');
		}
	}
}
