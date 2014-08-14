<?php 
class Controllers extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Controllers_Model');
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }

		$query = $this->Controllers_Model->selectToList($page, config_item('pageSize'), array('search' => $this->input->get('search')));
		
		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crList', 
			'meta'			=> array( 'title' => $this->lang->line('Edit controllers') ),
			'list'			=> array(
				'urlList'		=> strtolower(__CLASS__).'/listing',
				'urlEdit'		=> strtolower(__CLASS__).'/edit/%s',
				'urlAdd'		=> strtolower(__CLASS__).'/add',
				'columns'		=> array('controllerName' => $this->lang->line('Controller'), 'controllerUrl' => $this->lang->line('Url'), 'controllerActive' => $this->lang->line('Active')),
				'data'			=> $query['data'],
				'foundRows'		=> $query['foundRows'],
				'showId'		=> true
			)
		));
	}
	
	function edit($controllerId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'frmId'		=> 'frmControllersEdit',
			'fields'	=> array(
				'controllerId' => array(
					'type'		=> 'hidden', 
					'value'		=> $controllerId,
				),
				'controllerName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Controller'),
				),
				'controllerUrl' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Url'), 
				),
				'controllerActive' => array(
					'type'		=> 'checkbox',
					'label'		=> $this->lang->line('Active'),
				)
			)
		);
		
		if ((int)$controllerId > 0) {
			$form['urlDelete'] = base_url('controllers/delete/');
		}

		$form['rules'] = array( 
			array(
				'field' => 'controllerName',
				'label' => $form['fields']['controllerName']['label'],
				'rules' => 'trim|required|callback__validate_exitsName'
			),
			array(
				'field' => 'controllerUrl',
				'label' => $form['fields']['controllerUrl']['label'],
				'rules' => 'trim|required'
			),
		);		

		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Controllers_Model->save($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code, array('loadMenuAndTranslations' => true));
			}
		}

		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'meta'		=> array( 'title' => $this->lang->line('Edit controllers') ),
			'form'		=> populateCrForm($form, $this->Controllers_Model->get($controllerId)),
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return loadViewAjax($this->Controllers_Model->delete($this->input->post('controllerId'))); 
	}
	
	function _validate_exitsName() {
		return ($this->Controllers_Model->exitsController($this->input->post('controllerName'), (int)$this->input->post('controllerId')) != true);
	}
}
