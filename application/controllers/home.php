<?php 
class Home extends CI_Controller {
	public function index() {
		$this->load->model(array('Entries_Model', 'Users_Model'));

		$this->load->view('includes/template', 
			array(
				'view'			=> 'home', 
				'title'			=> 'cloneReader',
				'aJs'			=> array('cloneReader.js', 'moment.min.js', 'jquery.visible.min.js', 'jquery.nicescroll.min.js'),
				'aCss'			=> array('cloneReader.css'),
				'userFilters'	=> $this->Users_Model->getUserFiltersByUserId( $this->session->userdata('userId') )
			)
		);
	}
}
