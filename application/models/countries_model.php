<?php
class Countries_Model extends CI_Model {
	function select(){
		return $this->db->order_by('countryName')->get('countries')->result_array();
	}
	
	function selectToDropdown(){
		return $this->db
			-> select('countryId AS id, countryName AS text', true)
			->order_by('countryName')
			->get('countries')->result_array();
	}	
}
