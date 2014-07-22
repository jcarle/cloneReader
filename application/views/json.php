<?php
if (isset($view)) {
	$result = $this->load->view($view, '', true);
}

$output = $result;
			
if (isset($code)) { 
	$output = array(
				'code'		=> $code, 
				'result' 	=> $result
			);
}

$this->output
	->set_content_type('application/json')
	->set_output(json_encode($output));

if (isset($status_code)) {
	$this->output->set_status_header($status_code);
}
