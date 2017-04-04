<?php
class Menu extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->model(array('Controllers_Model', 'Menu_Model'));
	}

	function index() {
		$this->edit(0);
	}

	function edit($menuId) {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$data = getCrFormData($this->Menu_Model->get($menuId), $menuId);
		if ($data === null) { return error404(); }

		$form = array(
			'frmName'  => 'frmMenuEdit',
			'buttons'  => array( '<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.lang('Save').'</button> '),
			'fields'   => array(
				'menuId' => array(
					'type'  => 'hidden',
					'value' => $menuId
				),
				'menuTree' => array(
					'type'   => 'tree',
					'value'  => $menuId,
					'source'  => $this->Menu_Model->getMenu(0, false, $fields = array(
						"menuId AS id",
						"CONCAT(menuName, ' (', menuId, ')', IF(ISNULL(controllerName), '', CONCAT(' (', controllerName, ')'))) AS label",
						"CONCAT('menu/edit/', menuId) AS url"
					)),
				),
				'menuName' => array(
					'type'  => 'text',
					'label' => lang('Name'),
				),
				'controllerId' => array(
					'type'             => 'dropdown',
					'label'            => lang('Controller'),
					'appendNullOption' => true,
				),
				'menuParentId' => array(
					'type'  => 'text',
					'label' => 'menuParentId',
				),
				'menuPosition' => array(
					'type'  => 'text',
					'label' => lang('Position'),
				),
				'menuClassName' => array(
					'type'  => 'text',
					'label' => 'className',
				),
				'menuIcon' => array(
					'type'  => 'text',
					'label' => 'Icon',
				),
				'menuTranslate' => array(
					'type'  => 'checkbox',
					'label' => lang('Translate'),
				),
				'menuDividerBefore' => array(
					'type'  => 'checkbox',
					'label' => lang('Divider before'),
				),
				'menuDividerAfter' => array(
					'type'  => 'checkbox',
					'label' => lang('Divider after'),
				),
			)
		);

		if ((int)$menuId > 0) {
			$form['urlDelete'] = base_url('menu/delete');
			array_unshift($form['buttons'], '<button type="button" class="btn btn-danger"><i class="fa fa-trash-o"></i> '.lang('Delete').' </button>');
			array_unshift($form['buttons'], '<button type="button" class="btn btn-default" onclick="$.goToUrl(\''.base_url('menu').'\');" ><i class="fa fa-arrow-left"></i> '.lang('Cancel').' </button>');
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
				$menuId = $this->Menu_Model->save($this->input->post());
			}

			if ($this->input->is_ajax_request()) { // save data
				return loadViewAjax($code,  $code == false ? null : array('msg' => lang('Data updated successfully'), 'icon' => 'success', 'goToUrl' => base_url('menu/edit/'.$menuId), 'reloadMenu' => true));
			}
		}

		$form['fields']['controllerId']['source'] = $this->Controllers_Model->selectToDropdown(true);

		$this->load->view('pageHtml', array(
			'view'  => 'includes/crForm',
			'meta'  => array( 'title' => lang('Edit menu') ),
			'form'  => populateCrForm($form, $data),
		));
	}

	function add(){
		$this->edit(0);
	}

	function delete() {
		if (! $this->safety->allowByControllerName(__CLASS__.'/edit') ) { return errorForbidden(); }

		return loadViewAjax($this->Menu_Model->delete($this->input->post('menuId')));
	}
}
