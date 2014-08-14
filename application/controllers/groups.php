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
		$query = $this->Groups_Model->selectToList(config_item('pageSize'), ($page * config_item('pageSize')) - config_item('pageSize'), array('filter' => $this->input->get('filter')));
		
		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crList', 
			'meta'			=> array( 'title' => $this->lang->line('Edit groups') ),
			'list'			=> array(
				'urlList'		=> strtolower(__CLASS__).'/listing',
				'urlEdit'		=> strtolower(__CLASS__).'/edit/%s',
				'urlAdd'		=> strtolower(__CLASS__).'/add',
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
				'controllers' => array(
					'type'		=> 'groupCheckBox',
					'label'		=> $this->lang->line('Controllers'),
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
				return loadViewAjax($code, array('loadMenuAndTranslations' => true));
			}
		}
		
		$form['fields']['controllers']['source'] = $this->Controllers_Model->selectToDropdown(true);

		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crForm', 
			'meta'			=> array( 'title' => $this->lang->line('Edit groups') ),
			'form'			=> populateCrForm($form, $this->Groups_Model->get($groupId)),
		));
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return loadViewAjax($this->Groups_Model->delete($this->input->post('groupId')));	
	}
}
