<?php 
$CI	= &get_instance();

if (!isset($form['action'])) {
	$form['action'] = base_url().$this->uri->uri_string(); 
}

echo form_open($form['action'], array('id'=> element('frmId', $form, 'frmId'), 'class' => 'panel panel-default  crForm form-horizontal', 'role' => 'form' ));

if (isset($title)) {
//	echo '<div class="panel-heading">'.$title.'</div>';
}
echo '	<div class="panel-body"> '; 

$this->load->view('includes/formError'); 

$aFields 		= array();

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
					'.form_input(array('name' => $name, 'value' => $field['value'], 'class' => 'form-control', 'size' => ($field['type'] == 'datetime' ? 18 : 9), 'placeholder' => $this->lang->line('DATE_FORMAT').($field['type'] == 'datetime' ? ' hh:mm:ss' : '') )).'
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
				$source = array('-1' => '-- '.$this->lang->line('Choose').' --') + $source;
			}
		
			$aFields[] = sprintf($sField, form_dropdown($name, $source, $field['value'], 'class="form-control"'));
			break;						
		case 'groupCheckBox':
			$sTmp = '<ul class="groupCheckBox ">';
			foreach ($field['source'] as $key => $value) {
				$sTmp .= '<li>' 
					.form_checkbox($name, $key, element($key, $field['value']))
					.'<span>'.$value.' - '.$key.'</span>'
					.'</li>';
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
					.renderTree($field['source'], $field['value'])	
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

echo implode(' ', $aFields);

if (!isset($form['buttons'])) {
	$form['buttons'] = array();
	$form['buttons'][] = '<button type="button" class="btn btn-default" onclick="$.goToUrl($.base64Decode($.url().param(\'urlList\')));"><i class="icon-arrow-left"></i> '.$CI->lang->line('Back').' </button> ';
	if (isset($form['urlDelete'])) {
		$form['buttons'][] = '<button type="button" class="btn btn-danger"><i class="icon-trash"></i> '.$CI->lang->line('Delete').' </button>';
	}
	$form['buttons'][] = '<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$CI->lang->line('Save').' </button> ';	
}


echo ' </div>';

if (!empty($form['buttons'])) {
	echo 	'<div class="form-actions panel-footer" > ';
	foreach ($form['buttons'] as $button) {
		echo $button.' ';
	}
	echo '</div>';
}

echo form_close(); 

if (hasGallery($form) == true) {
	$this->load->view('includes/uploadfile', array('fileupload' => $fileupload ));
} 


function renderTree($aTree, $value){
	$sTmp = '<ul>';
	for ($i=0; $i<count($aTree); $i++) {
		$sTmp .= '	<li>'.anchor($aTree[$i]['url'], $aTree[$i]['label'], array('class' => ($value == $aTree[$i]['id'] ? 'selected' : '')));
		if (count($aTree[$i]['childs']) > 0) {			
			$sTmp .= renderTree($aTree[$i]['childs'], $value);
		}
		
		$sTmp .= '</li>';		
	}
	$sTmp .= '</ul>';
	return $sTmp;
}
