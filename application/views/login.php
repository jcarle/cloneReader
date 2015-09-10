<div class="row">
<?php
if (!isset($isPopUp)) {
	$isPopUp = false;
}

if ($isPopUp == true) {
	echo ' <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12">
		<div class="alert alert-warning" role="alert">
			<i class="fa fa-sign-in"></i> '.$this->lang->line('Please log in to continue').'
		</div>
	</div>';
}
?>

	<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
<?php
$this->load->view('includes/crForm');
?>
	</div>
	<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
		<ul class="list-group">
			<li class="list-group-item">
				<a href="<?php echo base_url(''); ?>" class="btn btn-success" >
					<i class="fa fa-rss"></i>
					<?php echo $this->lang->line('Demo'); ?>
				</a>
			</li>
			<li class="list-group-item">
				<a title="<?php echo $this->lang->line('Create account'); ?>" href="<?php echo base_url('register'); ?>" class="btn btn-info" >
					<i class="fa fa-user"></i>
					<?php echo $this->lang->line('Create account'); ?>
				</a>
			</li>
			<li class="list-group-item">
				<a title="<?php echo $this->lang->line('Facebook Login'); ?>" href="<?php  echo base_url('login/facebook');?>" class="btn btn-facebook" data-skip-app-link="true" >
					<i class="fa fa-facebook"></i>
					<?php echo $this->lang->line('Facebook Login'); ?>
				</a>
			</li>
			<li class="list-group-item">
				<a title="<?php echo $this->lang->line('Google Login'); ?>" href="<?php  echo base_url('login/google');?>" class="btn btn-google-plus" data-skip-app-link="true">
					<i class="fa fa-google-plus"></i>
					<?php echo $this->lang->line('Google Login'); ?>
				</a>
			</li>
		</ul>
	</div>
</div>
