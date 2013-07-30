<?php 
class Register extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Users_Model', 'Countries_Model'));
	}  
	
	function index() {
		$this->register();
	}
	
	function register() {
		if (! $this->safety->allowByControllerName('register') ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');
		$data 	= array(); //$this->Users_Model->get($userId);
		
		$form = array(
			'frmId'			=> 'frmRegister',
			'messages'	 	=> getRulesMessages(),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> Register</button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> 'Email',
					'value'	=> element('userEmail', $data)
				),
				'userPassword' => array(
					'type'	=> 'password',
					'label'	=> 'Password',
					'value'	=> ''
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
					
			$code = $this->Users_Model->register($userId, $this->input->post());
			if ($code != true) {
				return $this->load->view('ajax', array(
					'code'		=> $code, 
					'result' 	=> validation_errors() 
				));
			}
			
			$code = $this->safety->login($this->input->post('userEmail'), $this->input->post('userPassword'));
			return $this->load->view('ajax', array(
				'code'		=> true, 
				'result' 	=> array('goToUrl' => base_url('home')) 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> 'Signup',
			'form'		=> $form,
				  
		));		
	}
}
