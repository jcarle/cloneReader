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
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-signin"></i> '.$this->lang->line('Register').'</button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
					'value'	=> element('userEmail', $data)
				),
				'userPassword' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('Password'),
					'value'	=> ''
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
			)
		);
		
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
		
		if ($this->input->is_ajax_request()) { // save data
					
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
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Signup'),
			'form'		=> $form,
				  
		));		
	}

	function _validate_exitsEmail() {
		return $this->Users_Model->exitsEmail($this->input->post('userEmail'), 0);
	}
}
