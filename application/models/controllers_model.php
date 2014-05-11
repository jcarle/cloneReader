<?php
class Controllers_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db->select("SQL_CALC_FOUND_ROWS controllerId, controllerName, controllerUrl, IF(controllerActive, 'X', '') AS controllerActive", false)
						->like('controllerName', $filter)
						->get("controllers", $num, $offset);
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select($onlyActive = false){
		$query = $this->db->order_by('controllerName');
		
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
		
		$this->Menu_Model->destroyMenuCache();
		
		return true;
	}
	
	function delete($controllerId) {
		$this->db->delete('controllers', array('controllerId' => $controllerId));
		return true;
	}
	
	function exitsController($controllerName, $controllerId) {
		$this->db->where('controllerName', $controllerName);
		$this->db->where('controllerId !=', $controllerId);
		return ($this->db->get('controllers')->num_rows() > 0);
	}
}
