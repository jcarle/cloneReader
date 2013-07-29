<?php 
class Feeds extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Feeds_Model');
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$query	= $this->Feeds_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'));
		
		$this->load->view('includes/template', array(
			'controller'	=> strtolower(__CLASS__),
			'view'			=> 'includes/paginatedList', 
			'title'			=> 'Edit Feeds',
			'columns'		=> array('feedId' => '#', 'statusId' => array('class' => 'numeric', 'value' => 'Status'), 'feedName' => 'Name', 'feedUrl' => 'Url', 'feedLink' => 'feedLink'),
			'foundRows'		=> $query->foundRows,
			'data'			=> $query->result_array(),
			'pagination'	=> $this->pagination
		));
	}
	
	function edit($feedId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($feedId);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		$code = $this->form_validation->run(); 
		
		if ($this->input->is_ajax_request()) { // save data
			$feedId = $this->Feeds_Model->save($this->input->post());			
			return $this->load->view('ajax', array(
				'code'		=> ($feedId > 0), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> 'Edit Feeds',
			'form'		=> $form	  
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return $this->load->view('ajax', array(
			'code'		=> $this->Feeds_Model->delete($this->input->post('feedId')), 
			'result' 	=> validation_errors() 
		));	
	}

	
	function _getFormProperties($feedId) {
		$data = $this->Feeds_Model->get($feedId);
		
		$form = array(
			'frmId'		=> 'frmFeedEdit',
			'messages' 	=> getRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'feedId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('feedId', $data, 0)
				),
				'feedName' => array(
					'type'		=> 'text',
					'label'		=> 'Nombre', 
					'value'		=> element('feedName', $data)
				),				
				'feedUrl' => array(
					'type' 		=> 'text',
					'label'		=> 'Url', 
					'value'		=> element('feedUrl', $data)
				),
				'feedLink' => array(
					'type' 		=> 'text',
					'label'		=> 'Link', 
					'value'		=> element('feedLink', $data)
				),				
				'feedLastUpdate' => array(
					'type' 		=> 'datetime',
					'label'		=> 'Last update', 
					'value'		=> element('feedLastUpdate', $data)
				),					
				'statusId' => array(
					'type' 		=> 'text',
					'label'		=> 'Status', 
					'value'		=> element('statusId', $data),
					'disabled'	=> 'disabled'
				),									
							
			), 		
		);
		
		if ((int)$feedId > 0) {
			$form['urlDelete'] = base_url('feeds/delete/');
		}		
		
		$form['rules'] += array( 
			array(
				'field' => 'feedName',
				'label' => 'Nombre',
				'rules' => 'required'
			),
			array(
				'field' => 'feedUrl',
				'label' => 'Url',
				'rules' => 'required'
			),
		);
		return $form;
	}

	function search() { // TODO: implementar la seguridad!
		return $this->load->view('ajax', array(
			'result' 	=> $this->Feeds_Model->search($this->input->get('query'))
		));
	}	
}
