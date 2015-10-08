<?php
class Groups_Model extends CI_Model {
	function selectToList($pageCurrent = null, $pageSize = null, array $filters = array()){
		$query = $this->db
			->select('SQL_CALC_FOUND_ROWS groupId, groupName ', false)
			->from('groups');

		if (element('search', $filters) != null) {
			$this->db->like('groupName', $filters['search']);
		}

		$this->Commond_Model->appendLimitInQuery($pageCurrent, $pageSize);

		$query = $this->db->get();

		return array('data' => $query->result_array(), 'foundRows' => $this->Commond_Model->getFoundRows());
	}

	function select(){
		return $this->db->order_by('groupName')->get('groups')->result_array();
	}

	function selectToDropdown(){
		return $this->db
			->select('groupId AS id, groupName AS text', true)
			->order_by('groupName')
			->get('groups')->result_array();
	}

	function get($groupId){
		$query = $this->db
			->where('groupId', $groupId)
			->get('groups')->row_array();
		if (empty($query)) {
			return $query;
		}
		$query['controllers'] = sourceToArray($this->getControllers($groupId), 'controllerId');
		return $query;
	}

	function getControllers($groupId){
		$this->db->where('groupId', $groupId);
		$query = $this->db->get('groups_controllers')->result_array();

		return $query;
	}

	function save($data){
		$groupId = $data['groupId'];

		$values = array(
			'groupName' => $data['groupName'],
		);

		if ((int)$groupId != 0) {
			$this->db->where('groupId', $groupId);
			$this->db->update('groups', $values);
		}
		else {
			$this->db->insert('groups', $values);
			$groupId = $this->db->insert_id();
		}


		$this->db->where('groupId', $groupId)->delete('groups_controllers');
		$controllers = json_decode(element('controllers', $data));
		if (is_array($controllers)) {
			foreach ($controllers as $controllerId) {
				$this->db->insert('groups_controllers', array('groupId' => $groupId, 'controllerId' => $controllerId));
			}
		}

		$this->safety->destroyMenuCache();
		$this->safety->destroyControllersCache();

		return true;
	}

	function delete($groupId) {
		$this->db->delete('groups', array('groupId' => $groupId));

		$this->safety->destroyMenuCache();
		$this->safety->destroyControllersCache();

		return true;
	}
}
