<?php
class testing_Model extends CI_Model {
	
	function selectToList($num, $offset, $filter = null, $countryId = null){
		$this->db
			->select('SQL_CALC_FOUND_ROWS testing.testId, testName, countries.countryName, states.stateName', false)
			->join('countries', 'countries.countryId = testing.countryId', 'inner')
			->join('states', 'states.stateId = testing.stateId', 'inner');
			
		if ($filter != null) {
			$this->db->like(array('testName' => $filter));
		}
		if ($countryId != null) {
			$this->db->where('testing.countryId', $countryId);
		}

		$query = $this->db
			->order_by('testName')
		 	->get('testing', $num, $offset);
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function search($filter){
		$query = $this->db
			->select('DISTINCT testing.testId AS id, CONCAT(countryName, \' - \', stateName, \' - \', testName) AS text  ', false)
			->join('countries', 'countries.countryId = testing.countryId', 'inner')
			->join('states', 'states.countryId = testing.countryId AND states.stateId = testing.stateId', 'inner')		
			->like('CONCAT(countryName, \' \', stateName, \' \', testName)', $filter) // TODO: mejorar para no matar el servidor!
			->order_by('text')
			->get('testing', AUTOCOMPLETE_SIZE)->result_array();
		//pr($this->db->last_query());
		
		 return $query;
	}	
		
	function select(){
		return $this->db->get('testing');
	}

	function get($testId){
		$result = $this->db
					->where('testId', $testId)
					->get('testing')->row_array();
		return $result;
	}	

	function save($values){
		$testId = $values['testId'];
		unset($values['testId']);
		
		if ((int)$testId != 0) {		
			$this->db->where('testId', $testId);
			$this->db->update('testing', $values);
			return $testId;
		}

		$this->db->insert('testing', $values);
		return $this->db->insert_id();
	}
	
	function delete($testId) {
		$entityName = 'testing';
		$CI =& get_instance();
		$CI->load->model('Files_Model');
		$aProperties 	= $this->Files_Model->getPropertyByEntityName($entityName);
		$query 			= $this->Files_Model->getFilesByEntity($entityName, $testId, null);
		foreach ($query->result_array() as $row) {
			$this->Files_Model->deleteByFileId($entityName, $testId, $row['fileId']);
		}
		
		
		$this->db->delete('testing', array('testId' => $testId));
		return true;
	}	
}
