<?php
function appendMessagesToCrForm($form) {
	$CI = &get_instance();
	$CI->lang->load('form_validation');

	if (element('messages', $form) == null) {
		$form['messages'] = array();
	}

	if (element('rules', $form) == null) {
		return $form;
	}

	foreach ($form['rules'] as $rule) {
		$aRules = explode('|', $rule['rules']);
		foreach ($aRules as $key) {
			$form['messages'][$key] = $CI->lang->line(str_replace('callback_', '', $key));
		}
	}

	return $form;
}

/**
 * Para no pedir datos al pedo, completo las propiedades del form solo cuando se muestra la vista, no al validar
 */
function populateCrForm($form, $data) {
	foreach ($form['fields'] as $fieldName => $fieldValue) {
		switch ($form['fields'][$fieldName]['type']) {
			case 'hidden':
			case 'text':
			case 'dropdown':
			case 'date':
			case 'datetime':
			case 'typeahead':
			case 'textarea':
			case 'raty':
			case 'numeric':
			case 'upload':
				// TODO: revisar este IF. Esta puesto para que no sobreescriba fields hiddens con el parentId que se usa para agregar un child
				if ( element('value', $form['fields'][$fieldName]) === false ) {
					$form['fields'][$fieldName]['value'] = element($fieldName, $data, '');
				}
				break;
			case 'checkbox':
				$form['fields'][$fieldName]['checked'] = element($fieldName, $data);
				break;
			case 'groupCheckBox':
				$form['fields'][$fieldName]['value'] = element($fieldName, $data);
				break;
		}
	}

	return $form;
}

/**
 * Para chequear los datos de un crForm
 * @param $data
 * @param $id
 * @return si no esta vacio devuelve $data, si $id == 0 devuelve true (se usa en %s/add ), sino devuelve null
 */
function getCrFormData($data, $id) {
	if (!is_numeric($id)) {
		return null;
	}
	if (!empty($data)) {
		return $data;
	}
	if ($id == 0) {
		return true;
	}
	return null;
}

function getCrFormFieldGallery($entityTypeId, $entityId, $label) {
	$config = getEntityGalleryConfig($entityTypeId);

	return array(
		'type'          => 'gallery',
		'label'         => $label,
		'urlGallery'    => base_url(str_replace(array('$entityTypeId', '$entityId'), array($entityTypeId, $entityId),$config['urlGallery'])),
		'urlSave'       => base_url($config['urlSave']),
		'urlDelete'     => base_url($config['urlDelete']),
		'entityTypeId'  => $entityTypeId,
		'entityId'      => $entityId,
	);
}

function getCrFormFieldMoney(array $price, array $currency, array $exchange, array $total) {
	$CI = &get_instance();
	$CI->load->model('Coins_Model');

	$subscribe 	= array();
	$aFieldName = array( $price['name'], $currency['name'], $exchange['name'], );

	foreach ($aFieldName as $fieldName) {
		$subscribe[] = array(
			'field'         => $fieldName,
			'event'         => 'change',
			'callback'      => 'calculatePrice',
			'arguments'     => array(
				'this.getFieldByName(\''.$price['name'].'\')',
				'this.getFieldByName(\''.$currency['name'].'\')',
				'this.getFieldByName(\''.$exchange['name'].'\')',
				'this.getFieldByName(\''.$total['name'].'\')'
			)
		);
	}

	$subscribe[0]['runOnInit'] = true;

	return array(
		$price['name']	=> array(
			'type'          => 'text',
			'name'          => $price['name'],
			'label'         => $price['label'],
			'value'         => element('value', $price, 0),
			'placeholder'   => '0,00',
		),
		$currency['name']   => array(
			'type'          => 'dropdown',
			'name'          => $currency['name'],
			'label'         => $currency['label'],
			'value'         => element('value', $currency),
			'source'        => $CI->Coins_Model->selectToDropdown(),
		),
		$exchange['name']   => array(
			'type'          => 'text',
			'name'          => $exchange['name'],
			'label'         => $exchange['label'],
			'value'         => element('value', $exchange, 0),
			'placeholder'   => '0,00',
		),
		$total['name']      => array(
			'type'          => 'text',
			'name'          => $total['name'],
			'label'         => $total['label'],
			'value'         => null,
			'disabled'      => true,
			'subscribe'     => $subscribe
		),
	);
}

function getCrFormValidationFieldMoney(array $price, array $exchange) {
	return array(
		array(
			'field' => $price['name'],
			'label' => $price['label'],
			'rules' => 'trim|required|numeric'
		),
		array(
			'field' => $exchange['name'],
			'label' => $exchange['label'],
			'rules' => 'trim|required|numeric'
		)
	);
}

function subscribeForCrFormSumValues($fieldName, array $aFieldName) {
	foreach ($aFieldName as $fieldName) {
		$subscribe[] = array(
			'field'      => $fieldName,
			'event'      => 'change',
			'callback'   => 'sumValues',
			'arguments'  => array( json_encode($aFieldName) )
		);
	}
	return $subscribe;
}


function getCrFieldGallery($form) {
	foreach ($form['fields'] as $name => $field) {
		if ($field['type'] == 'gallery') {
			return $field;
		}
	}
	return null;
}

function selectGallery($entityTypeId, $entityId) {
	$CI = &get_instance();
	$CI->load->model('Files_Model');

	$files = $CI->Files_Model->selectEntityGallery($entityTypeId, $entityId, null, true);
	return loadViewAjax(true,  array('files' => $files));
}


/**
 * Guarda una imagen y la rezisea segun los parametros seteados en el config
 * SI  entityTypeId != NULL AND $entityId != NULL guarda la relacion en la table entities_files
 *
 * @param $config array. Ejemplo de $config = array(
 *		'folder'        => '/assets/images/%s/original/',
 *		'allowed_types' => 'gif|jpg|png',
 *		'max_size'      => 1024 * 8,
 *		'sizes'         => array(
 *			'thumb' => array( 'width' => 150,  'height' => 100, 'folder' => '/assets/images/%s/thumb/' ),
 *		)
 *	)
 *  @param entityTypeId
 *  @param $entityId
 * @return array
 * */
function savePicture($config, $entityTypeId = null, $entityId = null) {
	$CI = &get_instance();
	$CI->load->model('Files_Model');

	$CI->load->library('upload', array(
		'upload_path'     => '.'.$config['folder'],
		'allowed_types'   => $config['allowed_types'],
		'max_size'        => $config['max_size'],
		'encrypt_name'    => true,
		'is_image'        => true
	));

	if (!$CI->upload->do_upload()) {
		return array( 'code' => false, 'result' => $CI->upload->display_errors('', '') );
	}

	$data = $CI->upload->data();

	$CI->load->library('image_lib');

	// creo los sizes que esten seteados en el config
	resizePicure($config['sizes'], $data['full_path']);

	$fileId = $CI->Files_Model->insert($data['file_name'], $data['client_name']);
	if(!$fileId) {
		@unlink($data['full_path']);
		return array( 'code' => false, 'result' => 'Something went wrong when saving the file, please try again');
	}

	@unlink($_FILES[$file_element_name]);


	if ($entityTypeId != null &&  $entityId != null) {
		$CI->Files_Model->saveFileRelation($entityTypeId, $entityId ,$fileId);
	}

	return array( 'code' => true, 'fileId' => $fileId);
}


/**
 * Rezisea una imagen segun los parametros seteados en el config
 * TODO: hacer que $master_dim sea opcional
 */
function resizePicure($sizes, $sourceImage) {
	if (!is_array($sizes)) {
		return;
	}

	$CI = &get_instance();
	$CI->load->library('image_lib');

	$data       = getimagesize($sourceImage);
	$origWidth  = $data[0];
	$origHeight = $data[1];

	foreach ($sizes as $size) {
		$width  = $size['width'];
		$height = $size['height'];

		if ($origWidth < $size['width']) {
			$width = $origWidth;
		}
		if ($origHeight < $size['height']) {
			$height = $origHeight;
		}

		$dim    = (intval($origWidth) / intval($origHeight)) - ($width / $height);

		$config = array(
			'source_image'      => $sourceImage,
			'new_image'         => '.'.$size['folder'],
			'maintain_ratio'    => true,
			'width'             => $width,
			'height'            => $height,
			'master_dim'        => ($dim > 0) ? 'height' : 'width',
		);
		$CI->image_lib->initialize($config);
		$CI->image_lib->resize();
	}
}

/**
 * Guarda un archivo segun los parametros seteados en el config
 *
 * @param $config array. Ejemplo de $config = array(
 *		'folder'        => '/assets/images/%s/original/',
 *		'allowed_types' => 'gif|jpg|png',
 *		'max_size'      => 1024 * 8,
 *	)
 * @return array
 * */
function saveFile($config) {
	$CI = &get_instance();

	$CI->load->model('Files_Model');

	$CI->load->library('upload', array(
		'upload_path'     => '.'.$config['folder'],
		'allowed_types'   => $config['allowed_types'],
		'max_size'        => $config['max_size'],
		'encrypt_name'    => true,
	));

	if (!$CI->upload->do_upload()) {
		return array( 'code' => false, 'result'	=> $CI->upload->display_errors('', '') );
	}

	$data = $CI->upload->data();

	$fileId = $CI->Files_Model->insert($data['file_name'], $data['client_name']);
	if(!$fileId) {
		@unlink($data['full_path']);
		return array( 'code' => false, 'result'	=> 'Something went wrong when saving the file, please try again');
	}

	@unlink($_FILES[$file_element_name]);

	return array( 'code' => true, 'fileId'	=> $fileId);
}

function renderCrFormFields($form) {
	$CI         = &get_instance();
	$aFields    = array();
	$formErrors = array_keys(validation_array_errors());

	foreach ($form['fields'] as $name => $field) {

		$hasError = null;
		if (in_array($name, $formErrors)) {
			$hasError = 'has-error';
		}

		$properties = array('name' => $name, 'value' => element('value', $field), 'class' => 'form-control');
		if (element('disabled', $field) == true) {
			$properties += array('disabled' => 'disabled');
		}
		if (element('placeholder', $field) != '') {
			$properties += array('placeholder' => $field['placeholder']);
		}

		$sField = '
			<fieldset class="form-group '.$hasError.'">
				'.form_label(element('label', $field), null, array('class' => 'col-xs-12 col-sm-3 col-md-3 col-lg-3 control-label')).'
				<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9"> %s </div>
			</fieldset>';
		switch ($field['type']) {
			case 'hidden':
				$aFields[] = form_hidden($name, $field['value']);
				break;
			case 'text':
			case 'numeric':
			case 'typeahead':
				if ($field['type'] == 'typeahead') {
					unset($properties['value']);
				}
				$aFields[] = sprintf($sField, form_input($properties));
				break;
			case 'date':
			case 'datetime':
				$aFields[] = sprintf($sField,
					'<div class="input-group" style="width:1px">
						'.form_input(
							array(
								'name'        => $name,
								'value'       => element('value', $field),
								'class'       => 'form-control',
								'size'        => ($field['type'] == 'datetime' ? 18 : 9),
								'placeholder' => $CI->lang->line('DATE_FORMAT').($field['type'] == 'datetime' ? ' hh:mm:ss' : '')
							)
						).'
						<span class="input-group-addon"><i class="glyphicon glyphicon-remove fa fa-times"></i></span>
						<span class="input-group-addon"><i class="glyphicon glyphicon-th icon-th fa fa-th"></i></span>
					</div>');
				break;
			case 'password':
				$aFields[] = sprintf($sField, form_password(array('name' => $name, 'value' => element('value', $field), 'class' => 'form-control')));
				break;
			case 'textarea':
				$aFields[] = sprintf($sField, form_textarea($name, element('value', $field), 'class="form-control"'));
				break;
			case 'dropdown':
				$source = element('source', $field, array());
				$source = sourceToDropdown($source, element('appendNullOption', $field));
				if (element('multiple', $field) == true) {
					$properties += array('multiple' => 'multiple');
				}
				$aFields[] = sprintf($sField, form_dropdown($name, $source, element('value', $field, null), _attributes_to_string($properties)));
				break;
			case 'groupCheckBox':
				$showId = element('showId', $field, false);
				$sTmp = '<ul class="groupCheckBox" name="'.$name.'"> <li><input type="text" style="display:none" /> </li>';

				foreach ($field['source'] as $item) {
					$sTmp .=
						'<li>
							<div class="checkbox">
								 <label>'
									.form_checkbox(null, $item['id'], in_array($item['id'], $field['value']))
									.$item['text'].($showId == true ? ' - '.$item['id'] : '').'
								</label>
							</div>
						</li>';
				}
				$sTmp .= '</ul>';
				$aFields[] = sprintf($sField, $sTmp);
				break;
			case 'checkbox':
				$className = '';
				if (element('hideOffset', $field) == true) {
					$className = ' hide ';
				}

				$aFields[] = '
					<fieldset class="form-group">
						<div class="'.$className.'  hidden-xs col-sm-3 col-md-3 col-lg-3 "> </div>
						<div class="col-xs-12 col-sm-9 col-md-9  col-lg-9 ">
							<div class="checkbox" >
								<label>
									'.form_checkbox($name, 'on', $field['checked']).' '. element('label', $field) .' 
								</label>
							</div>
						</div>
					</fieldset>';

				break;
			case 'gallery':
				$aFields[] = sprintf($sField, '
						<div id="'.$name.'" data-toggle="modal-gallery" data-target="#modal-gallery" class="gallery well" >
							<button type="button" class="btn btn-success btn-sm btnEditPhotos fileinput-button">
								<i class="fa fa-picture-o" ></i>
								'.$CI->lang->line('Edit pictures').'
							</button>
							<div class="thumbnails" ></div>
						</div>
					');
				break;
			case 'subform':
				$aFields[] = sprintf($sField, '
					<div name="'.$name.'" class="subform ">
						<div class="alert alert-warning">
							<i class="fa fa-spinner fa-spin fa-lg"></i>
							<small>'.$CI->lang->line('loading ...').'</small>
						</div>
					</div>
				');
				break;
			case 'tree':
				$aFields[] = '<fieldset class="form-group tree">'
						.renderCrFormTree($field['source'], $field['value'])
					.'</fieldset>';
				break;
			case 'link':
				$sField = '
					<fieldset class="form-group" >
						'.form_label('', null, array('class' => 'hidden-xs col-sm-3 col-md-3 col-lg-3 control-label')).'
						<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9"> %s </div>
					</fieldset>';
				$aFields[] = sprintf($sField, anchor($field['value'], $field['label']));
				break;
			case 'raty':
				$aFields[] = sprintf($sField, '<div class="raty" name="'.$name.'" />');
				break;
			case 'upload':
				$aFields[] = sprintf($sField, '
					<div name="'.$name.'"> </div> ');
				break;
			case 'html':
				$aFields[] = $field['value'];
				break;
		}
	}

	return $aFields;
}


function renderCrFormTree($aTree, $value){
	$sTmp = '<ul>';
	for ($i=0; $i<count($aTree); $i++) {
		$sTmp .= '	<li>'.anchor($aTree[$i]['url'], $aTree[$i]['label'], array('class' => ($value == $aTree[$i]['id'] ? 'selected' : '')));
		if (count($aTree[$i]['childs']) > 0) {
			$sTmp .= renderCrFormTree($aTree[$i]['childs'], $value);
		}

		$sTmp .= '</li>';
	}
	$sTmp .= '</ul>';
	return $sTmp;
}

function getHtmlCrLink($url, $fieldName) {
	$CI = &get_instance();

	return '
		<fieldset class="form-group ">
			<label class="col-xs-12 col-sm-3 col-md-3 col-lg-3 control-label"> '.$CI->lang->line('Url').'</label>
			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 ">
				<a name="'.$fieldName.'" class="crLink" href="'.$url.'"> '.$url.'</a>
			</div>
		</fieldset>';
}

function validation_array_errors() {
	if (FALSE === ($OBJ =& _get_validation_object()))
	{
		return array();
	}

	return $OBJ->get_error_array();
}


/**
 * Se utiliza en comments y contacts
 * Se utiliza un prefix para obtenes los nombres de los fields a guardar y devolver en la cookie
 */
function saveCrFormCookie($data, $prefix = 'comment') {
	$CI = &get_instance();

	$phone = '';
	if (isset($data[$prefix.'Phone'])) {
		$phone = $data[$prefix.'Phone'];
	}
	else {
		$cookie = getCrFormCookie($prefix);
		if (isset($cookie[$prefix.'Phone'])) {
			$phone = $cookie[$prefix.'Phone'];
		}
	}

	$cookie = array(
		'firstName'  => element($prefix.'FirstName', $data),
		'lastName'   => element($prefix.'LastName', $data),
		'email'      => element($prefix.'Email', $data),
		'phone'      => $phone,
	);

	$CI->session->set_userdata('formCookie', $cookie);
}

function getCrFormCookie($prefix = 'comment') {
	$CI = &get_instance();

	$cookie = $CI->session->userdata('formCookie');

	if (!empty($cookie)) {
		if (is_array($cookie)) {
			$cookie[$prefix.'FirstName'] = element('firstName', $cookie);
			$cookie[$prefix.'LastName']  = element('lastName', $cookie);
			$cookie[$prefix.'Email']     = element('email', $cookie);
			$cookie[$prefix.'Phone']     = element('phone', $cookie);

			return $cookie;
		}
	}

	if ($CI->session->userdata('userId') == USER_ANONYMOUS) {
		return array();
	}

	$CI->load->model('Users_Model');

	$data = $CI->Users_Model->get($CI->session->userdata('userId'));

	return array(
		$prefix.'FirstName'  => element('userFirstName', $data),
		$prefix.'LastName'   => element('userLastName', $data),
		$prefix.'Email'      => element('userEmail', $data),
		$prefix.'Phone'      => element('userPhone', $data),
	);
}
