<?php 
class App extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
	}
	
	function index() {
		// TODO: implementar seguridad ?
		
		$this->load->view('app');
	}
	
	function selectMenuAndTranslations() {
		$userId = $this->session->userdata('userId');
		
		// TODO: centralizar el chacheado de los menus
		$this->load->driver('cache', array('adapter' => 'file'));

		if (!is_array($this->cache->file->get('MENU_PROFILE_'.$userId))) {
			$this->cache->file->save('MENU_PROFILE_'.$userId, $this->Menu_Model->getMenu(MENU_PROFILE));
		}
		if (!is_array($this->cache->file->get('MENU_PUBLIC_'.$userId))) {
			$this->cache->file->save('MENU_PUBLIC_'.$userId, $this->Menu_Model->getMenu(MENU_PUBLIC));  
		}
		if (!is_array($this->cache->file->get('MENU_ADMIN_'.$userId))) {
			$this->cache->file->save('MENU_ADMIN_'.$userId, $this->Menu_Model->getMenu(MENU_ADMIN));
		}
	
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
}

