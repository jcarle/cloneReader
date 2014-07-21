<?php 
class Feedback extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Feedbacks_Model', 'Users_Model'));
	}
	
	function index() {
		if (! $this->safety->allowByControllerName('feedback') ) { return errorForbidden(); }
		
		$this->load->helper('email');
		
		$userId = (int)$this->session->userdata('userId');
		$data	= array();
		if ($userId != USER_ANONYMOUS) {		
			$data = $this->Users_Model->get($userId);
		}

		$feedbackUserEmail = element('userEmail', $data);
		if (valid_email($feedbackUserEmail) == false) {
			$feedbackUserEmail = '';
		}

		$form = array(
			'frmId'		=> 'frmFeedbackEdit',
			'callback' 	=> 'function(response) { $.Feedback.onSaveFeedback(response); };',
			'fields' => array( 
				'feedbackId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('feedbackId', $data, 0)
				),
				'feedbackUserName' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Name'), 
					'value'		=> trim(element('userFirstName', $data).' '.element('userLastName', $data)),
				),						
				'feedbackUserEmail' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Email'), 
					'value'		=> $feedbackUserEmail
				),										
				'feedbackDesc' => array(
					'type'		=> 'textarea',
					'label'		=> $this->lang->line('Comment'), 
					'value'		=> ''
				),
			),
			'buttons'		=> array( '<button type="submit" class="btn btn-primary"><i class="fa fa-comment"></i> '.$this->lang->line('Send').'</button> '),
		);
		
		$form['rules'] = array(
			array(
				'field' => 'feedbackUserName',
				'label' => $form['fields']['feedbackUserName']['label'],
				'rules' => 'trim|required'
			),
			array(
				'field' => 'feedbackUserEmail',
				'label' => $form['fields']['feedbackUserEmail']['label'],
				'rules' => 'trim|required|valid_email'
			),			
			array(
				'field' => 'feedbackDesc',
				'label' => $form['fields']['feedbackDesc']['label'],
				'rules' => 'trim|required'
			),
		);	

		$this->form_validation->set_rules($form['rules']);
		
		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Feedbacks_Model->saveFeedback($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}
		
		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'meta'		=> array( 'title' => $this->lang->line('Feedback') ),
			'form'		=> $form,
			'langs'		=> array( 'Thanks for contacting us' )
		));
	}
}
