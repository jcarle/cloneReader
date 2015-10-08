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
			if (!in_array($orders[$i]['orderDir'], array('desc', 'asc'))) {
				$orders[$i]['orderDir'] = $defaultOrderDir;
			}

			$this->db->order_by($orders[$i]['orderBy'], $orders[$i]['orderDir']);
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

	function appendJoinEntitiesProperty($entityTypeId, $orders) {
		// TODO: mejorar esta parte; estoy joineando con entities_properties SOLO si ordenos por visitas,

		$entityConfig = getEntityConfig($entityTypeId);

		if (!empty($orders)) {
			if ($orders[0]['orderBy'] ==  'entityVisits') {
				$this->db
					->select(' entityVisits', false)
					->join('entities_properties', 'entities_properties.entityId = '.$entityConfig['tableName'].'.'.$entityConfig['fieldId'], 'left')
					->where('entities_properties.entityTypeId', $entityTypeId);
				return true;
			}
		}

		$this->db->select(' 0 AS entityVisits', false);
		return false;
	}

	/**
	* Devuelve un array con los userId que pertenecen a los grupos de $aGroupId; ej :array(GROUP_ROOT, GROUP_EDITOR)
	*/
	function selectUsersByGroupsId($aGroupId) {
		$result = array();
		$query = $this->db
			->select('DISTINCT userId', false)
			->from('users_groups')
			->where_in('groupId', $aGroupId)
			->get()->result_array();
		foreach ($query as $data) {
			$result[] = $data['userId'];
		}
		return $result;
	}

	/**
	 * Chequea en la tabla de cada entity si cambio el sef para hacer una redirección 301
	 * @return $entityId o NULL si no existe
	 */
	function hasNewEntitySef($entityTypeId, $entitySef) {
		$entityId = getEnityIdInEntitySef($entitySef, $entityTypeId);

		if (!is_numeric($entityTypeId) || !is_numeric($entityId)) {
			return null;
		}

		$entityConfig = getEntityConfig($entityTypeId);
		if (empty($entityConfig)) {
			return null;
		}

		$query = $this->db
			->select($entityConfig['fieldId'], false)
			->where('statusId', config_item('statusApproved'))
			->where($entityConfig['fieldId'], $entityId)
			->get($entityConfig['tableName'])->row_array();
		//pr($this->db->last_query()); die;
		if (!empty($query)) {
			return $query[$entityConfig['fieldId']];
		}
		return null;
	}

	/**
	 * Guarda el sef de una entidad
	 * @return $entitySef
	 * */
	function saveEntitySef($entityTypeId, $entityId, $entityName = null) {
		$entityConfig = getEntityConfig($entityTypeId);
		if ($entityConfig == null) {
			return false;
		}

		if ($entityName == null) {
			$query = $this->db
				->select($entityConfig['fieldName'], false)
				->where($entityConfig['fieldId'], $entityId)
				->get($entityConfig['tableName'])->row_array();
			$query = getCrFormData($query, $entityId);
			if (!is_array($query)) {
				return false;
			}
			$entityName = $query[$entityConfig['fieldName']];
		}

		$entitySef = url_title($entityName.'-'.$entityTypeId.'-'.$entityId, 'dash', true);

		if (trim($entitySef) == ''){
			return false;
		}

		$query = " INSERT INTO entities_properties
			(entityTypeId, entityId, entitySef)
			VALUES
			(".$entityTypeId.", ".$entityId.", ".$this->db->escape($entitySef).")
			ON DUPLICATE KEY UPDATE entitySef = VALUES(entitySef) ";
		$this->db->query($query);

		// Actualizo el sef en la tabla principal
		$this->db
			->where($entityConfig['fieldId'], $entityId)
			->update($entityConfig['tableName'], array($entityConfig['fieldSef'] => $entitySef));

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
			$this->saveEntityLog($entityTypeId, $data[$config['fieldId']]);
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

		$match = 'MATCH (entityFullSearch) AGAINST (\''.implode(' ', $aSearch).'\' IN BOOLEAN MODE)';
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
			->order_by('entityFullName')
			->limit(config_item('autocompleteSize'));

		if ($excludeIds != null) {
			$this->db->where_not_in('entityId', explode(',', $excludeIds));
		}
		$query = $this->db->get()->result_array();
		// pr($this->db->last_query());  die;
		return $query;
	}

	function getEntitySearch($entityTypeId, $entityId, $fieldName = 'entityName', $contactEntityTypeId = false) {
		if (empty($entityTypeId) || empty($entityId)) {
			return array();
		}

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
	 * @param     (int)    $entityTypeId
	 * @param     (int)    $entityId
	 * @return    (string) devuelve el sef de la entity
	 * */
	function getEntitySef($entityTypeId, $entityId) {
		$query = $this->db
			->select(' entitySef', false)
		 	->from(' entities_properties')
			->where('entityTypeId', $entityTypeId)
			->where('entityId', $entityId)
			->get()->row_array();
		//pr($this->db->last_query()); die;
		if (!empty($query)) {
			return $query['entitySef'];
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

	/**
	* Inicializa la query para parsear las visitas de una entidad
	*/
	function initQueryEntityVisits($excludeUsersRoot = true, $excludeSpiders = true) {
		$aUserId = array();
		if ($excludeUsersRoot == true) {
			$aUserId = $this->selectUsersByGroupsId(array(GROUP_ROOT, GROUP_EDITOR));
		}

		$query = $this->db->select('request_uri ', true)->from('usertracking');

		if (!empty($aUserId)) {
			$this->db->where_not_in('user_identifier', $aUserId);
		}
		if ($excludeSpiders == true) {
			$this->db->where_not_in('client_user_agent', array('XmlSitemapGenerator - http://xmlsitemapgenerator.org', 'Googlebot/2.1 (+http://www.google.com/bot.html)', 'Googlebot/2.1 (+http://www.googlebot.com/bot.html)', 'User-Agent: Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'));
		}
	}

	function saveEntitiesVisits($entityTypeId, $entitySef) {
		$this->clearEntitiesVisits($entityTypeId);

		$this->db->trans_start();

		$this->initQueryEntityVisits();
		$query = $this->db
			->like('request_uri', $entitySef)
			->get()->result_array();
		//pr($this->db->last_query());  die;
		foreach ($query as $data) {
			$entityId = getEnityIdInEntitySef($data['request_uri'], $entityTypeId);
			if ($entityId != null) {
				$this->saveEntityVisits($entityTypeId, $entityId);
			}
		}

		$this->db->trans_complete();
	}

	function saveEntityVisits($entityTypeId, $entityId) {
		$query = " INSERT INTO entities_properties
			(entityTypeId, entityId, entityVisits)
			VALUES
			(".$entityTypeId.", ".$entityId.", 1)
			ON DUPLICATE KEY UPDATE entityVisits = entityVisits + 1 ";
		$this->db->query($query);
	}

	function getEntityVisits($entityTypeId, $entityId) {
		$query = $this->db
			->select(' entityVisits', false)
		 	->from(' entities_properties')
			->where('entityTypeId', $entityTypeId)
			->where('entityId', $entityId)
			->get()->row_array();
		//pr($this->db->last_query()); die;
		if (!empty($query)) {
			return $query['entityVisits'];
		}

		return null;
	}

	function clearEntitiesVisits($entityTypeId) {
		$this->db
			->where('entityTypeId', $entityTypeId)
			->update('entities_properties', array('entityVisits' => 0));
		//pr($this->db->last_query());  die;
	}

	function appendPlaceDetail($query) {
		$this->load->model('Places_Model');

		$place  = $this->Places_Model->get($query['placeId']);
		if (!empty($place)) {
			$query['placeName'] = $place['placeName'];
			$query['placeSef']  = $place['placeSef'];
		}

		return $query;
	}

	/**
	 * Apendea los indices brandId y modelId al array $values
	 * Se utiliza para guardar los datos devueltor por el autocomplete de cars
	 * */
	function appendCarsToSave($values, $entityTypeId, $entityId) {
		$values['brandId'] = null;
		$values['modelId'] = null;

		if ($entityTypeId == config_item('entityTypeModel')) {
			$query = $this->db
				->select('* ', false)
				->where('modelId', $entityId)
				->get('models')->row_array();
			//pr($this->db->last_query());  die;
			$values['brandId'] = $query['brandId'];
			$values['modelId'] = $query['modelId'];
			return $values;
		}

		if ($entityTypeId == config_item('entityTypeBrand')) {
			$values['brandId'] = $entityId;
		}

		return $values;
	}

	function getEntityLogRaw($entityTypeId, $entityId) {
		$entityConfig = getEntityConfig($entityTypeId);
		if ($entityConfig == null) {
			return;
		}
		if (element('hasEntityLog', $entityConfig) != true) {
			return;
		}

		$modelName = ucfirst($entityConfig['entityTypeName']).'_Model';
		$this->load->model($modelName);

		if (method_exists($this->$modelName, 'getEntityLogRaw') == false) {
			return;
		}

		return $this->$modelName->getEntityLogRaw($entityId);
	}

	/**
	* Guarga un item en la tabla entities_logs
	*
	*/
	function saveEntityLog($entityTypeId, $entityId, $isMigrate = false) {
		$entityLogRaw = $this->getEntityLogRaw($entityTypeId, $entityId);
		if (empty($entityLogRaw)) {
			return;
		}
// TODO: borrar el parametro $isMigrate  de deployar
if ($isMigrate == true) {
	$entityLogDate = $entityLogRaw['lastUpdate'];
}
		unset($entityLogRaw['lastUpdate']);
		unset($entityLogRaw['entityUrl']);

		$values = array(
			'entityTypeId'  => $entityTypeId,
			'entityId'      => $entityId,
			'userId'        => $this->session->userdata('userId'),
			'entityLogRaw'  => json_encode($entityLogRaw),

		);
if ($isMigrate == true) {
	$values['entityLogDate'] = $entityLogDate;
}
		$this->db->insert('entities_logs', $values);
	}

	function processDiffEntityLog() {
		$this->db->trans_start();

		$query = $this->db
			->where('entityLogDiff', '')
			->limit(1000) // TODO: harckodeta
			->get('entities_logs');
		//pr($this->db->last_query()); die;
		foreach ($query->result_array() as $data) {
			$entityLogDiff  = array();
			$new            = (array)json_decode($data['entityLogRaw'], true);
			$statusId       = null;

			$oldData = $this->db
				->where('entityTypeId', $data['entityTypeId'])
				->where('entityId', $data['entityId'])
				->where('entityLogDate < ', $data['entityLogDate'])
				->order_by('entityLogDate', 'desc')
				->limit(1)
				->get('entities_logs')->row_array();
			//pr($this->db->last_query()); die;

			if (!empty($oldData)) {
				$old = (array)json_decode($oldData['entityLogRaw'], true);
				$entityLogDiff = array(
					'+' => array_diff_recursive($new, $old),
					'-' => array_diff_recursive($old, $new)
				);

				if (element('statusId', $entityLogDiff['+']) !== false) {
					$statusId = $entityLogDiff['+']['statusId'];
				}
			}
			else {
				$statusId = element('statusId', $new, null);
			}

			$values = array(
				'userFullName'    => getEntityName( config_item('entityTypeUser'), $data['userId']),
				'entityName'      => getEntityName( $data['entityTypeId'], $data['entityId']),
				'entityLogDiff'   => json_encode($entityLogDiff),
				'statusId'        => $statusId,
			);

			$this->db
				->where('entityLogId', $data['entityLogId'])
				->update('entities_logs', $values);
			//pr($this->db->last_query()); die;
		}

		$this->db->trans_complete();
	}

	/*
	 * @param  (array)  $filters es un array con el formato:
	 *      array(
	 *           'search'            => null,
	 *           'statusId'          => null,
	 *           'entityTypeId'      => null,
	 *           'userId'            => null,
	 *           'entityLogDateFrom' => null,
	 *           'entityLogDateTo'   => null,
	 *           'groupEntities'     => null,
	 *     );
	 * */
	function selectEntitiesLogsToList($pageCurrent = null, $pageSize = null, array $filters = array(), array $orders = array()){
		$this->db
			//->select(' SQL_CALC_FOUND_ROWS  CONCAT(entityTypeId, \'-\', entityId) AS id, entityTypeId, entityId, entityLogId, statusId, userFullName, entityName, entityLogDiff, entityLogDate ', false)
			->select(' SQL_CALC_FOUND_ROWS  CONCAT(entityTypeId, \'-\', entityId) AS id, entityTypeId, entityId, entityLogId, statusId, entityName ', false)
			->from('entities_logs');

		if (element('search', $filters) != null) {
			if (is_numeric($filters['search'])) {
				$this->db->where('entityId', $filters['search']);
			}
			else {
				$this->db->or_like(array('entityName' => $filters['search'], 'entityLogDiff' => $filters['search']));
			}
		}
		if (element('statusId', $filters) != null) {
			$this->db->where('statusId', $filters['statusId']);
		}
		if (element('entityTypeId', $filters) != null) {
			$this->db->where('entityTypeId', $filters['entityTypeId']);
		}
		if (element('userId', $filters) != null) {
			$this->db->where('userId', $filters['userId']);
		}
		if (element('entityLogDateFrom', $filters) != null) {
			$this->db->where('entityLogDate >=', date('Y-m-d', strtotime($filters['entityLogDateFrom'])).' 00:00:00');
		}
		if (element('entityLogDateTo', $filters) != null) {
			$this->db->where('entityLogDate <=', date('Y-m-d', strtotime($filters['entityLogDateTo'])).' 24:59:59');
		}

		if (element('groupEntities', $filters) == true) {
			$this->db->select(' \'\' AS entityLogDiff, GROUP_CONCAT(DISTINCT userFullName) AS userFullName, MAX(entityLogDate) AS entityLogDate ', false);
			$this->db->group_by('entityTypeId, entityId');
		}
		else {
			$this->db->select(' entityLogDiff, userFullName, entityLogDate ', false);
		}

		$this->appendOrderByInQuery($orders, array('entityLogDate', 'entityLogId', 'userId'), 'desc');
		$this->appendLimitInQuery($pageCurrent, $pageSize);


		$query = $this->db->get()->result_array();
		//pr($this->db->last_query()); die;
		$result = array('data' => array(), 'foundRows' => $this->getFoundRows());
		foreach ($query as $data) {
			$data['entityTypeName'] = langEntityTypeName($data['entityTypeId'], true);

			if ($data['statusId'] == config_item('statusApproved')) {
				$data['crRowClassName'] = 'success';
			}
			if ($data['statusId'] == config_item('statusRejected')) {
				$data['crRowClassName'] = 'danger';
			}

			$result['data'][] = $data;
		}

		return $result;
	}

	/*
	 * @param $entityTypeId
	 * @param $entityId
	 */
	function getEntityLog($entityTypeId, $entityId){
		$this->db
			->from('entities_logs')
			->where('entityTypeId', $entityTypeId)
			->where('entityId', $entityId)
			->order_by('entityLogDate', 'desc');
		return $this->db->get()->result_array();
	}

	function selectEntitiesTypeToDropdown() {
		// TODO: mejorar esta parte!
		$result = array();
		$query = $this->db->get('entities_type')->result_array();
		foreach ($query as $data) {
			$entityConfig = getEntityConfig($data['entityTypeId']);
			if (element('hasEntityLog', $entityConfig) == true) {
				$result[] = array(
					'id'   => $data['entityTypeId'],
					'text' => lang(ucfirst(element('entityTypeName', $entityConfig))),
				);
			}
		}

		usort($result, function($a, $b) {
			return $a['text'] < $b['text'] ? -1 : 1;
		});

		return $result;
	}

	/*
	public function closeDB() {
		$this->db->close();
	}*/
}
