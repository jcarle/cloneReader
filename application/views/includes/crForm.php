<?php
/**
 * El form tiene que tener este formato:
 * 
 * $form = array(
 *	'frmId'		=> 'frmId',
 *	'action'	=> base_url('entity/save'), // 
 *	'fields'	=> array(), // fields que va a incluir el formulario
 *	'rules'		=> array(), // reglas de validacion para cada campo
 * 	'buttons'	=> array(), // los bottones que se van a mostrar 
 *	'info'		=> array('position' => 'left|right', 'html' => ''), // si incluye info a los costados
 *	'title'		=> 'title',
 *	'icon'		=> 'fa fa-edit', // se utiliza en los popup form,
 *	'urlDelete' => base_url('entity/delete'), // url para borrar 
 *	'callback'	=> function javascript que se llama al enviar el form 
 *);
 *
 *	fields:
 * 		$upload = array(
 *			'type'       => 'upload',
 *			'label'      => 'Logo',
 *			'value'      => array('url' => $url, 'name' => $name), 
 *			'urlSave'    => base_url('%s/savePicture/'.$id),    // url del controlador para guardar el archivo
 *			'urlDelete'  => base_url('%s/deletePicture/'.$id),  // url del controlador para borrar el archivo
 *			'isPicture'  => true,           // indica si se va a subir una imagen u otro archivo
 * 			'disabled'   => false, // TODO: implementar!
 * 							// TODO: implementar acceptFileTypes, maxFileSize, maxNumberOfFiles
 *		);
 * 		En los controladores se pueden llamar a los metodos savePicture o saveFile.
 * 		Estos metodos utilizan un archivo de configuración con el formato:
 * 			$config = array(
 *				'folder'        => '/assets/images/%s/logo/original/',
 *				'allowed_types' => 'gif|jpg|png',
 *				'max_size'      => 1024 * 8,
 *				'sizes'         => array( // solo necesario para savePicture
 *					'thumb' => array( 'width' => 150,  'height' => 150, 'folder' => '/assets/images/%s/logo/thumb/' ),
 *				)
 *			);
 * 
 * 		$gallery = array(
 *				'label'         => 'Pictures',
 *				
 *				'entityTypeId'  => $entityTypeId,
 *				'entityId'      => $entityId,
 *			);
 * 		Las gallery le pegan al controlador base_url('gallery/savePicture') y necesitan los parametros  $entityTypeId y $entityId
 * 		Los metodos utilizan un archivo de configuracion con el formato:
 * 			$config = array(
 *				'controller'    => '%s/edit', // controller con el que se va a validar que el usuario logeado tenga permisos
 *				'folder'        => '/assets/images/%s/original/', // folder con la imagen original
 * 				'urlGallery'    => base_url('gallery/select/'.$entityTypeId.'/'.$entityId), // url que devuelve un json con todas las imagenes de la gallery
 *				'allowed_types' => 'gif|jpg|png',
 *				'max_size'      => 1024 * 8,
 *				'sizes'         => array( // thumb y large
 *					'thumb' => array( 'width' => 150,  'height' => 100, 'folder' => '/assets/images/%s/thumb/' ), 
 *					'large' => array( 'width' => 1024, 'height' => 660, 'folder' => '/assets/images/%s/large/' ),
 *				)
 *			);
 *		
 */
 

$CI	= &get_instance();

$form  = appendMessagesToCrForm($form);

if (!isset($form['action'])) {
	$form['action'] = base_url().$this->uri->uri_string(); 
}

echo form_open($form['action'], array('id'=> element('frmId', $form, 'frmId'), 'class' => 'panel panel-default crForm form-horizontal', 'role' => 'form' ));

if (isset($form['title'])) {
	echo '<div class="panel-heading">'.  $form['title'].'</div>';
}
echo '	<div class="panel-body"> '; 

$this->load->view('includes/formError'); 

$aFields = renderCrFormFields($form);

echo implode(' ', $aFields);

if (!isset($form['buttons'])) {
	$form['buttons'] = array();
	$form['buttons'][] = '<button type="button" class="btn btn-default" onclick="$.goToUrlList();"><i class="fa fa-arrow-left"></i> '.$CI->lang->line('Back').' </button> ';
	if (isset($form['urlDelete'])) {
		$form['buttons'][] = '<button type="button" class="btn btn-danger"><i class="fa fa-trash-o"></i> '.$CI->lang->line('Delete').' </button>';
	}
	$form['buttons'][] = '<button type="submit" class="btn btn-primary" disabled="disabled"><i class="fa fa-save"></i> '.$CI->lang->line('Save').' </button> ';	
}


echo ' </div>';

if (!empty($form['buttons'])) {
	echo 	'<div class="formButtons form-actions panel-footer" > ';
	foreach ($form['buttons'] as $button) {
		echo $button.' ';
	}
	echo '</div>';
}

echo form_close(); 

?>
<script type="text/javascript">
$(document).ready(function() {
	$('#<?php echo element('frmId', $form, 'frmId'); ?>').crForm(<?php echo json_encode($form); ?>);
});
</script>
