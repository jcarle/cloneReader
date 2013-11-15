<?php
$this->load->view('includes/jForm');
?>

<button href='#' onclick='facebookLogin()' class="btn btn-facebook" >
	<i class="icon-facebook"></i>
	<?php echo $this->lang->line('Facebook Login'); ?>
</button>

<button href='#' class="btn btn-google" >
	<i class="icon-google-plus"></i>
	<?php echo $this->lang->line('Google Login'); ?>
</button>
