<?php
class Menu_Model extends CI_Model {
	
	function getMenu($menuId, $checkPermissions = true, $fields = null) {
		$aMenu = array();
		
		if ($fields == null) {
			$fields = array('menuId AS id', 'menuName AS label', 'menuIcon AS icon', 'controllerUrl AS url', 'controllers.controllerId');
		}

		$this->db
				->select('DISTINCT '.implode(',', $fields), false)
				->join('controllers', 'menu.controllerId = controllers.controllerId', 'left')
				->join('groups_controllers', 'controllers.controllerId = groups_controllers.controllerId AND controllers.controllerActive = true', 'left')
				->where('menu.menuParentId', $menuId)
				->order_by('menuPosition');
		$query = $this->db->get('menu');
		//pr($this->db->last_query());			
		
		$aController = $this->safety->getControllersByUser($this->session->userdata('userId'));
		foreach ($query->result_array() as $row){
			$childs = $this->getMenu($row['id'], $checkPermissions, $fields);
			if (!empty($childs) || $checkPermissions != true || in_array($row['controllerId'], $aController)) {
				$row['childs'] = $childs;
				$aMenu[] = $row;
			}			
		}
		
		return $aMenu;
	}
	
	function select(){
		return $this->db->get('menu');
	}

	function get($menuId){
		$this->db->where('menuId', $menuId);
		$result				= $this->db->get('menu')->row_array();
		return $result;
	}	
	
	function save($data){
		$menuId = $data['menuId'];
		
		$values = array(
			'menuName'			=> $data['menuName'],
			'menuPosition'		=> $data['menuPosition'],
			'menuParentId' 		=> $data['menuParentId'],
			'menuIcon' 			=> $data['menuIcon'],
			'controllerId' 		=> ((int)$data['controllerId'] > 0 ? $data['controllerId'] : null) 
		);
		

		if ((int)$menuId != 0) {		
			$this->db->where('menuId', $menuId);
			$this->db->update('menu', $values);
		}
		else {
			$this->db->insert('menu', $values);
			$menuId = $this->db->insert_id();
		}
		
		$this->destroyMenuCache();

		return true;
	}
	
	function delete($menuId) {
		$this->db->delete('menu', array('menuId' => $menuId));
		
		$this->destroyMenuCache();
		
		return true;
	}	
	
	function destroyMenuCache() {
		$this->load->driver('cache', array('adapter' => 'file'));

		$cache = $this->cache->cache_info();
		foreach ($cache as $key => $value) {
			if (strrpos($key, 'MENU_') !== FALSE) {
				$this->cache->delete($key);
			}
		}
	}
	
	function createMenuCache($userId) {
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
	}
}
