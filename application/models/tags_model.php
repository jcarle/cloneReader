<?php
class Tags_Model extends CI_Model {
	// TODO: quitar el like si $filter esta vacio
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS tags.tagId, tagName', false)
						->like('tagName', $filter)
						->order_by('tagId')
		 				->get('tags', $num, $offset);
		//pr($this->db->last_query());						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select(){
		return $this->db->get('tags')->result_array();
	}

	function get($tagId){
		$result = $this->db
				->where('tags.tagId', $tagId)
				->get('tags')->row_array();
		return $result;
	}	
	
	function save($data){
		$tagId = $data['tagId'];
		
		$values = array(
			'tagName'		=> $data['tagName'],
		);
		

		if ((int)$tagId != 0) {		
			$this->db->where('tagId', $tagId);
			$this->db->update('tags', $values);
		}
		else {
			$this->db->insert('tags', $values);
			$tagId = $this->db->insert_id();
		}
		//pr($this->db->last_query());

		return $tagId;
	}
	
	function delete($tagId) {
		$this->db->delete('tags', array('tagId' => $tagId));
		return true;
	}
	
	function search($filter){
		$filter = $this->db->escape_like_str($filter);

		return $this->db
			->select('DISTINCT tags.tagId AS id, tagName AS text  ', false)
			->like('tagName', $filter)
			->order_by('text')
			->get('tags', AUTOCOMPLETE_SIZE)->result_array();
	}
	
	function selectByFeedId($feedId) {
		$query = $this->db
			->select(' tags.tagId, tagName ', false)
			->join('feeds_tags', 'feeds_tags.tagId = tags.tagId', 'inner')
			->where('feedId', $feedId)
			->order_by('tagName')
			->get('tags')->result_array();
						
		return $query;
	}	
}
