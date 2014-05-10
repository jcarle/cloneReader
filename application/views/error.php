<div class="well error404 ">
	<i class="fa fa-warning fa-4x"></i>
	<span><?php echo $message; ?></span>
</div>

<?php
if (isset($status_code)) {
	$this->output->set_status_header($status_code);
}