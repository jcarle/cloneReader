<?php
class Safety {
	function __construct() {
		$CI = &get_instance();

		if (ENVIRONMENT == 'production') {
			$CI->db->save_queries = false;
		}

		//$CI->output->enable_profiler(FALSE);
	}

	function initSession() {
		$CI = &get_instance();
//pr($CI->session->userdata); die;
		if ($CI->session->userdata('userId') == null) {
			$CI->session->set_userdata(array(
				'userId' => config_item('userAnonymous'),
				'groups' => array(config_item('groupAnonymous')),
			));
		}

		if ($CI->session->userdata('userId') != config_item('userAnonymous')) {
			if ($CI->session->userdata('last_activity') == $CI->session->now) {
				$CI->load->model('Users_Model');
				$CI->Users_Model->updateUserLastAccess();
				$CI->session->set_userdata('groups', sourceToArray($CI->Users_Model->getGroups($CI->session->userdata('userId')), 'groupId'));
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
			'userId'  => $row->userId,
			'langId'  => $row->langId,
			'groups'  => sourceToArray($CI->Users_Model->getGroups($row->userId), 'groupId'),
		));

		$CI->Users_Model->updateUserLastAccess();

		return true;
	}

	function getControllerCache($groups) {
		if (empty($groups)) {
			return array();
		}
		$CI = &get_instance();
		$CI->load->driver('cache', array('adapter' => 'file'));
		if (!is_array($CI->cache->file->get('controllers_'.json_encode($groups)))) {
			$CI->load->model('Controllers_Model');
			$CI->Controllers_Model->createControllersCache($groups);
		}
		return $CI->cache->file->get('controllers_'.json_encode($groups));
	}

	function allowByControllerName($controllerName) {
		$CI = &get_instance();

		$aController = $this->getControllerCache($CI->session->userdata('groups'));
		return in_array(str_replace('::', '/', strtolower($controllerName)), $aController);
	}

	function getGroupsByIdUsuario($userId){
		$CI = &get_instance();

		return $CI->db
			->where('userId', $userId)
			->get('users_groups')->result_array();
	}

	function allowAccountPrivilege($accountId, $privilegeId) {
		$CI = &get_instance();
		$CI->load->driver('cache', array('adapter' => 'file'));
		if (!is_array($CI->cache->file->get('account_'.$accountId))) {
			$CI->load->model('Accounts_Model');
			$CI->Accounts_Model->createAccountCache($accountId);
		}

		$account = $CI->cache->file->get('account_'.$accountId);
		for ($i=0; $i<count($account['privileges']); $i++) {
			if ($account['privileges'][$i] == $privilegeId) {
				return true;
			}
		}

		return false;
	}

	function destroyMenuCache() {
		$CI = &get_instance();
		$CI->load->model('Menu_Model');
		$CI->Menu_Model->destroyMenuCache();
	}

	function destroyControllersCache() {
		$CI = &get_instance();
		$CI->load->model('Controllers_Model');
		$CI->Controllers_Model->destroyControllersCache();
	}
}
