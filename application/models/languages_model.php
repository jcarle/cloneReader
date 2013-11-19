<?php
class Languages_Model extends CI_Model {
	function select(){
		return $this->db->order_by('langName')->get('languages')->result_array();
	}
}
