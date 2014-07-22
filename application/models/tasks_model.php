<?php
class Tasks_Model extends CI_Model {
	
	function addTask($taskMethod, $taskParams=array(), $langId = null) {
		if(empty($taskMethod)){
			return false;
		}
		
		if ($langId == null) {
			$langId = $this->session->userdata('langId');
		}		
		
		$this->db->insert('tasks_email', array(
			'langId'        => $langId,
			'taskMethod'    => $taskMethod,
			'taskParams'    => json_encode($taskParams),
			'taskRunning'   => TASK_PENDING,
			'taskRetries'   => 0,
			'taskDate'      => date("Y-m-d H:i:s")
		));
		
		return $this->db->insert_id();
	}
	
	function selectToList($num, $offset, $taskRunning = null, $orderBy = 'taskId', $orderDir = 'asc' ){
		$this->db
			->select('SQL_CALC_FOUND_ROWS tasks_email.taskId, tasks_email.taskMethod, tasks_email.taskParams, tasks_email.taskRunning, tasks_email.taskRetries, tasks_email.taskDate, tasks_status.statusTaskName, langId ', false)
			->join('tasks_status', 'tasks_status.statusTaskId = tasks_email.taskRunning', 'inner');
		
		if ($taskRunning !== null) {
			$this->db->where('taskRunning', $taskRunning);
		}
		if (!in_array($orderBy, array('taskId'))) {
			$orderBy = 'taskId';
		}

		$query = $this->db
			->order_by($orderBy, $orderDir == 'desc' ? 'desc' : 'asc')
			->get('tasks_email', $num, $offset);
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	
	function delete($taskId) {
		$this->db->delete('tasks_email', array('taskId' => $taskId));
		return true;
	}		
	
	function save($data){
		$taskId = $data['taskId'];
		
		$values = array(
			'taskMethod'            => $data['taskMethod'],
			'taskParams'            => $data['taskParams'],
			'taskRunning'           => $data['taskRunning'],
			'taskRetries'           => $data['taskRetries'],
		);
		
		if ((int)$taskId != 0) {
			$this->db->where('taskId', $taskId);
			$this->db->update('tasks_email', $values);
		}else{
			$this->db->insert('tasks_email', $values);
			$taskId = $this->db->insert_id();
		}
		
		return true;
	}
	
	function get($taskId){
		$query = $this->db
			->where('taskId', $taskId)
			->get('tasks_email')->row_array();
		return $query;
	}	
}
