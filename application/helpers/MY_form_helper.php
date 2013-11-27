<?php
function getCrFormRulesMessages() { // TODO: mover esto de aca!
	$CI	= &get_instance();
	
	return array(
		'required' 		=> $CI->lang->line('Please complete the field "%s"'),
		'valid_email' 	=> $CI->lang->line('Please enter a valid email address'),
		'numeric' 		=> $CI->lang->line('Please enter a valid number in the field "%s"'),
		'_login' 		=> $CI->lang->line('The email or password you entered are incorrect'),
	);
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
			'value'			=> $currency['value'],
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
			'rules' => 'required|numeric'
		),
		array(
			'field' => $exchange['name'],
			'label' => $exchange['label'],
			'rules' => 'required|numeric'
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

function getCrFieldUpload($form) {
	foreach ($form['fields'] as $name => $field) {
		if ($field['type'] == 'upload') {
			return $field;
		}
	}
	return null;
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
				$properties = array('name' => $name, 'value' => $field['value'], 'class' => 'form-control', 'placeholder' => element('placeholder', $field));
				if (element('disabled', $field) == true) {
					$properties += array('disabled' => 'disabled');
				} 
				$aFields[] = sprintf($sField, form_input($properties));
				break;
			case 'date':
			case 'datetime':
				$aFields[] = sprintf($sField, 
					'<div class="input-group" style="width:1px">
						'.form_input(array('name' => $name, 'value' => $field['value'], 'class' => 'form-control', 'size' => ($field['type'] == 'datetime' ? 18 : 9), 'placeholder' => $CI->lang->line('DATE_FORMAT').($field['type'] == 'datetime' ? ' hh:mm:ss' : '') )).'
						<span class="input-group-addon add-on"><i class="icon-remove"></i></span>
						<span class="input-group-addon add-on"><i class="icon-th"></i></span>
					</div>');
				break;
			case 'password':
				$aFields[] = sprintf($sField, form_password(array('name' => $name, 'value' => $field['value'], 'class' => 'form-control')));
				break;			
			case 'textarea':
				$aFields[] = sprintf($sField, form_textarea($name, $field['value'], 'class="form-control"'));
				break;			
			case 'typeahead':
				$aFields[] = sprintf($sField, 
					'<input name="'.$name.'"  class="form-control" />'
				);
				break;			
			case 'dropdown':
				$source = element('source', $field, array());
				if (element('appendNullOption', $field) == true) {
					$source = array('' => '-- '.$CI->lang->line('Choose').' --') + $source;
				}
			
				$aFields[] = sprintf($sField, form_dropdown($name, $source, $field['value'], 'class="form-control"'));
				break;						
			case 'groupCheckBox':
				$showId = element('showId', $field, false);
				$sTmp = '<ul class="groupCheckBox ">';
				foreach ($field['source'] as $key => $value) {
					$sTmp .= 
						'<li>
							<div class="checkbox">
								 <label>' 
									.form_checkbox($name, $key, element($key, $field['value']))
									.$value.($showId == true ? ' - '.$key : '').'
								</label>
							</div>
						</li>';
				}
				$sTmp .= '</ul>';
				$aFields[] = sprintf($sField, $sTmp);
				break;		
			case 'checkbox':
				$aFields[] = sprintf($sField, form_checkbox($name, 'on', $field['checked']));
				break;
			case 'gallery':
				$fileupload = array ( 
					'entityName' 	=> $field['entityName'],
					'entityId'		=> $field['entityId']
				);
					
				$aFields[] = sprintf($sField, '
						<div id="'.$name.'" data-toggle="modal-gallery" data-target="#modal-gallery" class="gallery well" >
							<button type="button" class="btn btn-success btn-sm btnEditPhotos fileinput-button">
								<i class="icon-picture" ></i>
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
							<i class="icon-spinner icon-spin icon-large"></i>
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
				<fieldset class="control-group">
					<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 col-sm-offset-3 col-md-offset-3 col-lg-offset-3"> %s </div>
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
							<i class="icon-plus icon-white"></i>
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
 * Apendeo js y css y agrego items al script que va a inicializar los objetos en el header
 */
function appendCrFormJsAndCss($view, $form, $hasForm, $hasGallery, $aScripts) {
	$CI = &get_instance();
	
	if ($hasForm == true) {
		if (!isset($form)) {
			$form = array('fields' => array());	
		}
	}

	if (is_array(element('fields', $form))) {
		$hasForm = true;
	}
	if ($view == 'includes/crForm') {
		$hasForm = true;
	}	
	
	if ($hasForm != true) {
		return $aScripts;
	}

	if ($hasGallery != true) {
		$hasGallery = (getCrFieldGallery($form) != null);
	}
	
	$CI->carabiner->js('crForm.js');
	$CI->carabiner->js('jquery.raty.js');
	$CI->carabiner->js('select2.js');
	$CI->carabiner->js('autoNumeric.js');
	$CI->carabiner->js('bootstrap-datetimepicker.min.js');
	
	$CI->carabiner->css('select2.css');
	$CI->carabiner->css('select2-bootstrap.css');
	$CI->carabiner->css('bootstrap-datetimepicker.css');
	
	if ($CI->session->userdata('langId') == 'es') {
		$CI->carabiner->js('select2_locale_es.js');	
		$CI->carabiner->js('bootstrap-datetimepicker.es.js');
	}
	
	if (getCrFieldUpload($form) != null) {
		$CI->carabiner->js('jquery.ui.widget.js');
		$CI->carabiner->js('jquery.fileupload.js');
		$CI->carabiner->js('jquery.fileupload-ui.js');
		$CI->carabiner->js('jquery.fileupload-process.js');
				
		$CI->carabiner->css('jquery.fileupload-ui.css');
	}	

	if ($hasGallery == true) {
		$CI->carabiner->js('tmpl.min.js');
		$CI->carabiner->js('jquery.ui.widget.js');
		$CI->carabiner->js('jquery.fileupload.js');
		$CI->carabiner->js('jquery.fileupload-ui.js');
		$CI->carabiner->js('jquery.fileupload-process.js');
		$CI->carabiner->js('jquery.imgCenter.js');
		$CI->carabiner->js('blueimp-gallery.js');

		$CI->carabiner->css('blueimp-gallery.css');
		$CI->carabiner->css('jquery.fileupload-ui.css');
	}
	
	$aScripts[] = '
		$(document).ready(function() {
			$(\'#'. element('frmId', $form, 'frmId').'\').crForm('.json_encode($form).');
		});';	
		
	
	return $aScripts;
}

/**
 * Apendeo js y css y agrego items al script que va a inicializar los objetos en el header
 */
function appendCrListJsAndCss($view, $list, $aScripts) {
	$CI = &get_instance();
	
	if ($view != 'includes/crList') {
		return $aScripts; 
	}
	
	$CI->carabiner->js('crList.js');
	
	$aScripts[] = '
		$(document).ready(function() {
			$(\'.crList\').crList();
		});	';
		
	
	$filters = element('filters', $list);
	if ($filters != null) {
		$aScripts = appendCrFormJsAndCss($view, array('fields' => $filters, 'sendWithAjax' => false, 'frmId' => 'frmCrList'), null, null, $aScripts);
	}
		
	return $aScripts; 	
}

