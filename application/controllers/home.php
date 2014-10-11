<?php 
class Home extends CI_Controller {
	public function index() {
		$this->load->model('Users_Model');

		$this->load->view('pageHtml',
			array(
				'view'          => 'home', 
				'userFilters'   => $this->Users_Model->getUserFiltersByUserId( $this->session->userdata('userId') ),
				'showTitle'     => false,
				'notRefresh'    => true,
			)
		);
	}
}
