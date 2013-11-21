<?php
class Feeds_Model extends CI_Model {
	function selectToList($num, $offset, $filter = null, $statusId = null, $countryId = null, $langId = null){
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
		
		$query = $this->db->get('feeds', $num, $offset);
						
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
	
	function search($filter){
		$filter = $this->db->escape_like_str($filter);
		
		return $this->db
			->select('DISTINCT feedId AS id, feedName AS text  ', false)
//			->where('statusId', STATUS_ACTIVE)
			->like('feedName', $filter)
			->order_by('text')
			->get('feeds', AUTOCOMPLETE_SIZE)->result_array();
	}	
	
}
