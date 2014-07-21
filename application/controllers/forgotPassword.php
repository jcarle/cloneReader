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
			if ($this->form_validation->run() == FALSE) {
				return loadViewAjax(false);
			}else{
				$this->load->model(array('Tasks_Model'));
				$data = array(
					'userEmail' => $this->input->post('userEmail')
				);
				$this->Tasks_Model->addTask('sendEmailToResetPassword', $data);
				return loadViewAjax(true, array( 'notification' => $this->lang->line('We have sent you an email with instructions to reset your password')));
			}
		}

		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'meta'		=> array( 'title' => $this->lang->line('Reset password') ),
			'form'		=> $form,
		));
	}
		
	function _validate_notExitsEmail() {
		return $this->Users_Model->exitsEmail($this->input->post('userEmail'), 0);
	}	
}
