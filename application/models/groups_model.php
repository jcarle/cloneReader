<?php
class Groups_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS groupId, groupName, groupHomePage', false)
						->like('groupName', $filter)
		 				->get('groups', $num, $offset);
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function select(){
		return $this->db->order_by('groupName')->order_by('groupName')->get('groups')->result_array();
	}	
			
	function get($groupId){
		$this->db->where('groupId', $groupId);
		$result 				= $this->db->get('groups')->row_array();
		$result['controllers'] 	= array_to_select($this->getControllers($groupId), 'controllerId', 'controllerId');
		return $result;
	}	
	
	function getControllers($groupId){
		$this->db->where('groupId', $groupId);
		$result = $this->db->get('groups_controllers')->result_array();

		return $result;		
	}
	
	function save($data){
		$groupId = $data['groupId'];
		
		$values = array(
			'groupName'		=> $data['groupName'], 	
			'groupHomePage'	=> $data['groupHomePage']
		);
		
		if ((int)$groupId != 0) {		
			$this->db->where('groupId', $groupId);
			$this->db->update('groups', $values);
		}
		else {
			$this->db->insert('groups', $values);
			$groupId = $this->db->insert_id();
		}
		
		
		$this->db->where('groupId', $groupId);
		$result = $this->db->delete('groups_controllers');
		if (is_array(element('controllers', $data))) {
			foreach ($data['controllers'] as $controllerId) {
				$this->db->insert('groups_controllers', array('groupId' => $groupId, 'controllerId' => $controllerId));			
			}		
		}
		
		$this->Menu_Model->destroyMenuCache();
		
		return true;
	}
	
	function delete($groupId) {
		$this->db->delete('groups', array('groupId' => $groupId));
		return true;
	}
}
