<?php
class Feeds_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS feeds.feedId AS id, feedName AS \'Nombre\', feedUrl AS \'Url\' ', false)
						->like('feedName', $filter)
		 				->get('feeds', $num, $offset);
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select(){
		return $this->db->get('feeds');
	}

	function get($feedId){
		$result = $this->db
				->where('feeds.feedId', $feedId)
				->get('feeds')->row_array();
		return $result;
	}	
	
	function save($data){
		$feedId = (int)element('feedId', $data);
		
		$values = array(
			'feedName'		=> $data['feedName'],
			'feedUrl'		=> $data['feedUrl'],
			'feedLink'		=> $data['feedLink']
		);
		

		$query = $this->db->where('feedUrl', $values['feedUrl'])->get('feeds')->result_array();
		//pr($this->db->last_query());
		if (!empty($query)) {
			$feedId = $query[0]['feedId'];
			
			if ((string)$query[0]['feedName'] == '') {
				$values['feedName'] = $values['feedLink'];
			}
		}
		
		if ((int)$feedId != -1) {
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
}
