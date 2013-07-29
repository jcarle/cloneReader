<?php 
class Comments extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Comments_Model');
	}  
	
	function index() {
		// TODO: !
	}
	
	function excursions($method = 'listing', $id = null, $id2 = null) { // TODO: ver si hay una manera mas elegante de obtener los parametros!
		$entityName = 'excursions';
		switch ($method) {
			case 'listing':
				$this->_listing($entityName); break;
			case 'edit':
				$this->_edit($entityName, $id); break;
			case 'add':
				$this->_edit($entityName, 0); break;
			case 'selectByExcursionId':
				$this->_selectByExcursionId($entityName, $id); break;
			case 'editPopup':
				$this->_editPopup($entityName, $id, $id2); break;
		}
	}
	
	
	function _listing($entityName) {
		if (! $this->safety->allowByControllerName(__METHOD__.'::'.$entityName) ) { return errorForbidden(); }
		
		$page = (int)$this->input->get('page');
		if ($page == 0) { $page = 1; }
		
		$this->load->view('includes/template', array(
			'controller'	=> strtolower(__CLASS__).'/'.$entityName,
			'view'			=> 'includes/paginatedList', 
			'title'			=> 'Editar Comentarios',
			'query'			=> $this->Comments_Model->selectToList(PAGE_SIZE, ($page * PAGE_SIZE) - PAGE_SIZE, $this->input->get('filter')),
			'pagination'	=> $this->pagination
		));
	}
	
	function _edit($entityName, $commentId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFormProperties($commentId, false);

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		if ($this->input->is_ajax_request()) { // save data			
			return $this->load->view('ajax', array(
				'code'		=> $this->Comments_Model->save($this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> 'Editar Comentarios',
			'form'		=> $form,
		));		
	}

	function _editPopup($entityName, $excursionId, $commentId){
		// TODO: implementar la seguridad!!
		$form = $this->_getFormProperties($commentId, true);
		$form['isSubForm'] 	= true;
		$form['title']		= ((int)$commentId < 1 ? 'Nuevo comentario' : 'Editar comentario');
		$form['action']		= base_url('comments/'.$entityName.'/edit/'.$commentId);
		
		$this->load->view('includes/jForm', array(
			'form'			=> $form,
		));			
	}
	
	function _selectByExcursionId($entityName, $excursionId){
		// TODO: implementar la seguridad!!
		
		$query = $this->Comments_Model->selectByExcursionId($excursionId);
		
		$this->load->view('includes/subform', array(
			'controller'	=> strtolower(__CLASS__).'/'.$entityName.'/editPopup/'.$excursionId.'/',
			'query'			=> $query,
			'frmParent'		=> $this->input->get('frmParent')
		));		
	}		
	
	function _getFormProperties($commentId, $excursionId = null) {
		$data = $this->Comments_Model->get($commentId);
		
		$form = array(
			'frmId'		=> 'frmCommentEdit',
			'messages' 	=> getRulesMessages(),
			'fields' => array( 
				'commentId' => array(
					'type'	=> 'hidden', 
					'value'	=> element('commentId', $data, 0)
				),
				'userFullName' => array(
					'type' 		=> 'autocomplete',
					'label'		=> 'Usuario',
					'value'		=> array( element('userId', $data) => element('userFullName', $data)), // el value es un array del tipo {key=>value}
					'fieldId'	=> 'userId', // field donde va a ir a para el id del autocomplete!
					'source' 	=> base_url('agencies/searchUsers/') 
				),				
				
				'commentUserName' => array(
					'type' 		=> 'text',
					'label'		=> 'Nombre y Apellido', 
					'value'		=> element('commentUserName', $data),
					'subscribe'	=> array(
						array(
							'field'			=> 'userId',
							'event'			=> 'change',   
							'callback'		=> 'toogleField',
							'arguments'		=> array( 'this.getFieldByName(\'userId\').val() == 0')
						)
					)
				),						
				'commentUserEmail' => array(
					'type' 		=> 'text',
					'label'		=> 'Email', 
					'value'		=> element('commentUserEmail', $data)
				),										
								
				'excursionName' => array(
					'type' 		=> 'autocomplete',
					'label'		=> 'Excursión',
					'fieldId'	=> 'excursionId', // field donde va a ir a para el id del autocomplete!
					'source' 	=> base_url('excursions/search/'),
					'value'		=> array( element('excursionId', $data) => element('excursionName', $data)), // el value es un array del tipo {key=>value}
				),										
				'commentDesc' => array(
					'type'		=> 'textarea',
					'label'		=> 'Comentario', 
					'value'		=> element('commentDesc', $data)
				),
				'commentDate' => array(
					'type' 		=> 'datetime',
					'label'		=> 'Fecha y hora', 
					'value'		=> element('commentDate', $data)
				),		
				'commentScore' => array(
					'type' 		=> 'text',
					'label'		=> 'Puntaje', 
					'value'		=> element('commentScore', $data)
				),						
			)
		);
		
		$form['rules'] = array(
			array(
				'field' => 'userFullName',
				'label' => $form['fields']['userFullName']['label'],
				'rules' => 'required'
			),
			array(
				'field' => 'excursionName',
				'label' => $form['fields']['excursionName']['label'],
				'rules' => 'required'
			),
			array(
				'field' => 'commentDesc',
				'label' => $form['fields']['commentDesc']['label'],
				'rules' => 'required'
			),
			array(
				'field' => 'commentDate',
				'label' => $form['fields']['commentDate']['label'],
				'rules' => 'required'
			),			
		);	
		
		if ($excursionId != null){
			unset($form['fields']['userFullName']);
			unset($form['fields']['excursionName']);
			unset($form['fields']['commentDate']);
		}

		return $form;				
	}	
}
