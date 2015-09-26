<?php
if (!isset($fileupload)) {
	$fileupload = array();
}
?>

<div class="modal" style="display:none" id="fileupload">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="#"  method="POST" enctype="multipart/form-data">
				<?php echo form_hidden('entityTypeId', element('entityTypeId', $fileupload)); ?>
				<?php echo form_hidden('entityId', element('entityId', $fileupload)); ?>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
					<h4> <i class="fa fa-edit"></i> <?php echo lang('Edit pictures'); ?> </h4>
				</div>

				<div class="modal-body" >
					<div class="row fileupload-buttonbar">
						<div class="span6">
							<!-- The fileinput-button span is used to style the file input field as button -->
							<span class="btn btn-success fileinput-button">
								<i class="fa fa-plus"></i>
								<span> <?php echo lang('Add photos'); ?> </span>
								<input type="file" name="userfile" multiple>
							</span>
							<button type="submit" class="btn btn-primary start hide ">
								<i class="fa fa-upload"></i>
								<span>Start upload</span>
							</button>
							<button type="reset" class="btn btn-warning cancel hide ">
								<i class="fa fa-ban"></i>
								<span>Cancel upload</span>
							</button>
							<button type="button" class="btn btn-danger delete">
								<i class="fa fa-trash-o"></i>
								<span> <?php echo lang('Delete'); ?> </span>
							</button>
							<input type="checkbox" class="toggle">
							<!-- The loading indicator is shown during file processing -->
							<span class="fileupload-loading"></span>
						</div>
						<!-- The global progress information -->
						<div class="span6 fileupload-progress fade">
							<!-- The global progress bar -->
							<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
								<div class="progress-bar progress-bar-success bar bar-success" style="width:0%;"></div>
							</div>
							<!-- The extended global progress information -->
							<div class="progress-extended">&nbsp;</div>
						</div>
					</div>

					<div style="overflow: auto; max-height: 500px;">
						<table role="presentation" class="table table-hover"><tbody class="files"></tbody></table>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>


<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) {  %}
	<tr class="template-upload fade">
		<td>
			<span class="preview"></span>
		</td>
		<td>
<!--			<p class="name">{%=file.name%}</p> -->
			{% if (file.error) { %}
				<div><span class="label label-important">Error</span> {%=file.error%}</div>
			{% } %}
		</td>
		<td>
<!--			<p class="size">{%=o.formatFileSize(file.size)%}</p> -->
			{% if (!o.files.error) { %}
				<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success bar bar-success" style="width:0%;"></div></div>
			{% } %}
		</td>
		<td class="text-right">
			{% if (!o.files.error && !i && !o.options.autoUpload) { %}
				<button class="btn btn-primary start">
					<i class="fa fa-upload"></i>
					<span>Start</span>
				</button>
			{% } %}
			{% if (!i) { %}
				<button class="btn btn-warning cancel">
					<i class="fa fa-ban"></i>
					<span> <?php echo lang('Cancel'); ?></span>
				</button>
			{% } %}
		</td>
	</tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) {  %}
	<tr class="template-download fade">
		<td>
			<span class="preview">
				{% if (file.urlThumbnail) { %}
					<a class="thumbnail" href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery data-skip-app-link="true"><img src="{%=file.urlThumbnail%}"></a>
				{% } %}
			</span>
		</td>
		<td>
			{% if (file.error) { %}
				<div><span class="label label-important">Error</span> {%=file.error%}</div>
			{% } %}
		</td>
		<td> </td>
		<td class="text-right">
			<button class="btn btn-danger delete" data-type="DELETE" data-url="{%=file.urlDelete%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
				<i class="fa fa-trash-o"></i>
				<span> <?php echo lang('Delete'); ?> </span>
			</button>
			<input type="checkbox" name="delete" value="1" class="toggle">
		</td>
	</tr>
{% } %}
</script>
