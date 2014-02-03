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
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$this->load->view('includes/template', array(
			'view'		=> 'profile', 
			'title'		=> $this->lang->line('Edit profile'),
			'hasForm'	=> true,
			'aJs'		=> array('profile.js'),
		));		
	}


	function _getFrmEditProfile() {
		$userId = $this->session->userdata('userId');
		$data 	= $this->Users_Model->get($userId);
		
		$this->load->helper('email');
		
		$form = array(
			'frmId'			=> 'frmEditProfile',
			'action'		=> base_url('profile/saveEditProfile/'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Save').' </button>'),
			'fields'		=> array(
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
				'userEmail' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Email'),
					'value'		=> valid_email(element('userEmail', $data)) == true ? element('userEmail', $data) : '',
					'disabled' 	=> true
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

		return $form;
	}
		

	function frmEditProfile() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }

		$form = $this->_getFrmEditProfile();
		
		$this->load->view('ajax', array(
			'view'			=> 'includes/crAjaxForm',
			'form'			=> $form,
			'title'			=> $this->lang->line('Edit profile'),
			'code'			=> true
		));
	}

	function saveEditProfile() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = $this->_getFrmEditProfile();
		
		if ($this->form_validation->run() == FALSE) {
			$code 		= false;
			$message 	= validation_errors();
		}
		else {		
			$this->Users_Model->editProfile($this->session->userdata('userId'), $this->input->post());
			$code 		= true;
			$message 	= array('notification' => $this->lang->line('Data updated successfully'));
		}
		
		return $this->load->view('ajax', array(
			'code'		=> $code,
			'result' 	=> $message
		));
	}


	function _getFrmChangeEmail() {
		$userId = $this->session->userdata('userId');
		$data 	= $this->Users_Model->get($userId);
		
		$this->load->helper('email');
		
		$form = array(
			'frmId'			=> 'frmChangeEmail',
			'action'		=> base_url('profile/sendEmailToChangeEmail/'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Save').' </button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
					'value'	=> valid_email(element('userEmail', $data)) == true ? element('userEmail', $data) : '',
				),
			)
		);
		
		$form['rules'] 	= array( 
			array(
				'field' => 'userEmail',
				'label' => $form['fields']['userEmail']['label'],
				'rules' => 'trim|required|valid_email|callback__validate_exitsEmail'
			),
		);		

		$this->form_validation->set_rules($form['rules']);
		return $form;
	}
		
		
	function frmChangeEmail() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$this->load->view('ajax', array(
			'view'			=> 'includes/crAjaxForm',
			'form'			=> $this->_getFrmChangeEmail(),
			'title'			=> $this->lang->line('Change email'),
			'code'			=> true
		));
	}

	function sendEmailToChangeEmail() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = $this->_getFrmChangeEmail();

		if ($this->form_validation->run() == FALSE) {
			return $this->load->view('ajax', array(
				'code'		=> false,
				'result' 	=> validation_errors()
			));	
		}

		$this->load->library('email');

		$userId 		= $this->session->userdata('userId');
		$userEmail 		= $this->input->post('userEmail');
		$user 			= $this->Users_Model->get($userId);
		$changeEmailKey = random_string('alnum', 20);
		
		$this->Users_Model->updateChangeEmailKey($userId, $userEmail, $changeEmailKey);

		$this->email->from('clonereader@gmail.com', 'cReader BETA');
		$this->email->to($userEmail); 
		$this->email->subject('cReader - '.$this->lang->line('Change email'));
		$this->email->message(sprintf($this->lang->line('Hello %s, <p>To change your  email in cReader, click here %s  </p> Regards'), $user['userFirstName'], base_url('profile/confirmEmail/'.$changeEmailKey)));
		$this->email->send();
		//echo $this->email->print_debugger();	die;	

		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> array( 'notification' => $this->lang->line('We have sent you an email with instructions to change your email')),
		));	
	}

	function confirmEmail($changeEmailKey) {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');
		$user 	= $this->Users_Model->getUserByUserIdAndChangeEmailKey($userId, $changeEmailKey);
		if (empty($user)) {
			return error404();
		}
		
		$this->Users_Model->confirmEmail($userId);

		$this->load->view('includes/template', array(
			'view'		=> 'message', 
			'title'		=> $this->lang->line('Change email'),
			'message'	=> $this->lang->line('Your email has been updated')
		));	
	}
	
	function _getFrmChangePassword() {
		$form = array(
			'frmId'			=> 'frmChangePassword',
			'action'		=> base_url('profile/saveChangePassword/'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Change password').' </button>'),
			'fields'		=> array(
				'passwordOld' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('Current password'), 
				),
				'passwordNew' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('New password'), 
				),
				'passwordRepeatNew' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('Repeat new password'), 
				),
			)
		);
		
		$form['rules'] = array( 
			array(
				'field' => 'passwordOld',
				'label' => $form['fields']['passwordOld']['label'],
				'rules' => 'trim|required|callback__validate_password'
			),
			array(
				'field' => 'passwordNew',
				'label' => $form['fields']['passwordNew']['label'],
				'rules' => 'trim|required|matches[passwordRepeatNew]'
			),
			array(
				'field' => 'passwordRepeatNew',
				'label' => $form['fields']['passwordRepeatNew']['label'],
				'rules' => 'trim|required'
			)
		);		
		
		$this->form_validation->set_rules($form['rules']);
		
		return $form;
	}

	function frmChangePassword() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = $this->_getFrmChangePassword();

		$this->load->view('ajax', array(
			'view'			=> 'includes/crAjaxForm',
			'form'			=> $form,
			'title'			=> $this->lang->line('Change password'),
			'code'			=> true
		));
	}
	
	function saveChangePassword() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = $this->_getFrmChangePassword();
		
		if ($this->form_validation->run() == FALSE) {
			$code 		= false;
			$message 	= validation_errors();
		}
		else {
			$this->Users_Model->updatePassword($this->session->userdata('userId'), $this->input->post('passwordNew'));		
			$code 		= true;
			$message 	= array('notification' => $this->lang->line('Data updated successfully'));
		}
		
		return $this->load->view('ajax', array(
			'code'		=> $code,
			'result' 	=> $message 
		));				
	}
	
	function _validate_password() {
		return $this->Users_Model->checkPassword($this->session->userdata('userId'), $this->input->post('passwordOld'));
	}
	
	function frmRemoveAccount() {
		return $this->load->view('ajax', array(
			'code'		=> false,
			'result' 	=> 'coming soon'
		));
	}
		

	
	function _validate_exitsEmail() {
		return ($this->Users_Model->exitsEmail($this->input->post('userEmail'), 0) != true);
	}
}
