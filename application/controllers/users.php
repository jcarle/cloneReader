<?php 
class Users extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Users_Model', 'Countries_Model', 'Groups_Model'));
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$query = $this->Users_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'));

				
		$this->load->view('includes/template', array(
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit users'),
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array('userEmail' => $this->lang->line('Email'), 'userFullName' => $this->lang->line('Name'), 'countryName' => $this->lang->line('Country'), 'groupsName' => $this->lang->line('Groups') ),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'pagination'	=> $this->pagination,
				'showId'		=> true
			)
		));
	}
	
	function edit($userId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$data = $this->Users_Model->get($userId);
		
		$form = array(
			'frmId'		=> 'frmUsersEdit',
			'messages' 	=> getCrFormRulesMessages(),
			'fields'	=> array(
				'userId' => array(
					'type' 		=> 'hidden',
					'value'		=> element('userId', $data, 0),
				),
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
					'value'	=> element('userEmail', $data)
				),
				'userFirstName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('First Name'), 
					'value'	=> element('userFirstName', $data)
				),
				'userLastName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Last Name'), 
					'value'	=> element('userLastName', $data)
				),
				'countryId' => array(
					'type'		=> 'dropdown',
					'label'		=> $this->lang->line('Country'),
					'value'		=> element('countryId', $data),
					'source'	=> array_to_select($this->Countries_Model->select(), 'countryId', 'countryName')
				),
				'groups[]' => array(
					'type'		=> 'groupCheckBox',
					'label'		=> $this->lang->line('Groups'),
					'source'	=> array_to_select($this->Groups_Model->select(), 'groupId', 'groupName'),
					'value'		=> $data['groups']
				)
			)
		);
		
		if ((int)element('userId', $data) > 0) {
			$form['urlDelete'] = base_url('users/delete/');
		}
		
		$form['rules'] 	= array( 
			array(
				'field' => 'userEmail',
				'label' => $form['fields']['userEmail']['label'],
				'rules' => 'required|valid_email'
			),
			array(
				'field' => 'userFirstName',
				'label' => $form['fields']['userFirstName']['label'],
				'rules' => 'required'
			),
			array(
				'field' => 'userLastName',
				'label' => $form['fields']['userLastName']['label'],
				'rules' => 'required'
			)
		);		

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		if ($this->input->is_ajax_request()) { // save data
			if ($this->Users_Model->exitsEmail($this->input->post('userEmail'), (int)$this->input->post('userId')) == true) {
				return $this->load->view('ajax', array(
					'code'		=> false, 
					'result' 	=> $this->lang->line('The email entered already exists in the database') 
				));
			}
					
			return $this->load->view('ajax', array(
				'code'		=> $this->Users_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit users'),
			'form'		=> $form,
				  
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
}
