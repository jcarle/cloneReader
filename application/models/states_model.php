<?php
class States_Model extends CI_Model {
	function selectStatesByCountryId($countryId){
		$query = $this->db
			->select('stateId AS id, stateName AS value')
			->from('states')
			->where('countryId', $countryId)
			->order_by('stateName')
			->get()->result_array();
			
		//pr($this->db->last_query());
		return $query;
	}

	function getStateById($id){
		return $this->db
			->select('stateId as id, stateName as text')
			->where('stateId', $id)
			->get('states')->row_array();
	}
}
