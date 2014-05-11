<?php
class Languages_Model extends CI_Model {
	function select(){
		return $this->db->order_by('langName')->get('languages')->result_array();
	}
	
	function selectToDropdown(){
		return $this->db
			->select('langId AS id, langName AS text', true)
			->order_by('langName')
			->get('languages')->result_array();
	}
	
	function getRelatedLangs($langId) {
		$result = array();
		$query = $this->db
			->where('langId LIKE \''.substr($langId, 0, 2).'%\'')
			->get('languages')->result_array();
		//pr($this->db->last_query());   die;			
		foreach ($query as $data) {
			$result[] = $data['langId'];
		}	

		return $result;
	}
}
