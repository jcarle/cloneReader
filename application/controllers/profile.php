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
			'messages'	 	=> getCrFormRulesMessages(),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Save').' </button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
					'value'	=> element('userEmail', $data)
				),
				'userFirstName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('First Name'), 
					'value'	=> element('userFirstName', $data)
				),
				'userLastName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Last Name'), 
					'value'	=> element('userLastName', $data)
				),
				'countryId' => array(
					'type'		=> 'dropdown',
					'label'		=> $this->lang->line('Country'),
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
					'result' 	=> $this->lang->line('The email entered already exists in the database')
				));
			}
					
			return $this->load->view('ajax', array(
				'code'		=> $this->Users_Model->editProfile($userId, $this->input->post()), 
				'result' 	=> validation_errors() 
			));
		}
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> $this->lang->line('Edit Profile'),
			'form'		=> $form,
				  
		));		
	}

	function importFeeds() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'action'	=> base_url('profile/doImportFeeds'),
			'messages' 	=> getCrFormRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'tagName' => array(
					'type'		=> 'upload',
					'label'		=> sprintf($this->lang->line('Choose %s'), 'subscriptions.xml'), 
				),
			),	
			'buttons'	=> array()
		);
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> $this->lang->line('Import feeds'),
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
		
		return $this->load->view('ajax', array('code' => true, 'result' => array('msg' => $this->lang->line('Import success'), 'goToUrl' => base_url(''))));		
	}

	function importStarred() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'action'	=> base_url('profile/doImportStarred'),
			'messages' 	=> getCrFormRulesMessages(),
			'rules'		=> array(),
			'fields'	=> array(
				'tagName' => array(
					'type'		=> 'upload',
					'label'		=> sprintf($this->lang->line('Choose %s'), 'starred.json'), 
				),				
			), 		
			'buttons'	=> array()
		);
				
		$this->load->view('includes/template', array(
			'view'		=> 'includes/jForm', 
			'title'		=> $this->lang->line('Import starred'),
			'form'		=> $form
		));		
	}
	
	function doImportStarred() {
		if (! $this->safety->allowByControllerName('profile/importFeeds') ) { return errorForbidden(); }
		
		$this->load->model('Entries_Model');
		
		$userId = $this->session->userdata('userId');
		
		$config	= array(
			'upload_path' 		=> './application/cache',
			'allowed_types' 	=> 'json',
			'max_size'			=> 1024 * 8,
			'encrypt_name'		=> false,
			'is_image'			=> false,
			'overwrite'			=> true,
			'file_name'			=> 'import_starred_'.$userId.'.json'
		);

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload()) {
			return $this->load->view('ajax', array('code' => false, 'result' => $this->upload->display_errors('', '')));					
		}


		$fileName 	= './application/cache/import_starred_'.$userId.'.json';
		$json 		= (array)json_decode(file_get_contents($fileName), true);

		foreach ($json['items'] as $data) {
			$entryContent = '';
			if (element('summary', $data) != null) {
				$entryContent = $data['summary']['content'];
			}
			else if (element('content', $data) != null) {
				$entryContent = $data['content']['content'];
			}

			$entry = array(
				'entryTitle' 	=> element('title', $data, '(title unknown)'),
				'entryUrl'		=> (string)$data['alternate'][0]['href'],
				'entryAuthor'	=> element('author', $data, null),
				'entryDate'		=> date('Y-m-d H:i:s', $data['published']),
				'entryContent' 	=> (string)$entryContent,
			);

			$feed = array(
				'feedName'	=> element('title', $data['origin']),
				'feedUrl' 	=> substr($data['origin']['streamId'], 5),
				'feedLink'	=> $data['origin']['htmlUrl'],
				'feedName'	=> element('title', $data['origin'])
			);
			
			$entry['feedId']	= $this->Entries_Model->addFeed($userId, $feed);
			$entry['entryId'] 	= $this->Entries_Model->saveEntry($entry);
			if ($entry['entryId'] == null) {
				$entry['entryId'] = $this->Entries_Model->getEntryIdByEntryUrl($entry['entryUrl']);
			}
			
			$this->Entries_Model->saveTmpUsersEntries((int)$userId, array(array( 'userId' => $userId, 'entryId'	=> $entry['entryId'], 'starred'	=> true,  'entryRead' => true )));
		}

		$this->Entries_Model->pushTmpUserEntries($userId);

		return $this->load->view('ajax', array('code' => true, 'result' => array('msg' => $this->lang->line('Import success'), 'goToUrl' => base_url(''))));
	}
}
