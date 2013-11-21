<?php 
$CI	= &get_instance();

if (!isset($form['action'])) {
	$form['action'] = base_url().$this->uri->uri_string(); 
}

echo form_open($form['action'], array('id'=> element('frmId', $form, 'frmId'), 'class' => 'panel panel-default crForm form-horizontal', 'role' => 'form' ));

if (isset($title)) {
//	echo '<div class="panel-heading">'.$title.'</div>';
}
echo '	<div class="panel-body"> '; 

$this->load->view('includes/formError'); 

$aFields = renderCrFormFields($form);

echo implode(' ', $aFields);

if (!isset($form['buttons'])) {
	$form['buttons'] = array();
	$form['buttons'][] = '<button type="button" class="btn btn-default" onclick="$.goToUrl($.base64Decode($.url().param(\'urlList\')));"><i class="icon-arrow-left"></i> '.$CI->lang->line('Back').' </button> ';
	if (isset($form['urlDelete'])) {
		$form['buttons'][] = '<button type="button" class="btn btn-danger"><i class="icon-trash"></i> '.$CI->lang->line('Delete').' </button>';
	}
	$form['buttons'][] = '<button type="submit" class="btn btn-primary" disabled="disabled"><i class="icon-save"></i> '.$CI->lang->line('Save').' </button> ';	
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

$fieldGallery = getCrFieldGallery($form);
if ($fieldGallery != null) {
	$this->load->view('includes/uploadfile', array(
		'fileupload' => array ( 
			'entityName' 	=> $fieldGallery['entityName'],
			'entityId'		=> $fieldGallery['entityId']
		) 
	));
}

