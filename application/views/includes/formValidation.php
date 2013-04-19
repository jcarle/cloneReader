<?php 
if (!isset($formAction)) {
	$formAction = base_url().$this->uri->uri_string(); 
}
echo form_open($formAction, array('id'=> element('frmId', $form, 'frmId'), 'class' => 'frmEdit')); 

$this->load->view('includes/formError'); 

$hasGallery = false;

foreach ($form['fields'] as $name => $field) {
	switch ($field['type']) {
		case 'hidden':
			echo form_hidden($name, $field['value']);
			break;
		case 'text':
		case 'date':
		case 'datetime':
			echo '<fieldset>'
				.form_label($field['label'])
				.form_input($name, $field['value']).
				'</fieldset>';
			break;
		case 'password':
			echo '<fieldset>'
				.form_label($field['label'])
				.form_password($name, $field['value']).
				'</fieldset>';
			break;			
		case 'textarea':
			echo '<fieldset>'
				.form_label($field['label'])
				.form_textarea($name, $field['value']).
				'</fieldset>';
			break;			
		case 'autocomplete':
			echo '<fieldset>'
				.form_label($field['label'])
				.form_input($name, reset($field['value']))
				.form_hidden($field['fieldId'], key($field['value']))
				.'</fieldset>';
			break;			
		case 'dropdown':
			echo '<fieldset>'
				.form_label($field['label'])
				.form_dropdown($name, $field['source'], $field['value'])
				.'</fieldset>';
			break;						
		case 'groupCheckBox':
			echo '<fieldset>'
				.form_label($field['label']) 				
				.'<ul class="groupCheckBox">';
			foreach ($field['source'] as $key => $value) {
				echo '<li>' 
					.form_checkbox($name, $key, element($key, $field['value']))
					.form_label($value.' - '.$key)
					.'</li>';
			}
			echo '</ul>	</fieldset>';
			break;		
		case 'checkbox':
			echo '<fieldset>'
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
			
			echo '<fieldset>'
					.form_label($field['label'])
					.'<div id="'.$name.'" data-toggle="modal-gallery" data-target="#modal-gallery">
						<input type="button" class="btnEditPhotos" value="Editar fotos" />
					</div>
				</fieldset>';
			break;
		case 'subform':
			echo '<fieldset>'
					.form_label($field['label'])
					.'<div name="'.$name.'" class="subform"></div>
				</fieldset>';
			break;		
		case 'tree':
			echo '<fieldset class="tree">'
					.renderTree($field['source'], $field['value'])	
				.'</fieldset>';			
			break;
	}	
}

echo form_submit(array('value'=> element('btnSubmitValue', $form, 'Guardar'), 'class'=>'btnSubmit'));
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
	$('#<?php echo $form['frmId'] ?>').formValidator(<?php echo json_encode($form); ?>);
});	
</script>

