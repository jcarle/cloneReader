<?php 
class Menu extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Controllers_Model');
	}  
	
	function index() {
		$this->edit(0);
	}
	
	function edit($menuId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$data = $this->Menu_Model->get($menuId);
		
		$form = array(
			'frmId'			=> 'frmMenuEdit',
			'messages' 		=> getRulesMessages(),
			'showBtnBack' 	=> false,
			'fields'		=> array(
				'menuId' => array(
					'type'	=> 'hidden',
					'value'	=> element('menuId', $data, 0)
				),
				'menuTree' => array(
					'type'		=> 'tree',
					'value'		=> element('menuId', $data),
					'source'	=> $this->Menu_Model->getMenu(0, false, $fields = array(
						"menuId AS id", 
						"CONCAT(menuName, ' (', menuId, ')', IF(ISNULL(controllerName), '', CONCAT(' (', controllerName, ')'))) AS label", 
						"CONCAT('menu/edit/', menuId) AS url"
					)),					
				),
				'menuName' => array(
					'type'	=> 'text',
					'label'	=> 'Nombre',
					'value'	=>  element('menuName', $data)
				),
				'controllerId' => array(
					'type'		=> 'dropdown',
					'label'		=> 'Controller', 
					'source'	=> array('0' => '-- seleccione --') + array_to_select($this->Controllers_Model->select(true), 'controllerId', 'controllerName'), 
					'value'		=> element('controllerId', $data)
				),
				'menuParentId' => array(
					'type'	=> 'text',
					'label'	=> 'menuParentId', 
					'value'	=> element('menuParentId', $data),
				),
				'menuPosition' => array(
					'type'	=> 'text',
					'label'	=> 'Position', 
					'value'	=> element('menuPosition', $data)
				)
			)
		);
		
		$form['rules'] = array( 
			array(
				'field' => 'menuName',
				'label' => $form['fields']['menuName']['label'],
				'rules' => 'required'
			)
		);


		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		if ($this->input->is_ajax_request()) { // save data
			$code = $this->form_validation->run(); 
			return $this->load->view('ajax', array(
				'code'		=> $this->Menu_Model->save($this->input->post()), 
				'result' 	=> ($this->form_validation->run() == false ? validation_errors() : array('goToUrl' => base_url('menu/edit/'.$menuId))) 
			));
		}
		
		$this->load->view('includes/template', array(
			'view'			=> 'includes/jForm', 
			'title'			=> 'Editar menu',
			'form'			=> $form
		));		
	}

	function add(){
		$this->edit(0);
	}		
}
