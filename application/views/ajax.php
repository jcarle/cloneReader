<?php
$output = $result;
			
if (isset($code)) { 
	$output = array(
	   	'code'		=> $code, 
	   	'result' 	=> $result
	);
}


/* FIXME: revisar esta parte
 * usando $this->output->set_content_type('application/json') anda lento
 * mejora enviando los ajax en text/plain y luego jquery lo parsea con $.ajaxSetup({dataType: "json"});
 * */
			 
$this->output
    ->set_content_type('text/plain')
    ->set_output(json_encode($output));
