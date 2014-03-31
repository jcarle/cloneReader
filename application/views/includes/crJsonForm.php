<?php 

if (!isset($form['action'])) {
	$form['action'] = base_url().$this->uri->uri_string(); 
}

$form  = appendMessagesToCrForm($form); 

return loadViewAjax(true, array('form' => $form));