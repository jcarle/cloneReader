<?php 
class Tools extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Tags_Model' , 'Feeds_Model', 'Entries_Model'));
	}
	
	function index() { }
	
	function tags() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$filters = array(
			'search'         => $this->input->get('search'), 
			'userId'         => $userId,
			'hideSystemTags' => true,
		);
		
		$query = $this->Tags_Model->selectToList($page, config_item('pageSize'), $filters, array(array('orderBy' => 'tagName', 'orderDir' => 'asc')));
		
		$this->load->view('pageHtml', array(
			'view'   => 'includes/crList', 
			'meta'   => array('title' => $this->lang->line('Edit tags'), 'robots' => 'noindex,nofollow'),
			'list'   => array(
				'urlList'       => strtolower(__CLASS__).'/tags',
				'urlEdit'       => strtolower(__CLASS__).'/tags/%s',
				'urlAdd'        => strtolower(__CLASS__).'/tags/add',
				'urlDelete'     => strtolower(__CLASS__).'/tagsDelete',
				'columns'       => array('tagName' => $this->lang->line('Name')),
				'data'          => $query['data'],
				'foundRows'     => $query['foundRows'],
				'showId'        => false,
				'showCheckbox'  => true,
			)
		));
	}
	
	function tagEdit($tagId) {
		if (! $this->safety->allowByControllerName('tools/tags') ) { return errorForbidden(); }
				
		$data = getCrFormData($this->Tags_Model->get($tagId), $tagId);
		if ($data === null) { return error404(); }
		
		$form = array(
			'frmId'   => 'frmTagEdit',
			'rules'   => array(),
			'fields'  => array(
				'tagId' => array(
					'type'  => 'hidden', 
					'value' => $tagId,
				),
				'tagName' => array(
					'type'   => 'text',
					'label'  => $this->lang->line('Name'), 
				),
			),
		);
		
		if ((int)$tagId > 0) {
			$form['urlDelete'] = base_url('tools/tagDelete/');
		}
		
		$form['rules'] += array( 
			array(
				'field' => 'tagName',
				'label' => $form['fields']['tagName']['label'],
				'rules' => 'trim|required'
			),
		);
		
		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Tags_Model->saveTagByUserId($this->session->userdata('userId'), $this->input->post('tagId'), $this->input->post('tagName'));
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}
				
		$this->load->view('pageHtml', array(
			'view'   => 'includes/crForm', 
			'meta'   => array('title' => $this->lang->line('Edit tags'), 'robots' => 'noindex,nofollow'),
			'form'   => populateCrForm($form, $data),
		));	
	}

	function tagAdd(){
		$this->tagEdit(0);
	}

	function tagDelete() {
		if (! $this->safety->allowByControllerName('tools/tags') ) { return errorForbidden(); }
		
		return loadViewAjax($this->Tags_Model->deleteTagByUserId($this->session->userdata('userId'), $this->input->post('tagId')));
	}
	
	function tagsDelete() {
		if (! $this->safety->allowByControllerName('tools/tags') ) { return errorForbidden(); }
		
		$aTagId = (array)json_decode($this->input->post('aDelete'));
		
		foreach ($aTagId as $tagId){
			$this->Tags_Model->deleteTagByUserId($this->session->userdata('userId'), $tagId);
		}
		
		return loadViewAjax(true);
	}	
	
	function feeds() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$query = $this->Feeds_Model->selectToList($page, config_item('pageSize'), array('search' => $this->input->get('search'), 'userId' => $userId ), array(array('orderBy' => 'feedName', 'orderDir' => 'asc')));
		$data  = array();
		foreach ($query['data'] as $row) {
			$data[] = array(
				'feedId'   => $row['feedId'], 
				'feedIcon' => '<img width="16" height="16" src="'.($row['feedIcon']== null ? site_url().'assets/images/default_feed.png' : site_url().'assets/favicons/'.$row['feedIcon']).'" />',
				'feedName' => $row['feedName'], 
				'feedUrl'  => $row['feedUrl'],
			);
		}

		$this->load->view('pageHtml', array(
			'view'   => 'includes/crList', 
			'meta'   => array('title' => $this->lang->line('Edit feeds'), 'robots' => 'noindex,nofollow'),
			'list'   => array(
				'urlList'       => strtolower(__CLASS__).'/feeds',
				'urlAdd'        => strtolower(__CLASS__).'/feeds/add',
				'urlDelete'     => strtolower(__CLASS__).'/feedsDelete',
				'columns'       => array(
					'feedIcon'  => array('value' => '', 'isHtml' => true),
					'feedName'  => $this->lang->line('Name'), 
					'feedUrl'   => $this->lang->line('Url') 
				),
				'data'          => $data,
				'foundRows'     => $query['foundRows'],
				'showId'        => false,
				'showCheckbox'  => true,
				'buttons'       => array(
					'<a class="btnDelete btn btn-sm btn-danger" > <i class="fa fa-trash-o fa-lg"></i> '.$this->lang->line('Unsubscribe').' </a>',
					'<a href="'.base_url('tools/feeds/add').'" class="btnAdd btn btn-sm btn-success"> <i class="fa fa-file-o fa-fw"></i> '.$this->lang->line('Add').' </a> ',
				),
			)
		));
	}

	function feedEdit($feedId) {
		if (! $this->safety->allowByControllerName('tools/feeds') ) { return errorForbidden(); }
		
		$form = array(
			'frmId'     => 'frmFeedEdit',
			'action'    => base_url('entries/addFeed'),
			'rules'     => array(),
			'fields'    => array(
				'feedId' => array(
					'type'  => 'hidden', 
					'value' => $feedId,
				),
				'feedUrl' => array(
					'type'   => 'text',
					'label'  => $this->lang->line('Url'),
					'placeholder' => $this->lang->line('Add feed url'), 
				),
			),
		);
		
		if ((int)$feedId > 0) {
			$form['urlDelete'] = base_url('tools/feedDelete/');
		}
		
		$form['rules'] += array( 
			array(
				'field' => 'feedUrl',
				'label' => $form['fields']['feedUrl']['label'],
				'rules' => 'trim|required'
			),
		);
		
		$this->form_validation->set_rules($form['rules']);

		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Entries_Model->addFeed($this->input->post('feedName'), $this->session->userdata('userId'));
			}
			
			if ($this->input->is_ajax_request()) {
				return loadViewAjax($code);
			}
		}

		$this->load->view('pageHtml', array(
			'view'   => 'includes/crForm', 
			'meta'   => array('title' => $this->lang->line('Add feed'), 'robots' => 'noindex,nofollow'),
			'form'   => populateCrForm($form, array()),
		));	
	}

	function feedAdd(){
		$this->feedEdit(0);
	}

	function feedsDelete() {
		if (! $this->safety->allowByControllerName('tools/feeds') ) { return errorForbidden(); }
		
		$aFeedId = (array)json_decode($this->input->post('aDelete'));
		
		foreach ($aFeedId as $feedId){
			$this->Entries_Model->unsubscribeFeed($feedId, (int)$this->session->userdata('userId'));
		}
		
		return loadViewAjax(true);
	}
}
