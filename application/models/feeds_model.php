<?php
class Feeds_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db
			->select('SQL_CALC_FOUND_ROWS feeds.feedId, feedName, feedUrl, feedLink', false)
			->like('feedName', $filter)
		 	->get('feeds', $num, $offset);
						
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
			vd($data);
			return null;
		}
		
		$values = array('feedUrl' => $data['feedUrl']);
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
			->select('DISTINCT feedId AS id, feedName AS value  ', false)
//			->where('statusId', STATUS_ACTIVE)
			->like('feedName', $filter)
			->get('feeds', AUTOCOMPLETE_SIZE)->result_array();
	}	
	
}
