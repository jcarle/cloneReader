<?php
class testing_Model extends CI_Model {
	
	function selectToList($pageCurrent = null, $pageSize = null, array $filters = array()){
		$this->db
			->select('SQL_CALC_FOUND_ROWS testing.testId, testName, countries.countryName, states.stateName', false)
			->from('testing')
			->join('countries', 'countries.countryId = testing.countryId', 'inner')
			->join('states', 'states.stateId = testing.stateId', 'inner');
			
		if (element('search', $filters) != null) {
					$this->db->like(array('testName' => $filters['search']));
		}
		if (element('countryId', $filters) != null) {
			$this->db->where('testing.countryId', $filters['countryId']);
		}
		
		$this->Commond_Model->appendLimitInQuery($pageCurrent, $pageSize);

		$query = $this->db
			->order_by('testName')
			->get();

		return array('data' => $query->result_array(), 'foundRows' => $this->Commond_Model->getFoundRows());
	}
	
	function search($filter){
		$query = $this->db
			->select('DISTINCT testing.testId AS id, CONCAT(countryName, \' - \', stateName, \' - \', testName) AS text  ', false)
			->join('countries', 'countries.countryId = testing.countryId', 'inner')
			->join('states', 'states.countryId = testing.countryId AND states.stateId = testing.stateId', 'inner')		
			->like('CONCAT(countryName, \' \', stateName, \' \', testName)', $filter) // TODO: mejorar para no matar el servidor!
			->order_by('text')
			->get('testing', config_item('autocompleteSize'))->result_array();
		//pr($this->db->last_query());
		
		 return $query;
	}	
		
	function select(){
		return $this->db->get('testing');
	}

	function get($testId, $getPicture = false){
		$query = $this->db
					->where('testId', $testId)
					->get('testing')->row_array();
					
		if (!empty($query) && $getPicture == true) {
			$this->load->model('Files_Model');
			$config = config_item('testPicture');
			$query['testPicture'] = $this->Files_Model->get($query['testPictureFileId'], $config['sizes']['thumb']['folder'], 'fileUrl');

			$config           = config_item('testDoc');
			$testDoc          = $this->Files_Model->get($query['testDocFileId'], $config['folder']);
			$query['testDoc'] = array('url' => $testDoc['fileUrl'], 'name' => $testDoc['fileTitle']);
			
			$query['testIco']     = base_url('assets/images/logo.png');
		}
//pr($query);die;
		return $query;
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
		$CI =& get_instance();
		$CI->load->model('Files_Model');
		$query 			= $this->Files_Model->selectEntityFiles($testId, config_item('entityTypeTesting'), $testId);
		foreach ($query->result_array() as $row) {
			$this->Files_Model->deleteEntityFile(config_item('entityTypeTesting'), $row['fileId']);
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
	
	function savePicture($testId, $testPictureFileId) {
		$this->db->where('testId', $testId)->update('testing', array('testPictureFileId' => $testPictureFileId));
	}

	function saveDoc($testId, $testDocFileId) {
		$this->db->where('testId', $testId)->update('testing', array('testDocFileId' => $testDocFileId));
	}
}
