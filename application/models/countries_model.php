<?php
class Countries_Model extends CI_Model {
	function select(){
		return $this->db->query('SELECT countryId, countryName FROM countries');
	}
}
