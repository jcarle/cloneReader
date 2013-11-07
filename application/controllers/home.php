<?php 
class Home extends CI_Controller {
	public function index() {
		$this->load->model(array('Entries_Model', 'Users_Model'));

		$this->load->view('includes/template', 
			array(
				'view'			=> 'home', 
				'title'			=> 'News reader and feeds',
				'aJs'			=> array('cloneReader.js', 'jquery.visible.min.js' 
				, 'jquery.applink.js'
				),
				'userFilters'	=> $this->Users_Model->getUserFiltersByUserId( $this->session->userdata('userId') )
			)
		);
	}
}
