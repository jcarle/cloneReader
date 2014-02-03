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
		
		$this->load->view('includes/template', array(
			'view'		=> 'profile', 
			'title'		=> $this->lang->line('Edit profile'),
			'hasForm'	=> true,
			'aJs'		=> array('profile.js'),
		));		
	}


	function _getFrmEditProfile() {
		$userId = $this->session->userdata('userId');
		$data 	= $this->Users_Model->get($userId);
		
		$this->load->helper('email');
		
		$form = array(
			'frmId'			=> 'frmEditProfile',
			'action'		=> base_url('profile/saveEditProfile/'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Save').' </button>'),
			'fields'		=> array(
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
				'userEmail' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Email'),
					'value'		=> valid_email(element('userEmail', $data)) == true ? element('userEmail', $data) : '',
					'disabled' 	=> true
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
				'field' => 'userFirstName',
				'label' => $form['fields']['userFirstName']['label'],
				'rules' => 'trim|required'
			),
			array(
				'field' => 'userLastName',
				'label' => $form['fields']['userLastName']['label'],
				'rules' => 'trim|required'
			)
		);		
		
		$this->form_validation->set_rules($form['rules']);

		return $form;
	}
		

	function frmEditProfile() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }

		$form = $this->_getFrmEditProfile();
		
		$this->load->view('ajax', array(
			'view'			=> 'includes/crAjaxForm',
			'form'			=> $form,
			'title'			=> $this->lang->line('Edit profile'),
			'code'			=> true
		));
	}

	function saveEditProfile() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = $this->_getFrmEditProfile();
		
		if ($this->form_validation->run() == FALSE) {
			$code 		= false;
			$message 	= validation_errors();
		}
		else {		
			$this->Users_Model->editProfile($this->session->userdata('userId'), $this->input->post());
			$code 		= true;
			$message 	= array('notification' => $this->lang->line('Data updated successfully'));
		}
		
		return $this->load->view('ajax', array(
			'code'		=> $code,
			'result' 	=> $message
		));
	}


	function _getFrmChangeEmail() {
		$userId = $this->session->userdata('userId');
		$data 	= $this->Users_Model->get($userId);
		
		$this->load->helper('email');
		
		$form = array(
			'frmId'			=> 'frmChangeEmail',
			'action'		=> base_url('profile/sendEmailToChangeEmail/'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Save').' </button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
					'value'	=> valid_email(element('userEmail', $data)) == true ? element('userEmail', $data) : '',
				),
			)
		);
		
		$form['rules'] 	= array( 
			array(
				'field' => 'userEmail',
				'label' => $form['fields']['userEmail']['label'],
				'rules' => 'trim|required|valid_email|callback__validate_exitsEmail'
			),
		);		

		$this->form_validation->set_rules($form['rules']);
		return $form;
	}
		
		
	function frmChangeEmail() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$this->load->view('ajax', array(
			'view'			=> 'includes/crAjaxForm',
			'form'			=> $this->_getFrmChangeEmail(),
			'title'			=> $this->lang->line('Change email'),
			'code'			=> true
		));
	}

	function sendEmailToChangeEmail() {
		$form = $this->_getFrmChangeEmail();

		if ($this->form_validation->run() == FALSE) {
			return $this->load->view('ajax', array(
				'code'		=> false,
				'result' 	=> validation_errors()
			));	
		}

		$this->load->library('email');

		$userId 		= $this->session->userdata('userId');
		$userEmail 		= $this->input->post('userEmail');
		$user 			= $this->Users_Model->get($userId);
		$changeEmailKey = random_string('alnum', 20);
		
		$this->Users_Model->updateChangeEmailKey($userId, $userEmail, $changeEmailKey);

		$this->email->from('clonereader@gmail.com', 'cReader BETA');
		$this->email->to($userEmail); 
		$this->email->subject('cReader - '.$this->lang->line('Change email'));
		$this->email->message(sprintf($this->lang->line('Hello %s, <p>To change your  email in cReader, click here %s  </p> Regards'), $user['userFirstName'], base_url('profile/confirmEmail/'.$changeEmailKey)));
		$this->email->send();
		//echo $this->email->print_debugger();	die;	

		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> array( 'notification' => $this->lang->line('We have sent you an email with instructions to change your email')),
		));	
	}

	function confirmEmail($changeEmailKey) {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');
		$user 	= $this->Users_Model->getUserByUserIdAndChangeEmailKey($userId, $changeEmailKey);
		if (empty($user)) {
			return error404();
		}
		
		$this->Users_Model->confirmEmail($userId);

		$this->load->view('includes/template', array(
			'view'		=> 'message', 
			'title'		=> $this->lang->line('Change email'),
			'message'	=> $this->lang->line('Your email has been updated')
		));	
	}
	
	function _getFrmChangePassword() {
		$form = array(
			'frmId'			=> 'frmChangePassword',
			'action'		=> base_url('profile/saveChangePassword/'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Change password').' </button>'),
			'fields'		=> array(
				'passwordOld' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('Current password'), 
				),
				'passwordNew' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('New password'), 
				),
				'passwordRepeatNew' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('Repeat new password'), 
				),
			)
		);
		
		$form['rules'] = array( 
			array(
				'field' => 'passwordOld',
				'label' => $form['fields']['passwordOld']['label'],
				'rules' => 'trim|required|callback__validate_password'
			),
			array(
				'field' => 'passwordNew',
				'label' => $form['fields']['passwordNew']['label'],
				'rules' => 'trim|required|matches[passwordRepeatNew]'
			),
			array(
				'field' => 'passwordRepeatNew',
				'label' => $form['fields']['passwordRepeatNew']['label'],
				'rules' => 'trim|required'
			)
		);		
		
		$this->form_validation->set_rules($form['rules']);
		
		return $form;
	}

	function frmChangePassword() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = $this->_getFrmChangePassword();

		$this->load->view('ajax', array(
			'view'			=> 'includes/crAjaxForm',
			'form'			=> $form,
			'title'			=> $this->lang->line('Change password'),
			'code'			=> true
		));
	}
	
	function saveChangePassword() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = $this->_getFrmChangePassword();
		
		if ($this->form_validation->run() == FALSE) {
			$code 		= false;
			$message 	= validation_errors();
		}
		else {
			$this->Users_Model->updatePassword($this->session->userdata('userId'), $this->input->post('passwordNew'));		
			$code 		= true;
			$message 	= array('notification' => $this->lang->line('Data updated successfully'));
		}
		
		return $this->load->view('ajax', array(
			'code'		=> $code,
			'result' 	=> $message 
		));				
	}
	
	function _validate_password() {
		return $this->Users_Model->checkPassword($this->session->userdata('userId'), $this->input->post('passwordOld'));
	}
	
	function frmRemoveAccount() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		return $this->load->view('ajax', array(
			'code'		=> false,
			'result' 	=> 'coming soon'
		));
	}
		

	function importFeeds() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'action'	=> base_url('profile/doImportFeeds'),
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
			'view'		=> 'includes/crForm', 
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
		
		return $this->load->view('ajax', array('code' => true, 'result' => array('msg' => $this->lang->line('The import was successful'), 'goToUrl' => base_url(''))));		
	}

	function importStarred() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'action'	=> base_url('profile/doImportStarred'),
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
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Import starred'),
			'form'		=> $form
		));		
	}
	
	function doImportStarred() {
		if (! $this->safety->allowByControllerName('profile/importStarred') ) { return errorForbidden(); }
		
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
				$entry['entryId'] = $this->Entries_Model->getEntryIdByFeedIdAndEntryUrl($entry['feedId'], $entry['entryUrl']);
			}
			
			$this->Entries_Model->saveTmpUsersEntries((int)$userId, array(array( 'userId' => $userId, 'entryId'	=> $entry['entryId'], 'starred'	=> true,  'entryRead' => true )));
		}

		$this->Entries_Model->pushTmpUserEntries($userId);

		return $this->load->view('ajax', array('code' => true, 'result' => array('msg' => $this->lang->line('The import was successful'), 'goToUrl' => base_url(''))));
	}
	
	function _validate_notExitsEmail() {
		return $this->Users_Model->exitsEmail($this->input->post('userEmail'), 0);
	}
	
	function _validate_exitsEmail() {
		return ($this->Users_Model->exitsEmail($this->input->post('userEmail'), 0) != true);
	}
	
	function _getFrmForgotPassword() {
		$form = array(
			'frmId'			=> 'frmForgotPassword',
			'action'		=> base_url('profile/sendEmailToResetPassword/'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Send').' </button>'),
			'fields'		=> array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
				),
			)
		);
		
		$form['rules'] 	= array( 
			array(
				'field' => 'userEmail',
				'label' => $form['fields']['userEmail']['label'],
				'rules' => 'trim|required|valid_email|callback__validate_notExitsEmail'
			),
		);
		
		$this->form_validation->set_rules($form['rules']);
		
		return $form;
	}	
	
	function forgotPassword() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = $this->_getFrmForgotPassword();

		$this->load->view('includes/template', array(
			'view'		=> 'includes/crForm', 
			'title'		=> $this->lang->line('Reset password'),
			'form'		=> $form,
		));
	}
	
	function sendEmailToResetPassword() {
		$form = $this->_getFrmForgotPassword();

		if ($this->form_validation->run() == FALSE) {
			return $this->load->view('ajax', array(
				'code'		=> false,
				'result' 	=> validation_errors()
			));	
		}

		$this->load->library('email');

		$user 				= $this->Users_Model->getByUserEmail($this->input->post('userEmail'));
		$resetPasswordKey 	= random_string('alnum', 20);
		
		$this->Users_Model->updateResetPasswordKey($user['userId'], $resetPasswordKey);

		$this->email->from('clonereader@gmail.com', 'cReader BETA');
		$this->email->to($user['userEmail']); 
		$this->email->subject('cReader - '.$this->lang->line('Reset password'));
		$this->email->message(sprintf($this->lang->line('Hello %s, <p>To reset your cReader password, click here %s  </p> Regards'), $user['userFirstName'], base_url('profile/resetPassword/'.$resetPasswordKey)));
		$this->email->send();
		//echo $this->email->print_debugger();	die;	

		return $this->load->view('ajax', array(
			'code'		=> true,
			'result' 	=> array( 'notification' => $this->lang->line('We have sent you an email with instructions to reset your password')),
		));	
	}
	
	function resetPassword($resetPasswordKey) {
		$user = $this->Users_Model->getUserByResetPasswordKey($resetPasswordKey);
		if (empty($user)) {
			return error404();
		}
		
		$form = $this->_getFrmResetPassword($resetPasswordKey);
		
		$this->load->view('includes/template', array(
			'view'			=> 'includes/crForm',
			'form'			=> $form,
			'title'			=> $this->lang->line('Reset password'),
			'code'			=> true
		));		
	}
	
	function _getFrmResetPassword($resetPasswordKey) {
		$form = array(
			'frmId'			=> 'frmResetPassword',
			'action'		=> base_url('profile/saveResetPassword/'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="icon-save"></i> '.$this->lang->line('Reset password').' </button>'),			
			'fields'		=> array(
				'resetPasswordKey' => array(
					'type'	=> 'hidden',
					'value' => $resetPasswordKey, 
				),			
				'passwordNew' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('New password'), 
				),
				'passwordRepeatNew' => array(
					'type'	=> 'password',
					'label'	=> $this->lang->line('Repeat new password'), 
				),				
			)
		);
		
		$form['rules'] = array( 
			array(
				'field' => 'passwordNew',
				'label' => $form['fields']['passwordNew']['label'],
				'rules' => 'trim|required|matches[passwordRepeatNew]'
			),
			array(
				'field' => 'passwordRepeatNew',
				'label' => $form['fields']['passwordRepeatNew']['label'],
				'rules' => 'trim|required'
			)
		);		

		$this->form_validation->set_rules($form['rules']);

		return $form;
	}

	function saveResetPassword() {
		$resetPasswordKey 	= $this->input->post('resetPasswordKey');
		$user 				= $this->Users_Model->getUserByResetPasswordKey($resetPasswordKey);
		if (empty($user)) {
			return error404();
		}
		
		$form = $this->_getFrmResetPassword($resetPasswordKey);
		
		if ($this->form_validation->run() == FALSE) {
			$code 		= false;
			$message 	= validation_errors();
		}
		else {
			$this->Users_Model->updatePassword($user['userId'], $this->input->post('passwordNew'));		
			$code 		= true;
			$message 	= array('msg' => $this->lang->line('Data updated successfully'), 'goToUrl' => base_url('login'));
		}
		
		return $this->load->view('ajax', array(
			'code'		=> $code,
			'result' 	=> $message 
		));
	}
}
