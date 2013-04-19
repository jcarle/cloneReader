<?php
class Tags_Model extends CI_Model {
	// TODO: quitar el like si $filter esta vacio
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS tags.tagId AS id, tagName AS \'Name\'', false)
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
		

		if ((int)$tagId != -1) {		
			$this->db->where('tagId', $tagId);
			$this->db->update('tags', $values);
		}
		else {
			$this->db->insert('tags', $values);
			$tagId = $this->db->insert_id();
		}
		//pr($this->db->last_query());

		return true;
	}
}
