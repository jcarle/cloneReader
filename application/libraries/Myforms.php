<?php
class Myforms {
	function __construct() {
	}
	
	public function getFormContact($contactId = null, $entityTypeId, $entityId = null) {
		$CI = &get_instance();
		
		$form = array(
			'frmId'     => 'frmServiceContact',
			'action'    => base_url('contacts/saveContact/'),
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
				'label'		=> $CI->lang->line('Service'),
				'source' 	=> base_url('search/services/'),
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
	
	public function getFormComment($commentId = null, $entityTypeId, $entityId = null, $ifForm = false) {
		$CI = &get_instance();
		
		$form = array(
			'frmId'     => 'frmServiceComment',
			'action'    => base_url('comments/saveComment/'),
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
				'label'		=> $CI->lang->line('Service'),
				'source' 	=> base_url('search/services/'),
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
}
