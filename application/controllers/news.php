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
				'showId'		=> false
			)
		));
	}
	
	function edit($newId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($newId);

		if (isSubmitCrForm() === true) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->News_Model->save($this->input->post());
			}
		}
		
		if ($this->input->is_ajax_request()) {
			return $this->load->view('ajax', array(
				'code'		=> $code, 
				'result' 	=> validation_errors() 
			));
		}

		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Edit news'),
			'form'		=> populateCrForm($form, $this->News_Model->get($newId, true)),
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
		$form = array(
			'frmId'		=> 'frmNewEdit',
			'rules'		=> array(),
			'fields'	=> array(
				'newId' => array(
					'type'	=> 'hidden', 
					'value'	=> $newId
				),
				'newTitle' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Title'), 
				),				
				'newContent' => array(
					'type' 		=> 'textarea',
					'label'		=> $this->lang->line('Content'), 
				),
				'userId' => array(
					'type' 		=> 'typeahead',
					'label'		=> $this->lang->line('Author'),
					'source' 	=> base_url('users/search/'),
				),
				'newDate' => array(
					'type' 		=> 'datetime',
					'label'		=> $this->lang->line('Date'), 
				),
			), 		
		);

		
		if ((int)$newId > 0) {
			$form['fields']['newSef'] = array(
				'type' 		=> 'text',
				'label'		=> 'Sef', 
				'disabled'	=> true,
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
