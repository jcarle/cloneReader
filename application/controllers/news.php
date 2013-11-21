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
		
		$query = $this->News_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter'));
		
		$this->load->view('includes/template', array(
			'view'			=> 'includes/crList', 
			'title'			=> $this->lang->line('Edit news'),
			'list'			=> array(
				'controller'	=> strtolower(__CLASS__),
				'columns'		=> array('userFullName' => $this->lang->line('Author'), 'newTitle' => $this->lang->line('Title'), 'newSef' => $this->lang->line('Sef'), 'newDate' => array('class' => 'datetime', 'value' => $this->lang->line('Date'))),
				'data'			=> $query->result_array(),
				'foundRows'		=> $query->foundRows,
				'pagination'	=> $this->pagination,
				'showId'		=> false
			)
		));
	}
	
	function edit($newId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($newId);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		$code = $this->form_validation->run(); 

		if ($this->input->is_ajax_request()) { // save data
			return $this->load->view('ajax', array(
				'code'		=> $this->News_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit news'),
			'form'		=> $form
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return $this->load->view('ajax', array(
			'code'		=> $this->News_Model->delete($this->input->post('newId')), 
			'result' 	=> validation_errors() 
		));	
	}

	function _getFormProperties($newId) {
		$data = $this->News_Model->get($newId);
		$user = $this->Users_Model->get($this->session->userdata('userId'));
		
		$form = array(
			'frmId'		=> 'frmNewEdit',
			'messages' 	=> getCrFormRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'newId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('newId', $data, 0)
				),
				'newTitle' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Title'), 
					'value'		=> element('newTitle', $data)
				),				
				'newContent' => array(
					'type' 		=> 'textarea',
					'label'		=> $this->lang->line('Content'), 
					'value'		=> element('newContent', $data)
				),
				'userId' => array(
					'type' 		=> 'typeahead',
					'label'		=> $this->lang->line('Author'),
					'source' 	=> base_url('users/search/'),
					'value'		=> array( 'id' => element('userId', $data, $this->session->userdata('userId')), 'text' => element('userName', $data, 
					$user['userFirstName'].' '.$user['userLastName']
					)), 
				),				
				'newDate' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Date'), 
					'value'		=> element('newDate', $data, date('Y-m-d H:i:s') )
				),
			), 		
		);

		
		if ((int)element('newId', $data) > 0) {
			$form['fields']['newSef'] = array(
				'type' 		=> 'text',
				'label'		=> 'Sef', 
				'value'		=> element('newSef', $data),
				'disabled'	=> true,	
			); 
			
			$form['urlDelete'] = base_url('news/delete/');
		}		
		
		$form['rules'] += array( 
			array(
				'field' => 'newTitle',
				'label' => 'Title',
				'rules' => 'required'
			),
			array(
				'field' => 'newContent',
				'label' => 'Sef',
				'rules' => 'required'
			),
		);

		return $form;		
	}

	function view($newSef) {
//		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
				
		$new 	= $this->News_Model->getByNewSef($newSef);
		
		$this->load->view('includes/template', 
			array(
				'view'			=> 'newView', 
				'title'			=> $new['newTitle'],
				'new'			=> $new,
				'breadcrumb'	=> array(
					array('text' => 'home', 'href' => base_url()),
					array('text' => $new['newTitle'], 'active' => true),
				)
			)
		);
	}
}
