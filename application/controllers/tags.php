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
		
		$query = $this->Tags_Model->selectToList(config_item('pageSize'), ($page * config_item('pageSize')) - config_item('pageSize'), array('filter' => $this->input->get('filter')));
		
		$this->load->view('pageHtml', array(
			'view'   => 'includes/crList', 
			'meta'   => array('title' => $this->lang->line('Edit tags')),
			'list'   => array(
				'urlList'		=> strtolower(__CLASS__).'/listing',
				'urlEdit'		=> strtolower(__CLASS__).'/edit/%s',
				'urlAdd'		=> strtolower(__CLASS__).'/add',
				'columns'		=> array('tagName' => $this->lang->line('Name')),
				'data'			=> $query['data'],
				'foundRows'		=> $query['foundRows'],
				'showId'		=> true,
			)
		));
	}
	
	function edit($tagId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($tagId);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Tags_Model->save($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}
				
		$this->load->view('pageHtml', array(
			'view'   => 'includes/crForm', 
			'meta'   => array('title' => $this->lang->line('Edit tags')),
			'form'   => populateCrForm($form, $this->Tags_Model->get($tagId)),
		));		
	}

	function add(){
		$this->edit(0);
	}

	function delete() {
		return loadViewAjax($this->Tags_Model->delete($this->input->post('tagId')));	
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
}
