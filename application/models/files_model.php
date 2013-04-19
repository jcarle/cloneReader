<?php
class Files_Model extends CI_Model {
	function insert($fileName, $fileTitle) {
		
		$this->db->insert('files', array(
									'fileName'		=> $fileName,
									'fileTitle'		=> $fileTitle
		));
		
		return $this->db->insert_id();
	}
	
	function deleteByFileId($fileId) {
		return $this->db->where('fileId', $fileId)->delete('files');
	}
	
	function get($fileId) {
		return $this->db->where('fileId', $fileId)->get('files')->row_array();
	}	

	function getPropertyByEntityName($entityName) {
		// TODO: optimizar para que devuelva al toque si ya lo tiene
		switch ($entityName) {
			case 'lodgments':
				return array(
					'tableName'		=> 'files_lodgments',
					'fieldName' 	=> 'lodgmentId',
					'folder'		=> '/img/lodgments/'
				);
				break;
			case 'excursions':
				return array(	
					'tableName'		=> 'files_excursions',
					'fieldName' 	=> 'excursionId',
					'folder'		=> '/img/excursions/'
				);
				break;
		}
	}
	
	function saveFileRelation($entityName, $fileId, $fieldValue ) {
		$aProperties = $this->getPropertyByEntityName($entityName);
		
		$this->db->insert($aProperties['tableName'], 
			array(
				'fileId'				 	=> $fileId,
				$aProperties['fieldName']	=> $fieldValue
		));
	}
	
	function getFilesByEntity($entityName, $fieldValue, $fileId = null) {
		$aProperties = $this->getPropertyByEntityName($entityName);
		
		$this->db->select('files.fileId, fileName')
			->join($aProperties['tableName'], 'files.fileId =  '.$aProperties['tableName'].'.fileId')
			->where($aProperties['fieldName'], $fieldValue);
		if ($fileId != null) {
			$this->db->where('files.fileId', $fileId);
		}
		$query = $this->db->get('files');
		//pr($this->db->last_query());
		return $query;
	}	

	function getUrl($entityName, $fileName, $thumb = false){
		$aProperties = $this->getPropertyByEntityName($entityName);

		return base_url($aProperties['folder'].($thumb == true ? 'thumbs/' : '').$fileName);				
	}
}