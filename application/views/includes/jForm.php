<?php 
if (!isset($formAction)) {
	$formAction = base_url().$this->uri->uri_string(); 
}

if (!isset($form['showBtnBack'])) {
	$form['showBtnBack'] = true;
}
if (!isset($form['iconSend'])) {
	$form['iconSend'] = 'icon-save';
}

echo form_open($formAction, array('id'=> element('frmId', $form, 'frmId'), 'class' => 'jForm form-horizontal')); 

$this->load->view('includes/formError'); 

$hasGallery = false;
$aFields 	= array();
$inputSize	= 'span10';

foreach ($form['fields'] as $name => $field) {
/*
	$sField = '
		<fieldset class="control-group">
			<div class="controls"> %s </div>
		</fieldset>';
*/
	if (isset($field['label'])) {
		$sField = '
			<fieldset class="control-group">
				'.form_label($field['label'], null, array('class' => 'control-label')).'
				<div class="controls"> %s </div>
			</fieldset>';
	}	
	
	switch ($field['type']) {
		case 'hidden':
			$aFields[] = form_hidden($name, $field['value']);
			break;
		case 'text':
			$aFields[] = sprintf($sField, form_input(array('name' => $name, 'value' => $field['value'], 'class' => $inputSize)));
			break;
		case 'date':
		case 'datetime':
			$aFields[] = '<fieldset class="control-group">'
				.form_label($field['label'], null, array('class' => 'control-label')).'
				<div class="input-append">
					'.form_input(array('name' => $name, 'value' => $field['value'], 'class' => 'input-small')).'
					<span class="add-on">
						<i class="icon-calendar"></i>
					</span>					
				</fieldset>';
			break;
		case 'password':
			$aFields[] = sprintf($sField, form_password(array('name' => $name, 'value' => $field['value'], 'class' => $inputSize)));
			break;			
		case 'textarea':
			$aFields[] = sprintf($sField, form_textarea($name, $field['value'], 'class="'.$inputSize.'"'));
			break;			
		case 'autocomplete':
			$aFields[] = '<fieldset class="control-group">'
				.form_label($field['label'], null, array('class' => 'control-label'))
				.form_input($name, reset($field['value']))
				.form_hidden($field['fieldId'], key($field['value']))
				.'</fieldset>';
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
			$hasGallery = true;
			
			$fileupload = array ( 
				'entityName' 	=> $field['entityName'],
				'entityId'		=> $field['entityId']
			);
			
			$aFields[] = '<fieldset class="control-group">'
					.form_label($field['label'], null, array('class' => 'control-label'))
					.'<div id="'.$name.'" data-toggle="modal-gallery" data-target="#modal-gallery">
						<input type="button" class="btnEditPhotos" value="Editar fotos" />
					</div>
				</fieldset>';
			break;
		case 'subform':
			$aFields[] = '<fieldset class="control-group">'
					.form_label($field['label'], null, array('class' => 'control-label'))
					.'<div name="'.$name.'" class="subform"></div>
				</fieldset>';
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
			$aFields[] = '<fieldset class="control-group">'
					.form_label($field['label'], null, array('class' => 'control-label')).
					'<div class="raty" name="'.$name.'" />
				</fieldset>';		
			break;		
	}	
}

echo implode(' ', $aFields);
echo '<div class="form-actions" >';
echo 	'<button type="submit" class="btn btn-primary"><i class="'.$form['iconSend'].'"></i> '.element('btnSubmitValue', $form, 'Guardar').'</button> ';

if (element('showBtnBack', $form) == true) {
	echo 	'<button type="button" class="btn" onclick="$.goToUrl($.base64Decode($.url().param(\'urlList\')));"> '.element('btnSubmitValue', $form, 'Cancelar').'</button>';
}

echo '</div>';


    
echo form_close(); 

if ($hasGallery == true) {
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
	$('#<?php echo $form['frmId'] ?>').jForm(<?php echo json_encode($form); ?>);
});	
</script>

