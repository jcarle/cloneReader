<?php
class Tasks_Model extends CI_Model {
	
	function addTask($taskMethod, $taskParams=array()) {
		if(empty($taskMethod)){
			return false;
		}
		$this->db->insert('tasks_email', array(
			'taskMethod'	=> $taskMethod,
			'taskParams'	=> json_encode($taskParams),
			'taskRunning'	=> TASK_PENDING,
			'taskRetries'	=> 0,
			'taskDate'		=> date("Y-m-d H:i:s")
		));
		
		return $this->db->insert_id();
	}
	
	function selectToList($num, $offset, $taskRunning = null, $orderBy = 'taskProcess', $orderDir = 'asc' ){
		$this->db
			->select('SQL_CALC_FOUND_ROWS tasks_email.taskProcess, tasks_email.taskMethod, tasks_email.taskParams, tasks_email.taskRunning, tasks_email.taskRetries, tasks_email.taskDate, tasks_status.statusTaskName', false)
			->join('tasks_status', 'tasks_status.statusTaskId = tasks_email.taskRunning', 'inner');
		
		if ($taskRunning !== null) {
			$this->db->where('taskRunning', $taskRunning);
		}
		if (!in_array($orderBy, array('taskProcess'))) {
			$orderBy = 'taskProcess';
		}

		$query = $this->db
			->order_by($orderBy, $orderDir == 'desc' ? 'desc' : 'asc')
			->get('tasks_email', $num, $offset);
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	
	function delete($taskId) {
		$this->db->delete('tasks_email', array('taskProcess' => $taskId));
		return true;
	}		
	
	function save($data){
		$taskId = $data['taskProcess'];
		
		$values = array(
			'taskMethod'			=> $data['taskMethod'],
			'taskParams'			=> $data['taskParams'],
			'taskRunning'			=> $data['taskRunning'],
			'taskRetries'			=> $data['taskRetries']
		);
		
		if ((int)$taskId != 0) {
			$this->db->where('taskProcess', $taskId);
			$this->db->update('tasks_email', $values);
		}else{
			$this->db->insert('tasks_email', $values);
			$taskId = $this->db->insert_id();
		}
		
		return true;
	}
	
	function get($taskId){
		$query = $this->db
			->where('taskProcess', $taskId)
			->get('tasks_email')->row_array();
		return $query;
	}	
}
