<?php 
class Tags extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Tags_Model');
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$query = $this->Tags_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'));
		
		$this->load->view('includes/template', array(
			'view'			=> 'includes/paginatedList', 
			'title'			=> $this->lang->line('Edit tags'),
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array('tagName' => $this->lang->line('Name')),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'pagination'	=> $this->pagination,
				//'urlDelete'		=> 'asa'
			)
		));
	}
	
	function edit($tagId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($tagId);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		$code = $this->form_validation->run(); 
		
		if ($this->input->is_ajax_request()) { // save data			
			return $this->load->view('ajax', array(
				'code'		=> $this->Tags_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> $this->lang->line('Edit tags'),
			'form'		=> $form	  
		));		
	}

	function add(){
		$this->edit(0);
	}

	function delete() {
		return $this->load->view('ajax', array(
			'code'		=> $this->Tags_Model->delete($this->input->post('tagId')), 
			'result' 	=> validation_errors() 
		));	
	}

	function _getFormProperties($tagId) {
		$data = $this->Tags_Model->get($tagId);
		
		$form = array(
			'frmId'		=> 'frmTagEdit',
			'messages' 	=> getRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'tagId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('tagId', $data, 0)
				),
				'tagName' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Name'), 
					'value'		=> element('tagName', $data)
				),				
			), 		
		);
		
		if ((int)$tagId > 0) {
			$form['urlDelete'] = base_url('tags/delete/');
		}
		
		$form['rules'] += array( 
			array(
				'field' => 'tagName',
				'label' => $form['fields']['tagName']['label'],
				'rules' => 'required'
			),
		);

		return $form;		
	}
}
