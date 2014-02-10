<?php 
class Users extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Users_Model', 'Countries_Model', 'Languages_Model', 'Groups_Model'));
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$aRemoteLogin = array();
		if (is_array($this->input->get('remoteLogin'))) {
			foreach ($this->input->get('remoteLogin') as $provider) {
				$aRemoteLogin[$provider] = $provider;
			}
		}		

		$query = $this->Users_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'), $this->input->get('countryId'), $this->input->get('langId'), $aRemoteLogin, $this->input->get('orderBy'), $this->input->get('orderDir') );

		$this->load->view('includes/template', array(
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit users'),
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
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
						'source'			=> array_to_select($this->Countries_Model->select(), 'countryId', 'countryName'),
						'appendNullOption'	=> true,
					),				
					'langId' => array(
						'type'				=> 'dropdown',
						'label'				=> $this->lang->line('Language'), 
						'value'				=> $this->input->get('langId'),
						'source'			=> array_to_select($this->Languages_Model->select(), 'langId', 'langName'),
						'appendNullOption'	=> true,
					),
					'remoteLogin[]' => array(
						'type'		=> 'groupCheckBox',
						'label'		=> $this->lang->line('Remote login'),
						'source'	=> array(
							'facebook' 	=> 'Facebook',
							'google' 	=> 'Google',
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
					'type'		=> 'dropdown',
					'label'		=> $this->lang->line('Country'),
					'source'	=> array_to_select($this->Countries_Model->select(), 'countryId', 'countryName')
				),
				'groups[]' => array(
					'type'		=> 'groupCheckBox',
					'label'		=> $this->lang->line('Groups'),
					'source'	=> array_to_select($this->Groups_Model->select(), 'groupId', 'groupName'),
					'showId'	=> true,
				),
			)
		);
		
		if ((int)$userId > 0) {
			$form['urlDelete'] 		= base_url('users/delete/');
			$form['fields']['link']	= array(
				'type'	=> 'link',
				'label'	=> $this->lang->line('View feeds'), 
				'value'	=> base_url('feeds/listing/?userId='.$userId),
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
		}
		
		if ($this->input->is_ajax_request()) {
			return $this->load->view('ajax', array(
				'code'		=> $code, 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit users'),
			'form'		=> populateCrForm($form, $this->Users_Model->get($userId)),
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return $this->load->view('ajax', array(
			'code'		=> $this->Users_Model->delete($this->input->post('userId')), 
			'result' 	=> validation_errors() 
		));	
	}
	
	function search() { // TODO: implementar la seguridad!
		return $this->load->view('ajax', array(
			'result' 	=> $this->Users_Model->search($this->input->get('query'), $this->input->get('groupId'))
		));
	}
	
	function _validate_exitsEmail() {
		return ($this->Users_Model->exitsEmail($this->input->post('userEmail'), (int)$this->input->post('userId')) != true);
	}	
}
