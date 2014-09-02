<?php 
class Register extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Users_Model', 'Countries_Model', 'Tasks_Model'));
	}
	
	function index() {
		$this->register();
	}
	
	function register() {
		if (! $this->safety->allowByControllerName('register') ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');

		$form = array(
			'frmId'			=> 'frmRegister',
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="fa fa-sign-in"></i> '.$this->lang->line('Register').'</button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
				),
				'userPassword' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('Password'),
				),				
				'userFirstName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('First name'), 
				),
				'userLastName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Last name'), 
				),
				'countryId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Country'),
					'appendNullOption' 	=> true,
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

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Users_Model->register($userId, $this->input->post());
				$userEmail = $this->input->post('userEmail');
				
				if ($this->safety->login($this->input->post('userEmail'), $this->input->post('userPassword')) != true) {
					return loadViewAjax(false);
				}
	
				$userId          = $this->session->userdata('userId');
				$confirmEmailKey = random_string('alnum', 20);
				$this->Users_Model->updateConfirmEmailKey($userId, $userEmail, $confirmEmailKey);
				$this->Tasks_Model->addTask('sendEmailWelcome', array('userId' => $userId));
				
				return loadViewAjax($code, array('goToUrl' => base_url('home'), 'skipAppLink' => true));
			}

			return loadViewAjax($code);
		}
				
		$form['fields']['countryId']['source'] = $this->Countries_Model->selectToDropdown();

		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'meta'		=> array( 'title' => $this->lang->line('Signup') ),
			'form'		=> populateCrForm($form, array()),
		));
	}

	function _validate_exitsEmail() {
		return ($this->Users_Model->exitsEmail($this->input->post('userEmail'), 0) != true);
	}
}
