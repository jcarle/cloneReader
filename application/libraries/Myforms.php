<?php
/**
 * TODO: documentar
 * 
 * 
 * Las entidades que tengan comentarios, tienen que tener un metodo 'saveContact' en su controller. Puede llamar al helper saveContact y validar los datos 
 * 
 */

class Myforms {
	function __construct() {
	}
	
	public function getFormContact($contactId = null, $entityTypeId, $entityId = null) {
		$CI           = &get_instance();
		$entityConfig = getEntityConfig($entityTypeId);
		
		$form = array(
			'frmId'     => 'frm'.$CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])).'Contact',
			'action'    => base_url($entityConfig['entityTypeName'].'/saveContact/'),
			'fields'    => array(
				'contactId' => array(
					'type'     => 'hidden', 
					'value'    => $contactId,
				),
				'entityTypeId' => array(
					'type'    => 'hidden', 
					'value'   => $entityTypeId,
				),
				'entityId' => array(
					'type'      => 'hidden', 
					'value'     => $entityId,
				),
				'entityName' => array(
					'type'      => 'hidden', 
				),
				'contactFirstName' => array(
					'type'   => 'text',
					'label'  => $CI->lang->line('First name'),
				),
				'contactLastName' => array(
					'type'  => 'text',
					'label' => $CI->lang->line('Last name'), 
				),
				'contactEmail' => array(
					'type'  => 'text',
					'label' => $CI->lang->line('Email'),
				),
				'contactPhone' => array(
					'type'    => 'text',
					'label'   => $CI->lang->line('Phone'),
				),
				'contactDate' => array(
					'type'   => 'datetime',
					'label'  => $CI->lang->line('Date'),
				),
				'contactDesc' => array(
					'type'   => 'textarea',
					'label'  => $CI->lang->line('Comment'),
				),
			)
		);
		
		if ((int)$contactId > 0) {
//			$form['urlDelete'] = base_url('contacts/delete/');
			$form['fields']['entityId'] = array(
				'type' 		=> 'typeahead',
				'label'		=> $CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])),
				'source' 	=> base_url('search/'.$entityConfig['entityTypeName']),
			);
		}
		else {
			unset($form['fields']['contactDate']);
		}
		
		$form['rules'] = array();
		if ((int)$contactId > 0) {
			$form['rules'][] = array(
				'field' => 'entityId',
				'label' => $form['fields']['entityId']['label'],
				'rules' => 'required'
			);
		}		 
		$form['rules'][] = array(
			'field' => 'contactFirstName',
			'label' => $form['fields']['contactFirstName']['label'],
			'rules' => 'required'
		);
		$form['rules'][] = array(
			'field' => 'contactLastName',
			'label' => $form['fields']['contactLastName']['label'],
			'rules' => 'required'
		);
		$form['rules'][] = array(
			'field' => 'contactEmail',
			'label' => $form['fields']['contactEmail']['label'],
			'rules' => 'required|valid_email'
		);
		if ((int)$contactId > 0) {
			$form['rules'][] = array(
				'field' => 'contactDate',
				'label' => $form['fields']['contactDate']['label'],
				'rules' => 'required'
			);
		}
		
		return $form;
	}
	
	public function getFormComment($commentId = null, $entityTypeId, $entityId = null) {
		$CI           = &get_instance();
		$entityConfig = getEntityConfig($entityTypeId);
		
		$form = array(
			'frmId'     => 'frm'.$CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])).'Comment',
			'action'    => base_url($entityConfig['entityTypeName'].'/saveComment/'),
			'fields'    => array(
				'commentId' => array(
					'type'     => 'hidden', 
					'value'    => $commentId,
				),
				'entityTypeId' => array(
					'type'    => 'hidden', 
					'value'   => $entityTypeId,
				),
				'entityId' => array(
					'type'      => 'hidden', 
					'value'     => $entityId,
				),
				'commentFirstName' => array(
					'type'   => 'text',
					'label'  => $CI->lang->line('First name'),
				),
				'commentLastName' => array(
					'type'  => 'text',
					'label' => $CI->lang->line('Last name'), 
				),
				'commentEmail' => array(
					'type'  => 'text',
					'label' => $CI->lang->line('Email'),
				),
				'commentRating' => array(
					'type'    => 'raty',
					'label'   => $CI->lang->line('Rating'),
				),
				'commentDate' => array(
					'type'   => 'datetime',
					'label'  => $CI->lang->line('Date'),
				),
				'commentDesc' => array(
					'type'   => 'textarea',
					'label'  => $CI->lang->line('Comment'),
				),
			)
		);
		
		if ((int)$commentId > 0) {
//			$form['urlDelete'] = base_url('comments/delete/');
			$form['fields']['entityId'] = array(
				'type' 		=> 'typeahead',
				'label'		=> $CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])),
				'source' 	=> base_url('search/'.$entityConfig['entityTypeName']),
			);
		}
		else {
			unset($form['fields']['commentDate']);
		}
		
		$form['rules'] = array();
		if ((int)$commentId > 0) {
			$form['rules'][] = array(
				'field' => 'entityId',
				'label' => $form['fields']['entityId']['label'],
				'rules' => 'required'
			);
		}		 
		$form['rules'][] = array(
			'field' => 'commentFirstName',
			'label' => $form['fields']['commentFirstName']['label'],
			'rules' => 'required'
		);
		$form['rules'][] = array(
			'field' => 'commentLastName',
			'label' => $form['fields']['commentLastName']['label'],
			'rules' => 'required'
		);
		$form['rules'][] = array(
			'field' => 'commentEmail',
			'label' => $form['fields']['commentEmail']['label'],
			'rules' => 'required|valid_email'
		);
		if ((int)$commentId > 0) {
			$form['rules'][] = array(
				'field' => 'commentDate',
				'label' => $form['fields']['commentDate']['label'],
				'rules' => 'required'
			);
		}
		
		return $form;
	}

	/**
	 * Guarda un registro en la tabla contacts
	 * @param  $values  
	 * @param  $saveCookie
	 * @return $contactId si pudo grabar el comment o null en caso de error 
	 */
	function saveContact($values, $saveCookie = false) {
		$CI = &get_instance();
		$CI->load->model(array('Contacts_Model'));

		$form = $this->getFormContact($values['contactId'], $values['entityTypeId'], $values['entityId']);
		$CI->form_validation->set_rules($form['rules']);
		if (!$CI->form_validation->run()) {
			return null;
		}
		
		if ($saveCookie == true) {
			$CI->Contacts_Model->saveCookie($values, $form['frmId']);
		}
		
		$values['userId']         = $CI->session->userdata('userId');
		$values['contactIp']      = $CI->input->server('REMOTE_ADDR');
		$contactId =  $CI->Contacts_Model->save($values); 

		return $contactId;
	}
	
	
	/**
	 * Guarda un registro en la tabla comments
	 * @param  $values  
	 * @param  $saveCookie
	 * @return $commentId si pudo grabar el comment o null en caso de error 
	 */
	function saveComment($values) {
		$CI = &get_instance();
		$CI->load->model(array('Comments_Model'));

		$form = $this->getFormComment($values['commentId'], $values['entityTypeId'], $values['entityId']);
		$CI->form_validation->set_rules($form['rules']);
		if (!$CI->form_validation->run()) {
			return null;
		}
		
		$values['userId']         = $CI->session->userdata('userId');
		$values['commentIp']      = $CI->input->server('REMOTE_ADDR');
		$commentId =  $CI->Comments_Model->save($values); 

		return $commentId;
	}	
}
