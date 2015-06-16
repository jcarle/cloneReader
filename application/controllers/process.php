<?php 
class Process extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		if (!$this->input->is_cli_request()) {
			if (!$this->safety->allowByControllerName(__CLASS__)) { 
				throw new Exception(' Not Found');
			}
		}

		$this->output->enable_profiler(false);
		$this->db->save_queries = false;

		set_time_limit(0);
		ini_set('memory_limit', '512M');
	}
	
	function index() {
		if (! $this->safety->allowByControllerName(__CLASS__) ) { return errorForbidden(); }

		$this->load->view('pageHtml', array(
			'view'       => 'process',
			'meta'       => array( 'title' => $this->lang->line('Process') ),
		));
	}
	

	function scanAllFeeds($userId = null) {
		$this->load->model(array('Feeds_Model'));
		$this->Feeds_Model->scanAllFeeds($userId);
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function rescanAll404Feeds() {
		$this->load->model(array('Feeds_Model'));
		$this->Feeds_Model->scanAllFeeds(null, null, true);
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function scanFeed($feedId) {
		$this->load->model(array('Feeds_Model'));
		$this->db->trans_start();
		
		if ($this->input->is_cli_request()) {
			echo date("Y-m-d H:i:s").' scan feed '.$feedId."\n";
		}

		$this->Feeds_Model->scanFeed($feedId);
		$this->Feeds_Model->updateFeedCounts($feedId);
		
		$this->db->trans_complete();
	}
	
	function deleteOldEntries($feedId = null) {
		$this->load->model('Feeds_Model');
		$this->Feeds_Model->deleteOldEntries($feedId);
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function processFeedsTags() {
		$this->load->model(array('Feeds_Model'));
		$this->Feeds_Model->processFeedsTags();
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}

	function saveEntitiesSearch($entityTypeId = null, $onlyUpdates = false) {
		$onlyUpdates        = ($onlyUpdates == 'true');
		$deleteEntitySearch = ($onlyUpdates != true);
		if ($entityTypeId == 'null') {
			$entityTypeId = null;
		}
		
		if ($entityTypeId == null || $entityTypeId == config_item('entityTypeUser')) {
			$this->load->model('Users_Model');
			$this->Users_Model->saveUsersSearch($deleteEntitySearch, $onlyUpdates);
		}
		if ($entityTypeId == null || $entityTypeId == config_item('entityTypeTag')) {
			$this->load->model('Tags_Model');
			$this->Tags_Model->saveTagsSearch($deleteEntitySearch, $onlyUpdates);
		}
		if ($entityTypeId == null || $entityTypeId == config_item('entityTypeFeed')) {
			$this->load->model('Feeds_Model');
			$this->Feeds_Model->saveFeedsSearch($deleteEntitySearch, $onlyUpdates);
		}
		if ($entityTypeId == null || $entityTypeId == config_item('entityTypeEntry')) {
			$this->load->model('Entries_Model');
			$this->Entries_Model->saveEntriesSearch($deleteEntitySearch, $onlyUpdates);
		}
		if ($entityTypeId == null && $onlyUpdates == false) {
			$this->optimezeTableEntitiesSearch();
		}

		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}

	function optimezeTableEntitiesSearch() {
		$this->load->dbutil();
		$this->dbutil->optimize_table('entities_search');
	}
}
