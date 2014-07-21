<?php
class Feedbacks_Model extends CI_Model {
	function selectToList($num, $offset, $filter){
		$query = $this->db->select('SQL_CALC_FOUND_ROWS feedbacks.feedbackId, feedbackDesc, feedbackDate, feedbackUserName, feedbackUserEmail ', false)
						->like('feedbackDesc', $filter)
						->join('users', 'feedbacks.userId = users.userId', 'inner')						
		 				->get('feedbacks', $num, $offset);
		//pr($this->db->last_query());				
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
			
	function save($data){
		$feedbackId = $data['feedbackId'];
		
		$values = array(
			'userId'		=> element('userId', $data),
			'feedbackDesc' 	=> element('feedbackDesc', $data),
			'feedbackDate' 	=> element('feedbackDate', $data),
		);
		
		if ((int)$feedbackId != 0) {		
			$this->db->where('feedbackId', $feedbackId);
			$this->db->update('feedbacks', $values);
		}
		else {
			$this->db->insert('feedbacks', $values);
			$feedbackId = $this->db->insert_id();
		}
		
		$this->db->where('feedbackId', $feedbackId);

		return true;
	}	
	
	function saveFeedback($data) {
		$values = array(
			'userId'			=> $this->session->userdata('userId'),
			'feedbackDesc' 		=> element('feedbackDesc', $data),
			'feedbackDate'	 	=> date("Y-m-d H:i:s"),
			'feedbackUserName'	=> element('feedbackUserName', $data),
			'feedbackUserEmail'	=> element('feedbackUserEmail', $data),
		);
		
		$this->db->insert('feedbacks', $values);
		
		$this->load->model(array('Tasks_Model'));
		$this->Tasks_Model->addTask('sendFeedback', $values);
		
		return true;
	}
	
	function get($feedbacksId) {
		$query = $this->db
				->select('feedbacks.feedbackId, feedbackDesc, users.userId, feedbackDate, feedbackUserName, feedbackUserEmail ', false)
				->where('feedbacks.feedbackId', $feedbackId)
				->join('users', 'feedbacks.userId = users.userId', 'inner')
				->get('feedbacks')->row_array();
		//pr($this->db->last_query());				
		return $query;		
	}
	
	function delete($feedbackId) {
		$this->db->delete('feedbacks', array('feedbackId' => $feedbackId));
		return true;
	}
}
