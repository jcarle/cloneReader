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
		
	/*
	public function closeDB() {
		$this->db->close();
	}*/
}
