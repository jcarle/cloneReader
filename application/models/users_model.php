<?php
class Users_Model extends CI_Model {
	
	function login($userEmail, $userPassword) {
		$this->db->where('userEmail', $userEmail);
		$this->db->where('userPassword', md5($userPassword));

		return $this->db->get('users');
	}
	
	function loginRemote($userEmail, $userLastName, $userFirstName, $provider, $remoteUserId) {
		
		$fieldName = ($provider == 'facebook' ? 'facebookUserId' : 'googleUserId');

		$query = $this->db
			->where($fieldName, $remoteUserId)
			->get('users');
		if ($query->num_rows() > 0) { // ya existe
			return $query->row();
		}
		
		$values = array(
			'userLastName' 		=> $userLastName, 
			'userFirstName'		=> $userFirstName, 
			$fieldName			=> $remoteUserId, 
		);		

		if (trim($userEmail) == '') {
			$userEmail = null;
		}
		
		if ($userEmail != null) {
			$query = $this->db
				->where('userEmail', $userEmail)
				->get('users');
			if ($query->num_rows() > 0) { // si existe un user con el mail, updateo
				$this->db
					->where('userId', $query->row()->userId)
					->update('users', $values);
					return $query->row();
			}
		}


		// creo el usuario
		$values['userEmail'] 	= $userEmail;
		$values['userDateAdd'] 	= date("Y-m-d H:i:s");
		$this->db->insert('users', $values);
		$userId = $this->db->insert_id();

		$this->db->ignore()->insert('users_groups', array('userId' => $userId, 'groupId' => GROUP_DEFAULT));			

		$query = $this->db
			->where($fieldName, $remoteUserId)
			->get('users');
		return $query->row();
	}

	function updateUserLastAccess() {
		$this->db
			->where('userId', $this->session->userdata('userId'))
			->update('users', array('userLastAccess' => date("Y-m-d H:i:s")));		
	}
	
	function selectToList($num, $offset, $filter = null, $countryId = null, $langId = null, $aRemoteLogin = null, $orderBy = 'userId', $orderDir = 'asc' ){
		$this->db
			->select('SQL_CALC_FOUND_ROWS users.userId, userEmail, CONCAT(userFirstName, \' \', userLastName) AS userFullName, countryName, langName, GROUP_CONCAT(groups.groupName) AS groupsName, userDateAdd, userLastAccess, IF(facebookUserId IS NULL, \'\', \'X\') AS facebookUserId, IF(googleUserId IS NULL, \'\', \'X\') AS googleUserId', false)
			->join('countries', 'users.countryId = countries.countryId', 'left')
			->join('languages', 'users.langId = languages.langId', 'left')
			->join('users_groups', 'users.userId = users_groups.userId', 'left')
			->join('groups', 'groups.groupId = users_groups.groupId', 'left');
						
		if ($filter != null) {	
			$this->db->or_like(array('userFirstName' => $filter, 'userLastName' => $filter));
		}
		if ($countryId != null) {
			$this->db->where('users.countryId', $countryId);
		} 
		if ($langId != null) {
			$this->db->where('users.langId', $langId);
		}
		
		if ($aRemoteLogin != null) {
			$aTmp = array();
			if (element('facebook', $aRemoteLogin)) {
				$aTmp[] = 'facebookUserId IS NOT NULL';
			}
			if (element('google', $aRemoteLogin)) {
				$aTmp[] = 'googleUserId IS NOT NULL';
			}
			if (!empty($aTmp)) {
				$this->db->where('('.implode(' OR ', $aTmp). ' )');
			}
		}
		
		if (!in_array($orderBy, array('userId', 'userEmail', 'userDateAdd', 'userLastAccess' ))) {
			$orderBy = 'userId';
		}

		$query = $this->db
			->group_by('users.userId')
			->order_by($orderBy, $orderDir == 'desc' ? 'desc' : 'asc')
			->get('users', $num, $offset);
						
//pr($this->db->last_query()); die;
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}
	
	function search($filter, $groupId = null){
		$this->db
			->select('DISTINCT users.userId AS id, CONCAT(userFirstName, \' \', userLastName) AS text  ', false)
			->join('users_groups', 'users.userId = users_groups.userId')
			->or_like(array('userFirstName' => $filter, 'userLastName' => $filter))
			->order_by('text');
		 				
		if ($groupId != null) {
			$this->db->where('groupId', $groupId);	
		}
		
		return $this->db->get('users', AUTOCOMPLETE_SIZE)->result_array();
	}	
		
	function select(){
		return $this->db->get('users');
	}

	function get($userId){
		$this->db->where('userId', $userId);
		$result				= $this->db->get('users')->row_array();
		$result['groups'] 	= array_to_select($this->getGroups($userId), 'groupId', 'groupId');
		return $result;
	}	
	
	function getByUserEmail($userEmail) {
		return $this->db->where('userEmail', $userEmail)->get('users')->row_array();
	}	
	
	function getGroups($userId){
		return $this->db
					->where('userId', $userId)
					->get('users_groups')->result_array();
	}	
	
	function save($data){
		$userId = $data['userId'];
		
		$values = array(
			'userEmail' 		=> $data['userEmail'],
			'userFirstName'		=> $data['userFirstName'],
			'userLastName'		=> $data['userLastName'],
			'countryId'			=> $data['countryId']
		);
		

		if ((int)$userId != 0) {		
			$this->db->where('userId', $userId);
			$this->db->update('users', $values);
		}
		else {
			$values['userDateAdd'] 	= date("Y-m-d H:i:s");
			$this->db->insert('users', $values);
			$userId = $this->db->insert_id();
		}

		$this->db->where('userId', $userId)->delete('users_groups');
		if (is_array(element('groups', $data))) {
			foreach ($data['groups'] as $groupId) {
				$this->db->insert('users_groups', array('userId' => $userId, 'groupId' => $groupId));			
			}		
		}
		
		$this->Menu_Model->destroyMenuCache();
		
		return true;
	}
	
	function delete($userId) {
		$this->db->delete('users', array('userId' => $userId));
		return true;
	}		
	
	function editProfile($userId, $data){
		$values = array(
			'userFirstName' => element('userFirstName', $data),
			'userLastName' 	=> element('userLastName', $data),
			'countryId' 	=> element('countryId', $data),
		);		
		
		$this->db->where('userId', $userId)->update('users', $values);

		return true;
	}
	
	function register($userId, $data){
		$values = array(
			'userEmail' 	=> element('userEmail', $data),
			'userPassword' 	=> md5(element('userPassword', $data)),
			'userFirstName' => element('userFirstName', $data),
			'userLastName' 	=> element('userLastName', $data),
			'countryId' 	=> element('countryId', $data),
			'userDateAdd' 	=> date("Y-m-d H:i:s"),
		);
		
		$this->db->insert('users', $values);

		$userId = $this->db->insert_id();

		$this->db->insert('users_groups', array('userId' => $userId, 'groupId' => GROUP_DEFAULT));			

		return true;
	}		
	
	function exitsEmail($userEmail, $userId) {
		$query = $this->db
			->where('userEmail', $userEmail)
			->where('userId !=', $userId)
			->get('users');		
		return ($query->num_rows() > 0);
	}
	
	function checkPassword($userId, $userPassword) {
		$query = $this->db
			->where( array('userId' => $userId, 'userPassword' => md5($userPassword)) )
			->get('users');	
		return ((int)$query->num_rows() > 0);
	}
	
	function updatePassword($userId, $userPassword) {
		$values = array(
			'userPassword' 			=> md5($userPassword),
			'resetPasswordKey'		=> null,
			'resetPasswordDate'		=> null,
		);

		$this->db->update('users', $values, array('userId' => $userId));
	}

	function updateChangeEmailKey($userId, $changeEmailValue, $changeEmailKey) {
		$this->db->update('users', array('changeEmailKey' => $changeEmailKey, 'changeEmailValue' => $changeEmailValue, 'changeEmailDate' => date("Y-m-d H:i:s")), array('userId' => $userId ));
	}

	function updateUserFiltersByUserId($userFilters, $userId) {
			$this->db->where('userId', $userId)->update('users', array('userFilters' => json_encode($userFilters)));
	}
	
	function updateResetPasswordKey($userId, $resetPasswordKey) {
		$this->db->update('users', array('resetPasswordKey' => $resetPasswordKey, 'resetPasswordDate' => date("Y-m-d H:i:s")), array('userId' => $userId ));
	}
	
	function getUserByResetPasswordKey($resetPasswordKey) {
		$query = $this->db
			->where('resetPasswordKey', $resetPasswordKey) 
			->where('DATE_ADD(resetPasswordDate, INTERVAL '.URL_SECRET_TIME.' MINUTE)  > NOW()')
			->get('users')->row_array();	

		return $query;
	}
	
	function getUserByUserIdAndChangeEmailKey($userId, $changeEmailKey) {
		$query = $this->db
			->where('userId', $userId)
			->where('changeEmailKey', $changeEmailKey) 
			->where('DATE_ADD(changeEmailDate, INTERVAL '.URL_SECRET_TIME.' MINUTE)  > NOW()')
			->get('users')->row_array();	
		//pr($this->db->last_query()); die;
		return $query;
	}
	
	function confirmEmail($userId){
		$this->db
			->set('userEmail', 'changeEmailValue', false)
			->set('changeEmailKey', null) 
			->set('changeEmailDate', null)
			->set('changeEmailValue', null)
			->where('userId', $userId)
			->update('users');
		//pr($this->db->last_query()); die;		
	}	
	

	function getUserFiltersByUserId($userId) {
		if ($userId == USER_ANONYMOUS) {
			return '{}';
		}
		
		$query = $this->db
				->select('userFilters')
				->where('userId', $userId)
				->get('users')->result_array();
		return $query[0]['userFilters'];
	}
	
	function updateLangIdByUserId($langId, $userId) {
			$this->db->where('userId', $userId)->update('users', array('langId' => $langId));
	}	
}
