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
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit tags'),
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array('tagName' => $this->lang->line('Name')),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'showId'		=> true,
			)
		));
	}
	
	function edit($tagId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($tagId);

		if (isSubmitCrForm() === true) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Tags_Model->save($this->input->post());
			}
		}
		
		if ($this->input->is_ajax_request()) {
			return $this->load->view('ajax', array(
				'code'		=> $code, 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit tags'),
			'form'		=> populateCrForm($form, $this->Tags_Model->get($tagId)),
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
		$form = array(
			'frmId'		=> 'frmTagEdit',
			'rules'		=> array(),
			'fields'	=> array(
				'tagId' => array(
					'type'	=> 'hidden', 
					'value'	=> $tagId,
				),
				'tagName' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Name'), 
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
				'rules' => 'trim|required'
			),
		);
		
		$this->form_validation->set_rules($form['rules']);

		return $form;
	}

	function search() { // TODO: implementar la seguridad!
		return $this->load->view('ajax', array(
			'result' 	=> $this->Tags_Model->search($this->input->get('query'), $this->input->get('onlyWithFeeds') == 'true')
		));
	}	
}
