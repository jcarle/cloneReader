<?php
function getRulesMessages() { // TODO: mover esto de aca!
	return array(
		'required' 		=> 'Por favor, completa el campo "%s"',
		'valid_email' 	=> 'Por favor ingrese un email válido',
		'numeric' 		=> 'Por favor ingrese un número válido en el campo "%s"',
		'_login' 		=> 'El email o la contraseña ingresados son incorrectos'
				
	);
}



function getFieldMoney(array $price, array $currency, array $exchange, array $total) {
	$CI = &get_instance();
	$CI->load->model('Coins_Model');
	
	$subscribe 	= array();
	$aFieldName = array( $price['name'], $currency['name'], $exchange['name'], );
	
	foreach ($aFieldName as $fieldName) {
		$subscribe[] = array(
			'field' 		=> $fieldName,
			'event'			=> 'change', 
			'callback'		=> 'calculatePrice',
			'arguments'		=> array(
				'this.getFieldByName(\''.$price['name'].'\')',
				'this.getFieldByName(\''.$currency['name'].'\')',
				'this.getFieldByName(\''.$exchange['name'].'\')',
				'this.getFieldByName(\''.$total['name'].'\')'
			)
		);
	}
	
	$subscribe[0]['runOnInit'] = true;
			
	return array(
		$price['name']	=> array(
			'type'	 		=> 'text',
			'name'			=> $price['name'],
			'label'			=> $price['label'], 
			'value'			=> element('value', $price, 0),
			'placeholder'	=> '0,00',
		),
		$currency['name']	=> array(
			'type'			=> 'dropdown',
			'name' 			=> $currency['name'],
			'label'			=> $currency['label'], 
			'value'			=> $currency['value'],
			'source'		=> array_to_select($CI->Coins_Model->select(true), 'currencyId', 'currencyName'),
		),
		$exchange['name']	=> array(
			'type'	 		=> 'text',
			'name'			=> $exchange['name'],
			'label'			=> $exchange['label'], 
			'value'			=> element('value', $exchange, 0),
			'placeholder'	=> '0,00',
		),
		$total['name']	=> array(
			'type'	 		=> 'text',
			'name'			=> $total['name'],
			'label'			=> $total['label'], 
			'value'			=> null,
			'disabled'		=> true,
			'subscribe'		=> $subscribe
		),		
	);
}

function getValidationFieldMoney(array $price, array $exchange) {
	return array(
		array(
			'field' => $price['name'],
			'label' => $price['label'],
			'rules' => 'required|numeric'
		),
		array(
			'field' => $exchange['name'],
			'label' => $exchange['label'],
			'rules' => 'required|numeric'
		)
	);
}

function subscribeForSumValues($fieldName, array $aFieldName) {
	foreach ($aFieldName as $fieldName) {
		$subscribe[] = array(
			'field' 		=> $fieldName,
			'event'			=> 'change', 
			'callback'		=> 'sumValues',
			'arguments'		=> array( json_encode($aFieldName) )
		);
	}
	return $subscribe;
}

function hasGallery($form) {
	foreach ($form['fields'] as $name => $field) {
		if ($field['type'] == 'gallery') {
			return true;
		}
	}
	return false;
}