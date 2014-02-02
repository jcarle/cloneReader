<?php 
class Comments extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Comments_Model');
	}
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$query = $this->Comments_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'));
		
		$this->load->view('includes/template', array(
			'controller'	=> strtolower(__CLASS__),
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit comments'),
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array(
					'commentDesc'		=> $this->lang->line('Description'),
					'commentDate'		=> array('class' => 'datetime', 'value' => $this->lang->line('Date')),
					'commentUserName'	=> $this->lang->line('Name'), 
					'commentUserEmail'	=> $this->lang->line('Email'),
				),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'showId'		=> true,
			)
		));
	}
	
	function edit($commentId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($commentId, false);
		$this->form_validation->set_rules($form['rules']);
		
		if ($this->input->is_ajax_request()) { // save data
			$code = $this->form_validation->run();
			if ($code === TRUE) {
				$this->Comments_Model->save($this->input->post());
			}
					
			return $this->load->view('ajax', array(
				'code'		=> $code, 
				'result' 	=> validation_errors() 
			));
		}

		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit comments'),
			'form'		=> $form,
		));		
	}
	
	function add(){
		$this->edit(0);
	}

	function _getFormProperties($commentId) {
		$data = $this->Comments_Model->get($commentId);
		
		$form = array(
			'frmId'		=> 'frmCommentEdit',
			'fields' => array( 
				'commentId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('commentId', $data, 0)
				),
				'commentUserName' => array(
					'type' 			=> 'text',
					'label'			=> $this->lang->line('Name'),
					'value'			=> element('commentUserName', $data),
					'disabled'		=> 'disabled', 
				),
				'commentUserEmail' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Email'), 
					'value'		=> element('commentUserEmail', $data)
				),										
								
				'commentDesc' => array(
					'type'		=> 'textarea',
					'label'		=> $this->lang->line('Description'), 
					'value'		=> element('commentDesc', $data)
				),
				'commentDate' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Date'), 
					'value'		=> element('commentDate', $data)
				),		
			)
		);
		
		$form['rules'] = array(
			array(
				'field' => 'commentDesc',
				'label' => $form['fields']['commentDesc']['label'],
				'rules' => 'required'
			),
			array(
				'field' => 'commentDate',
				'label' => $form['fields']['commentDate']['label'],
				'rules' => 'required'
			),			
		);	
		
		if ((int)element('commentId', $data) > 0) {
			$form['urlDelete'] = base_url('comments/delete/');
		}
		
		return $form;
	}

	function delete() {
		return $this->load->view('ajax', array(
			'code'		=> $this->Comments_Model->delete($this->input->post('commentId')), 
			'result' 	=> validation_errors() 
		));	
	}	
}
