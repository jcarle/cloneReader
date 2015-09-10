<?php
// TODO: borrar este archivo, creo que ya no se utiliza
$this->form_validation->set_error_delimiters('<li>', '</li>');

if(strlen(validation_errors())) {
	echo '
	<div class="alert alert-danger">
		<ul>
			'.validation_errors().'
		</ul>
	</div>';
}
