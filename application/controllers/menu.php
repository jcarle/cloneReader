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
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$data = $this->Menu_Model->get($menuId);
		
		$form = array(
			'frmId'			=> 'frmMenuEdit',
			'messages' 		=> getCrFormRulesMessages(),
			'buttons'		=> array( '<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Save').'</button> '),
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
					'label'	=> $this->lang->line('Name'),
					'value'	=>  element('menuName', $data)
				),
				'controllerId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Controller'), 
					'source'			=> array_to_select($this->Controllers_Model->select(true), 'controllerId', 'controllerName'), 
					'value'				=> element('controllerId', $data),
					'appendNullOption' 	=> true,
				),
				'menuParentId' => array(
					'type'	=> 'text',
					'label'	=> 'menuParentId', 
					'value'	=> element('menuParentId', $data),
				),
				'menuPosition' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Position'), 
					'value'	=> element('menuPosition', $data)
				),
				'menuIcon' => array(
					'type'	=> 'text',
					'label'	=> 'Icon', 
					'value'	=> element('menuIcon', $data)
				)				
			)
		);
		
		if ((int)$menuId > 0) {
			$form['urlDelete'] 	= base_url('menu/delete');
			array_unshift($form['buttons'], '<button type="button" class="btn btn-danger"><i class="icon-trash"></i> '.$this->lang->line('Delete').' </button>');
			array_unshift($form['buttons'], '<button type="button" class="btn btn-default" onclick="$.goToUrl(\''.base_url('menu').'\');" ><i class="icon-arrow-left"></i> '.$this->lang->line('Cancel').' </button>');
		}
		
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
			'view'			=> 'includes/crForm', 
			'title'			=> $this->lang->line('Edit menu'),
			'form'			=> $form
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return $this->load->view('ajax', array(
			'code'		=> $this->Menu_Model->delete($this->input->post('menuId')), 
			'result' 	=> validation_errors() 
		));	
	}
}
