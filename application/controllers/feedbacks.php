<?php 
class Feedbacks extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Feedbacks_Model');
	}
	
	function index() {
	}
	
	function addFeedback() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$this->load->helper('email');
		$this->load->model('Users_Model');
		
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
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$query = $this->Feedbacks_Model->selectToList($page, config_item('pageSize'), array('filter' => $this->input->get('filter')));
		
		$this->load->view('pageHtml', array(
			'view'    => 'includes/crList', 
			'meta'    => array( 'title' => $this->lang->line('Edit feedbacks')),
			'list'    => array(
				'urlList'   => strtolower(__CLASS__).'/listing',
				'urlEdit'   => strtolower(__CLASS__).'/edit/%s',
				'urlAdd'    => strtolower(__CLASS__).'/add',
				'columns'   => array(
					'feedbackDesc'       => array('class' => 'dotdotdot', 'value' =>  $this->lang->line('Description')),
					'feedbackDate'       => array('class' => 'datetime', 'value' => $this->lang->line('Date')),
					'feedbackUserName'   => $this->lang->line('Name'), 
					'feedbackUserEmail'  => $this->lang->line('Email'),
				),
				'data'       => $query->result_array(),
				'foundRows'  => $query->foundRows,
				'showId'     => true,
			)
		));
	}
	
	function edit($feedbackId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($feedbackId, false);
		
		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Feedbacks_Model->save($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}

		$this->load->view('pageHtml', array(
			'view'    => 'includes/crForm', 
			'meta'    => array('title'		=> $this->lang->line('Edit feedbacks')),
			'form'    => populateCrForm($form, $this->Feedbacks_Model->get($feedbackId)),
		));	
	}
	
	function add(){
		$this->edit(0);
	}

	function _getFormProperties($feedbackId) {
		$form = array(
			'frmId'		=> 'frmFeedbackEdit',
			'buttons' 	=> array('<button type="button" class="btn btn-default" onclick="$.goToUrlList();"><i class="fa fa-arrow-left"></i> '.$this->lang->line('Back').' </button> '),
			'fields' => array( 
				'feedbackId' => array(
					'type'	=> 'hidden', 
					'value'	=> $feedbackId,
				),
				'feedbackUserName' => array(
					'type' 			=> 'text',
					'label'			=> $this->lang->line('Name'),
					'disabled'		=> true, 
				),
				'feedbackUserEmail' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Email'), 
				),
				'feedbackDesc' => array(
					'type'		=> 'textarea',
					'label'		=> $this->lang->line('Description'), 
				),
				'feedbackDate' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Date'), 
				),		
			)
		);

		$form['rules'] = array(
			array(
				'field' => 'feedbackDesc',
				'label' => $form['fields']['feedbackDesc']['label'],
				'rules' => 'trim|required'
			),
			array(
				'field' => 'feedbackDate',
				'label' => $form['fields']['feedbackDate']['label'],
				'rules' => 'trim|required'
			),			
		);	

		if ((int)$feedbackId > 0) {
			$form['urlDelete'] = base_url('feedbacks/delete/');
			
			$form['buttons'][] = '<button type="button" class="btn btn-danger" ><i class="fa fa-trash-o"></i> '.$this->lang->line('Delete').' </button>';
		}

		$this->form_validation->set_rules($form['rules']);

		return $form;
	}

	function delete() {
		return loadViewAjax($this->Feedbacks_Model->delete($this->input->post('feedbackId')));
	}	
}
