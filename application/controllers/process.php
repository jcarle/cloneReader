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
		
		$this->load->model(array('Feeds_Model'));
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
	

	function scanAllFeeds($userId = null) {
		$this->Feeds_Model->scanAllFeeds($userId);
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function rescanAll404Feeds() {
		$this->Feeds_Model->scanAllFeeds(null, null, true);
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function scanFeed($feedId) {
		$this->db->trans_start();

		$this->Feeds_Model->scanFeed($feedId);
		$this->Feeds_Model->updateFeedCounts($feedId);
		
		$this->db->trans_complete();
	}
	
	function deleteOldEntries() {
		$this->Feeds_Model->deleteOldEntries();
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function processFeedsTags() {
		$this->Feeds_Model->processFeedsTags();
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}	
}
