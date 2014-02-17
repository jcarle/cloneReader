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
	
	function selectChildsByTestId($testId) {
		$query = $this->db
			->select('DISTINCT testing_childs.testChildId, currencyName, testChildPrice, testChildExchange, testChildDate, testChildName, countryName ', false)
			->join('countries', 'testing_childs.countryId = countries.countryId', 'inner')
			->join('coins', 'testing_childs.currencyId = coins.currencyId', 'inner')
			->where('testing_childs.testId', $testId)
			->order_by('testChildDate')
			->get('testing_childs')->result_array();

		//pr($this->db->last_query()); die;
		return $query;		
	}
	
	function getTestChild($testChildId) {
		$query = $this->db
			->select('testing_childs.* ', false)
			->where('testChildId', $testChildId)
			->get('testing_childs')->row_array();
		
		return $query;		
	}
	
	function saveTestingChilds($data) {
		$testChildId = $data['testChildId'];
		
		$values = array(
			'testId'				=> $data['testId'],
			'testChildDate'			=> $data['testChildDate'],
			'testChildName'			=> $data['testChildName'],
			'countryId'				=> $data['countryId'],
			'currencyId'			=> $data['currencyId'],
			'testChildPrice'		=> $data['testChildPrice'],
			'testChildExchange'		=> $data['testChildExchange'],
		);
		

		if ((int)$testChildId != 0) {		
			$this->db->where('testChildId', $testChildId);
			$this->db->update('testing_childs', $values);
		}
		else {
			$this->db->insert('testing_childs', $values);
			$testChildId = $this->db->insert_id();
		}
		//pr($this->db->last_query());
		return true;		
	}
	
	function selectUsersByTestChildId($testChildId) {
		$query = $this->db
			->select('users.* ', false)
			->join('users', 'testing_childs_users.userId = users.userId', 'inner')
			->where('testChildId', $testChildId)
			->order_by('userFirstName')
			->get('testing_childs_users')->result_array();

		//pr($this->db->last_query()); die;
		return $query;		
	}
	
	function saveTestChildUser($testChildId, $userId) {
		$this->db->ignore()->insert('testing_childs_users', array(
			'userId' 			=> $userId, 
			'testChildId' 		=> $testChildId
		));

		return true;		
	}
	
	function exitsTestChildUser($testChildId, $userId) {
		$query = $this->db
			->where(array(
				'testChildId' 	=> $testChildId, 
				'userId' 		=> $userId
			))
			->get('testing_childs_users');
		return ($query->num_rows() > 0);
	}
	
	function deleteTestChildUser($testChildId, $userId) {
		$this->db->delete('testing_childs_users', array(
			'testChildId' 	=> $testChildId, 
			'userId' 		=> $userId
		));

		return true;		
	} 
}
