<?php 
class Groups extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Groups_Model', 'Controllers_Model'));
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		$query = $this->Groups_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'));
		
		$this->load->view('includes/template', array(
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit groups'),
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array('groupName' => $this->lang->line('Name'), 'groupHomePage' => $this->lang->line('Home page')),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'showId'		=> true
			)
		));
	}
	
	function edit($groupId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'frmId'		=> 'frmGroupsEdit',
			'fields'	=> array(
				'groupId' => array(
					'type'		=> 'hidden', 
					'value'		=> $groupId,
				),
				'groupName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Name'), 
				),
				'groupHomePage' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Home page'),
				),
				'controllers[]' => array(
					'type'		=> 'groupCheckBox',
					'label'		=> $this->lang->line('Controllers'),
					'source'	=> array_to_select($this->Controllers_Model->select(true), 'controllerId', 'controllerName'), 
					'showId'	=> true
				)
			)
		);
		
		if ((int)$groupId > 0) {
			$form['urlDelete'] = base_url('groups/delete/');
		}
		
		$form['rules'] 	= array( 
			array(
				'field' => 'groupName',
				'label' => $form['fields']['groupName']['label'],
				'rules' => 'trim|required'
			),
		);		

		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Groups_Model->save($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewCrFormSaveAjax($code);
			}
		}
				
		$this->load->view('includes/template', array(
			'view'			=> 'includes/crForm', 
			'title'			=> $this->lang->line('Edit groups'),
			'form'			=> populateCrForm($form, $this->Groups_Model->get($groupId)),
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return $this->load->view('ajax', array(
			'code'		=> $this->Groups_Model->delete($this->input->post('groupId')), 
			'result' 	=> validation_errors() 
		));	
	}
}
