<?php 
class Profile extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model(array('Users_Model', 'Countries_Model'));
	}
	
	function index() {
		$this->edit();
	}
	
	function edit() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');
		$data 	= $this->Users_Model->get($userId);
		
		$form = array(
			'frmId'			=> 'frmUsersEdit',
			'messages'	 	=> getRulesMessages(),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> Guardar</button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> 'Email',
					'value'	=> element('userEmail', $data)
				),
				'userFirstName' => array(
					'type'	=> 'text',
					'label'	=> 'Nombre', 
					'value'	=> element('userFirstName', $data)
				),
				'userLastName' => array(
					'type'	=> 'text',
					'label'	=> 'Apellido', 
					'value'	=> element('userLastName', $data)
				),
				'countryId' => array(
					'type'		=> 'dropdown',
					'label'		=> 'País',
					'value'		=> element('countryId', $data),
					'source'	=> array_to_select($this->Countries_Model->select(), 'countryId', 'countryName')
				),
			)
		);
		
		$form['rules'] 	= array( 
			array(
				'field' => 'userEmail',
				'label' => $form['fields']['userEmail']['label'],
				'rules' => 'required|valid_email'
			),
			array(
				'field' => 'userFirstName',
				'label' => $form['fields']['userFirstName']['label'],
				'rules' => 'required'
			),
			array(
				'field' => 'userLastName',
				'label' => $form['fields']['userLastName']['label'],
				'rules' => 'required'
			)
		);		

		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);
		
		if ($this->input->is_ajax_request()) { // save data
			if ($this->Users_Model->exitsEmail($this->input->post('userEmail'), (int)$userId) == true) {
				return $this->load->view('ajax', array(
					'code'		=> false, 
					'result' 	=> 'El mail ingresado ya existe en la base de datos' 
				));
			}
					
			return $this->load->view('ajax', array(
				'code'		=> $this->Users_Model->editProfile($userId, $this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> 'Edit Profile',
			'form'		=> $form,
				  
		));		
	}

	function importFeeds() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'action'	=> base_url('profile/doImportFeeds'),
			'messages' 	=> getRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'tagName' => array(
					'type'		=> 'upload',
					'label'		=> 'Choose subscriptions.xml', 
				),				
			), 		
			'rules' 	=> array( 
				array(
					'field' => 'tagName',
					'label' => 'Nombre',
					'rules' => 'required'
				),
			),
			'buttons'	=> array()
		);
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> 'Import feeds',
			'form'		=> $form	  
		));		
	}
	
	function doImportFeeds() {
		if (! $this->safety->allowByControllerName('profile/importFeeds') ) { return errorForbidden(); }
		
		$this->load->model('Entries_Model');
		
		$userId = $this->session->userdata('userId');
		
		$config	= array(
			'upload_path' 		=> './application/cache',
			'allowed_types' 	=> 'xml',
			'max_size'			=> 1024 * 8,
			'encrypt_name'		=> false,
			'is_image'			=> false,
			'overwrite'			=> true,
			'file_name'			=> 'import_feeds_'.$userId.'.xml'
		);

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload()) {
			return $this->load->view('ajax', array('code' => false, 'result' => $this->upload->display_errors('', '')));					
		}
		
		
		$fileName 	= './application/cache/import_feeds_'.$userId.'.xml';
		$xml 		= simplexml_load_file($fileName);

		foreach ($xml->xpath('//body/outline') as $tag) {
			if (count($tag->children()) > 0) {
				$tagName = (string)$tag['title'];

				foreach ($tag->children() as $feed) {
					
					$feed = array(
						'feedName'	=> (string)$feed->attributes()->title,
						'feedUrl' 	=> (string)$feed->attributes()->xmlUrl,
						'feedLink'	=> (string)$feed->attributes()->htmlUrl
					);
					$feedId	=  $this->Entries_Model->addFeed($userId, $feed);
					$this->Entries_Model->addTag($tagName, $userId, $feedId);
				}
			}
			else {
				$feed = array(
					'feedName' 	=> (string)$tag->attributes()->title,
					'feedUrl' 	=> (string)$tag->attributes()->xmlUrl,
					'feedLink'	=> (string)$tag->attributes()->htmlUrl
				);
				$this->Entries_Model->addFeed($userId, $feed);
			}
		}
		
		return $this->load->view('ajax', array('code' => true, 'result' => array('msg' => 'Import success ', 'goToUrl' => base_url(''))));		
	}	
}
