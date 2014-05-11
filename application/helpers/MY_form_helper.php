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
 * TODO: implementar que los sources de [dropdown, menuTree] se llenen con este metodo
 */
function populateCrForm($form, $data) {
	foreach ($form['fields'] as $fieldName => $fieldValue) {
		switch ($form['fields'][$fieldName]['type']) {
			case 'hidden':
			case 'text':
			case 'dropdown':
			case 'datetime':
			case 'logo':
			case 'typeahead':
			case 'textarea':
			case 'raty':
			case 'numeric':
				// TODO: revisar este IF. Esta puesto para que no sobreescriba fields hiddens con el parentId que se usa para agregar un child
				if ( element('value', $form['fields'][$fieldName]) === false ) { 
					$form['fields'][$fieldName]['value'] = element($fieldName, $data, '');
				}
				break;
			case 'checkbox':
				$form['fields'][$fieldName]['checked'] = element($fieldName, $data);
				break;
			case 'groupCheckBox':
				$form['fields'][$fieldName]['value'] = element(str_replace('[]', '', $fieldName), $data);
				break;
		}
	}
	
	return $form;
} 

function getCrFormFieldMoney(array $price, array $currency, array $exchange, array $total) {
	$CI = &get_instance();
	$CI->load->model('Coins_Model');
	
	$subscribe 	= array();
	$aFieldName = array( $price['name'], $currency['name'], $exchange['name'], );
	
	foreach ($aFieldName as $fieldName) {
		$subscribe[] = array(
			'field' 		=> $fieldName,
			'event'			=> 'change', 
			'callback'		=> 'calculatePrice',
			'arguments'		=> array(
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
			'type'	 		=> 'text',
			'name'			=> $price['name'],
			'label'			=> $price['label'], 
			'value'			=> element('value', $price, 0),
			'placeholder'	=> '0,00',
		),
		$currency['name']	=> array(
			'type'			=> 'dropdown',
			'name' 			=> $currency['name'],
			'label'			=> $currency['label'], 
			'value'			=> element('value', $currency),
			'source'		=> array_to_select($CI->Coins_Model->select(true), 'currencyId', 'currencyName'),
		),
		$exchange['name']	=> array(
			'type'	 		=> 'text',
			'name'			=> $exchange['name'],
			'label'			=> $exchange['label'], 
			'value'			=> element('value', $exchange, 0),
			'placeholder'	=> '0,00',
		),
		$total['name']	=> array(
			'type'	 		=> 'text',
			'name'			=> $total['name'],
			'label'			=> $total['label'], 
			'value'			=> null,
			'disabled'		=> true,
			'subscribe'		=> $subscribe
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
			'field' 		=> $fieldName,
			'event'			=> 'change', 
			'callback'		=> 'sumValues',
			'arguments'		=> array( json_encode($aFieldName) )
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

function hasCrUploadFile($form) {
	foreach ($form['fields'] as $name => $field) {
		if ($field['type'] == 'gallery') {
			return true;
		}
		if ($field['type'] == 'upload') {
			return true;
		}		
	}
	return false;
}

function renderCrFormFields($form) {
	$CI			= &get_instance();
	$aFields 	= array();
	
	foreach ($form['fields'] as $name => $field) {
		$sField = '
			<fieldset class="form-group">
				'.form_label(element('label', $field), null, array('class' => 'col-xs-12 col-sm-3 col-md-3 col-lg-3 control-label')).'
				<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9"> %s </div>
			</fieldset>';
		
		switch ($field['type']) {
			case 'hidden':
				$aFields[] = form_hidden($name, $field['value']);
				break;
			case 'text':
			case 'numeric':
				$properties = array('name' => $name, 'value' => element('value', $field), 'class' => 'form-control', 'placeholder' => element('placeholder', $field));
				if (element('disabled', $field) == true) {
					$properties += array('disabled' => 'disabled');
				} 
				$aFields[] = sprintf($sField, form_input($properties));
				break;
			case 'date':
			case 'datetime':
				$aFields[] = sprintf($sField, 
					'<div class="input-group" style="width:1px">
						'.form_input(array('name' => $name, 'value' => element('value', $field), 'class' => 'form-control', 'size' => ($field['type'] == 'datetime' ? 18 : 9), 'placeholder' => $CI->lang->line('DATE_FORMAT').($field['type'] == 'datetime' ? ' hh:mm:ss' : '') )).'
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
			case 'typeahead':
				$aFields[] = sprintf($sField, 
					'<input name="'.$name.'"  type="text" class="form-control" />'
				);
				break;			
			case 'dropdown':
				$source = element('source', $field, array());
				if (element('appendNullOption', $field) == true) {
					$source = array('' => '-- '.$CI->lang->line('Choose').' --') + $source;
				}

				$properties = array('class="form-control"');
				if (element('disabled', $field) == true) {
					$properties[] = 'disabled="disabled"';
				}

				$aFields[] = sprintf($sField, form_dropdown($name, $source, element('value', $field, null), implode(' ', $properties)));
				break;						
			case 'groupCheckBox':
				$showId = element('showId', $field, false);
				$sTmp = '<ul class="groupCheckBox ">';
				foreach ($field['source'] as $key => $value) {
					$sTmp .= 
						'<li>
							<div class="checkbox">
								 <label>' 
									.form_checkbox($name, $key, in_array($key, $field['value']))
									.$value.($showId == true ? ' - '.$key : '').'
								</label>
							</div>
						</li>';
				}
				$sTmp .= '</ul>';
				$aFields[] = sprintf($sField, $sTmp);
				break;		
			case 'checkbox':
				$aFields[] = '
					<fieldset class="form-group">
						<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3 ">
						</div>
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
						<div class="alert alert-info">
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
					<div class="col-md-5">
						<span class="btn btn-success fileinput-button">
							<i class="fa fa-plus"></i>
							<span>'.$CI->lang->line('Add File').'</span>
							<input type="file" name="userfile" >
						</span>
					</div>
					<div class="col-md-5 fileupload-progress fade">
						<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
							<div class="progress-bar progress-bar-success bar bar-success" style="width:0%;"></div>
						</div>
						<div class="progress-extended">&nbsp;</div>
					</div>
				');
				break;
			case 'logo':
				// TODO: mejorar este field, agregar el btn upload, etc
				$aFields[] = sprintf($sField, '<img src="'.$field['value'].'" />');				
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


/**
 * Apendeo js y css necesarios para los formularios
 */
function appendCrFormJsAndCss($aScripts) {
	$CI = &get_instance();
	
	$CI->carabiner->js('crForm.js');
	$CI->carabiner->js('jquery.raty.js');
	$CI->carabiner->js('select2.js');
	$CI->carabiner->js('autoNumeric.js');
	$CI->carabiner->js('bootstrap-datetimepicker.min.js');

	if ($CI->session->userdata('langId') != 'en') {
		$aTmp = explode('-', $CI->session->userdata('langId'));
		if (count($aTmp) == 2) {
			$CI->carabiner->js('select2/select2_locale_'.$aTmp[0].'-'.strtoupper($aTmp[1]).'.js');	
			$CI->carabiner->js('datetimepicker/bootstrap-datetimepicker.'.$aTmp[0].'-'.strtoupper($aTmp[1]).'.js');
		}
		else {
			$CI->carabiner->js('select2/select2_locale_'.$CI->session->userdata('langId').'.js');	
			$CI->carabiner->js('datetimepicker/bootstrap-datetimepicker.'.$CI->session->userdata('langId').'.js');
		}
	}
	
	$CI->carabiner->js('tmpl.min.js');	
	$CI->carabiner->js('jquery.ui.widget.js');
	$CI->carabiner->js('jquery.fileupload.js');
	$CI->carabiner->js('jquery.fileupload-ui.js');
	$CI->carabiner->js('jquery.fileupload-process.js');
	$CI->carabiner->js('jquery.ui.widget.js');
	$CI->carabiner->js('jquery.imgCenter.js');
	$CI->carabiner->js('blueimp-gallery.js');

	$CI->carabiner->css('select2.css');
	$CI->carabiner->css('select2-bootstrap.css');
	$CI->carabiner->css('bootstrap-datetimepicker.css');
	$CI->carabiner->css('blueimp-gallery.css');
	$CI->carabiner->css('jquery.fileupload-ui.css');
	
	return $aScripts;
}

/**
 * Apendeo js y css necesarios para los listados 
 */
function appendCrListJsAndCss($aScripts) {
	$CI = &get_instance();
	
	$CI->carabiner->js('crList.js');
	$CI->carabiner->js('bootstrap-paginator.js');
	
	return $aScripts; 	
}
