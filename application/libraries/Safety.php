<?php
class Safety {
	function __construct() {
		$CI 			= &get_instance();
		$this->db 		= $CI->db;
		$this->session 	= $CI->session;
		

//$CI->output->enable_profiler(TRUE);

		initLang();

		if ($this->session->userdata('userId') == null) {
			$this->session->set_userdata('userId', USER_ANONYMOUS);
			if ($this->isCommandLine() != true && uri_string() != 'rss') {
				redirect('login');
			}
		}
	}
	
	function login($email, $password) {
		$CI = &get_instance();
		$CI->load->model('Users_Model');
		
		$query = $CI->Users_Model->login($email, $password);

		if ($query->num_rows() == null) {
			return false;
		}

		$row = $query->row();
		
		$CI->session->set_userdata(array(
			'userId'  	=> $row->userId,
			'langId'	=> $row->langId,
		));		
		
		return true;
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

	function getGroupsByIdUsuario($userId){
		return $this->db
			->where('userId', $userId)
			->get('users_groups')->result_array();
	}

	public static function isCommandLine() {
		return PHP_SAPI === 'cli';
	}	
}
