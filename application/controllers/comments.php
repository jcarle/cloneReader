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
		
		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Comments_Model->save($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}

		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit comments'),
			'form'		=> populateCrForm($form, $this->Comments_Model->get($commentId)),
		));		
	}
	
	function add(){
		$this->edit(0);
	}

	function _getFormProperties($commentId) {
		$form = array(
			'frmId'		=> 'frmCommentEdit',
			'buttons' 	=> array('<button type="button" class="btn btn-default" onclick="$.goToUrlList();"><i class="icon-arrow-left"></i> '.$this->lang->line('Back').' </button> '),
			'fields' => array( 
				'commentId' => array(
					'type'	=> 'hidden', 
					'value'	=> $commentId,
				),
				'commentUserName' => array(
					'type' 			=> 'text',
					'label'			=> $this->lang->line('Name'),
					'disabled'		=> true, 
				),
				'commentUserEmail' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Email'), 
				),
				'commentDesc' => array(
					'type'		=> 'textarea',
					'label'		=> $this->lang->line('Description'), 
				),
				'commentDate' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Date'), 
				),		
			)
		);
		
		$form['rules'] = array(
			array(
				'field' => 'commentDesc',
				'label' => $form['fields']['commentDesc']['label'],
				'rules' => 'trim|required'
			),
			array(
				'field' => 'commentDate',
				'label' => $form['fields']['commentDate']['label'],
				'rules' => 'trim|required'
			),			
		);	
		
		if ((int)$commentId > 0) {
			$form['urlDelete'] = base_url('comments/delete/');
			
			$form['buttons'][] = '<button type="button" class="btn btn-danger" ><i class="icon-trash"></i> '.$this->lang->line('Delete').' </button>';			
		}

		$this->form_validation->set_rules($form['rules']);
		
		return $form;
	}

	function delete() {
		return loadViewAjax($this->Comments_Model->delete($this->input->post('commentId')));	
	}	
}
