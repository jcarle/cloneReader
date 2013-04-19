<!-- TODO: tratar de poner los includes en el header de la page! -->
<script type="text/javascript" src="<?php echo base_url();?>js/tmpl.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/load-image.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>js/bootstrap-image-gallery.min.js"></script>

<link rel="stylesheet" href="<?php echo base_url();?>css/bootstrap.min.css" />
<link rel="stylesheet" href="<?php echo base_url();?>css/bootstrap-image-gallery.min.css" />

<div id="modal-gallery" class="modal modal-gallery hide fade" >
	<div class="modal-header">
		<a class="close" data-dismiss="modal">&times;</a>
		<h3 class="modal-title"></h3>
	</div>
	<div class="modal-body">
		<div class="modal-image"></div>
	</div>
	<div class="modal-footer">
		<a class="btn modal-download" target="_blank"> <i class="icon-download"></i> <span>Download</span> </a>
		<a class="btn btn-success modal-play modal-slideshow" data-slideshow="5000"> <i class="icon-play icon-white"></i> <span>Slideshow</span> </a>
		<a class="btn btn-info modal-prev"> <i class="icon-arrow-left icon-white"></i> <span>Previous</span> </a>
		<a class="btn btn-primary modal-next"> <span>Next</span> <i class="icon-arrow-right icon-white"></i> </a>
	</div>
</div>

<script id="template-upload" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
	<tr class="template-upload fade">
	<td class="preview"><span class="fade"></span></td>
	<td class="name"><span>{%=file.name%}</span></td>
	<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
	{% if (file.error) { %}
	<td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
	{% } else if (o.files.valid && !i) { %}
	<td>
	<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
	</td>
	<td class="start">{% if (!o.options.autoUpload) { %}
	<button class="btn btn-primary">
	<i class="icon-upload icon-white"></i>
	<span>{%=locale.fileupload.start%}</span>
	</button>
	{% } %}</td>
	{% } else { %}
	<td colspan="2"></td>
	{% } %}
	<td class="cancel">{% if (!i) { %}
	<button class="btn btn-warning">
	<i class="icon-ban-circle icon-white"></i>
	<span>{%=locale.fileupload.cancel%}</span>
	</button>
	{% } %}</td>
	</tr>
	{% } %}
</script>

<script id="template-download" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
	<tr class="template-download fade">
	{% if (file.error) { %}
	<td></td>
	<td class="name"><span>{%=file.name%}</span></td>
	<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
	<td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
	{% } else { %}
	<td class="preview">{% if (file.thumbnail_url) { %}
	<a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
	{% } %}</td>
	<td class="name">
	{%=file.name%}
	</td>
	<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
	<td colspan="2"></td>
	{% } %}
	<td class="delete">
	<button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
	<i class="icon-trash icon-white"></i>
	<span>{%=locale.fileupload.destroy%}</span>
	</button>
	<input type="checkbox" name="delete" value="1">
	</td>
	</tr>
	{% } %}
</script>

<script>
$(document).ready(function() {
	$('.gallery img').imgCenter( {
		show: false,
		createFrame: true,
		complete:
			function(img) {
				$(img).fadeIn('slow', function() {
					$(img).parent().css('background', 'none');
				});
			}
	});	
});	
</script>
