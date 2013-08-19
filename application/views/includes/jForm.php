<?php 
if (!isset($form['action'])) {
	$form['action'] = base_url().$this->uri->uri_string(); 
}

echo form_open($form['action'], array('id'=> element('frmId', $form, 'frmId'), 'class' => 'jForm form-horizontal')); 

$this->load->view('includes/formError'); 

$aFields 		= array();
$inputSize		= 'span11';

foreach ($form['fields'] as $name => $field) {
	$sField = '
		<fieldset class="control-group">
			'.form_label(element('label', $field), null, array('class' => 'control-label')).'
			<div class="controls"> %s </div>
		</fieldset>';
	
	switch ($field['type']) {
		case 'hidden':
			$aFields[] = form_hidden($name, $field['value']);
			break;
		case 'text':
			$properties = array('name' => $name, 'value' => $field['value'], 'class' => $inputSize, 'placeholder' => element('placeholder', $field));
			if (element('disabled', $field) == true) {
				$properties += array('disabled' => 'disabled');
			} 
			$aFields[] = sprintf($sField, form_input($properties));
			break;
		case 'date':
		case 'datetime':
			$aFields[] = sprintf($sField, 
				'<div class="input-append">
					'.form_input(array('name' => $name, 'value' => $field['value'], 'size' => ($field['type'] == 'datetime' ? 18 : 9), 'placeholder' => 'dd/mm/yyyy'.($field['type'] == 'datetime' ? ' hh:mm:ss' : '') )).'
					<span class="add-on"><i class="icon-remove"></i></span>
					<span class="add-on"><i class="icon-th"></i></span>
				</div>');
			break;
		case 'password':
			$aFields[] = sprintf($sField, form_password(array('name' => $name, 'value' => $field['value'], 'class' => $inputSize)));
			break;			
		case 'textarea':
			$aFields[] = sprintf($sField, form_textarea($name, $field['value'], 'class="'.$inputSize.'"'));
			break;			
		case 'typeahead':
			$aFields[] = sprintf($sField, 
				form_input(array('name' => $name, 'value' => reset($field['value']), 'class' => $inputSize, 'autocomplete' => 'off')).
				form_hidden($field['fieldId'], ((int)key($field['value']) == 0 ? '' : (int)key($field['value'])) )
			);
			break;			
		case 'dropdown':
			$aFields[] = sprintf($sField, form_dropdown($name, element('source', $field, array()), $field['value'], 'class="'.$inputSize.'"'));
			break;						
		case 'groupCheckBox':
			$sTmp = '<ul class="groupCheckBox '.$inputSize.'">';
			foreach ($field['source'] as $key => $value) {
				$sTmp .= '<li>' 
					.form_checkbox($name, $key, element($key, $field['value']))
					.form_label($value.' - '.$key)
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
					<div id="'.$name.'" data-toggle="modal-gallery" data-target="#modal-gallery" class="span11">
						<button type="button" class="btn btn-success btnEditPhotos fileinput-button">
							<i class="icon-picture" ></i>
							Editar fotos
						</button>
					</div>
				');
			break;
		case 'subform':
			$aFields[] = sprintf($sField, '
				<div name="'.$name.'" class="subform '.$inputSize.'"> 
					<div class="alert alert-info">
						<i class="icon-spinner icon-spin icon-large"></i>
						<small>cargando ...</small>
					</div>
				</div>
			');
			break;
		case 'tree':
			$aFields[] = '<fieldset class="tree">'
					.renderTree($field['source'], $field['value'])	
				.'</fieldset>';			
			break;
		case 'link':
			$sField = '
			<fieldset class="control-group">
				<div class="controls"> %s </div>
			</fieldset>';		
			$aFields[] = sprintf($sField, anchor($field['value'], $field['label']));
			break;
		case 'raty':
			$aFields[] = sprintf($sField, '<div class="raty" name="'.$name.'" />');
			break;
		case 'upload':
			$aFields[] = sprintf($sField, '
				<div class="span5">
					<span class="btn btn-success fileinput-button">
						<i class="icon-plus icon-white"></i>
						<span>Add File...</span>
						<input type="file" name="userfile" >
					</span>
				</div>
				<div class="span5 fileupload-progress fade">
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
	$form['buttons'][] = '<button type="button" class="btn" onclick="$.goToUrl($.base64Decode($.url().param(\'urlList\')));"><i class="icon-arrow-left"></i> Volver</button> ';
	if (isset($form['urlDelete'])) {
		$form['buttons'][] = '<button type="button" class="btn btn-danger"><i class="icon-trash"></i> Eliminar </button>';
	}
	$form['buttons'][] = '<button type="submit" class="btn btn-primary"><i class="icon-save"></i> Guardar </button> ';	
}


echo ' <div class="form-actions" > ';
foreach ($form['buttons'] as $button) {
	echo $button.' ';
}
echo '</div>';
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
?>
<script>
$(document).ready(function() {
	$('#<?php echo element('frmId', $form, 'frmId') ?>').jForm(<?php echo json_encode($form); ?>);
});	
</script>

