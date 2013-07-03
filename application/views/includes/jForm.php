<?php 
if (!isset($formAction)) {
	$formAction = base_url().$this->uri->uri_string(); 
}
echo form_open($formAction, array('id'=> element('frmId', $form, 'frmId'), 'class' => 'jForm')); 

$this->load->view('includes/formError'); 

$hasGallery = false;

$aFields = array();

foreach ($form['fields'] as $name => $field) {
	switch ($field['type']) {
		case 'hidden':
			$aFields[] = form_hidden($name, $field['value']);
			break;
		case 'text':
		case 'date':
		case 'datetime':
			$aFields[] = '<fieldset>'
				.form_label($field['label'])
				.form_input($name, $field['value']).
				'</fieldset>';
			break;
		case 'password':
			$aFields[] = '<fieldset>'
				.form_label($field['label'])
				.form_password($name, $field['value']).
				'</fieldset>';
			break;			
		case 'textarea':
			$aFields[] = '<fieldset>'
				.form_label($field['label'])
				.form_textarea($name, $field['value']).
				'</fieldset>';
			break;			
		case 'autocomplete':
			$aFields[] = '<fieldset>'
				.form_label($field['label'])
				.form_input($name, reset($field['value']))
				.form_hidden($field['fieldId'], key($field['value']))
				.'</fieldset>';
			break;			
		case 'dropdown':
			$aFields[] = '<fieldset>'
				.form_label($field['label'])
				.form_dropdown($name, element('source', $field, array()), $field['value'])
				.'</fieldset>';
			break;						
		case 'groupCheckBox':
			$sTmp = '<fieldset>'
				.form_label($field['label']) 				
				.'<ul class="groupCheckBox">';
			foreach ($field['source'] as $key => $value) {
				$sTmp .= '<li>' 
					.form_checkbox($name, $key, element($key, $field['value']))
					.form_label($value.' - '.$key)
					.'</li>';
			}
			$sTmp .= '</ul>	</fieldset>';
			$aFields[] = $sTmp;
			break;		
		case 'checkbox':
			$aFields[] = '<fieldset>'
				.form_label($field['label'])
				.form_checkbox($name, 'on', $field['checked'])
				.'</fieldset>';
			break;
		case 'gallery':
			$hasGallery = true;
			
			$fileupload = array ( 
				'entityName' 	=> $field['entityName'],
				'entityId'		=> $field['entityId']
			);
			
			$aFields[] = '<fieldset>'
					.form_label($field['label'])
					.'<div id="'.$name.'" data-toggle="modal-gallery" data-target="#modal-gallery">
						<input type="button" class="btnEditPhotos" value="Editar fotos" />
					</div>
				</fieldset>';
			break;
		case 'subform':
			$aFields[] = '<fieldset>'
					.form_label($field['label'])
					.'<div name="'.$name.'" class="subform"></div>
				</fieldset>';
			break;		
		case 'tree':
			$aFields[] = '<fieldset class="tree">'
					.renderTree($field['source'], $field['value'])	
				.'</fieldset>';			
			break;
		case 'link':
			$aFields[] = '<fieldset>'.anchor($field['value'], $field['label'], array('class' => 'link')).'</fieldset>';
			break;
		case 'raty':
			$aFields[] = '<fieldset>'
					.form_label($field['label']).
					'<div class="raty" name="'.$name.'" />
				</fieldset>';		
			break;		
	}	
}

echo implode(' ', $aFields);
echo form_submit(array('value'=> element('btnSubmitValue', $form, 'Guardar'), 'class'=>'btnSubmit btn btn-small'));
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

