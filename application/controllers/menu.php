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
		
		$form = array(
			'frmId'			=> 'frmMenuEdit',
			'buttons'		=> array( '<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Save').'</button> '),
			'fields'		=> array(
				'menuId' => array(
					'type'	=> 'hidden',
					'value'	=> $menuId
				),
				'menuTree' => array(
					'type'		=> 'tree',
					'value'		=> $menuId,
					'source'	=> $this->Menu_Model->getMenu(0, false, $fields = array(
						"menuId AS id", 
						"CONCAT(menuName, ' (', menuId, ')', IF(ISNULL(controllerName), '', CONCAT(' (', controllerName, ')'))) AS label", 
						"CONCAT('menu/edit/', menuId) AS url"
					)),					
				),
				'menuName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Name'),
				),
				'controllerId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Controller'), 
					'appendNullOption' 	=> true,
				),
				'menuParentId' => array(
					'type'	=> 'text',
					'label'	=> 'menuParentId', 
				),
				'menuPosition' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Position'), 
				),
				'menuIcon' => array(
					'type'	=> 'text',
					'label'	=> 'Icon', 
				)
			)
		);
		
		if ((int)$menuId > 0) {
			$form['urlDelete'] 	= base_url('menu/delete');
			array_unshift($form['buttons'], '<button type="button" class="btn btn-danger"><i class="fa fa-trash-o"></i> '.$this->lang->line('Delete').' </button>');
			array_unshift($form['buttons'], '<button type="button" class="btn btn-default" onclick="$.goToUrl(\''.base_url('menu').'\');" ><i class="fa fa-arrow-left"></i> '.$this->lang->line('Cancel').' </button>');
		}
		
		$form['rules'] = array( 
			array(
				'field' => 'menuName',
				'label' => $form['fields']['menuName']['label'],
				'rules' => 'trim|required'
			)
		);

		$this->form_validation->set_rules($form['rules']);
		
		if ($this->input->post() != false) {
			$code = $this->form_validation->run();
			if ($code == true) {
				$this->Menu_Model->save($this->input->post());
			}
			
			if ($this->input->is_ajax_request()) { // save data
				return loadViewAjax($code,  $code == false ? validation_errors() : array('goToUrl' => base_url('menu/edit/'.$menuId), 'loadMenuAndTranslations' => true));
			}
		}
		
		$form['fields']['controllerId']['source'] = $this->Controllers_Model->selectToDropdown(true);
		
		$this->load->view('pageHtml', array(
			'view'			=> 'includes/crForm', 
			'meta'			=> array(
				'title'			=> $this->lang->line('Edit menu'),
				'h1'			=> $this->lang->line('Edit menu'),
			),
			'form'			=> populateCrForm($form, $this->Menu_Model->get($menuId)),
		));		
	}

	function add(){
		$this->edit(0);
	}
	
	function delete() {
		return loadViewAjax($this->Menu_Model->delete($this->input->post('menuId')));	
	}
}
