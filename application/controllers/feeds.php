
<?php 
class Feeds extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Feeds_Model');
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
			'title'			=> 'Editar Feeds',
			'query'			=> $this->Feeds_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter')),
			'pagination'	=> $this->pagination
		));
	}
	
	function edit($feedId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$form = $this->_getFormProperties($feedId);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		$code = $this->form_validation->run(); 
		
		if ($this->input->is_ajax_request()) { // save data			
			return $this->load->view('ajax', array(
				'code'		=> $this->Feeds_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/formValidation', 
			'title'		=> 'Editar Feeds',
			'form'		=> $form	  
		));		
	}

	function add(){
		$this->edit(-1);
	}
	
	function _getFormProperties($feedId) {
		$data = $this->Feeds_Model->get($feedId);
		
		$form = array(
			'frmId'		=> 'frmFeedEdit',
			'messages' 	=> getRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'feedId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('feedId', $data, -1)
				),
				'feedName' => array(
					'type'		=> 'text',
					'label'		=> 'Nombre', 
					'value'		=> element('feedName', $data)
				),				
				'feedUrl' => array(
					'type' 		=> 'text',
					'label'		=> 'Url', 
					'value'		=> element('feedUrl', $data)
				),
			), 		
		);
		
		$form['rules'] += array( 
			array(
				'field' => 'feedName',
				'label' => 'Nombre',
				'rules' => 'required'
			),
			array(
				'field' => 'feedUrl',
				'label' => 'Url',
				'rules' => 'required'
			),
		);

		

		return $form;		
	}
}
