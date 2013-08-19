<?php 
class Files extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Files_Model'));
	}
	
	function hotels($entityId) {
		$this->_listing('hotels', $entityId);
	}
	
	function _listing($entityName, $entityId, $fileId = null) {
		// TODO: implementar seguridad!!
		
		$result = array('files' => array());

		$aProperties 	= $this->Files_Model->getPropertyByEntityName($entityName);
		$query 			= $this->Files_Model->getFilesByEntity($entityName, $entityId, $fileId);
		
		foreach ($query->result_array() as $row) {
			$result['files'][] = array(
				'name' 				=> $row['fileName'],
				'url'				=> $this->Files_Model->getUrl($entityName, $row['fileName']),
				'size'				=> filesize('.'.$aProperties['folder'].'original/'.$row['fileName']), 
				'thumbnailUrl'		=> $this->Files_Model->getUrl($entityName, $row['fileName'], true),
				'deleteUrl'			=> base_url('files/remove/'.$entityName.'/'.$entityId.'/'.$row['fileId']),
				'deleteType'		=> 'DELETE'
			);
		}
		
		return $this->load->view('ajax', array(
			'result' 	=> $result
		));		
	}
	
	function save() {
		// TODO: implementar seguridat!!
		
		// TODO: cambiar los filenames para que sean unicos!
		
		$entityName = $this->input->post('entityName');
		$entityId	= $this->input->post('entityId');
		
		$aProperties = $this->Files_Model->getPropertyByEntityName($entityName);
		
		$config	= array(
			'upload_path' 		=> '.'.$aProperties['folder'].'/original',
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

		$aSizes = array(
			'thumb'	=> array( 'width' => 150, 	'height' => 100),
			'large'	=> array( 'width' => 1024, 	'height' => 660),
		);
		
		$this->load->library('image_lib');
		
		// creo el thumb y el large
		foreach ($aSizes as $key => $size) {
			$config = array(
				'source_image' 		=> $data['full_path'],
				'new_image' 		=> '.'.$aProperties['folder'] . $key,
				'maintain_ratio' 	=> true,
				'width' 			=> $size['width'],
				'height' 			=> $size['height']
			);
			$this->image_lib->initialize($config);
			$this->image_lib->resize();
		}

			
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
		$this->Files_Model->deleteByFileId($entityName, $entityId, $fileId);
		
		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> array()
		));		
	}
}