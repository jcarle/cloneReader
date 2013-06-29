<?php
class Comments_Model extends CI_Model {
	// TODO: quizas haya que modificar un poco esta clase, para que soporte meter comentarios en distintas entidades como hace Files_Model 
		
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS comments.commentId AS id, commentDesc AS Comentario, DATE_FORMAT(commentDate, \'%Y-%m-%e %H:%i\') AS Fecha, commentScore AS Puntaje, IF(users.userId = 0, commentUserName, CONCAT(userFirstName, \' \', userLastName)) AS Nombre, excursions.excursionName AS Excursion ', false)
						->like('commentDesc', $filter)
						->join('users', 'comments.userId = users.userId', 'inner')						
						->join('excursions_comments', 'excursions_comments.commentId = comments.commentId', 'inner')
						->join('excursions', 'excursions.excursionId = excursions_comments.excursionId', 'inner')
		 				->get('comments', $num, $offset);
		//pr($this->db->last_query());				
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function selectByExcursionId($excursionId) {
		$query = $this->db->select('comments.commentId AS id, commentDesc AS Comentario, DATE_FORMAT(commentDate, \'%Y-%m-%e %H:%i\') AS Fecha, commentScore AS Puntaje, IF(users.userId = 0, commentUserName, CONCAT(userFirstName, \' \', userLastName)) AS Nombre ', false)
						->join('users', 'comments.userId = users.userId', 'inner')						
						->join('excursions_comments', 'excursions_comments.commentId = comments.commentId', 'inner')
						->join('excursions', 'excursions.excursionId = excursions_comments.excursionId', 'inner')
						->where('excursions.excursionId', $excursionId)
		 				->get('comments');
		//pr($this->db->last_query());				
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}		
			
	function save($data){
		$commentId = $data['commentId'];
		
		$values = array(
			'commentDesc'	=> $data['commentDesc'],
			'userId'		=> element('userId', $data),
			'commentDesc' 	=> element('commentDesc', $data),
			'commentDate' 	=> element('commentDate', $data),
			'commentScore' 	=> element('commentScore', $data)
		);
		
		if ((int)$commentId != -1) {		
			$this->db->where('commentId', $commentId);
			$this->db->update('comments', $values);
		}
		else {
			$this->db->insert('comments', $values);
			$commentId = $this->db->insert_id();
		}
		
		
		$this->db->where('commentId', $commentId);
		$result = $this->db->delete('excursions_comments');
		if ((int)$data['userId'] > 0) {
			$this->db->insert('excursions_comments', array('commentId' => $commentId, 'excursionId' => $data['excursionId']));			
		}
		return true;
	}	
	
	function get($commentId) {
		$query = $this->db
				->select('comments.commentId, commentDesc, users.userId, DATE_FORMAT(commentDate, \'%Y-%m-%e %H:%i\') AS commentDate, commentScore, commentUserName, commentUserEmail, CONCAT(userFirstName, \' \', userLastName) AS userFullName, excursions.excursionId, excursions.excursionName ', false)
				->where('comments.commentId', $commentId)
				->join('users', 'comments.userId = users.userId', 'inner')

				->join('excursions_comments', 'excursions_comments.commentId = comments.commentId', 'inner')
				->join('excursions', 'excursions.excursionId = excursions_comments.excursionId', 'inner')				
				->get('comments')->row_array();
		//pr($this->db->last_query());				
		return $query;		
	}	
}