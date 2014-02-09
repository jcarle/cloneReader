<?php
class ForgotPassword extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Users_Model'));
	}
	
	function index() {
		if (! $this->safety->allowByControllerName('forgotPassword') ) { return errorForbidden(); }
		
		$form = array(
			'frmId'			=> 'frmForgotPassword',
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Send').' </button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
				),
			)
		);
		
		$form['rules'] 	= array( 
			array(
				'field' => 'userEmail',
				'label' => $form['fields']['userEmail']['label'],
				'rules' => 'trim|required|valid_email|callback__validate_notExitsEmail'
			),
		);
		
		$this->form_validation->set_rules($form['rules']);
		
		if ($this->input->post() != false) {
			return $this->_sendEmailToResetPassword();
		}

		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Reset password'),
			'form'		=> $form,
		));
	}
	
	function _sendEmailToResetPassword() {
		if ($this->form_validation->run() == FALSE) {
			return $this->load->view('ajax', array(
				'code'		=> false,
				'result' 	=> validation_errors()
			));	
		}

		$this->load->library('email');
		$this->load->helper('email');

		$user 				= $this->Users_Model->getByUserEmail($this->input->post('userEmail'));
		$resetPasswordKey 	= random_string('alnum', 20);
		$url 				= base_url('resetPassword?key='.$resetPasswordKey);
		$message 			= 
			'<p>'.sprintf($this->lang->line('Someone recently requested that the password be reset for %s'), $user['userFirstName'].' '.$user['userLastName']).'</p>'.
			'<p>'.sprintf($this->lang->line('To reset your password please click <a href="%s">here</a>'), $url).'</p>'.
			'<p>'.$this->lang->line('If this is a mistake just ignore this email - your password will not be changed').'</p>';
		
		$this->Users_Model->updateResetPasswordKey($user['userId'], $resetPasswordKey);

		$this->email->from('clonereader@gmail.com', 'cReader BETA');
		$this->email->to($user['userEmail']); 
		$this->email->subject('cReader - '.$this->lang->line('Reset password'));
		$this->email->message(getEmailTemplate($message, $url));
		$this->email->send();
		//echo $this->email->print_debugger();	die;	

		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> array( 'notification' => $this->lang->line('We have sent you an email with instructions to reset your password')),
		));	
	}
	
	function _validate_notExitsEmail() {
		return $this->Users_Model->exitsEmail($this->input->post('userEmail'), 0);
	}	
}
