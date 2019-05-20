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

		if (!is_array($this->cache->file->get('menuProfile_'.json_encode($groups)))) {
			$this->load->model('Menu_Model');
			$this->Menu_Model->createMenuCache($groups);
		}

		$aMenu = array(
			'menuProfile'   => array(
				'items'     => $this->cache->file->get('menuProfile_'.json_encode($groups)),
				'className' => 'menuProfile nav navbar-nav navbar-right',
				'parent'    => '.navbar-ex1-collapse',
			),
			'menuPublic'    => array(
				'items'     => $this->cache->file->get('menuPublic_'.json_encode($groups)),
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

		$this->Commond_Model->saveEntityLog($entityTypeId, $entityId);
		$entityUrl = getEntityUrl($entityTypeId, $entityId);

		return loadViewAjax(true, array('entityUrl' => $entityUrl));
	}

  function forbidden($forceJson = false) {
    // $this->language->init();
    $this->safety->initSession();

    if ($forceJson === true || ($this->input->is_ajax_request() && $this->input->get('pageJson') != true)) {
      return $this->load->view('json', array(
        'code'         => false,
        'result'       => lang('Not authorized for the action to take'),
        'status_code'  => 403
      ));
    }

    $this->load->view('pageHtml', array(
      'view'        => 'error',
      'meta'        => array( 'title' => 'Error 403' ),
      'message'     => lang('Not authorized for the action to take'),
      'status_code' => 403
    ));
  }

  function error404($forceJson = false) {
    // $this->language->init();
    $this->safety->initSession();

    if ($forceJson === true || ($this->input->is_ajax_request() && $this->input->get('pageJson') != true)) {
      return $this->load->view('json', array(
        'code'          => false,
        'result'        => lang('The page you requested does not exist'),
        'status_code'   => 404
      ));
    }

    $this->load->view('pageHtml', array(
      'view'          => 'error',
      'meta'          => array( 'title' => 'Error 404' ),
      'message'       => lang('The page you requested does not exist'),
      'status_code'   => 404
    ));
  }
}
