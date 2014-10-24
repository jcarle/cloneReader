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
		
		if ($this->input->is_cli_request()) {
			echo date("Y-m-d H:i:s").' scan feed '.$feedId."\n";
		}

		$this->Feeds_Model->scanFeed($feedId);
		$this->Feeds_Model->updateFeedCounts($feedId);
		
		$this->db->trans_complete();
	}
	
	function deleteOldEntries($feedId = null) {
		$this->Feeds_Model->deleteOldEntries($feedId);
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function processFeedsTags() {
		$this->Feeds_Model->processFeedsTags();
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function saveFeedsSearch() {
		$this->clearEntitySearch( array(config_item('entityTypeFeed')));

		$searchKey = ' searchFeeds ';
		$query = 'INSERT INTO entities_search
			(entityTypeId, entityId, entitySearch, entityTree, entityReverseTree)
			SELECT '.config_item('entityTypeFeed').', feedId, CONCAT(IF(statusId = '.config_item('feedStatusApproved').', \' feedStatusApproved \', \'\'),  \''.$searchKey.'\', feedName), FeedName, FeedName
			FROM feeds  ';
		$this->db->query($query);
		//pr($this->db->last_query()); die;

		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}
	
	function saveTagsSearch() {
		$this->clearEntitySearch( array(config_item('entityTypeTag')));

		$searchKey = ' searchTags ';
		$query = 'INSERT INTO entities_search
			(entityTypeId, entityId, entitySearch, entityTree, entityReverseTree)
			SELECT DISTINCT '.config_item('entityTypeTag').', tags.tagId, CONCAT( IF(feedId IS NOT NULL, \' tagHasFeed \', \'\'), \''.$searchKey.'\', tagName), tagName, tagName
			FROM tags
			LEFT JOIN feeds_tags ON feeds_tags.tagId = tags.tagId
			WHERE tags.tagId NOT IN ( '.config_item('tagAll').', '.config_item('tagStar').', '.config_item('tagHome').', '.config_item('tagBrowse').' )  ';
		$this->db->query($query);
		//pr($this->db->last_query()); die;

		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully')));
	}	
	
	function clearEntitySearch( array $aEntityTypeId) {
		$this->db
			->where_in('entityTypeId', $aEntityTypeId)
			->delete('entities_search');
	}
}
