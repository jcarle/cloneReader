<!-- TODO: tratar de poner los includes en el header de la page! -->
<?php $this->load->view('includes/gallery'); ?>

<script type="text/javascript" src="<?php echo base_url();?>js/jquery.fileupload.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/locale.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/jquery.fileupload-ui.js"></script>

<link rel="stylesheet" href="<?php echo base_url();?>css/jquery.fileupload-ui.css">

<form id="fileupload" action="<?php echo base_url() . 'files/save'; ?>" method="POST" enctype="multipart/form-data" style="display:none">
	
	<?php echo form_hidden('entityName', $fileupload['entityName']); ?>
	<?php echo form_hidden('entityId', $fileupload['entityId']); ?>
	
	<div class="row fileupload-buttonbar">
		<div class="span7">
			<span class="btn btn-success fileinput-button"> 
				<i class="icon-plus icon-white"></i> 
				<span>Agregar fotos...</span>
				<input type="file" name="userfile" multiple>
			</span>
			<!--<button type="submit" class="btn btn-primary start">
				<i class="icon-upload icon-white"></i>
				<span>Iniciar</span>
			</button>
			<button type="reset" class="btn btn-warning cancel">
				<i class="icon-ban-circle icon-white"></i>
				<span>Cancelar</span>
			</button>-->
			<button type="button" class="btn btn-danger delete">
				<i class="icon-trash icon-white"></i>
				<span>Borrar</span>
			</button>
			<input type="checkbox" class="toggle">
		</div>
		<div class="span5 fileupload-progress fade">
			<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
				<div class="bar" style="width:0%;"></div>
			</div>
			<div class="progress-extended">
				&nbsp;
			</div>
		</div>
	</div>
	<div class="fileupload-loading"></div>
	<br/>
	<table role="presentation" class="table table-striped">
		<tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody>
	</table>
</form>



