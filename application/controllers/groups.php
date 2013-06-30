<?php 
class Groups extends CI_Controller {

    function __construct() {
        parent::__construct();	
		
		$this->load->model(array('Groups_Model', 'Controllers_Model'));
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$this->load->view('includes/template', array(
			'controller'	=> strtolower(__CLASS__),
			'view'			=> 'includes/paginatedList', 
			'title'			=> 'Editar Grupos',
			'query'			=> $this->Groups_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter')),
			'pagination'	=> $this->pagination
		));
	}
	
	function edit($groupId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$data = $this->Groups_Model->get($groupId);
		
		$form = array(
			'frmId'		=> 'frmGroupsEdit',
			'messages' 	=> getRulesMessages(),
			'fields'	=> array(
				'groupId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('groupId', $data, 0)
				),
				'groupName' => array(
					'type'	=> 'text',
					'label'	=> 'Nombre', 
					'value'	=> element('groupName', $data)
				),
				'webSiteHome' => array(
					'type'	=> 'text',
					'label'	=> 'Home Page',
					'value'	=> element('webSiteHome', $data)
				),
				'controllers[]' => array(
					'type'		=> 'groupCheckBox',
					'label'		=> 'Controllers',
					'source'	=> array_to_select($this->Controllers_Model->select(true)->result_array(), 'controllerId', 'controllerName'), 
					'value'		=> $data['controllers']
				)
			)
		);
		
		$form['rules'] 	= array( 
			array(
    			'field' => 'groupName',
				'label' => $form['fields']['groupName']['label'],
				'rules' => 'required'
			),
		);		

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		if ($this->input->is_ajax_request()) { // save data					
			return $this->load->view('ajax', array(
				'code'		=> $this->Groups_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'			=> 'includes/formValidation', 
			'title'			=> 'Editar Grupos',
			'form'			=> $form,
		));		
	}

	function add(){
		$this->edit(0);
	}		
}
