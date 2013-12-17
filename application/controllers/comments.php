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
		$this->form_validation->set_message($form['messages']);
		
		if ($this->input->is_ajax_request()) { // save data			
			return $this->load->view('ajax', array(
				'code'		=> $this->Comments_Model->save($this->input->post()), 
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

/*
	function _editPopup($commentId){
		// TODO: implementar la seguridad!!
		$form = $this->_getFormProperties($commentId, true);
		$form['isSubForm'] 	= true;
		$form['title']		= ((int)$commentId < 1 ? 'Nuevo comentario' : 'Editar comentario');
		$form['action']		= base_url('comments/edit/'.$commentId);
		
		$this->load->view('includes/crForm', array(
			'form'			=> $form,
		));			
	}*/
	
	function _getFormProperties($commentId) {
		$data = $this->Comments_Model->get($commentId);
		
		$form = array(
			'frmId'		=> 'frmCommentEdit',
			'messages' 	=> getCrFormRulesMessages(),
			'fields' => array( 
				'commentId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('commentId', $data, 0)
				),
				'userFullName' => array(
					'type' 		=> 'autocomplete',
					'label'		=> $this->lang->line('User'),
					'value'		=> array( element('userId', $data) => element('userFullName', $data)), // el value es un array del tipo {key=>value}
					'fieldId'	=> 'userId', // field donde va a ir a para el id del autocomplete!
					'source' 	=> base_url('agencies/searchUsers/') 
				),				
				
				'commentUserName' => array(
					'type' 		=> 'text',
					'label'		=> $this->lang->line('Name'), 
					'value'		=> element('commentUserName', $data),
					'subscribe'	=> array(
						array(
							'field'			=> 'userId',
							'event'			=> 'change',   
							'callback'		=> 'toogleField',
							'arguments'		=> array( 'this.getFieldByName(\'userId\').val() == 0')
						)
					)
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
				'field' => 'userFullName',
				'label' => $form['fields']['userFullName']['label'],
				'rules' => 'required'
			),
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
