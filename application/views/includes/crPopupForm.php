<?php 
$CI	= &get_instance();

if (!isset($form['action'])) {
	$form['action'] = base_url().$this->uri->uri_string(); 
}

?>

<div class="modal" role="dialog" >
	<div class="modal-dialog" >
		<div class="modal-content" >
<?php
echo form_open($form['action'], array('id'=> element('frmId', $form, 'frmId'), 'class' => 'crForm form-horizontal', 'role' => 'form' ));
?>

			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">
					<i class="icon-remove"></i>
				</button>
				<h4> 
					<i class="<?php echo element('icon', $form, 'icon-edit'); ?>"></i> <?php echo $form['title']; ?> 
				</h4>
			</div>
			<div class="modal-body">
<?php
$this->load->view('includes/formError'); 
$aFields = renderCrFormFields($form);
echo implode(' ', $aFields);
?>
			</div>
			<div class="modal-footer" >
<?php
if (!isset($form['buttons'])) {
	$form['buttons'] = array();

	if (isset($form['urlDelete'])) {
		$form['buttons'][] = '<button type="button" class="btn btn-danger"><i class="icon-trash"></i> '.$CI->lang->line('Delete').' </button>';
	}
	$form['buttons'][] = '<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$CI->lang->line('Save').' </button> ';	
	
	array_unshift($form['buttons'], '<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">'.$this->lang->line('Close').'</button>'); 
}

foreach ($form['buttons'] as $button) {
	echo $button.' ';
}

echo form_close(); 

$form  = appendMessagesToCrForm($form);
?>

<script type="text/javascript">
$(document).ready(function() {
	$('#<?php echo element('frmId', $form, 'frmId'); ?>').crForm(<?php echo json_encode($form); ?>);
});
</script>
<?php

$fieldGallery = getCrFieldGallery($form);
if ($fieldGallery != null) {
	$this->load->view('includes/uploadfile', array(
		'fileupload' => array ( 
			'entityName' 	=> $fieldGallery['entityName'],
			'entityId'		=> $fieldGallery['entityId']
		) 
	));
}




