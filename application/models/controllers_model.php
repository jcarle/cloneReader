<?php
class Controllers_Model extends CI_Model {
	function selectToList($pageCurrent = null, $pageSize = null, array $filters = array()){
		$this->db
			->select('SQL_CALC_FOUND_ROWS controllerId, controllerName, controllerUrl, IF(controllerActive, \'X\', \'\') AS controllerActive ', false)
			->from('controllers');
			
		if (element('search', $filters) != null) {
			$this->db->like('controllerName', $filters['search']);
		}
			
		$this->Commond_Model->appendLimitInQuery($pageCurrent, $pageSize);
		
		$query = $this->db->get();
		//pr($this->db->last_query()); die;
		
		return array('data' => $query->result_array(), 'foundRows' => $this->Commond_Model->getFoundRows());
	}
	
	function select($onlyActive = false){
		$query = $this->db->order_by('controllerName');
		
		if ($onlyActive == true) {
			$query->where('controllerActive', true);
		}

		return $query->get('controllers')->result_array();
	}	
	
	
	function selectToDropdown($onlyActive = false){
		$query = $this->db
			->select('controllerId AS id, controllerName AS text', true)
			->order_by('controllerName');
		
		if ($onlyActive == true) {
			$query->where('controllerActive', true);
		}

		return $query->get('controllers')->result_array();
	}

	function get($controllerId){
		$this->db->where('controllerId', $controllerId);
		return $this->db->get('controllers')->row_array();
	}
	
	function save($data){
		$controllerId = $data['controllerId'];
		unset($data['controllerId']);
		
		$data['controllerActive'] = (element('controllerActive', $data) == 'on'); 

		if ((int)$controllerId != 0) {		
			$this->db->where('controllerId', $controllerId);
			$this->db->update('controllers', $data);
		}
		else {
			$this->db->insert('controllers', $data);
		}
		
		$this->safety->destroyMenuCache();
		$this->safety->destroyControllersCache();
		
		return true;
	}
	
	function delete($controllerId) {
		$this->db->delete('controllers', array('controllerId' => $controllerId));
		
		$this->safety->destroyMenuCache();
		$this->safety->destroyControllersCache();
		
		return true;
	}
	
	function exitsController($controllerName, $controllerId) {
		$this->db->where('controllerName', $controllerName);
		$this->db->where('controllerId !=', $controllerId);
		return ($this->db->get('controllers')->num_rows() > 0);
	}

	function selectControllersByUserId($userId) {
		$query = $this->db
			->select('DISTINCT controllers.controllerId, controllerName', false)
			->from('controllers')
			->join('groups_controllers', 'controllers.controllerId = groups_controllers.controllerId', 'inner')
			->join('users_groups', 'users_groups.groupId = groups_controllers.groupId', 'inner')
			->where('controllerActive', true)
			->where('users_groups.userId', $userId)
			->get()->result_array(); 
		//echo $this->db->last_query(); 				
		return $query;
	}
	
	function selectControllersByGroupId($groups) {
		if (empty($groups)) {
			return null;
		}
		$query = $this->db
			->select('DISTINCT controllers.controllerId, controllerName', false)
			->from('controllers')
			->join('groups_controllers', 'controllers.controllerId = groups_controllers.controllerId', 'inner')
			->where('controllerActive', true)
			->where_in('groupId', $groups)
			->get()->result_array(); 
		//echo $this->db->last_query(); die; 				
		return $query;
	}	

	function destroyControllersCache() {
		$this->load->driver('cache', array('adapter' => 'file'));

		$cache = $this->cache->cache_info();
		foreach ($cache as $key => $value) {
			if (strrpos($key, 'CONTROLLERS_') !== FALSE) {
				$this->cache->delete($key);
			}
		}
	}

	/**
	* Guarda un array en un archivo con los controllers permitidos por grupos, con el formato: 
	*		array('controllerId' => 'controllerName')
	*/
	function createControllersCache($groups) {
		if (empty($groups)) {
			return;
		}
		$this->load->driver('cache', array('adapter' => 'file'));

		if (!is_array($this->cache->file->get('CONTROLLERS_'.json_encode($groups)))) {
			$aController = array();
			$query = $this->selectControllersByGroupId($groups);
			foreach ($query as $row) {
				$aController[$row['controllerId']] = strtolower($row['controllerName']);
			}
			$this->cache->file->save('CONTROLLERS_'.json_encode($groups), $aController);
		}
	}
}
