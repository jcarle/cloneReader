<?php
/**
 * TODO: documentar
 * 
 * 
 * Las entidades que tengan comentarios, tienen que tener un metodo 'saveContact' en su controller. Puede llamar al helper saveContact que valida los datos y graba la cookie 
 * 
 */

class Myforms {
	function __construct() {
	}
	
	public function getFormContact($contactId = null, $entityTypeId, $entityId = null, $showTypeahead = false, $showContactDate = false, $showContactIp = false) {
		$CI           = &get_instance();
		$entityConfig = getEntityConfig($entityTypeId);
		
		$form = array(
			'frmId'     => 'frm'.$CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])).'Contact',
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
				'contactIp' => array(
					'type'     => 'text',
					'label'    => 'Ip',
					'disabled' => true,
				),
			)
		);
		
		if ((int)$contactId > 0) {
			$form['urlDelete'] = base_url('contacts/'.$entityConfig['entityTypeName'].'/delete/');
		}
		
		if ($showTypeahead == true) {
			$form['fields']['entityId'] = array(
				'type'   => 'typeahead',
				'label'  => $CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])),
				'source' => base_url('search/'.$entityConfig['entityTypeName']),
			);
		}
		if ($showContactDate == false) {
			unset($form['fields']['contactDate']);
		}
		if ($showContactIp == false) {
			unset($form['fields']['contactIp']);
		}
		
		$form['rules'] = array();
		if ($showTypeahead == true) {
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
		if ($showContactDate == true) {
			$form['rules'][] = array(
				'field' => 'contactDate',
				'label' => $form['fields']['contactDate']['label'],
				'rules' => 'required'
			);
		}
		
		return $form;
	}
	
	public function getFormComment($commentId = null, $entityTypeId, $entityId = null, $showTypeahead = false, $showCommentDate = false, $showCommentIp = false) {
		$CI           = &get_instance();
		$entityConfig = getEntityConfig($entityTypeId);
		
		$form = array(
			'frmId'     => 'frm'.$CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])).'Comment',
			'fields'    => array(
				'commentId' => array(
					'type'     => 'hidden', 
					'value'    => $commentId,
				),
				'userId' => array(
					'type'     => 'hidden', 
				),
				'entityTypeId' => array(
					'type'    => 'hidden', 
					'value'   => $entityTypeId,
				),
				'entityId' => array(
					'type'    => 'typeahead',
					'label'   => $CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])),
					'source'  => base_url('search/'.$entityConfig['entityTypeName']),
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
				'commentIp' => array(
					'type'     => 'text',
					'label'    => 'Ip',
					'disabled' => true,
				),
			)
		);

		if ((int)$commentId > 0) {
			$form['urlDelete'] = base_url('comments/'.$entityConfig['entityTypeName'].'/delete/');
		}
		
		if ($showTypeahead == true) {
			$form['fields']['entityId'] = array(
				'type'    => 'typeahead',
				'label'   => $CI->lang->line(ucwords($entityConfig['entityTypeSingularName'])),
				'source'  => base_url('search/'.$entityConfig['entityTypeName']),
			);
		}
		if ($showCommentDate == false) {
			unset($form['fields']['commentDate']);
		}
		if ($showCommentIp == false) {
			unset($form['fields']['commentIp']);
		}
		
		$form['rules'] = array();
		if ($showTypeahead == true) {
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
		if ($showCommentDate == true) {
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
		$CI->load->model('Contacts_Model');

		$form = $this->getFormContact($values['contactId'], $values['entityTypeId'], $values['entityId']);
		$CI->form_validation->set_rules($form['rules']);
		if (!$CI->form_validation->run()) {
			return null;
		}
		
		if ($saveCookie == true) {
			$CI->Contacts_Model->saveCookie($values, $form['frmId']);
		}

		$contactId =  $CI->Contacts_Model->save($values); 

		return $contactId;
	}
	
	
	/**
	 * Guarda un registro en la tabla comments
	 * @param  $values  
	 * @return $commentId si pudo grabar el comment o null en caso de error 
	 */
	function saveComment($values) {
		$CI = &get_instance();
		$CI->load->model(array('Comments_Model'));

		$form = $this->getFormComment($values, $values['entityTypeId'], $values['entityId']);
		$CI->form_validation->set_rules($form['rules']);
		if (!$CI->form_validation->run()) {
			return null;
		}
		
		$commentId =  $CI->Comments_Model->save($values); 

		return $commentId;
	}	
	
	function getHtmlComments($comments, $title = 'Comments') {
		if (empty($comments)) {
			return '';
		}
		$CI   = &get_instance();
		$html = '<div class="entityComments">
					<h2>'.$CI->lang->line($title).'</h2>';
					
		foreach ($comments as $comment) {
			$html .= '
				<div class="media">
					<div class="pull-left" >
						<a class="thumbnail" >
							<i class="fa fa-user fa-4x"></i>
						</a>
						<div class="raty" data-score="'. $comment['commentRating'].'" ></div>
						'.(isset($comment['entityUrl']) ? $comment['entityUrl'] : '').'
					</div>
					<div class="media-body">
					<h3 class="media-heading"> 
						'.htmlspecialchars($comment['commentFirstName'].' '.$comment['commentLastName']).' 
						<small class="small datetime fromNow">'.$comment['commentDate'].'</small>
					</h3>
						<p>'. nl2br(htmlspecialchars($comment['commentDesc'])).'</p>
					</div>
				</div>';
		}
		$html .= '</div>';
		
		return $html;
	}
}
