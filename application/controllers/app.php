<?php 
class App extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
	}
	
	function index() {
	}
	
	function selectMenuAndTranslations() {
		$userId = $this->session->userdata('userId');
		$this->Menu_Model->createMenuCache($userId);
	
		$aMenu = array(
			'MENU_PROFILE' => array(
				'items' 	=> $this->cache->file->get('MENU_PROFILE_'.$userId), 
				'className'	=> 'menuProfile nav navbar-nav pull-right',
				'parent'	=> '.navbar-ex1-collapse'
			),
			'MENU_PUBLIC' 	=> array(
				'items' 	=> $this->cache->file->get('MENU_PUBLIC_'.$userId), 
				'className' =>'menuPublic',
				'parent'	=> '.menu.label-primary div',
			)
		);
		
		$lines = array_keys($this->lang->language);
		
		$aLangs = array();
		foreach ((array)$lines as $line) {
			$aLangs[$line] = $this->lang->line($line);
		}

		return loadViewAjax(true, array(
			'aMenu' 	=> $aMenu,
			'aLangs' 	=> $aLangs,
		));
	}
	
	function uploadFile() {
		$this->load->view('includes/uploadfile', array( 'fileupload' => array ()));
	}
}

