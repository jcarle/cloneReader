<?php
class Tasks_Status_Model extends CI_Model {
	function select(){
		return $this->db->order_by('statusTaskId')->get('tasks_status')->result_array();
	}
	
	function selectToDropdown() {
		return $this->db
			->select('statusTaskId AS id, statusTaskName AS text', true)
			->order_by('statusTaskId')
			->get('tasks_status')->result_array();
	}
}
