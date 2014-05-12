<?php
class Status_Model extends CI_Model {
	function select(){
		return $this->db->order_by('statusName')->get('status')->result_array();
	}
	
	function selectToDropdown() {
		return $this->db
			->select('statusId AS id, statusName AS text', true)
			->order_by('statusName')
			->get('status')->result_array();
	}
}
