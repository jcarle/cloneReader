<?php
class Menu_Model extends CI_Model {
	
	function getMenu($menuId, $checkPermissions = true, $fields = null) {
		$aMenu = array();
		
		if ($fields == null) {
			$fields = array('menuId AS id', 'menuName AS label', 'controllerUrl AS url', 'controllers.controllerId');
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
			'controllerId' 		=> ((int)$data['controllerId'] > 0 ? $data['controllerId'] : null) 
		);
		

		if ((int)$menuId != -1) {		
			$this->db->where('menuId', $menuId);
			$this->db->update('menu', $values);
		}
		else {
			$this->db->insert('menu', $values);
			$menuId = $this->db->insert_id();
		}
		
		$this->destroyMenuSession();

		return true;
	}
	
	function destroyMenuSession() {
		$this->session->set_userdata(array(
			'MENU_PROFILE' 	=> null,
			'MENU_PUBLIC'	=> null,
			'MENU_ADMIN'	=> null
		));
	}
}
