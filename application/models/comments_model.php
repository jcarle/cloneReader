<?php
class Comments_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS comments.commentId, commentDesc, commentDate, commentUserName, commentUserEmail ', false)
						->like('commentDesc', $filter)
						->join('users', 'comments.userId = users.userId', 'inner')						
		 				->get('comments', $num, $offset);
		//pr($this->db->last_query());				
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
			
	function save($data){
		$commentId = $data['commentId'];
		
		$values = array(
			'userId'		=> element('userId', $data),
			'commentDesc' 	=> element('commentDesc', $data),
			'commentDate' 	=> element('commentDate', $data),
		);
		
		if ((int)$commentId != 0) {		
			$this->db->where('commentId', $commentId);
			$this->db->update('comments', $values);
		}
		else {
			$this->db->insert('comments', $values);
			$commentId = $this->db->insert_id();
		}
		
		$this->db->where('commentId', $commentId);

		return true;
	}	
	
	function saveFeedback($data) {
		$values = array(
			'userId'			=> $this->session->userdata('userId'),
			'commentDesc' 		=> element('commentDesc', $data),
			'commentDate'	 	=> date("Y-m-d H:i:s"),
			'commentUserName'	=> element('commentUserName', $data),
			'commentUserEmail'	=> element('commentUserEmail', $data),
		);
		
		$this->db->insert('comments', $values);
		
		$this->sendEmail($data);
		
		return true;
	}
	
	function get($commentId) {
		$query = $this->db
				->select('comments.commentId, commentDesc, users.userId, commentDate, commentUserName, commentUserEmail ', false)
				->where('comments.commentId', $commentId)
				->join('users', 'comments.userId = users.userId', 'inner')
				->get('comments')->row_array();
		//pr($this->db->last_query());				
		return $query;		
	}
	
	function delete($commentId) {
		$this->db->delete('comments', array('commentId' => $commentId));
		return true;
	}
	
	function sendEmail($data) {
		$this->load->library('email');
		
		$this->email->from(element('commentUserEmail', $data), element('commentUserName', $data));
		$this->email->to('jcarle@gmail.com');  // TODO: desharckodear!
		$this->email->subject('cReader - Comentario de '.element('commentUserName', $data));
		$this->email->message('
			Fecha: '.date("Y-m-d H:i:s").'
			Nombre: '.element('commentUserName', $data).'
			Email: '.element('commentUserEmail', $data).'
			Comentario: '.element('commentDesc', $data)
		);
		
		$this->email->send();
		
		echo $this->email->print_debugger();	die;	
	}
}
