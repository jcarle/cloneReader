<?php 
class Users extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Users_Model', 'Countries_Model', 'Languages_Model', 'Groups_Model', 'Feeds_Model'));
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$aRemoteLogin 	= array();
		$remoteLogin	= json_decode($this->input->get('remoteLogin'));
		if (is_array($remoteLogin)) {
			foreach ($remoteLogin as $provider) {
				$aRemoteLogin[] = $provider;
			}
		}
		
		$feed 	= null;
		$feedId = $this->input->get('feedId');
		if ($feedId != null) {
			$feed = $this->Feeds_Model->get($feedId);
		}		

		$query = $this->Users_Model->selectToList(config_item('pageSize'), ($page * config_item('pageSize')) - config_item('pageSize'), $this->input->get('filter'), $this->input->get('countryId'), $this->input->get('langId'), $this->input->get('groupId'), $aRemoteLogin, $feedId, $this->input->get('orderBy'), $this->input->get('orderDir') );

		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit users'),
			'list'			=> array(
				'urlList'		=> 'users/listing',
				'urlEdit'		=> 'users/edit/%s',
				'urlAdd'		=> 'users/add',
				'columns'		=> array(
					'userEmail' 		=> $this->lang->line('Email'), 
					'userFullName' 		=> $this->lang->line('Name'), 
					'countryName' 		=> $this->lang->line('Country'), 
					'langName' 			=> $this->lang->line('Language'),
					'groupsName' 		=> $this->lang->line('Groups'),
					'userDateAdd'		=> array('class' => 'datetime', 'value' => $this->lang->line('Record date')),
					'userLastAccess'	=> array('class' => 'datetime', 'value' => $this->lang->line('Last access')),
					'facebookUserId'	=> 'Facebook', 
					'googleUserId'		=> 'Google',
				),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'showId'		=> true,
				'filters'		=> array(
					'countryId' => array(
						'type'				=> 'dropdown',
						'label'				=> $this->lang->line('Country'),
						'value'				=> $this->input->get('countryId'),
						'source'			=> $this->Countries_Model->selectToDropdown(),
						'appendNullOption'	=> true,
					),				
					'langId' => array(
						'type'				=> 'dropdown',
						'label'				=> $this->lang->line('Language'), 
						'value'				=> $this->input->get('langId'),
						'source'			=> $this->Languages_Model->selectToDropdown(),
						'appendNullOption'	=> true,
					),
					'groupId' => array(
						'type'				=> 'dropdown',
						'label'				=> $this->lang->line('Group'),
						'source'			=> $this->Groups_Model->selectToDropdown(),
						'value'				=> $this->input->get('groupId'),
						'appendNullOption'	=> true,
					),
					'feedId' => array(
						'type' 		=> 'typeahead',
						'label'		=> $this->lang->line('Feed'),
						'source' 	=> base_url('feeds/search/'),
						'value'		=> array( 'id' => element('feedId', $feed), 'text' => element('feedName', $feed)), 
					),
					'remoteLogin' => array(
						'type'		=> 'groupCheckBox',
						'label'		=> $this->lang->line('Remote login'),
						'source'	=> array(
							array('id' => 'facebook', 	'text' => 'Facebook'),
							array('id' => 'google' ,	'text'	=> 'Google'),
						), 
						'value'		=> $aRemoteLogin
					)
				),
				'sort' => array(
					'userId'			=> '#',
					'userEmail'			=> $this->lang->line('Email'),
					'userDateAdd'		=> $this->lang->line('Record date'),
					'userLastAccess'	=> $this->lang->line('Last access'),
				)
			)
		));
	}
	
	function edit($userId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'frmId'		=> 'frmUsersEdit',
			'fields'	=> array(
				'userId' => array(
					'type' 		=> 'hidden',
					'value'		=> $userId,
				),
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
				),
				'userFirstName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('First Name'), 
				),
				'userLastName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Last Name'), 
				),
				'countryId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Country'),
					'appendNullOption'	=> true,
				),
				'groups' => array(
					'type'		=> 'groupCheckBox',
					'label'		=> $this->lang->line('Groups'),
					'showId'	=> true,
				),
			)
		);
		
		if ((int)$userId > 0) {
			$form['urlDelete'] 		= base_url('users/delete/');
			$form['fields']['userFeeds']	= array(
				'type'	=> 'link',
				'label'	=> $this->lang->line('View feeds'), 
				'value'	=> base_url('feeds/listing/?userId='.$userId),
			);
			$form['fields']['userLogs']	= array(
				'type'	=> 'link',
				'label'	=> $this->lang->line('View logs'), 
				'value'	=> base_url('users/logs/?userId='.$userId.'&orderBy=userLogDate&orderDir=desc'),
			);			
		}
		
		$form['rules'] 	= array(
			array(
				'field' => 'userEmail',
				'label' => $form['fields']['userEmail']['label'],
				'rules' => 'trim|required|valid_email|callback__validate_exitsEmail'
			),
			array(
				'field' => 'userFirstName',
				'label' => $form['fields']['userFirstName']['label'],
				'rules' => 'trim|required'
			),
			array(
				'field' => 'userLastName',
				'label' => $form['fields']['userLastName']['label'],
				'rules' => 'trim|required'
			)
		);		

		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Users_Model->save($this->input->post());
			}
		
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}
		
		$form['fields']['countryId']['source'] 	= $this->Countries_Model->selectToDropdown();
		$form['fields']['groups']['source']		= $this->Groups_Model->selectToDropdown();

		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit users'),
			'form'		=> populateCrForm($form, $this->Users_Model->get($userId)),
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return loadViewAjax($this->Users_Model->delete($this->input->post('userId')));
	}
	
	function search() { // TODO: implementar la seguridad!
		return $this->load->view('ajax', array(
			'result' 	=> $this->Users_Model->search($this->input->get('query'), $this->input->get('groupId'))
		));
	}
	
	function searchFriends() {
		if ($this->session->userdata('userId') == USER_ANONYMOUS) {
			return errorForbidden();
		}
		
		// FIXME: chapuza; hacer que los fields typeahead permitan agregar datos y validarlos
		// Si el item que ingreso el usuario es un mail valido, lo apendeo a los resultados del autocomplete para que pueda seleccionarlo!
		$this->load->helper('email');
		
		$query 	= $this->input->get('query');
		$result = $this->Users_Model->searchFriends($query, $this->session->userdata('userId'));
		
		
		if (valid_email($query) == true) {
			$result[] = array('id' => $query, 'text' => $query);
		}
		
		return $this->load->view('ajax', array(
			'result' 	=> $result
		));
	}	
	
	function logs() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$user 	= null;
		$userId = $this->input->get('userId');
		if ($userId != null) {
			$user = $this->Users_Model->get($userId);
		}

		$query = $this->Users_Model->selectUsersLogsToList(config_item('pageSize'), ($page * config_item('pageSize')) - config_item('pageSize'), $this->input->get('filter'), $userId, $this->input->get('orderBy'), $this->input->get('orderDir') );

		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('User logs'),
			'list'			=> array(
				'controller'	=> 'users/logs',
				'readOnly'		=> true,
				'columns'		=> array(
					'userEmail' 		=> $this->lang->line('Email'),
					'userFullName' 		=> $this->lang->line('Name'), 
					'userLogDate'		=> array('class' => 'date', 'value' => $this->lang->line('Date')),
				),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'showId'		=> true,
				'filters'		=> array(
					'userId' => array(
						'type' 			=> 'typeahead',
						'label'			=> $this->lang->line('User'),
						'source' 		=> base_url('users/search/'),
						'value'			=> array( 'id' => element('userId', $user), 'text' => element('userFirstName', $user).' '.element('userLastName', $user) ), 
						'multiple'		=> false,
						'placeholder' 	=> $this->lang->line('User')
					),					
				),
				'sort' => array(
					'userId'		=> '#',
					'userLogDate'	=> $this->lang->line('Date'),
				)
			)
		));
	}	
	
	function _validate_exitsEmail() {
		return ($this->Users_Model->exitsEmail($this->input->post('userEmail'), (int)$this->input->post('userId')) != true);
	}
}
