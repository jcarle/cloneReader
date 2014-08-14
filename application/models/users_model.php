<?php
class Users_Model extends CI_Model {
	
	function login($userEmail, $userPassword) {
		$this->db->where('userEmail', $userEmail);
		$this->db->where('userPassword', md5($userPassword));

		return $this->db->get('users');
	}
	
	function loginRemote($userEmail, $userLastName, $userFirstName, $userLocation, $userBirthday, $providerName, $remoteUserId) {
		$fieldName = ($providerName == 'facebook' ? 'facebookUserId' : 'googleUserId');

		$query = $this->db
			->where($fieldName, $remoteUserId)
			->get('users');
		if ($query->num_rows() > 0) { // ya existe
			return $query->row();
		}

		//Seteo el Pais del Usuario
		$countryId = null;
		if(!empty($userLocation)){
			$aZones = array_reverse(explode(',', $userLocation));
			if(!empty($aZones[0])){
				$this->load->model('Countries_Model');
				$rsCountry = $this->Countries_Model->search(trim($aZones[0]));
				if(!empty($rsCountry)){
					$countryId = $rsCountry['id'];
				}
			}
		}
		
		//Seteo Fecha de Cumpleaños del Usuario
		$birthday = null;
		if(!empty($userBirthday)){
			$date = explode('/', $userBirthday);
			if(isset($date[2]) && isset($date[0]) && isset($date[1])){
				$birthday = $date[2].'-'.$date[0]."-".$date[1];
			}
		}
		
		$values = array(
			'userLastName' 		=> $userLastName,
			'userFirstName'		=> $userFirstName,
			'countryId'			=> $countryId,
			'userBirthDate'		=> $birthday,
			$fieldName			=> $remoteUserId
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
		$userId = $this->session->userdata('userId');
		$date	= date("Y-m-d H:i:s");
		
		$this->db
			->where('userId', $userId)
			->update('users', array('userLastAccess' => $date));
			
//$this->db->insert('users_logs', array( 'userId' => $userId, 'userLogDate' => $date ));
	}
	
	
	/*
	 * @param  (array)  $filters es un array con el formato: 
	 * 		array(
	 * 			'filter'         => null, 
	 * 			'countryId'      => null, 
	 * 			'langId'         => null, 
	 * 			'groupId'        => null, 
	 * 			'aRemoteLogin'   => null, 
	 * 			'feedId'         => null,
	 * 		);
	 * 
	 * */
	function selectToList($num, $offset, array $filters = array(), array $orders = array()){
		$this->db
			->select('SQL_CALC_FOUND_ROWS users.userId, userEmail, CONCAT(userFirstName, \' \', userLastName) AS userFullName, countryName, langName, GROUP_CONCAT(groups.groupName) AS groupsName, userDateAdd, userLastAccess, IF(facebookUserId IS NULL, \'\', \'X\') AS facebookUserId, IF(googleUserId IS NULL, \'\', \'X\') AS googleUserId', false)
			->from('users')
			->join('countries', 'users.countryId = countries.countryId', 'left')
			->join('languages', 'users.langId = languages.langId', 'left')
			->join('users_groups', 'users.userId = users_groups.userId', 'left')
			->join('groups', 'groups.groupId = users_groups.groupId', 'left');

		if (element('filter', $filters) != null) {
			$this->db->or_like(array('userFirstName' => $filters['filter'], 'userLastName' => $filters['filter'], 'userEmail' => $filters['filter']));
		}
		if (element('countryId', $filters) != null) {
			$this->db->where('users.countryId', $filters['countryId']);
		} 
		if (element('langId', $filters) != null) {
			$this->db->where('users.langId', $filters['langId']);
		}
		if (element('groupId', $filters) != null) {
			$this->db->where('users_groups.groupId', $filters['groupId']);
		}
		
		if (element('aRemoteLogin', $filters) != null) {
			$aTmp = array();
			if (in_array('facebook', $filters['aRemoteLogin'])) {
				$aTmp[] = 'facebookUserId IS NOT NULL';
			}
			if (in_array('google', $filters['aRemoteLogin'])) {
				$aTmp[] = 'googleUserId IS NOT NULL';
			}
			if (!empty($aTmp)) {
				$this->db->where('('.implode(' OR ', $aTmp). ' )');
			}
		}
		if (element('feedId', $filters) != null) {
			$this->db
				->join('users_feeds', 'users.userId = users_feeds.userId', 'left')
				->where('users_feeds.feedId', $filters['feedId']);
		}
		
		$this->Commond_Model->appendOrderByInQuery($orders, array('userId', 'userEmail', 'userDateAdd', 'userLastAccess' ));

		$query = $this->db
			->group_by('users.userId')
			->limit($num, $offset)
			->get();
						
//pr($this->db->last_query()); die;
						
		$query->foundRows = $this->Commond_Model->getFoundRows();
		return $query;
	}

	/*
	 * @param  (array)  $filters es un array con el formato: 
	 * 		array(
	 * 			'filter'      => null, 
	 * 			'userId'      => null,
	 * 		);
	 * */
	function selectUsersLogsToList($num, $offset, array $filters = array(), array $orders = array()){
		// TODO: mejorar esta query, si hay muchos datos puede explotar
		// quizás haya que agrupar en otra tabla
		$this->db
			->from('usertracking')
			->select(' SQL_CALC_FOUND_ROWS DISTINCT users.userId, userEmail, CONCAT(userFirstName, \' \', userLastName) AS userFullName, user_identifier, DATE_FORMAT(from_unixtime(timestamp), \'%Y-%m-%d\') AS userLogDate ', false) 
			->join('users', 'users.userId = usertracking.user_identifier', 'inner');

		if (element('filter', $filters) != null) {
			$this->db->or_like(array('userFirstName' => $filters['filter'], 'userLastName' => $filters['filter']));
		}
		if (element('userId', $filters) != null) {
			$this->db->where('users.userId', $filters['userId']);
		} 
		
		$this->Commond_Model->appendOrderByInQuery($orders, array('userId', 'userEmail', 'userLogDate' ));


		$query = $this->db
			->limit($num, $offset)
			->get();
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
		
		return $this->db->get('users', config_item('autocompleteSize'))->result_array();
	}

	function searchFriends($filter, $userId){
		return $this->db
			->select('DISTINCT userFriendEmail AS id, userFriendEmail AS text  ', false)
			->where('userId', $userId)
			->like('userFriendEmail', $filter)
			->order_by('text')
			->get('users_friends', config_item('autocompleteSize'))->result_array();
	}
		
	function select(){
		return $this->db->get('users');
	}

	function get($userId){
		$this->db->where('userId', $userId);
		$result				= $this->db->get('users')->row_array();
		$result['groups'] 	= sourceToArray($this->getGroups($userId), 'groupId');
		return $result;
	}	
	
	function getByUserEmail($userEmail) {
		return $this->db->where('userEmail', $userEmail)->get('users')->row_array();
	}	
	
	function getGroups($userId){
		return $this->db
			->select('groupId')
			->where('userId', $userId)
			->get('users_groups')->result_array();
	}	
	
	function save($data){
		$userId = $data['userId'];
		
		$values = array(
			'userEmail' 		=> $data['userEmail'],
			'userFirstName'		=> $data['userFirstName'],
			'userLastName'		=> $data['userLastName'],
			'countryId'			=> element('countryId', $data, null),
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
		$groups = json_decode(element('groups', $data));
		if (is_array($groups)) {
			foreach ($groups as $groupId) {
				$this->db->insert('users_groups', array('userId' => $userId, 'groupId' => $groupId));
			}		
		}
		
		$this->load->model('Menu_Model');
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
			'countryId' 	=> element('countryId', $data, null),
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
			'countryId' 	=> element('countryId', $data, null),
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
			->where('DATE_ADD(resetPasswordDate, INTERVAL '.config_item('urlSecretTime').' MINUTE)  > NOW()')
			->get('users')->row_array();	

		return $query;
	}
	
	function getUserByUserIdAndChangeEmailKey($userId, $changeEmailKey) {
		$query = $this->db
			->where('userId', $userId)
			->where('changeEmailKey', $changeEmailKey) 
			->where('DATE_ADD(changeEmailDate, INTERVAL '.config_item('urlSecretTime').' MINUTE)  > NOW()')
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
	
	
	function saveUserFriend($userId, $userFriendEmail, $userFriendName) {
		if (trim($userFriendEmail) == '') {
			return null;
		}

		$query = $this->db
			->where(array( 'userId' => $userId, 'userFriendEmail' => $userFriendEmail))
			->get('users_friends');
		if ($query->num_rows() > 0) {
			$query = $query->row_array();
			return $query['userFrieldId'];
		}
		
		$this->db->insert('users_friends', array(
			'userId' 			=> $userId, 
			'userFriendEmail' 	=> $userFriendEmail,
			'userFriendName' 	=> $userFriendName,
		));
		return $this->db->insert_id();
	}
	
	function saveSharedByEmail($data) {
		$values = array(
			'userId'				=> element('userId', $data),
			'entryId'				=> element('entryId', $data),
			'userFriendId'			=> element('userFriendId', $data),
			'shareByEmailDate'		=> date("Y-m-d H:i:s"),
			'shareByEmailComment'	=> element('shareByEmailComment', $data),
		);

		$this->db->insert('shared_by_email', $values);
		return $this->db->insert_id();
	}
	
	
	function getUserId() {
		return $this->session->userdata('userId');
	}	
	
	function allowTracking() {
		if ($this->safety->isCommandLine() == true) {
			return false;
		}
		return true;
	}
}
