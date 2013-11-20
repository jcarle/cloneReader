<?php
class Status_Model extends CI_Model {
	function select(){
		return $this->db->order_by('statusName')->get('status')->result_array();
	}
}
