<?php
class Countries_Model extends CI_Model {
	function select(){
		return $this->db->order_by('countryName')->get('countries')->result_array();
	}

	function search($filter){
		return $this->db
			->select('countryId as id, countryName as text')
			->like('countryName', $filter)
			->get('countries')->row_array();
	}

	function getCountryById($id){
		return $this->db
			->select('countryId as id, countryName as text')
			->where('countryId', $id)
			->get('countries')->row_array();
	}

	function selectToDropdown(){
		return $this->db
			->select('countryId AS id, countryName AS text', true)
			->order_by('countryName')
			->get('countries')->result_array();
	}

	function saveZonesSearch($deleteEntitySearch = false, $onlyUpdates = false) {
		if ($deleteEntitySearch == true) {
			$this->Commond_Model->deleteEntitySearch(config_item('entityTypeCity'));
			$this->Commond_Model->deleteEntitySearch(config_item('entityTypeState'));
			$this->Commond_Model->deleteEntitySearch(config_item('entityTypeCountry'));
		}

		$searchKey  = 'searchZones';
		$lastUpdate = $this->Commond_Model->getProcessLastUpdate('saveZonesSearch');

		// Countries
		$aWhere = array();
		if ($onlyUpdates == true) {
			$aWhere[] = ' countries.lastUpdate > \''.$lastUpdate.'\' ';
		}
		$query = " REPLACE INTO entities_search
			(entityTypeId, entityId, entityNameSearch, entityName, entityFullName, entityReverseFullName)
			SELECT ".config_item('entityTypeCountry').", countryId,
			CONCAT_WS(' ', '$searchKey', countryName),
			countryName, countryName, countryName
			FROM countries
			".(!empty($aWhere) ? ' WHERE '.implode(' AND ', $aWhere) : '')." ";
		$this->db->query($query);
		//pr($this->db->last_query()); die;

		// States
		$aWhere = array();
		if ($onlyUpdates == true) {
			$aWhere[] = ' (countries.lastUpdate > \''.$lastUpdate.'\' OR states.lastUpdate > \''.$lastUpdate.'\' ) ';
		}
		$query = "REPLACE INTO entities_search
			(entityTypeId, entityId, entityNameSearch, entityName, entityFullName, entityReverseFullName)
			SELECT ".config_item('entityTypeState').", stateId,
			CONCAT_WS(' ', '$searchKey', countryName, stateName),
			stateName,
			CONCAT_WS(', ', countryName, stateName),
			CONCAT_WS(', ', stateName, countryName)
			FROM states
			INNER JOIN countries ON  countries.countryId = states.countryId
			".(!empty($aWhere) ? ' WHERE '.implode(' AND ', $aWhere) : '')." ";
		$this->db->query($query);
		//pr($this->db->last_query()); die;

		// Cities
		$aWhere = array();
		if ($onlyUpdates == true) {
			$aWhere[] = ' (countries.lastUpdate > \''.$lastUpdate.'\' OR states.lastUpdate > \''.$lastUpdate.'\' OR cities.lastUpdate > \''.$lastUpdate.'\' ) ';
		}
		$query = "REPLACE INTO entities_search
			(entityTypeId, entityId, entityNameSearch, entityName, entityFullName, entityReverseFullName)
			SELECT ".config_item('entityTypeCity').", cityId,
			CONCAT_WS(' ', '$searchKey', countryName, stateName, cityName),
			cityName,
			CONCAT_WS(', ', countryName, stateName, cityName),
			CONCAT_WS(', ', cityName, stateName, countryName)
			FROM cities
			INNER JOIN states    ON states.stateId      = cities.stateId
			INNER JOIN countries ON countries.countryId = states.countryId
			".(!empty($aWhere) ? ' WHERE '.implode(' AND ', $aWhere) : '')." ";
		$this->db->query($query);
		//pr($this->db->last_query()); die;

		$this->Commond_Model->updateProcessDate('saveZonesSearch');

		return true;
	}

	function saveCountrySef() {
		$query = ' REPLACE INTO entities_properties
 					(entityTypeId, entityId, entitySef )
					SELECT '.config_item('entityTypeCountry').', countryId, countryId
					FROM countries ';
		$this->db->query($query);
		//pr($this->db->last_query()); die;
		return true;
	}
}
