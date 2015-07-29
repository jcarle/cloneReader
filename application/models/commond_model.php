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

	/*
	 * Agrega uno o varios order_by a una query.
	 *
	 * @param   $orders    un array con el formato:
	 * 						array(
	 * 							array(
	 * 								'orderBy'  = 'serviceId',
	 * 								'orderDir' = 'asc',
	 * 							)
	 * 						);
	 * @param   $allowFields  un array con los fields permitidos para ordenar
	 * @param   $defaultOrderDir
	 *
	 * */
	function appendOrderByInQuery($orders, array $allowFields = array(), $defaultOrderDir = 'asc') {
		if (empty($orders)) {
			if (!empty($allowFields)) {
				$orders[] = array('orderBy' => $allowFields[0], 'orderDir' => $defaultOrderDir);
			}
		}

		for ($i=0; $i<count($orders); $i++) {
			if (!in_array($orders[$i]['orderBy'], $allowFields)) {
				$orders[$i]['orderBy'] = $allowFields[0];
			}
			$this->db->order_by($orders[$i]['orderBy'], $orders[$i]['orderDir'] == 'desc' ? 'desc' : 'asc');
		}
	}

	/**
	 * Apendea los limit en una queries
	 */
	function appendLimitInQuery($pageCurrent = 1, $pageSize = null) {
		if ($pageSize == null) {
			$pageSize = config_item('pageSize');
		}

		$this->db->limit($pageSize, ($pageCurrent * $pageSize) - $pageSize);
	}

	/**
	 * Chequea en la tabla de cada entity si cambio el sef para hacer una redirección 301
	 * @return $entitySef o NULL si no existe
	 */
	function hasNewEntitySef($entitySef, $entityTypeId) {
		$aTmp = explode('-',  $entitySef);
		if (count($aTmp) < 2) {
			return null;
		}
		if ($entityTypeId != $aTmp[count($aTmp)-2]) {
			return null;
		}
		$entityId = $aTmp[count($aTmp)-1];

		if (!is_numeric($entityTypeId) || !is_numeric($entityId)) {
			return null;
		}

		$entityConfig = getEntityConfig($entityTypeId);
		if (empty($entityConfig)) {
			return null;
		}

		$query = $this->db
			->select($entityConfig['fieldSef'], false)
			->where('statusId', config_item('statusApproved'))
			->where($entityConfig['fieldId'], $entityId)
			->get($entityConfig['tableName'])->row_array();
		//pr($this->db->last_query()); die;
		if (!empty($query)) {
			return $query[$entityConfig['fieldSef']];
		}
		return null;
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

		$query = " INSERT INTO entities_properties
			(entityTypeId, entityId, entitySef)
			VALUES
			(".$entityTypeId.", ".$entityId.", ".$this->db->escape($entitySef).")
			ON DUPLICATE KEY UPDATE entitySef = ".$this->db->escape($entitySef)." ";
		$this->db->query($query);

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
	 * Guarda todos los sefs de una entidad
	 * @param  $entityTypeId
	 * @return (bool)
	 *
	 * */
	function saveSefByEntityTypeId($entityTypeId) {
		if ($entityTypeId == config_item('entityTypeCountry')) {
			$this->load->model('Countries_Model');
			return $this->Countries_Model->saveCountrySef();
		}

		$config = getEntityConfig($entityTypeId);
		if ($config == null) {
			return false;
		}

		if (!isset($config['fieldSef'])) {
			return false;
		}

		$query = $this->db
			->select( 'COUNT(1) AS total ', false)
			->get($config['tableName'])->row_array();
		$pageSize  = 5000;
		$foundRows = $query['total'];
		$pageCount = ceil($foundRows / $pageSize);
		for ($pageCurrent = 1; $pageCurrent<=$pageCount; $pageCurrent++) {
			$this->saveSefByEntityTypeIdPage($entityTypeId, $pageCurrent, $pageSize);
		}
		return true;
	}

	function saveSefByEntityTypeIdPage($entityTypeId, $pageCurrent, $pageSize) {
		$config = getEntityConfig($entityTypeId);

		$query = $this->db
			->limit($pageSize, ($pageCurrent * $pageSize) - $pageSize)
			->get($config['tableName']);

		$this->db->trans_start();
		foreach ($query->result_array() as $data) {
			$this->saveEntitySef($entityTypeId, $data[$config['fieldId']], $data[$config['fieldName']]);
		}

		$this->db->trans_complete();
		$query->free_result();
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
			->get('entities_properties')->result_array();
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
			$fieldId          = getEntityConfig($filter['entityTypeId'], 'fieldId');
			$result[$fieldId] = $filter['entityId'];
		}
		return $result;
	}


	function searchEntityFulltext($search, $searchKey = '', $pageCurrent, $pageSize, $onlyApproved = true) {
		$result = array('data' => array(), 'foundRows' => 0);
		if (trim($search) == '') {
			return $result;
		}

		$aSearchKey = array($searchKey);
		if ($onlyApproved == true) {
			 $aSearchKey[] = 'statusApproved';
		}
		$aSearch   = cleanSearchString($search, $aSearchKey);
		if (empty($aSearch)) {
			return array();
		}

		$match     = 'MATCH (entityFullSearch) AGAINST (\''.implode(' ', $aSearch).'\' IN BOOLEAN MODE)';
		$this->db
			->select('SQL_CALC_FOUND_ROWS entityId, entityName, '.$match.' AS score, MATCH (entityNameSearch) AGAINST (\''.implode(' ', cleanSearchString($search, $aSearchKey, false, false)).'\' IN BOOLEAN MODE) AS scoreName ', false)
			->from('entities_search')
			->where($match, NULL, FALSE)
			->order_by('scoreName DESC, score DESC');
		$this->appendLimitInQuery($pageCurrent, $pageSize);

		$query = $this->db->get()->result_array();
		//pr($this->db->last_query()); die;

		return array('data' => $query, 'foundRows' => $this->getFoundRows());
	}

	function searchEntityName($search, $searchKey = '', $fieldName = null, $contactEntityTypeId = false, $onlyApproved = true, $excludeIds = null) {
		if (trim($search) == '') {
			return array();
		}

		if ($fieldName == null) {
			$fieldName = 'entityName';
		}

		$aSearchKey = explode(' ', $searchKey);
		if ($onlyApproved == true) {
			 $aSearchKey[] = 'statusApproved';
		}
		$aSearch   = cleanSearchString($search, $aSearchKey, true, true);
		if (empty($aSearch)) {
			return array();
		}

		$fieldId   = ($contactEntityTypeId == true ? " CONCAT(entityTypeId, '-', entityId) " : " entityId ");
		$match     = "MATCH (entityNameSearch) AGAINST ('".implode(' ', $aSearch)."' IN BOOLEAN MODE) ";
		$this->db
			->select($fieldId.' AS id, '.$fieldName.' AS text, '.$match.' AS score ', false)
			->from('entities_search')
			->where($match, NULL, FALSE)
//			->order_by('entityTypeId')
			->order_by('entityTree')
			->limit(config_item('autocompleteSize'));

		if ($excludeIds != null) {
			$this->db->where_not_in('entityId', explode(',', $excludeIds));
		}
		$query = $this->db->get()->result_array();
		// pr($this->db->last_query());  die;
		return $query;
	}

	function getEntitySearch($entityTypeId, $entityId, $fieldName = 'entityName', $contactEntityTypeId = false) {
		$fieldId   = ($contactEntityTypeId == true ? ' CONCAT(entityTypeId, \'-\', entityId) ' : ' entityId ');

		$query = $this->db
			->select($fieldId.' AS id, '.$fieldName.' AS text ', false)
			->where('entityTypeId', $entityTypeId)
			->where('entityId', $entityId)
			->get('entities_search')->row_array();
		//pr($this->db->last_query());  die;
		return $query;
	}

	function deleteEntitySearch($entityTypeId, $aEntityId = null) {
		$affectedRows = 1;

		if ($aEntityId != null && is_array($aEntityId) == false) {
			$aEntityId = array($aEntityId);
		}

		while ($affectedRows > 0) {
			$query = ' DELETE QUICK FROM entities_search
				WHERE entityTypeId = '.(int)$entityTypeId.'
				'.($aEntityId != null ? ' AND entityId IN ('.implode(', ', $aEntityId).') ' : '').'
				LIMIT 10000 ';
			$this->db->query($query);
			//vd($this->db->last_query()); die;
			$affectedRows = $this->db->affected_rows();
		}
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
			$values['countryId'] = $entityId;
			return $values;
		}

		return $values;
	}

	/**
	 *
	 * Apendea al array filters el id del tipo de zona que corresponda (countryId, stateId, cityId)
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
	* Busca en el array $data las properties countryId, stateId y cityId el item de menos profundidad y devuelve la zona con el path completo
	*
	*/
	function getZoneToTypeahead($data, $fieldName = 'entityReverseTree'){
		$entityTypeId = null;
		$entityId     = null;
		if ($data['cityId'] != null) {
			$entityTypeId = config_item('entityTypeCity');
			$entityId     = $data['cityId'];
		}
		else if ($data['stateId'] != null) {
			$entityTypeId = config_item('entityTypeState');
			$entityId     = $data['stateId'];
		}
		else if ($data['countryId'] != null) {
			$entityTypeId = config_item('entityTypeCountry');
			$entityId     = $data['countryId'];
		}

		return $this->getEntitySearch($entityTypeId, $entityId, $fieldName, true);
	}

	/**
	 *
	 * @param     (string) $id  un string con el formato: [entityTypeId]-[entityId]
	 * @param     (string) $fieldName
	 * @return    (array)  devuelve un array con el formato:
	 * 		array( 'id' => 3-1822, 'text' => 'country' )
	 * */
	function getEntityToTypeahead($id, $fieldName = 'entityName') {
		if (empty($id)) {
			return array();
		}

		$aTmp = explode('-', $id);
		return $this->Commond_Model->getEntitySearch($aTmp[0], $aTmp[1], $fieldName, true);
	}

	/**
	 * @param     (int)    $entityTypeId
	 * @param     (int)    $entityId
	 * @param     (string) $fieldName
	 * @return    (string) devuelve el nombre de la entity
	 * */
	function getEntityName($entityTypeId, $entityId, $fieldName = 'entityName') {
		$entity = $this->getEntitySearch($entityTypeId, $entityId, $fieldName);
		if (!empty($entity)) {
			return $entity['text'];
		}
		return '';
	}

	function getProcessLastUpdate($processName) {
		$query = $this->db
			->where('processName', $processName)
			->get('process')->row_array();
		//pr($this->db->last_query()); die;
		return $query['lastUpdate'];
	}

	function updateProcessDate($processName, $lastUpdate = null) {
		$query = " UPDATE process set
			lastUpdate = ".($lastUpdate == null ? " NOW() " : "'".$lastUpdate."'")."
			WHERE processName = '".$processName."' ";
		$this->db->query($query);
		//pr($this->db->last_query()); die;
	}

	/**
	 * Obtiene el detalle de las zonas
	 * @param (array) $data debe tener alguno de los items: [countryId, stateId, cityId]
	 * @return (array) devuelve un array con el detalle de las zonas
	 * */
	function selectZoneDetail($data) {
		if (element('countryId', $data) == null && element('stateId', $data) == null && element('cityId', $data) == null) {
			return array();
		}
		if (element('countryId', $data) == null) {
			return array();
		}

		$aFields = array('countries.countryId', 'countryName');
		$this->db->where('countries.countryId', element('countryId', $data));

		if (element('stateId', $data) != null) {
			$aFields = array_merge($aFields, array('states.stateId', 'stateName', 'stateSef'));
			$this->db
				->join('states', 'states.countryId = countries.countryId', 'inner')
				->where('states.stateId', element('stateId', $data));
		}
		if (element('cityId', $data) != null) {
			$aFields = array_merge($aFields, array('cities.cityId', 'cityName', 'citySef'));
			$this->db
				->join('cities', 'cities.stateId = states.stateId', 'inner')
				->where('cities.cityId', element('cityId', $data));
		}

		$this->db->select(implode(', ', $aFields));

		$data = $this->db->get('countries')->row_array();
		//pr($this->db->last_query()); die;
		return $data;
	}

	/*
	public function closeDB() {
		$this->db->close();
	}*/
}
