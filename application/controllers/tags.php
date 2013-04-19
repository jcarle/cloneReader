
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
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$this->load->view('includes/template', array(
			'controller'	=> strtolower(__CLASS__),
			'view'			=> 'includes/paginatedList', 
			'title'			=> 'Edit Tags',
			'query'			=> $this->Tags_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter')),
			'pagination'	=> $this->pagination
		));
	}
	
	function edit($tagId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$form = $this->_getFormProperties($tagId);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		$code = $this->form_validation->run(); 
		
		if ($this->input->is_ajax_request()) { // save data			
			return $this->load->view('ajax', array(
				'code'		=> $this->Tags_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/formValidation', 
			'title'		=> 'Edit Tags',
			'form'		=> $form	  
		));		
	}

	function add(){
		$this->edit(-1);
	}

	function _getFormProperties($tagId) {
		$data = $this->Tags_Model->get($tagId);
		
		$form = array(
			'frmId'		=> 'frmTagEdit',
			'messages' 	=> getRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'tagId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('tagId', $data, -1)
				),
				'tagName' => array(
					'type'		=> 'text',
					'label'		=> 'Name', 
					'value'		=> element('tagName', $data)
				),				
			), 		
		);
		
		$form['rules'] += array( 
			array(
				'field' => 'tagName',
				'label' => 'Nombre',
				'rules' => 'required'
			),
		);

		return $form;		
	}
}
