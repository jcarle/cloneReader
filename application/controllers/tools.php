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
		
		$query = $this->Tags_Model->selectByUserId(config_item('pageSize'), ($page * config_item('pageSize')) - config_item('pageSize'), $userId, array('filter' => $this->input->get('filter')), array());
		
		$this->load->view('pageHtml', array(
			'view'   => 'includes/crList', 
			'meta'   => array('title' => $this->lang->line('Edit tags')),
			'list'   => array(
				'urlList'		=> strtolower(__CLASS__).'/tags',
				'urlEdit'		=> strtolower(__CLASS__).'/tags/%s',
				'urlAdd'		=> strtolower(__CLASS__).'/tags/add',
				'columns'		=> array('tagName' => $this->lang->line('Name')),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'showId'		=> false,
			)
		));
	}
	
	function tagEdit($tagId) {
		if (! $this->safety->allowByControllerName('tools/tags') ) { return errorForbidden(); }
		
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
			'meta'   => array('title' => $this->lang->line('Edit tags')),
			'form'   => populateCrForm($form, $this->Tags_Model->get($tagId)),
		));	
	}

	function tagAdd(){
		$this->tagEdit(0);
	}

	function tagDelete() {
		return loadViewAjax($this->Tags_Model->deleteTagByUserId($this->session->userdata('userId'), $this->input->post('tagId')));
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function feeds() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$query = $this->Feeds_Model->selectToList(config_item('pageSize'), ($page * config_item('pageSize')) - config_item('pageSize'), $this->input->get('filter'), null, null, null, null, $userId);
		
		$this->load->view('pageHtml', array(
			'view'   => 'includes/crList', 
			'meta'   => array('title' => $this->lang->line('Edit feeds')),
			'list'   => array(
				'urlList'       => strtolower(__CLASS__).'/feeds',
				'urlAdd'        => strtolower(__CLASS__).'/feeds/add',
				'urlDelete'     => strtolower(__CLASS__).'/feedDelete',
				'columns'       => array('feedName' => $this->lang->line('Name'), 'feedUrl' => $this->lang->line('Url') ),
				'data'          => $query->result_array(),
				'foundRows'     => $query->foundRows,
				'showId'        => true,
				'showCheckbox'  => true,
/*				'buttons'       => array(
					'<a class="btnDelete btn btn-sm btn-danger" > <i class="fa fa-trash-o fa-lg"></i> '.$this->lang->line('Unsubscribe').' </a>',
					'<a href="'.base_url('tools/feeds/add').'" class="btnAdd btn btn-sm btn-success"> <i class="fa fa-file-o fa-fw"></i> '.$this->lang->line('Add').' </a> ',
				),*/
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
			'meta'   => array('title' => $this->lang->line('Add feed')),
			'form'   => populateCrForm($form, $this->Feeds_Model->get($feedId)),
		));	
	}

	function feedAdd(){
		$this->feedEdit(0);
	}

	function feedDelete() {
		return loadViewAjax($this->Feeds_Model->deleteFeedByUserId($this->session->userdata('userId'), $this->input->post('feedId')));
	}



}
