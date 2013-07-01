<?php 
class Profile extends CI_Controller {

    function __construct() {
        parent::__construct();	
		
		$this->load->model(array('Users_Model', 'Countries_Model'));
	}  
	
	function index() {
		$this->edit();
	}
	
	function edit() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$userId = $this->session->userdata('userId');
		$data 	= $this->Users_Model->get($userId);
		
		$form = array(
			'frmId'			=> 'frmUsersEdit',
			'messages'	 	=> getRulesMessages(),
			'showBtnBack'	=> false,
			'fields'		=> array(
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
			)
		);
		
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
			if ($this->Users_Model->exitsEmail($this->input->post('userEmail'), (int)$userId) == true) {
				return $this->load->view('ajax', array(
					'code'		=> false, 
					'result' 	=> 'El mail ingresado ya existe en la base de datos' 
				));
			}
					
			return $this->load->view('ajax', array(
				'code'		=> $this->Users_Model->editProfile($userId, $this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> 'Edit Profile',
			'form'		=> $form,
				  
		));		
	}
}
