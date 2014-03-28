<div class="row">
	<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
	
<?php
$form  = appendMessagesToCrForm($form); // TODO: centralizar !

$this->load->view('includes/crForm');
?>
	</div>
	<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
		<ul class="list-group">
			<li class="list-group-item"> 
				<a href="<?php echo base_url(''); ?>" class="btn btn-success" >
					<i class="icon-rss"></i>
					<?php echo $this->lang->line('Demo'); ?>
				</a>		
			</li>	
			<li class="list-group-item">
				<a href="<?php echo base_url('register'); ?>" class="btn btn-info" >
					<i class="icon-user"></i>
					<?php echo $this->lang->line('Create account'); ?>
				</a>
			</li>	
			<li class="list-group-item">
				<a href="<?php  echo base_url('login/facebook');?>" class="btn btn-facebook" data-skip-app-link="true" >
					<i class="icon-facebook"></i>
					<?php echo $this->lang->line('Facebook Login'); ?>
				</a>
			</li>
			<li class="list-group-item">
				<a href="<?php  echo base_url('login/google');?>" class="btn btn-google" data-skip-app-link="true">
					<i class="icon-google-plus"></i>
					<?php echo $this->lang->line('Google Login'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('#<?php echo element('frmId', $form, 'frmId'); ?>').crForm(<?php echo json_encode($form); ?>);
});
</script>

