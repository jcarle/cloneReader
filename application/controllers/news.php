<?php
class News extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model(array('News_Model', 'Users_Model'));
	}

	function index() {
		$this->listing();
	}

	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }

		$query = $this->News_Model->selectToList($page, config_item('pageSize'), array('search' => $this->input->get('search')));

		$this->load->view('pageHtml', array(
			'view'   => 'includes/crList',
			'meta'   => array( 'title' => lang('Edit news') ),
			'list'   => array(
				'urlList'   => strtolower(__CLASS__).'/listing',
				'urlEdit'   => strtolower(__CLASS__).'/edit/%s',
				'urlAdd'    => strtolower(__CLASS__).'/add',
				'columns'   => array('userFullName' => lang('Author'), 'newTitle' => lang('Title'), 'newSef' => lang('Sef'), 'newDate' => array('class' => 'datetime', 'value' => lang('Date'))),
				'data'      => $query['data'],
				'foundRows' => $query['foundRows'],
				'showId'    => false
			)
		));
	}

	function edit($newId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$data = getCrFormData($this->News_Model->get($newId, true), $newId);
		if ($data === null) { return error404(); }

		$form = $this->_getFormProperties($newId);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->News_Model->save($this->input->post());
			}

			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}

		$this->load->view('pageHtml', array(
			'view'   => 'includes/crForm',
			'meta'   => array('title' => lang('Edit news')),
			'form'   => populateCrForm($form, $data),
		));
	}

	function add(){
		$this->edit(0);
	}

	function delete() {
		if (! $this->safety->allowByControllerName(__CLASS__.'/edit') ) { return errorForbidden(); }

		return loadViewAjax($this->News_Model->delete($this->input->post('newId')));
	}

	function _getFormProperties($newId) {
		$form = array(
			'frmName' => 'frmNewEdit',
			'rules'   => array(),
			'fields'  => array(
				'newId' => array(
					'type' => 'hidden',
					'value'=> $newId
				),
				'newTitle' => array(
					'type'  => 'text',
					'label' => lang('Title'),
				),
				'newContent' => array(
					'type'  => 'textarea',
					'label' => lang('Content'),
				),
				'userId' => array(
					'type'   => 'typeahead',
					'label'  => lang('Author'),
					'source' => base_url('search/users/'),
				),
				'newDate' => array(
					'type'  => 'datetime',
					'label' => lang('Date'),
				),
			),
		);

		if ((int)$newId > 0) {
			$form['fields']['newSef'] = array(
				'type'      => 'text',
				'label'     => 'Sef',
				'disabled'  => true,
			);

			$form['urlDelete'] = base_url('news/delete/');
		}

		$form['rules'] += array(
			array(
				'field' => 'newTitle',
				'label' => 'Title',
				'rules' => 'trim|required'
			),
			array(
				'field' => 'newContent',
				'label' => 'Sef',
				'rules' => 'trim|required'
			),
		);

		$this->form_validation->set_rules($form['rules']);

		return $form;
	}

	function view($newSef) {
//		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$new 	= $this->News_Model->getByNewSef($newSef);

		$this->load->view('pageHtml',
			array(
				'view'   => 'newView',
				'meta'   => array( 'title' => $new['newTitle']),
				'new'    => $new,
				'breadcrumb' => array(
					array('text' => lang('Home'), 'href' => base_url()),
					array('text' => $new['newTitle'], 'active' => true),
				)
			)
		);
	}
}
