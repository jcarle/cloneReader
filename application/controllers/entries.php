<?php 
class Entries extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Entries_Model');
	}  
	
	function index() {
		$this->listing();
	}
	
	function listing() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$this->load->view('includes/template', array(
			'controller'	=> strtolower(__CLASS__),
			'view'			=> 'includes/paginatedList', 
			'title'			=> 'Editar entries',
			'query'			=> $this->Entries_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter')),
			'pagination'	=> $this->pagination
		));
	}
	
	function select($page = 1) {
		// TODO: implementar la seguridad! 
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> $this->Entries_Model->select((array)json_decode($this->input->post('post'))),
		));
	}

	function selectFeeds() {
		// TODO: implementar la seguridad! 
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> $this->Entries_Model->selectFeeds(),
		));
	}
	
	function edit($entryId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$form = $this->_getFormProperties($entryId);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		$code = $this->form_validation->run(); 
		
		if ($this->input->is_ajax_request()) { // save data			
			return $this->load->view('ajax', array(
				'code'		=> $this->Entries_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/formValidation', 
			'title'		=> 'Editar Entries',
			'form'		=> $form	  
		));		
	}

	function add(){
		$this->edit(-1);
	}
	
	function _getFormProperties($entryId) {
		$data = $this->Entries_Model->get($entryId);
		
		$form = array(
			'frmId'		=> 'frmEntryEdit',
			'messages' 	=> getRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'entryId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('entryId', $data, -1)
				),
				'entryTitle' => array(
					'type'		=> 'text',
					'label'		=> 'Title', 
					'value'		=> element('entryTitle', $data)
				),				
				'entryUrl' => array(
					'type' 		=> 'text',
					'label'		=> 'Url', 
					'value'		=> element('entryUrl', $data)
				),
				'entryContent' => array(
					'type' 		=> 'textarea',
					'label'		=> 'Content', 
					'value'		=> element('entryContent', $data)
				),				
			), 		
		);
		
		$form['rules'] += array( 
			array(
				'field' => 'entryTitle',
				'label' => 'Title',
				'rules' => 'required'
			),
			array(
				'field' => 'entryUrl',
				'label' => 'Url',
				'rules' => 'required'
			),
		);

		return $form;		
	}
	
	function getNewsEntries($userId = null) {
		// scanea todos los feeds!
		$this->Entries_Model->getNewsEntries($userId);
		
		// TODO: implementar la seguridad! 
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> 'ok',
		));				
	}
	
	function saveData() {
		$entries 	= (array)json_decode($this->input->post('entries'), true);
		$tags 		= (array)json_decode($this->input->post('tags'), true);
		
		$this->Entries_Model->saveUserEntries((int)$this->session->userdata('userId'), $entries);		
		$this->Entries_Model->saveUserTags((int)$this->session->userdata('userId'), $tags);
		
		
		// TODO: implementar la seguridad! 
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> 'ok',
		));		
	}

	function addFeed() {
		$result = $this->Entries_Model->addFeed($this->input->post('feedUrl'), $this->session->userdata('userId'));

		// TODO: implementar la seguridad! 
		return $this->load->view('ajax', array(
			'code'		=> (is_array($result)),
			'result' 	=> $result,
		));
	}

	function saveUserFeedTag() {
		$result = $this->Entries_Model->saveUserFeedTag((int)$this->session->userdata('userId'), $this->input->post('feedId'), $this->input->post('tagId'), ($this->input->post('append') == 'true'));

		// TODO: implementar la seguridad! 
		return $this->load->view('ajax', array(
			'code'		=> ($result === true),
			'result' 	=> ($result === true ? 'ok': $result),
		));
	}
}
