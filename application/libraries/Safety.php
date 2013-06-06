<?php
class Safety {
	function __construct() {
		$CI = &get_instance();
		$this->db = $CI->db;
		$this->session = $CI->session;
		
		if ($this->session->userdata('userId') == null) {
			$this->session->set_userdata('userId', USER_ANONYMOUS);
		}
	}

	function isRoot() {
		$query = $this->db
			->where(array( 'userId'	=> $this->session->userdata('userId'), 'groupId' => GROUP_ROOT))
			->get('users_groups');
		return ($query->num_rows() > 0);
	}

	function allowByControllerName($controllerName) {
		$query = $this->db
			->where(array('controllerActive' => true, 'controllerName' => str_replace('::', '/', strtolower($controllerName)), 'userId' => $this->session->userdata('userId')))
			->join('groups_controllers', 'controllers.controllerId = groups_controllers.controllerId')
			->join('users_groups', 'users_groups.groupId = groups_controllers.groupId')
			->get('controllers');
		//echo $this->db->last_query(); return true;
		return ($query->num_rows() > 0);
	}
	
	function getControllersByUser($userId) {
		if (isset($this->aController)) {
			return $this->aController;
		}
		
		$query = $this->db
					->select('DISTINCT controllers.controllerId', false)
					->join('groups_controllers', 'controllers.controllerId = groups_controllers.controllerId', 'inner')
					->join('users_groups', 'users_groups.groupId = groups_controllers.groupId', 'inner')
					->where('controllerActive', true)
					->where('users_groups.userId', $userId)
					->get('controllers')->result_array(); 
		//echo $this->db->last_query(); 					
		
		$this->aController = array();
		foreach ($query as $row) {
			$this->aController[] = $row['controllerId'];
		}		
		return $this->aController;
	}	
	

	function allowByGroupProject($groupId, $projectId) {
		if ($this->isRoot()) {
			return true;
		}

		$sql = "SELECT  1
			FROM users_groups_projects
			WHERE groupId   = $groupId
			AND   projectId = $projectId 
			AND   userId    = " . $this->session->userdata('userId') . " ";
		//echo $sql;
		return (getFieldValue($sql) == 1);
	}


	function getGroupsByIdUsuario($userId){
		return $this->db
					->where('userId', $userId)
					->get('users_groups')->result_array();
	}
	
	function getArrayGroupsByIdUsuario($userId){
		$aResult = array();
		$query = $this-> getGroupsByIdUsuario($userId);
		foreach ($query as $row) {
			$aResult[] = $row['groupId'];
		}
		return $aResult;
	}	



	function getUserGroups($userId = null, $projectId = null) {
		if ($userId == null) {
			 $userId = $this->session->userdata('userId');
		}

		$aTmp = array();

		$sql = " SELECT groupId
			FROM users_groups_projects
			WHERE userId    = " . $userId . " ";
		if ($projectId != null) { $sql = addSqlFilter($sql, " projectId = " . $projectId);
		}
		//echo $sql;
		$aTmp = sqlToArray($sql, $aTmp);
		return $aTmp;
	}

	function getWebSiteHome() {
		$sql = " SELECT webSiteHome
			FROM groups
			WHERE groupId   = " . $this->session->userdata('groupId') . " ";
		//echo $sql;
		return getFieldValue($sql);
	}

}
