<?php 
$CI	= &get_instance();

$aFields = renderCrFormFields($form);
?>

<div class="btn-group">
	<div class="dropdown">
		<button type="button" class="btn btn-default dropdown-toggle dropdown-toggle" data-toggle="dropdown">
			<i class="fa fa-filter" ></i>
		</button>	
		<div class="crFilterList  panel panel-default fade in crForm form-horizontal dropdown-menu">
			<div class="panel-heading"> <?php echo $CI->lang->line('Filter'); ?> </div>
			<div class="panel-body">	
				<?php echo implode(' ', $aFields); ?>
			</div>
			<div class="modal-footer form-actions">
				<button type="submit" class="btn btn-default"> <i class="fa fa-search" ></i> <?php echo $CI->lang->line('Search'); ?></button>
			</div>
		</div>
	</div>
</div>
