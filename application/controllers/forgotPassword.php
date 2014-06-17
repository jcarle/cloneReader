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
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Send').' </button>'),
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

		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'meta'		=> array(
				'title'			=> $this->lang->line('Reset password'),
				'h1'			=> $this->lang->line('Reset password'),
			),
			'form'		=> $form,
		));
	}
	
	function _sendEmailToResetPassword() {
		if ($this->form_validation->run() == FALSE) {
			return loadViewAjax(false);
		}

		$this->load->library('email');
		$this->load->helper('email');

		$user 				= $this->Users_Model->getByUserEmail($this->input->post('userEmail'));
		$resetPasswordKey 	= random_string('alnum', 20);
		$url 				= base_url('resetPassword?key='.$resetPasswordKey);
		$message 			= $this->load->view('pageEmail',
			array(
				'emailView' 	=> 'email/resetPassword.php',
				'user'			=> $user,
				'url'			=> $url,
			),
			true);
		
		$this->Users_Model->updateResetPasswordKey($user['userId'], $resetPasswordKey);

		$this->email->from(config_item('emailFrom'), config_item('siteName'));
		$this->email->to($user['userEmail']); 
		$this->email->subject(config_item('siteName').' - '.$this->lang->line('Reset password'));
		$this->email->message($message);
		$this->email->send();
		//echo $this->email->print_debugger();	die;	

		return loadViewAjax(true, array( 'notification' => $this->lang->line('We have sent you an email with instructions to reset your password')));
	}
	
	function _validate_notExitsEmail() {
		return $this->Users_Model->exitsEmail($this->input->post('userEmail'), 0);
	}	
}
