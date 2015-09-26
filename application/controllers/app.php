<?php
class App extends CI_Controller {

	function __construct() {
		parent::__construct();
	}

	function index() {
		return error404();
	}

	function out() {
		header("Location: ".$this->input->get('url'));
	}

	function selectMenu() {
		$this->load->driver('cache', array('adapter' => 'file'));
		$groups = $this->session->userdata('groups');

		if (!is_array($this->cache->file->get('MENU_PROFILE_'.json_encode($groups)))) {
			$this->load->model('Menu_Model');
			$this->Menu_Model->createMenuCache($groups);
		}

		$aMenu = array(
			'MENU_PROFILE'  => array(
				'items'     => $this->cache->file->get('MENU_PROFILE_'.json_encode($groups)),
				'className' => 'menuProfile nav navbar-nav navbar-right',
				'parent'    => '.navbar-ex1-collapse',
			),
			'MENU_PUBLIC'   => array(
				'items'     => $this->cache->file->get('MENU_PUBLIC_'.json_encode($groups)),
				'className' =>'menuPublic',
				'parent'    => '.menu.label-primary div',
			)
		);

		return loadViewAjax(true, array( 'aMenu' => $aMenu ));
	}

	function uploadFile() {
		$this->load->view('includes/uploadfile', array( 'fileupload' => array ()));
	}

	// TODO: mover esto de aca
	function saveEntitySef($entityTypeId, $entityId) {
		$entityConfig = getEntityConfig($entityTypeId);
		$controller   = sprintf('%s/edit', $entityConfig['entityTypeName']); // TODO: hacer un config

		if (! $this->safety->allowByControllerName($controller) ) { return errorForbidden(); }

		$entitySef = $this->Commond_Model->saveEntitySef($entityTypeId, $entityId);
		if ($entitySef == null) {
			return error404();
		}

		$entityUrl = getEntityUrl($entityTypeId, $entitySef);

		return loadViewAjax(true, array('entityUrl' => $entityUrl));
	}
}
