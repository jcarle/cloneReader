<?php
class Feeds_Model extends CI_Model {
	function selectToList($num, $offset, $filter = null, $statusId = null, $countryId = null, $langId = null, $tagId = null, $userId = null, $feedSuggest = null){
		$this->db
			->select('SQL_CALC_FOUND_ROWS feeds.feedId, feedName, feedDescription, feedUrl, feedLink, statusId, countryName, langName, feedLastScan, feedLastEntryDate', false)
			->join('countries', 'countries.countryId = feeds.countryId', 'left')
			->join('languages', 'languages.langId = feeds.langId', 'left');
			
		if ($filter != null) {
			$this->db->like('feedName', $filter);
		}
		if ($statusId != null) {
			$this->db->where('feeds.statusId', $statusId);
		}
		if ($countryId != null) {
			$this->db->where('feeds.countryId', $countryId);
		}
		if ($langId != null) {
			$this->db->where('feeds.langId', $langId);
		}
		if ($tagId != null) {
			$this->db->join('feeds_tags', 'feeds_tags.feedId = feeds.feedId', 'inner');
			$this->db->where('feeds_tags.tagId', $tagId);
		}
		if ($userId != null) {
			$this->db->join('users_feeds', 'users_feeds.feedId = feeds.feedId', 'inner');
			$this->db->where('users_feeds.userId', $userId);
		}
		if ($feedSuggest == true) {
			$this->db->where('feeds.feedSuggest', true);
		}
		
		$query = $this->db->get('feeds', $num, $offset);
		//pr($this->db->last_query()); die;
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select(){
		return $this->db->get('feeds');
	}

	function get($feedId){
		return $this->db->where('feeds.feedId', $feedId)->get('feeds')->row_array();
	}	
	
	function save($data){
		$feedId = (int)element('feedId', $data);
		
		if (trim($data['feedUrl']) == '') {
			return null;
		}
		
		$values = array(
			'feedUrl' 			=> $data['feedUrl'], 
			'feedDescription' 	=> element('feedDescription', $data),
			'statusId' 			=> FEED_STATUS_PENDING,
			'countryId'			=> element('countryId', $data),
			'langId'			=> element('langId', $data),
			'feedSuggest'		=> (element('feedSuggest', $data) == 'on'),
		);
		
		if (isset($data['feedName'])) {
			$values['feedName'] = $data['feedName'];
		}
		if (isset($data['feedLink'])) {
			$values['feedLink']	= $data['feedLink'];
		}
		

		$query = $this->db->where('feedUrl', $values['feedUrl'])->get('feeds')->result_array();
		//pr($this->db->last_query());
		if (!empty($query)) {
			$feedId = $query[0]['feedId'];
			
			if ((string)$query[0]['feedName'] == '') {
				$values['feedName'] = element('feedLink', $values);
			}
		}
		
		if ((int)$feedId != 0) {
			$this->db
				->where('feedId', $feedId)
				->update('feeds', $values);
		}
		else {
			$this->db->insert('feeds', $values);
			$feedId = $this->db->insert_id();
		}
		//pr($this->db->last_query());

		return $feedId;
	}
	
	function delete($feedId) {
		$this->db->delete('feeds', array('feedId' => $feedId));
		return true;
	}
	
	function scan($feedId) {
		$this->db
			->where('feedId', $feedId)
			->update('feeds', array(
				'feedLastScan' 			=> null,
				'feedLastEntryDate'		=> null, 
				'statusId' 				=> 0,
			));		
			
		$this->load->model('Entries_Model');
		
		$this->Entries_Model->getNewsEntries(null, $feedId);
	}
	
	function search($filter){
		$filter = $this->db->escape_like_str($filter);
		
		return $this->db
			->select('DISTINCT feedId AS id, feedName AS text  ', false)
//			->where('statusId', STATUS_ACTIVE)
			->like('feedName', $filter)
			->order_by('text')
			->get('feeds', AUTOCOMPLETE_SIZE)->result_array();
	}	
	
	
	/**
	 * Busca tags que tengan feeds. 
	 */
	function searchTags($filter){
		$filter = $this->db->escape_like_str($filter);

		return $this->db
			->select('DISTINCT tags.tagId AS id, tagName AS text  ', false)
			->join('feeds_tags', 'feeds_tags.tagId = tags.tagId ', 'inner')
			->like('tagName', $filter)
			->order_by('text')
			->get('tags', AUTOCOMPLETE_SIZE)->result_array();
	}	
}
