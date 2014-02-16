<?php
class Files_Model extends CI_Model {
	function insert($fileName, $fileTitle) {
		
		$this->db->insert('files', array(
			'fileName'		=> $fileName,
			'fileTitle'		=> $fileTitle
		));
		
		return $this->db->insert_id();
	}
	
	function deleteByFileId($entityName, $entityId, $fileId) {
		$aProperties = $this->getPropertyByEntityName($entityName);
		
		$data 		= $this->get($fileId);
		$fileName 	= $data['fileName'];
		
		@unlink('.'.$aProperties['folder'].'original/'.$fileName);
		@unlink('.'.$aProperties['folder'].'thumb/'.$fileName);
		@unlink('.'.$aProperties['folder'].'large/'.$fileName);
				
		return $this->db->where('fileId', $fileId)->delete('files');
	}
	
	function get($fileId) {
		return $this->db->where('fileId', $fileId)->get('files')->row_array();
	}	

	function getPropertyByEntityName($entityName) {
		// TODO: optimizar para que devuelva al toque si ya lo tiene
		switch ($entityName) {
			case 'testing':
				return array(
					'tableName'		=> 'testing_files',
					'fieldName' 	=> 'testId',
					'folder'		=> '/assets/images/testing/'
				);
				break;
		}
	}
	
	function saveFileRelation($entityName, $fileId, $entityId ) {
		$aProperties = $this->getPropertyByEntityName($entityName);
		
		$this->db->insert($aProperties['tableName'], 
			array(
				'fileId'				 	=> $fileId,
				$aProperties['fieldName']	=> $entityId
		));
	}
	
	function getFilesByEntity($entityName, $entityId, $fileId = null, $calculateSize = true) {
		$aProperties 	= $this->getPropertyByEntityName($entityName);
		$result 		= array();
		
		$this->db->select('files.fileId, fileName')
			->join($aProperties['tableName'], 'files.fileId =  '.$aProperties['tableName'].'.fileId')
			->where($aProperties['fieldName'], $entityId);
		if ($fileId != null) {
			$this->db->where('files.fileId', $fileId);
		}
		$query = $this->db->get('files')->result_array();
		//pr($this->db->last_query());
		
		foreach ($query as $row) {
			$result[] = array(
				'name' 				=> $row['fileName'],
				'url'				=> $this->getUrl($entityName, $row['fileName']),
				'size'				=> filesize('.'.$aProperties['folder'].'original/'.$row['fileName']), 
				'thumbnailUrl'		=> $this->getUrl($entityName, $row['fileName'], true),
				'deleteUrl'			=> base_url('files/remove/'.$entityName.'/'.$entityId.'/'.$row['fileId']),
				'deleteType'		=> 'DELETE'
			);
		}		
		
		return $result;
	}	

	function getUrl($entityName, $fileName, $thumb = false){
		$aProperties = $this->getPropertyByEntityName($entityName);

		return base_url($aProperties['folder'].($thumb == true ? 'thumb/' : 'large/').$fileName);				
	}
}
