<?php 
$this->form_validation->set_error_delimiters('<li>', '</li>');

if(strlen(validation_errors())) { 
	echo '
	<div class="errors">
		<ul>
			'.validation_errors().'
		</ul>
	</div>';
}