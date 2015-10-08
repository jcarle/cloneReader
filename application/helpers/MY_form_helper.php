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
			$form['messages'][$key] = lang(str_replace('callback_', '', $key));
		}
	}

	return $form;
}

/**
 * Para no pedir datos al pedo, completo las propiedades del form solo cuando se muestra la vista, no al validar
 */
function populateCrForm($form, $data) {
	if (isset($form['fields']['entityUrl'])) { // TODO: chequear esta parte, quizas esto convenga setearlo desde afuera
		$form['fields']['entityUrl']['value'] = getHtmlCrLink(element('entityUrl', $data), 'entityUrl');
	}

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
			case 'html':
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
	$gallery = getEntityGalleryConfig($entityTypeId);
	$config   = getEntityConfig($entityTypeId);

	return array(
		'type'          => 'gallery',
		'label'         => $label,
		'urlGallery'    => base_url(str_replace(array('$entityTypeId', '$entityId'), array($entityTypeId, $entityId),$gallery['urlGallery'])),
		'urlSave'       => base_url($gallery['urlSave']),
		'urlDelete'     => base_url($gallery['urlDelete']),
		'entityTypeId'  => $entityTypeId,
		'entityId'      => $entityId,
		'hasEntityLog'  => element('hasEntityLog', $config),
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
								'placeholder' => lang('DATE_FORMAT').($field['type'] == 'datetime' ? ' hh:mm:ss' : '')
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
								'.lang('Edit pictures').'
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
							<small>'.lang('loading ...').'</small>
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
		$sTmp .= ' <li>'.anchor($aTree[$i]['url'], $aTree[$i]['label'], array('class' => ($value == $aTree[$i]['id'] ? 'selected' : '')));
		if (count($aTree[$i]['childs']) > 0) {
			$sTmp .= renderCrFormTree($aTree[$i]['childs'], $value);
		}

		$sTmp .= '</li>';
	}
	$sTmp .= '</ul>';
	return $sTmp;
}

function getCrFormFieldEntityLog($entityTypeId, $entityId) {
	return array(
		'type'   => 'html',
		'value'  => '
			<fieldset class="form-group ">
				<label class="col-xs-12 col-sm-3 col-md-3 col-lg-3 control-label"> </label>
				<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 ">
					<a class="btn btn-default " href="javascript:$.showPopupEntityLog('.$entityTypeId.', '.$entityId.');"> <i class="fa fa-files-o text-info"> </i> '.lang('View logs').'</a>
				</div>
			</fieldset>'
	);
}

function getHtmlCrLink($url, $fieldName) {
	return '
		<fieldset class="form-group ">
			<label class="col-xs-12 col-sm-3 col-md-3 col-lg-3 control-label"> '.lang('Url').'</label>
			<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 ">
				<a name="'.$fieldName.'" class="crLink" href="'.$url.'" target="_blank"> '.$url.'</a>
			</div>
		</fieldset>';
}

function validation_array_errors() {
	if (FALSE === ($OBJ =& _get_validation_object())) {
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

/*
 * Devuelve las porperties de una entidad, se utiliza para definir el upload de archivos, folder, tamaños, etc
 */
function getEntityConfig($entityTypeId, $key = null) {
	$entityConfig  = config_item('entityConfig');
	$entityConfig  = element($entityTypeId, $entityConfig);
	if ($entityConfig != null) {
		if ($key != null) {
			return $entityConfig[$key];
		}
		return $entityConfig;
	}

	return null;
}

/**
 * @return $entityTypeId
 *
 * */
function getEntityTypeIdByEnityTypeName($entityTypeName) {
	$entities = config_item('entityConfig');
	// TODO: pensar si conviene indexar el entityTypeName, para que no tenga que recorrerlo
	foreach ($entities as $entityTypeId => $entityConfig) {
		if ($entityConfig['entityTypeName'] == $entityTypeName) {
			return $entityTypeId;
		}
	}
	return null;
}

/**
 * Devuelve el config de una gallery, si no esta definida usa la gallery por default
 */
function getEntityGalleryConfig($entityTypeId) {
	$config   = getEntityConfig($entityTypeId);
	$gallery  = element('gallery', $config);
	if ($gallery != null) {
		return $gallery;
	}

	if ($config == null) {
		return null;
	}

	// Si no existe, devuelve las properties por defecto, haciendo un sprintf de los folder y del controller con el name de la entidad
	$entityConfig   = config_item('entityConfig');
	$galleryDefault = $entityConfig['default']['gallery'];
	$entityTypeName = $entityConfig[$entityTypeId]['entityTypeName'];

	$galleryDefault['controller']                = sprintf($galleryDefault['controller'], $entityTypeName);
	$galleryDefault['folder']                    = sprintf($galleryDefault['folder'], $entityTypeName);
	$galleryDefault['sizes']['thumb']['folder']  = sprintf($galleryDefault['sizes']['thumb']['folder'], $entityTypeName);
	$galleryDefault['sizes']['large']['folder']  = sprintf($galleryDefault['sizes']['large']['folder'], $entityTypeName);

	return $galleryDefault;
}

/**
 *
 * @param     (string) $id  un string con el formato: [entityTypeId]-[entityId]
 * @param     (string) $fieldName
 * @return    (array)  devuelve un array con el formato:
 * 		array( 'id' => 3-1822, 'text' => 'country' )
 * */
function getEntityToTypeahead($id, $fieldName = 'entityName', $contactEntityTypeId = true) {
	if (empty($id)) {
		return array();
	}

	$CI = &get_instance();

	$aTmp = explode('-', $id);
	return $CI->Commond_Model->getEntitySearch($aTmp[0], $aTmp[1], $fieldName, $contactEntityTypeId);
}

/**
 * @param     (int)    $entityTypeId
 * @param     (int)    $entityId
 * @param     (string) $fieldName
 * @return    (string) devuelve el nombre de la entity
 * */
function getEntityName($entityTypeId, $entityId, $fieldName = 'entityName') {
	$CI = &get_instance();

	$entityConfig = getEntityConfig($entityTypeId);
	if (element('customGetEntityName', $entityConfig) == true) {
		$modelName = ucfirst($entityConfig['entityTypeName']).'_Model';
		$CI->load->model($modelName);
		return $CI->$modelName->getEntityName($entityId);
	}

	$entity = $CI->Commond_Model->getEntitySearch($entityTypeId, $entityId, $fieldName);
	if (!empty($entity)) {
		return $entity['text'];
	}
	return '';
}


/**
 * Devuelve la url de una entidad;
 * Puede recibir un $entityId o un arrayy $data que incluya el $fieldSet; ambos campos son opcionales pero deben incluirse uno de los dos
 * Algunas entidades pueden necesitar un metodo custom para obtener la url, en este caso hay que setear customGetEntityUrl=true  en el config de la entidad
 *
 * */
function getEntityUrl($entityTypeId, $entityId = null, $data = null) {
	$CI = &get_instance();

	if (empty($data) && empty($entityId)) {
		return '';
	}

	$entityConfig = getEntityConfig($entityTypeId);
	if ($entityConfig == null) {
		return '';
	}

	if (element('customGetEntityUrl', $entityConfig) == true && !empty($entityId)) {
		$modelName = ucfirst($entityConfig['entityTypeName']).'_Model';
		$CI->load->model($modelName);
		return $CI->$modelName->getEntityUrl($entityId);
	}
	if (!empty($data)) {
		return base_url(sprintf($entityConfig['entityUrl'], $data[$entityConfig['fieldSef']]));
	}
	if (!empty($entityId)) {
		return base_url(sprintf($entityConfig['entityUrl'], $CI->Commond_Model->getEntitySef($entityTypeId, $entityId)));
	}

	return '';
}

/**
* Busca en el array $data las properties countryId, stateId y cityId el item de menos profundidad y devuelve la zona con el path completo
*
*/
function getZoneToTypeahead($data, $fieldName = 'entityReverseFullName'){
	$CI = &get_instance();
	$entityTypeId = null;
	$entityId     = null;
	if ($data['cityId'] != null) {
		$entityTypeId = config_item('entityTypeCity');
		$entityId     = $data['cityId'];
	}
	else if ($data['stateId'] != null) {
		$entityTypeId = config_item('entityTypeState');
		$entityId     = $data['stateId'];
	}
	else if ($data['countryId'] != null) {
		$entityTypeId = config_item('entityTypeCountry');
		$entityId     = $data['countryId'];
	}

	return $CI->Commond_Model->getEntitySearch($entityTypeId, $entityId, $fieldName, true);
}

/**
 *
 * Apendea al array filters el id del tipo de zona que corresponda (countryId, stateId, cityId)
 * Se utiliza en los listados
 *
 * @param array $filters
 * @param       $zoneId  un string con el formato: [entityTypeId]-[entityId]
 * */
function appendZoneToFilters(array $filters, $zoneId) {
	if (empty($zoneId)) {
		return $filters;
	}
	$aTmp = explode('-', $zoneId);

	switch ($aTmp[0]) {
		case config_item('entityTypeCountry'):
			$filters['countryId'] = $aTmp[1];
			break;
		case config_item('entityTypeState'):
			$filters['stateId'] = $aTmp[1];
			break;
		case config_item('entityTypeCity'):
			$filters['cityId'] = $aTmp[1];
			break;
	}

	return $filters;
}

/**
 *
 * Apendea al array filters el id del tipo que corresponda (brandId, modelId)
 * Se utiliza en los listados
 *
 * @param array $filters
 * @param       $carId  un string con el formato: [entityTypeId]-[entityId]
 * */
function appendCarToFilters(array $filters, $carId) {
	if (empty($carId)) {
		return $filters;
	}
	$aTmp = explode('-', $carId);

	switch ($aTmp[0]) {
		case config_item('entityTypeBrand'):
			$filters['brandId'] = $aTmp[1];
			break;
		case config_item('entityTypeModel'):
			$filters['modelId'] = $aTmp[1];
			break;
	}

	return $filters;
}


function langEntityTypeName($entityTypeId, $singular = false) {
	$entityConfig = getEntityConfig($entityTypeId);
	if ($entityConfig == null) {
		return '';
	}
	return lang(ucfirst($entityConfig[($singular == true ? 'entityTypeSingularName' : 'entityTypeName')]));
}

/**
 * Se utiliza para relodear un crForm luego de guardar los datos
 * */
function loadAjaxSaveCrForm($code, $entityTypeId, $entityId){
	if ($code == false || empty($entityTypeId) || empty($entityId)) {
		return loadViewAjax($code);
	}
	$CI           = &get_instance();
	$entityConfig = getEntityConfig($entityTypeId);
	if ($entityConfig == null) {
		return loadViewAjax($code);
	}

	$goToUrl = base_url($entityConfig['entityTypeName'].'/edit/'.$entityId);

	$CI->load->library('user_agent');
	$url = parse_url($CI->agent->referrer());
	parse_str(element('query', $url), $params);
	if (isset($params['urlList'])) {
		$goToUrl .= '?urlList='.$params['urlList'];
	}
	return loadViewAjax($code, array( 'msg' => lang('Data updated successfully'), 'icon' => 'success', 'goToUrl' => $goToUrl ));
}

/**
 * Se ejecuta despues de guardar datos en un crForm
 * 		Soporta las urls ('$entityTypeName/edit/$entityId', '$entityTypeName/add', '$entityTypeName/savePicture/$entityId', '$entityTypeName/deletePicture/$entityId' );
 *		En caso de necesitar guardar en entities_logs desde otro controller, llamar a mano al method $this->Commond_Model->saveEntityLog
 * 		En modo Edit: las variables $entityTypeId y $entityId esta en la url;
 * 		En modo Add:  hay que setear la variable "lastInsertId"; ya que el entityId no está en la url (aun no existe)
 *					Ej: $this->config->set_item('lastInsertId', $testId);
 * 			 		Si en el config de la entidad existe 'fieldSef' ejecuta saveEntitySef
 * 		En ambos casos: si en el config de la entidad esta seteado hasEntityLog==true guarda en la tabla entities_logs
 * */
function onSaveCrForm() {
	$CI = &get_instance();

	if ($CI->input->post() === false) {
		return;
	}
	if (http_response_code() != 200) {
		return;
	}
	$aErrors = validation_array_errors();
	if (!empty($aErrors)) {
		return;
	}
	if (isset($CI->upload)) {
		$aErrors = $CI->upload->display_errors('', '');
		if (!empty($aErrors)) {
			return;
		}
	}

	$aTmp = explode('/', uri_string());
	if (count($aTmp) < 2) {
		return;
	}
	$method = $aTmp[1];

	if (!in_array($method, array('add', 'edit', 'savePicture', 'deletePicture'))) {
		return;
	}
	$entityTypeId = getEntityTypeIdByEnityTypeName($aTmp[0]);
	if ($entityTypeId === null) {
		return;
	}

	$entityConfig = getEntityConfig($entityTypeId);
	if ($entityConfig == null) {
		return;
	}

	if ($method == 'add') {
		$entityId = config_item('lastInsertId');
	}
	else {
		$entityId = $aTmp[2];
	}

	if ($method == 'add' && element('fieldSef', $entityConfig) !== false) {
		$CI->Commond_Model->saveEntitySef($entityTypeId, $entityId);
	}
	if (element('hasEntityLog', $entityConfig) == true) {
		$CI->Commond_Model->saveEntityLog($entityTypeId, $entityId);
	}
}
