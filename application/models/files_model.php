<?php
class Files_Model extends CI_Model {
	
	function insert($fileName, $fileTitle) {
		
		$this->db->insert('files', array(
			'fileName'		=> $fileName,
			'fileTitle'		=> $fileTitle
		));
		
		return $this->db->insert_id();
	}
	
	function deleteFile($config, $fileId){
		$data      = $this->get($fileId);
		if (empty($data)) {
			return;
		}
		$fileName  = $data['fileName'];
		
		@unlink('.'.$config['folder'].$fileName);
		
		if (isset($config['sizes'])) {
			if (is_array($config['sizes'])) {
				foreach ($config['sizes'] as $size) {
					@unlink('.'.$size['folder'].$fileName);
				}
			}
		}

		return $this->db->where('fileId', $fileId)->delete('files');
	}
	
	function deleteEntityFile($entityTypeId, $fileId) {
		$config = getEntityGalleryConfig($entityTypeId);
		return $this->deleteFile($config, $fileId);
	}
	
	function get($fileId, $folder = null, $fieldName = null) {
		$query = $this->db->where('fileId', $fileId)->get('files')->row_array();
		if (empty($query)) {
			return null;
		}
		if ($folder != null) {
			$query['fileUrl'] = base_url($folder.$query['fileName']);
		}
		if ($fieldName != null) {
			return $query[$fieldName];
		}
		return $query;
	}
	
	function saveFileRelation($entityTypeId, $entityId, $fileId) {
		$this->db->insert('entity_files', 
			array(
				'entityTypeId'   => $entityTypeId,
				'entityId'       => $entityId,
				'fileId'         => $fileId,
		));
	}
	
	function selectEntityFiles($entityTypeId, $entityId, $fileId = null) {
		$config   = getEntityGalleryConfig($entityTypeId);
		$result   = array();

		$this->db->select('files.fileId, fileName, fileTitle')
			->join('entity_files', 'files.fileId =  entity_files.fileId', 'inner')
			->where('entity_files.entityTypeId', $entityTypeId)
			->where('entityId', $entityId);
		if ($fileId != null) {
			$this->db->where('files.fileId', $fileId);
		}
		$query = $this->db->get('files')->result_array();
		//pr($this->db->last_query());
		return $query;
	}
	
	function selectEntityGallery($entityTypeId, $entityId, $fileId = null, $allowDelete = false, $calculateSize = false) {
		$config    = getEntityGalleryConfig($entityTypeId);
		$result    = array();
		$query     = $this->selectEntityFiles($entityTypeId, $entityId, $fileId);

		foreach ($query as $row) {
			$picture = array(
				'name'           => $row['fileName'],
				'fileTitle'  => $row['fileTitle'],
				'urlLarge'       => base_url($config['sizes']['large']['folder'].$row['fileName']),
				'urlThumbnail'   => base_url($config['sizes']['thumb']['folder'].$row['fileName']),
			);
			if ($allowDelete == true) {
				$picture['urlDelete'] = base_url('gallery/deletePicture/'.$entityTypeId.'/'.$row['fileId']);
			}
			if ($calculateSize == true) {
				$picture['size'] = filesize('.'.$config['folder'].$row['fileName']); 
			}
			$result[] = $picture;
		}
		
		return $result;
	}
	
	function getEntityPicture($entityTypeId, $entityId, $size = 'thumb') {
		$config       = getEntityGalleryConfig($entityTypeId);
		$pictures     = $this->selectEntityFiles($entityTypeId, $entityId);
		if (!empty($pictures)) {
			return base_url($config['sizes'][$size]['folder'].$pictures[0]['fileName']);
		}
	}
}
