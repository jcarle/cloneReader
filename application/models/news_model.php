<?php
class News_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db
			->select('SQL_CALC_FOUND_ROWS news.newId, newTitle, newSef, newDate, CONCAT(userFirstName, \' \', userLastName) AS userFullName ', false)
			->like('newTitle', $filter)
			->join('users', 'news.userId = users.userId', 'inner')
			->order_by('news.newId')
		 	->get('news', $num, $offset);
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}

	function get($newId){
		$result = $this->db
				->select('news.*', true)
				->where('newId', $newId)
				->get('news')->row_array();
		return $result;
	}
	
	function getByNewSef($newSef){
		$result = $this->db
				->select('news.*', true)
				->where('newSef', $newSef)
				->get('news')->row_array();
		return $result;
	}	
	
	function save($data){
		$newId = $data['newId'];

		$values = array(
			'newTitle'		=> element('newTitle', $data),
			'newContent'	=> element('newContent', $data),
			'userId'		=> element('userId', $data),
			'newDate'		=> element('newDate', $data),
		);

		if ((int)$newId != 0) {		
			$this->db->where('newId', $newId)->update('news', $values);
		}
		else {
			$values['newSef'] = url_title($data['newTitle'], '_', true);
			
			$this->db->insert('news', $values);
			$newId = $this->db->insert_id();
		}
		//pr($this->db->last_query()); 

		return true;
	}
	
	function delete($newId) {
		$this->db->delete('news', array('newId' => $newId));
		return true;
	}	

	function selectToRss(){
		$query = $this->db
			->select(' news.newId, newTitle, newContent, newSef, newDate, CONCAT(userFirstName, \' \', userLastName) AS userFullName ', false)
			->join('users', 'news.userId = users.userId', 'inner')
			->order_by('newDate DESC')
		 	->get('news', 30, 0);

		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
}
