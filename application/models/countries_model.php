<?php
class Countries_Model extends CI_Model {
	function select(){
		return $this->db->order_by('countryName')->get('countries')->result_array();
	}
	
	function search($filter){
		return $this->db
			->select('countryId as id, countryName as text')
			->like('countryName', $filter)
			->get('countries')->row_array();
	}

	function getCountryById($id){
		return $this->db
			->select('countryId as id, countryName as text')
			->where('countryId', $id)
			->get('countries')->row_array();
	}

	function selectToDropdown(){
		return $this->db
			-> select('countryId AS id, countryName AS text', true)
			->order_by('countryName')
			->get('countries')->result_array();
	}
}
