<?php
class ResetPassword extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Users_Model'));
	}
	
	function index() {
		if (! $this->safety->allowByControllerName('forgotPassword') ) { return errorForbidden(); }
		
		$resetPasswordKey 	= $this->input->get('key');
		$user 				= $this->Users_Model->getUserByResetPasswordKey($resetPasswordKey);
		if (empty($user)) {
			return error404();
		}
		
		$form = array(
			'frmId'			=> 'frmResetPassword',
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Reset password').' </button>'),			
			'fields'		=> array(
				'resetPasswordKey' => array(
					'type'	=> 'hidden',
					'value' => $resetPasswordKey, 
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
		
		if ($this->input->post() != false) {
			return $this->_saveResetPassword();
		}		
		
		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crForm',
			'form'			=> $form,
			'meta'			=> array( 'title' => $this->lang->line('Reset password') ),
			'code'			=> true
		));		
	}

	function _saveResetPassword() {
		$resetPasswordKey 	= $this->input->post('resetPasswordKey');
		$user 				= $this->Users_Model->getUserByResetPasswordKey($resetPasswordKey);
		if (empty($user)) {
			return error404();
		}
		
		if ($this->form_validation->run() == FALSE) {
			return loadViewAjax(false);
		}
		
		$this->Users_Model->updatePassword($user['userId'], $this->input->post('passwordNew'));
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully'), 'goToUrl' => base_url('login')));
	}
}
