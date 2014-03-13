<?php 
class Feedback extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Comments_Model', 'Users_Model'));
	}
	
	function index() {
		if (! $this->safety->allowByControllerName('feedback') ) { return errorForbidden(); }
		
		$this->load->helper('email');
		
		$userId = (int)$this->session->userdata('userId');
		$data	= array();
		if ($userId != USER_ANONYMOUS) {		
			$data = $this->Users_Model->get($userId);
		}

		$commentUserEmail = element('userEmail', $data);
		if (valid_email($commentUserEmail) == false) {
			$commentUserEmail = '';
		}

		$form = array(
			'frmId'		=> 'frmCommentEdit',
			'callback' 	=> 'function(response) { $.Feedback.onSaveFeedback(response); };',
			'fields' => array( 
				'commentId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('commentId', $data, 0)
				),
				'commentUserName' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Name'), 
					'value'		=> trim(element('userFirstName', $data).' '.element('userLastName', $data)),
				),						
				'commentUserEmail' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Email'), 
					'value'		=> $commentUserEmail
				),										
				'commentDesc' => array(
					'type'		=> 'textarea',
					'label'		=> $this->lang->line('Comment'), 
					'value'		=> ''
				),
			),
			'buttons'		=> array( '<button type="submit" class="btn btn-primary"><i class="icon-comment"></i> '.$this->lang->line('Send').'</button> '),
		);
		
		$form['rules'] = array(
			array(
				'field' => 'commentUserName',
				'label' => $form['fields']['commentUserName']['label'],
				'rules' => 'trim|required'
			),
			array(
				'field' => 'commentUserEmail',
				'label' => $form['fields']['commentUserEmail']['label'],
				'rules' => 'trim|required|valid_email'
			),			
			array(
				'field' => 'commentDesc',
				'label' => $form['fields']['commentDesc']['label'],
				'rules' => 'trim|required'
			),
		);	

		$this->form_validation->set_rules($form['rules']);
		
		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Comments_Model->saveFeedback($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}
		
		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Feedback'),
			'form'		=> $form,
			'aJs'		=> array('feedback.js'),
			'langs'		=> array( 'Thanks for contacting us' )
		));		
	}
}
