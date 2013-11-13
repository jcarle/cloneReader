<?php 
class Rss extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('News_Model');
	}
	
	function index() {
		// Es publico, evito la peticion a la db
//		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		
		
		$this->load->view('rss', array(
			'feedTitle'		=> 'cloneReader',
			'feedDesc'		=> 'news of cloneReader',
			'title'			=> 'Edit Feeds',
			'news'			=> $this->News_Model->selectToRss()->result_array()
		));
	}
	

}
