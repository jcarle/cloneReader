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
			'view'			=> 'includes/paginatedList', 
			'title'			=> 'Edit Users',
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array('userId' => '#', 'userEmail' => 'Email', 'userFullName' => 'Nombre', 'countryName' => 'País', 'groupsName' => 'Grupos' ),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'pagination'	=> $this->pagination,
				//'allowDelete'	=> true
			)
		));
	}
	
	function edit($userId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$data = $this->Users_Model->get($userId);
		
		$form = array(
			'frmId'		=> 'frmUsersEdit',
			'messages' 	=> getRulesMessages(),
			'fields'	=> array(
				'userId' => array(
					'type' 		=> 'hidden',
					'value'		=> element('userId', $data, 0),
				),
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> 'Email',
					'value'	=> element('userEmail', $data)
				),
				'userFirstName' => array(
					'type'	=> 'text',
					'label'	=> 'Nombre', 
					'value'	=> element('userFirstName', $data)
				),
				'userLastName' => array(
					'type'	=> 'text',
					'label'	=> 'Apellido', 
					'value'	=> element('userLastName', $data)
				),
				'countryId' => array(
					'type'		=> 'dropdown',
					'label'		=> 'País',
					'value'		=> element('countryId', $data),
					'source'	=> array_to_select($this->Countries_Model->select(), 'countryId', 'countryName')
				),
				'groups[]' => array(
					'type'		=> 'groupCheckBox',
					'label'		=> 'Grupos',
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
					'result' 	=> 'El mail ingresado ya existe en la base de datos' 
				));
			}
					
			return $this->load->view('ajax', array(
				'code'		=> $this->Users_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> 'Edit Users',
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
}
