<?php
class Commond_Model extends CI_Model {
		
	function getFoundRows(){
		$row = $this->db->query('SELECT FOUND_ROWS() AS foundRows')->row();
		return $row->foundRows;
	}

	public function getCurrentDateTime() {
		$row = $this->db->query('SELECT NOW() AS datetime')->row();
		return $row->datetime;
	}
}
