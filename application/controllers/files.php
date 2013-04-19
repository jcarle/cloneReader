<?php 
class Files extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Files_Model'));
	}
	
	function lodgments($entityId) {
		$this->_listing('lodgments', $entityId);
	}
	
	function excursions($entityId) {
		$this->_listing('excursions', $entityId);
	}	
	
	function _listing($entityName, $entityId, $fileId = null) {
		// TODO: implementar seguridad!!
		
		$result = array();

		$aProperties 	= $this->Files_Model->getPropertyByEntityName($entityName);
		$query 			= $this->Files_Model->getFilesByEntity($entityName, $entityId, $fileId, false);
		
		foreach ($query->result_array() as $row) {
			$result[] = array(
				'name' 				=> $row['fileName'],
				'url'				=> $this->Files_Model->getUrl($entityName, $row['fileName']),
				'size'				=> filesize('./'.$aProperties['folder'].$row['fileName']), 
				'thumbnail_url'		=> $this->Files_Model->getUrl($entityName, $row['fileName'], true),
				'delete_url'		=> base_url('files/remove/'.$entityName.'/'.$entityId.'/'.$row['fileId']),
			);
		}
		
		return $this->load->view('ajax', array(
			'result' 	=> $result
		));		
	}
	
	function save() {
		// TODO: implementar seguridat!!
		
		$entityName = $this->input->post('entityName');
		$entityId	= $this->input->post('entityId');
		
		$aProperties = $this->Files_Model->getPropertyByEntityName($entityName);
		
		$config	= array(
			'upload_path' 		=> './'.$aProperties['folder'],
			'allowed_types' 	=> 'gif|jpg|png',
			'max_size'			=> 1024 * 8,
			'encrypt_name'		=> false,
			'is_image'			=> true
		);
		
		$this->load->library('upload', $config);

		if (!$this->upload->do_upload()) {
			return $this->load->view('ajax', array('code' => false, 'result' => $this->upload->display_errors('', '')));					
		}
		
		$data = $this->upload->data();
		
		// creo el thumb
		$this->load->library('image_lib', array(
											'source_image' 		=> $data['full_path'],
											'new_image' 		=> './'.$aProperties['folder'] . '/thumbs',
											'maintain_ratio' 	=> true,
											'width' 			=> 150,
											'height' 			=> 100
 		));
		$this->image_lib->resize();

			
		$fileId = $this->Files_Model->insert($data['file_name'], '' /*$_POST['title']*/); // TODO:
		if(!$fileId) {
			unlink($data['full_path']);
			return $this->load->view('ajax', array('code' => false, 'result' => 'Something went wrong when saving the file, please try again'));
		}
					
		$this->Files_Model->saveFileRelation($entityName, $fileId, $entityId);
		@unlink($_FILES[$file_element_name]);
		
		$this->_listing($entityName, $entityId, $fileId);
	}

	function remove($entityName, $entityId, $fileId) {
		// TODO: implementar la seguridad!
		
		$aProperties = $this->Files_Model->getPropertyByEntityName($entityName);
		
		$query = $this->Files_Model->get($fileId);
		
		$fileName = $query['fileName'];
		
		@unlink('./'.$aProperties['folder'].$fileName);
		@unlink('./'.$aProperties['folder'].'thumbs/'.$fileName);
		
		$query = $this->Files_Model->deleteByFileId($fileId);
		
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> array()
		));		
	}
}