<?php
class Commond_Model extends CI_Model {
		
	function getFoundRows(){
		$row = $this->db->query('SELECT FOUND_ROWS() AS foundRows')->row();
		return $row->foundRows;
	}

	function getCurrentDateTime() {
		$row = $this->db->query('SELECT NOW() AS datetime')->row();
		return $row->datetime;
	}
	
	/**
	 * Guarda el sef de una entidad
	 * Los parametros $entitySef y $entityName son opcionales; pero deben incluirse alguno de los dos
	 * @return $entitySef
	 * */
	function saveEntitySef($entityTypeId, $entityId, $entityName = null, $entitySef = null) {
		if ($entitySef == null) {
			$entitySef = url_title($entityName.'-'.$entityTypeId.'-'.$entityId, 'dash', true);
		}
		if (trim($entitySef) == '' || $entitySef == null ){
			return false;
		}
		
		$values = array(
			'entityTypeId'  => $entityTypeId, 
			'entityId'      => $entityId,
			'entitySef'     => $entitySef,
		);
		
		$this->db->replace('entities_sef', $values);
		
		// Si exite un $entityConfig, actualizo el sef en la tabla principal; ya que puede convenir duplicar la info algunas veces para evitar el join 
		$entityConfig = getEntityConfig($entityTypeId);
		if ($entityConfig != null) {
			$this->db
				->where($entityConfig['fieldId'], $entityId)
				->update($entityConfig['tableName'], array($entityConfig['fieldSef'] => $entitySef));
		} 
		
		return $entitySef;
	}

	
	/**
	 * 
	 * @return array  con el formato:
	 * 		array(
	 * 			array(
	 * 				'entityTypeId' => 8,
	 * 				'entityId'     => 1838, 
	 * 				'entitySef'    => 'blabla-8-1838'
	 * 			)
	 * 		)
	 */
	function getEntityFiltersSef() {
		$aUriSegment  = $this->uri->segment_array();
		$filters      = array();
		
		$query = $this->db
			->select(' entityTypeId, entityId, entitySef ', false)
			->where_in('entitySef', $aUriSegment)
			->get('entities_sef')->result_array();
		//pr($this->db->last_query()); die; 
		foreach ($query as $data) {
			$filters[$data['entityTypeId']] = $data;
		}

		return $filters;
	}
	
	/**
	 * @return array  con el formato:
	 * 		array( 'stateId' => 1822, 'categoryId' => 33 ) 
	 */
	function getEntityFiltersId() {
		$filters = $this->getEntityFiltersSef();
		$result  = array();
		foreach ($filters as $filter) {
			$fieldId = getEntityConfig($filter['entityTypeId'], 'fieldId'); 
			$result[$fieldId] = $filter['entityId'];
		}
		return $result;	
	}

	
	function searchZoneResume($filter, $reverse){
		if (trim($filter) == '') {
			return array();
		}
		
		$filter    = '+'.str_replace(' ', ' +', $this->db->escape_like_str($filter)).'*';
		$fieldTree = ($reverse == true ? 'zonaReverseTree' : 'zonaTree');
		$match     = 'MATCH ('.$fieldTree.') AGAINST (\''.$filter.'\' IN BOOLEAN MODE)';
		
		$query = $this->db
			->select('DISTINCT CONCAT(entityTypeId, \'-\', entityId) AS id, '.$fieldTree.' AS text, '.$match.' AS rating ', false)
			->where($match, NULL, FALSE)
			->order_by('rating')
			->get('zones_resume', config_item('autocompleteSize'))->result_array();
		//pr($this->db->last_query());  die;
		return $query;
	}
	
	function getZonaTree($entityTypeId, $entityId, $reverse = true) {
		$fieldTree = ($reverse == true ? 'zonaReverseTree' : 'zonaTree');
		
		$query = $this->db
			->select('DISTINCT CONCAT(entityTypeId, \'-\', entityId) AS id, '.$fieldTree.' AS text ', false)
			->where('entityTypeId', $entityTypeId)
			->where('entityId', $entityId)
			->get('zones_resume')->row_array();
		//pr($this->db->last_query());  die;
		return $query;
	}
	
	/**
	 * Apendea los indices countryId, stateId, y cityId al array $values
	 * Se utiliza para guardar los datos devueltor por el autocomplete de zonas
	 * */
	function appendZonesToSave($values, $entityTypeId, $entityId) {
		$values['countryId'] = null;
		$values['stateId']   = null;
		$values['cityId']    = null;
		
		if ($entityTypeId == config_item('entityTypeCity')) {
			$query = $this->db
				->select('* ', false)
				->where('cityId', $entityId)
				->get('cities')->row_array();
			//pr($this->db->last_query());  die;
			$values['countryId'] = $query['countryId'];
			$values['stateId']   = $query['stateId'];
			$values['cityId']    = $query['cityId'];
			return $values;
		}
		

		if ($entityTypeId == config_item('entityTypeState')) {
			$query = $this->db
				->select('* ', false)
				->where('stateId', $entityId)
				->get('states')->row_array();
			//pr($this->db->last_query());  die;
			$values['countryId'] = $query['countryId'];
			$values['stateId']   = $query['stateId'];
			return $values;
		}

		if ($entityTypeId == config_item('entityTypeCountry')) {
			$query = $this->db
				->select('* ', false)
				->where('countryId', $entityId)
				->get('countries')->row_array();
			//pr($this->db->last_query());  die;
			$values['countryId'] = $query['countryId'];
			return $values;
		}
		
		return $values;
	}
	
	/**
	 * 
	 * Apendea al array filters el id del typo de zona que corresponda (countryId, stateId, cityId)
	 * Se utiliza en los listados 
	 * 
	 * @param array $filters
	 * @param       $zoneId  un string con el formato: [entityTypeId]-[entityId]
	 * */
	function appendZoneToFilters(array $filters, $zoneId) {
		if (empty($zoneId)) {
			return $filters;
		}
		$aTmp = explode('-', $zoneId);
		
		switch ($aTmp[0]) {
			case config_item('entityTypeCountry'):
				$filters['countryId'] = $aTmp[1];
				break;
			case config_item('entityTypeState'):
				$filters['stateId'] = $aTmp[1];
				break;
			case config_item('entityTypeCity'):
				$filters['cityId'] = $aTmp[1];
				break;
		}
		
		return $filters;
	}
	
	/**
	 * 
	 * 
	 * @param     (string) $zoneId  un string con el formato: [entityTypeId]-[entityId]
	 * @return    (array)  devuelve un array con el formato:  
	 * 		array( 'stateId' => 1822, 'categoryId' => 33 ) 	
	 * */
	function getZoneToTypeahead($zoneId) {
		if (empty($zoneId)) {
			return array();
		}
		
		$aTmp = explode('-', $zoneId);
		return $this->Commond_Model->getZonaTree($aTmp[0], $aTmp[1]);
	}


	/**
	 * Obtiene las zonas de una entidad
	 * */
	function selectZoneDetail($query) {

		$this->db
			->select('countries.countryId, countryName, states.stateId, stateName, stateSef, cities.cityId, cityName, citySef ')
			->join('states', 'states.countryId = countries.countryId', 'left')
			->join('cities', 'cities.stateId = states.stateId', 'left');
		
		if (element('countryId', $query) != null) {
			$this->db->where('countries.countryId', element('countryId', $query));
		}
		if (element('stateId', $query) != null) {
			$this->db->where('states.stateId', element('stateId', $query));
		}
		if (element('cityId', $query) != null) {
			$this->db->where('cities.cityId', element('cityId', $query));
		}
			
		$query = $this->db->get('countries')->row_array();
		return $query;
		//pr($this->db->last_query()); die; 
	}

	/*
	public function closeDB() {
		$this->db->close();
	}*/
}
