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
		$query = $this->Groups_Model->selectToList($page, config_item('pageSize'), array('search' => $this->input->get('search')));

		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crList',
			'meta'			=> array( 'title' => lang('Edit groups') ),
			'list'			=> array(
				'urlList'		=> strtolower(__CLASS__).'/listing',
				'urlEdit'		=> strtolower(__CLASS__).'/edit/%s',
				'urlAdd'		=> strtolower(__CLASS__).'/add',
				'columns'		=> array('groupName' => lang('Name'), 'groupHomePage' => lang('Home page')),
				'data'			=> $query['data'],
				'foundRows'		=> $query['foundRows'],
				'showId'		=> true
			)
		));
	}

	function edit($groupId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$data = getCrFormData($this->Groups_Model->get($groupId), $groupId);
		if ($data === null) { return error404(); }

		$form = array(
			'frmName' => 'frmGroupsEdit',
			'fields'  => array(
				'groupId' => array(
					'type'  => 'hidden',
					'value' => $groupId,
				),
				'groupName' => array(
					'type'  => 'text',
					'label' => lang('Name'),
				),
				'groupHomePage' => array(
					'type'  => 'text',
					'label' => lang('Home page'),
				),
				'controllers' => array(
					'type'   => 'groupCheckBox',
					'label'  => lang('Controllers'),
					'showId' => true
				)
			)
		);

		if ((int)$groupId > 0) {
			$form['urlDelete'] = base_url('groups/delete/');
		}

		$form['rules'] = array(
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
				return loadViewAjax($code, array('reloadMenu' => true));
			}
		}

		$form['fields']['controllers']['source'] = $this->Controllers_Model->selectToDropdown(true);

		$this->load->view('pageHtml', array(
			'view' => 'includes/crForm',
			'meta' => array( 'title' => lang('Edit groups') ),
			'form' => populateCrForm($form, $data),
		));
	}

	function add(){
		$this->edit(0);
	}

	function delete() {
		if (! $this->safety->allowByControllerName(__CLASS__.'/edit') ) { return errorForbidden(); }

		return loadViewAjax($this->Groups_Model->delete($this->input->post('groupId')));
	}
}
