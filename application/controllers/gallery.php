<?php 
class Gallery extends CI_Controller {

	function __construct() {
		parent::__construct();
		
		$this->load->model(array('Files_Model'));
	}
	
	function select($entityTypeId, $entityId) {
		$config = getEntityGalleryConfig($entityTypeId);
		if (! $this->safety->allowByControllerName($config['controller']) ) { return errorForbidden(); }
		
		$files = $this->Files_Model->selectEntityGallery($entityTypeId, $entityId, null, true);
		return loadViewAjax(true,  array('files' => $files));
	}
	
	function savePicture() {
		$entityTypeId   = $this->input->post('entityTypeId');
		$entityId       = $this->input->post('entityId');
		$config         = getEntityGalleryConfig($entityTypeId);
		
		if (! $this->safety->allowByControllerName($config['controller']) ) { return errorForbidden(); }
		
		$result = savePicture($config, $entityTypeId, $entityId);

		if ($result['code'] != true) {
			return loadViewAjax(false, $result['result']);
		}
		
		$files = $this->Files_Model->selectEntityGallery($entityTypeId, $entityId, $result['fileId'], true);
		return loadViewAjax(true,  array('files' => $files));
	}

	function deletePicture($entityTypeId, $fileId) {
		$config = getEntityGalleryConfig($entityTypeId);
		if (! $this->safety->allowByControllerName($config['controller']) ) { return errorForbidden(); }

		if ($this->Files_Model->hasFileIdInEntityTypeId($entityTypeId, $fileId) == false) {
			return errorForbidden();
		}
		
		$this->Files_Model->deleteEntityFile($entityTypeId, $fileId);
		
		return loadViewAjax(true, array());
	}
}
