<?php 
class Process extends CI_Controller {
	function __construct() {
		parent::__construct();	
		
		if (!$this->input->is_cli_request()) {
			if (!$this->safety->allowByControllerName(__CLASS__)) { 
				throw new Exception(' Not Found');
			}
		}
		
		set_time_limit(0);
		ini_set('memory_limit', '512M');
	}
	
	function index() {
		if (! $this->safety->allowByControllerName(__CLASS__) ) { return errorForbidden(); }

		$this->load->view('pageHtml', array(
			'view'       => 'process',
			'meta'       => array( 'title' => $this->lang->line('Process') ),
			'breadcrumb' => array(
				array('text' => $this->lang->line('Home'),      'href' => base_url()),
				array('text' => $this->lang->line('Process'),   'active' => true ) 
			)
		));
	}
	

	function getNewsEntries($userId = null) {
		$this->load->model(array('Entries_Model'));
		
		$this->Entries_Model->getNewsEntries($userId);
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function rescanFeeds404() {
		set_time_limit(0);
		
		$this->db
			->select(' DISTINCT feeds.feedId, feedUrl, feedLink, feedIcon, fixLocale', false)
			->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner')
			->where('feeds.statusId', config_item('feedStatusNotFound'));

		$query = $this->db->get('feeds');
		//vd($this->db->last_query()); 
		$count = 0;
		foreach ($query->result() as $row) {
			exec('nohup '.PHP_PATH.'  '.BASEPATH.'../index.php feeds/scanFeed/'.(int)$row->feedId.' >> '.BASEPATH.'../application/logs/scanFeed.log &');
			
			$count++;
			if ($count % 40 == 0) {
				sleep(10);
			}
		}
	}
}
