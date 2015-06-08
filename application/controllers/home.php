<?php 
class Home extends CI_Controller {
	public function index() {
		if (uri_string() == 'home') {
			redirect('', 'location', 301);
		}

		$this->load->view('pageHtml',
			array(
				'view'            => 'home', 
				'showTitle'       => false,
				'notRefresh'      => true,
				'skipBreadcrumb'  => true,
			)
		);
	}
}
