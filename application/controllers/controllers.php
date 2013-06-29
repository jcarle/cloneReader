<?php 
class Controllers extends CI_Controller {

    function __construct() {
        parent::__construct();	
		
		$this->load->model('Controllers_Model');
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
			'title'			=> 'Editar Controles',
			'query'			=> $this->Controllers_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter')),
			'pagination'	=> $this->pagination
		));
	}
	
	function edit($controllerId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$data = $this->Controllers_Model->get($controllerId);
		
		$form = array(
			'frmId'		=> 'frmControllersEdit',
			'messages' 	=> getRulesMessages(),
			'fields'	=> array(
				'controllerId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('controllerId', $data, 0)
				),
				'controllerName' => array(
					'type'	=> 'text',
					'label'	=> 'Controller',
					'value'	=> element('controllerName', $data)
				),
				'controllerUrl' => array(
					'type'	=> 'text',
					'label'	=> 'Url', 
					'value'	=> element('controllerUrl', $data)
				),
				'controllerActive' => array(
					'type'		=> 'checkbox',
					'label'		=> 'Activo',
					'checked'	=> element('controllerActive', $data)
				)
			)
		);
		
		$form['rules'] = array( 
			array(
    			'field' => 'controllerName',
				'label' => $form['fields']['controllerName']['label'],
				'rules' => 'required'
			),
			array(
    			'field' => 'controllerUrl',
				'label' => $form['fields']['controllerUrl']['label'],
				'rules' => 'required'
			),			
		);		

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		if ($this->input->is_ajax_request()) { // save data			
		
			if ($this->Controllers_Model->exitsController($this->input->post('controllerName'), (int)$this->input->post('controllerId')) == true) {
				return $this->load->view('ajax', array(
					'code'		=> false, 
					'result' 	=> 'El nombre ingresado ya existe en la base de datos' 
				));
			}		
				
			return $this->load->view('ajax', array(
				'code'		=> $this->Controllers_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/formValidation', 
			'title'		=> 'Editar Controles',
			'form'		=> $form,
		));		
	}

	function add(){
		$this->edit(0);
	}		
}
