<?php 
class Gallery extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Files_Model'));
	}
	
	function select($entityTypeId, $entityId) {
		// TODO: implementar seguridad!!
		
		$files 	= $this->Files_Model->selectEntityGallery($entityTypeId, $entityId, null, true);
		return loadViewAjax(true,  array('files' => $files));
	}
	
	function savePicture() {
		$entityTypeId   = $this->input->post('entityTypeId');
		$entityId       = $this->input->post('entityId');
		$config         = getEntityConfig($entityTypeId);
		
		if (! $this->safety->allowByControllerName($config['controller']) ) { return errorForbidden(); }
		
		$result = savePicture($config);
		
		if ($result['code'] != true) {
			return loadViewAjax(false, $result['result']);
		}
		
		$fileId = $result['fileId'];
		
		$this->Files_Model->saveFileRelation($entityTypeId, $entityId ,$fileId);
		
		$files 	= $this->Files_Model->selectEntityGallery($entityTypeId, $entityId, $fileId);
		return loadViewAjax(true,  array('files' => $files));
	}

	function deletePicture($entityTypeId, $fileId) {
		$config    = getEntityConfig($entityTypeId);
		if (! $this->safety->allowByControllerName($config['controller']) ) { return errorForbidden(); }
				
		$this->Files_Model->deleteEntityFile($entityTypeId, $fileId);
		
		return loadViewAjax(true, array());
	}
}
