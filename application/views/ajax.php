<?php
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
