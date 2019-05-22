<?php
class Tasks_Model extends CI_Model {

	function addTask($taskMethod, $taskParams=array(), $langId = null, $taskSchedule = null) {
		if(empty($taskMethod)){
			return false;
		}

		if ($langId == null) {
			$langId = $this->session->userdata('langId');
		}
		if ($taskSchedule == null) {
			$taskSchedule = date("Y-m-d H:i:s");
		}

		$this->db->insert('tasks_email', array(
			'langId'        => $langId,
			'taskMethod'    => $taskMethod,
			'taskParams'    => json_encode($taskParams),
			'statusTaskId'  => config_item('taskPending'),
			'taskRetries'   => 0,
			'taskSchedule'  => $taskSchedule,
		));

		return $this->db->insert_id();
	}


	/**
	 * @param  (int )   $pageCurrent
	 * @param  (int)    $pageSize
	 * @param  (array)  $filters es un array con el formato:
	 * 						array(
	 * 							'search'         => null,
	 * 							'statusTaskId'   => null,
	 * 							'validDate'      => null, // Filtra las emails que ya pueden enviarse
	 *						)
	 * @param   $orders    un array con el formato:
	 * 						array(
	 * 							array(
	 * 								'orderBy'  = 'taskId',
	 * 								'orderDir' = 'asc',
	 * 							)
	 * 						);
	 * */
	function selectToList($pageCurrent = null, $pageSize = null, array $filters = array(), array $orders = array() ){
		$this->db
			->select('SQL_CALC_FOUND_ROWS tasks_email.taskId, tasks_email.taskMethod, tasks_email.taskParams, tasks_email.statusTaskId, tasks_email.taskRetries, tasks_email.taskSchedule, tasks_status.statusTaskName, languages.langId, langName ', false)
			->from('tasks_email')
			->join('languages', 'tasks_email.langId = languages.langId', 'inner')
			->join('tasks_status', 'tasks_status.statusTaskId = tasks_email.statusTaskId', 'inner');


		if (element('statusTaskId', $filters) !== null) {
			$this->db->where('tasks_email.statusTaskId', $filters['statusTaskId']);
		}
		if (element('validDate', $filters) == true) {
			$this->db->where('taskSchedule < NOW() ');
		}

		$this->Commond_Model->appendOrderByInQuery($orders, array('taskId', 'taskMethod', 'taskSchedule'));
		$this->Commond_Model->appendLimitInQuery($pageCurrent, $pageSize);

		$query = $this->db->get();
		//pr($this->db->last_query()); die;

		return array('data' => $query->result_array(), 'foundRows' => $this->Commond_Model->getFoundRows());
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
