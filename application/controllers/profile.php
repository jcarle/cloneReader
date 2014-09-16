<?php 
class Profile extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Users_Model');
	}
	
	function index() {
		$this->edit();
	}
	
	function edit() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }

		$this->load->view('pageHtml', array(
			'view'		=> 'profile', 
		));
	}

	function editProfile() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = array(
			'frmId'			=> 'frmEditProfile',
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Save').' </button>'),
			'title'			=> $this->lang->line('Edit profile'),
			'fields'		=> array(
				'userFirstName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('First name'), 
				),
				'userLastName' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Last name'), 
				),
				'userEmail' => array(
					'type'		=> 'text',
					'label'		=> $this->lang->line('Email'),
					'disabled' 	=> true
				),
				'countryId' => array(
					'type'				=> 'dropdown',
					'label'				=> $this->lang->line('Country'),
					'appendNullOption' 	=> true,
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
		
		if ($this->input->post() != false) {
			return $this->_saveEditProfile();
		}
		
		$this->load->model('Countries_Model');
		$userId = $this->session->userdata('userId');
		$form['fields']['countryId']['source'] = $this->Countries_Model->selectToDropdown();
		
		return $this->load->view('includes/crJsonForm', array( 'form' => populateCrForm($form, $this->Users_Model->get($userId)) ));
	}

	function _saveEditProfile() {
		if ($this->form_validation->run() == FALSE) {
			return loadViewAjax(false);
		}

		$this->Users_Model->editProfile($this->session->userdata('userId'), $this->input->post());
		
		return loadViewAjax(true, array('notification' => $this->lang->line('Data updated successfully')));
	}
		
	function changeEmail() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$userId = $this->session->userdata('userId');
		$data   = $this->Users_Model->get($userId);
		
		$this->load->helper('email');
		
		$form = array(
			'frmId'			=> 'frmChangeEmail',
			'title'			=> $this->lang->line('Change email'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Save').' </button>'),
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
		
		if ($this->input->post() != false) {
			$this->load->model(array('Tasks_Model'));
			
			$userId          = $this->session->userdata('userId');
			$userEmail       = $this->input->post('userEmail');
			$confirmEmailKey = random_string('alnum', 20);
			
			$this->Users_Model->updateConfirmEmailKey($userId, $userEmail, $confirmEmailKey);
			
			$this->Tasks_Model->addTask('sendEmailToChangeEmail', array( 'userId' => $userId,));
			return loadViewAjax(true, array( 'notification' => $this->lang->line('We have sent you an email with instructions to change your email')));	
		}		
		
		return $this->load->view('includes/crJsonForm', array( 'form' => $form ));
	}
	
	function confirmEmail() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$confirmEmailKey = $this->input->get('key');
		$userId          = $this->session->userdata('userId');
		$user            = $this->Users_Model->getUserByUserIdAndConfirmEmailKey($userId, $confirmEmailKey);
		if (empty($user)) {
			return error404();
		}

		$this->Users_Model->confirmEmail($userId);

		$this->load->view('pageHtml', array(
			'view'     => 'message', 
			'meta'     => array( 'title' => $this->lang->line('Confirm email') ),
			'message'  => $this->lang->line('Your email has been confirmed'),
		));	
	}

	/**
	 * Muestro 3 tipos de forms dependiendo de cada caso:
	 * 		Si el usuario no tiene email: email, nuevo password. Se envia por mail un link de confirmación de email
	 * 		Si el usuario no tiene password: nuevo password
	 * 		Si el usuario tiene email y password: old password, nuevo password
	 * */
	function changePassword() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		
		$user = $this->Users_Model->get($this->session->userdata('userId'));
		if ($user['userEmail'] == null) {
			return $this->_insertEmailAndPassword();
		}
		else if ($user['userPassword'] == null) {
			return $this->_insertPassword();
		}	
		else {
			return $this->_updatePassword();
		}
	}
	
	function _insertEmailAndPassword() {
		$form = array(
			'frmId'     => 'frmChangePassword',
			'title'     => $this->lang->line('Change password'),
			'buttons'   => array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Save').' </button>'),
//			'info'		=> array('position' => 'left|right', 'html' => '<div class="alert alert-info"> No t </div>'),
			'fields'    => array(
				'userEmail' => array(
					'type'	=> 'text',
					'label'	=> $this->lang->line('Email'),
				),
				'passwordNew' => array(
					'type'   => 'password',
					'label'  => $this->lang->line('New password'), 
				),
				'passwordRepeatNew' => array(
					'type'   => 'password',
					'label'  => $this->lang->line('Repeat new password'), 
				),
			)
		);
		
		$form['rules'] = array( 
			array(
				'field' => 'userEmail',
				'label' => $form['fields']['userEmail']['label'],
				'rules' => 'trim|required|valid_email|callback__validate_exitsEmail'
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
		
		if ($this->input->post() != false) {
			if ($this->form_validation->run() == FALSE) {
				return loadViewAjax(false);
			}


			$this->load->model(array('Tasks_Model'));
			
			$userId          = $this->session->userdata('userId');
			$userEmail       = $this->input->post('userEmail');
			$confirmEmailKey = random_string('alnum', 20);
			
			$this->Users_Model->updateConfirmEmailKey($userId, $userEmail, $confirmEmailKey);
			$this->Users_Model->updatePassword($this->session->userdata('userId'), $this->input->post('passwordNew'));
			
			$this->Tasks_Model->addTask('sendEmailToChangeEmail', array( 'userId' => $userId,));
			return loadViewAjax(true, array( 'notification' => $this->lang->line('We have sent you an email with instructions to change your email')));	
		}

		$this->load->view('includes/crJsonForm', array( 'form' => $form ));	
	}	
	
	function _insertPassword() {
		$form = array(
			'frmId'     => 'frmChangePassword',
			'title'     => $this->lang->line('Change password'),
			'buttons'   => array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Save').' </button>'),
			'fields'    => array(
				'passwordNew' => array(
					'type'   => 'password',
					'label'  => $this->lang->line('New password'), 
				),
				'passwordRepeatNew' => array(
					'type'   => 'password',
					'label'  => $this->lang->line('Repeat new password'), 
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
		
		if ($this->input->post() != false) {
			if ($this->form_validation->run() == FALSE) {
				return loadViewAjax(false);
			}

			$this->Users_Model->updatePassword($this->session->userdata('userId'), $this->input->post('passwordNew'));
			
			return loadViewAjax(true, array('notification' => $this->lang->line('Data updated successfully')));
		}

		$this->load->view('includes/crJsonForm', array( 'form' => $form ));	
	}
	
	function _updatePassword() {
		$form = array(
			'frmId'     => 'frmChangePassword',
			'title'     => $this->lang->line('Change password'),
			'buttons'   => array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Save').' </button>'),
			'fields'    => array(
				'passwordOld' => array(
					'type'  => 'password',
					'label' => $this->lang->line('Current password'), 
				),
				'passwordNew' => array(
					'type'   => 'password',
					'label'  => $this->lang->line('New password'), 
				),
				'passwordRepeatNew' => array(
					'type'   => 'password',
					'label'  => $this->lang->line('Repeat new password'), 
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
		
		if ($this->input->post() != false) {
			if ($this->form_validation->run() == FALSE) {
				return loadViewAjax(false);
			}

			$this->Users_Model->updatePassword($this->session->userdata('userId'), $this->input->post('passwordNew'));
			
			return loadViewAjax(true, array('notification' => $this->lang->line('Data updated successfully')));			
		}

		$this->load->view('includes/crJsonForm', array( 'form' => $form ));	
	}	
	

	function forgotPassword() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { return errorForbidden(); }
		
		$form = array(
			'frmId'      => 'frmForgotPassword',
			'buttons'    => array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Send').' </button>'),
			'fields'     => array(
				'userEmail' => array(
					'type'  => 'text',
					'label' => $this->lang->line('Email'),
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
		
		if ($this->input->post() != false) {
			if ($this->form_validation->run() == FALSE) {
				return loadViewAjax(false);
			}else{
				$this->load->model(array('Tasks_Model'));
				
				$user             = $this->Users_Model->getByUserEmail($this->input->post('userEmail'));
				$userId           = $user['userId'];
				$resetPasswordKey = random_string('alnum', 20);
				
				$this->Users_Model->updateResetPasswordKey($userId, $resetPasswordKey);
				
				$this->Tasks_Model->addTask('sendEmailToResetPassword', array( 'userId' => $userId ));
				return loadViewAjax(true, array( 'notification' => $this->lang->line('We have sent you an email with instructions to reset your password')));
			}
		}

		$this->load->view('pageHtml', array(
			'view'		=> 'includes/crForm', 
			'meta'		=> array( 'title' => $this->lang->line('Reset password') ),
			'form'		=> $form,
		));
	}

	function resetPassword() {
		if (! $this->safety->allowByControllerName('profile/forgotPassword') ) { return errorForbidden(); }
		
		$resetPasswordKey  = $this->input->get('key');
		
		$form = array(
			'frmId'     => 'frmResetPassword',
			'buttons'   => array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Reset password').' </button>'),
			'fields'    => array(
				'resetPasswordKey' => array(
					'type'  => 'hidden',
					'value' => $resetPasswordKey, 
				),
				'passwordNew' => array(
					'type'   => 'password',
					'label'  => $this->lang->line('New password'), 
				),
				'passwordRepeatNew' => array(
					'type'   => 'password',
					'label'  => $this->lang->line('Repeat new password'), 
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
		
		if ($this->input->post() != false) {
			return $this->_saveResetPassword();
		}
		else {
			$user = $this->Users_Model->getUserByResetPasswordKey($resetPasswordKey);
			if (empty($user)) {
				return error404();
			}
		}
		
		$this->load->view('pageHtml', array(
			'view'  => 'includes/crForm',
			'form'  => $form,
			'meta'  => array( 'title' => $this->lang->line('Reset password') ),
			'code'  => true
		));	
	}

	function _saveResetPassword() {
		$resetPasswordKey  = $this->input->post('resetPasswordKey');
		$user              = $this->Users_Model->getUserByResetPasswordKey($resetPasswordKey);
		if (empty($user)) {
			return error404();
		}
		
		if ($this->form_validation->run() == FALSE) {
			return loadViewAjax(false);
		}
		
		$this->Users_Model->updatePassword($user['userId'], $this->input->post('passwordNew'));
		
		return loadViewAjax(true, array('msg' => $this->lang->line('Data updated successfully'), 'goToUrl' => base_url('login')));
	}
	
	function _validate_password() {
		return $this->Users_Model->checkPassword($this->session->userdata('userId'), $this->input->post('passwordOld'));
	}
	
	function _validate_exitsEmail() {
		return ($this->Users_Model->exitsEmail($this->input->post('userEmail'), 0) != true);
	}

	function _validate_notExitsEmail() {
		return $this->Users_Model->exitsEmail($this->input->post('userEmail'), 0);
	}

	function _validate_remove_account($str) {
		return ( $this->input->post('chkRemoveAccount') == true );
	}

	function removeAccount() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = array(
			'frmId'         => 'frmRemoveAccount',
			'urlDelete'     => base_url('profile/removeAccount'),
			'buttons'       => array('<button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> '.$this->lang->line('Remove account').' </button>'),
			'title'         => $this->lang->line('Remove account'),
			'fields'        => array(
				'fieldRemoveAccount' => array( // FIXME: agrego un field para que  " if ($this->input->post() != false) "
					'type'	=> 'hidden',
					'value' => 'true', 
				),
				'removeAccount' => array(
					'type'  => 'html',
					'value' => '<p>'.$this->lang->line('Are you sure you want to remove your account?').'</p>
								<div class="alert alert-danger" role="alert">'.$this->lang->line('Deleting your member account will be permanent').'</div>'
				),
				'chkRemoveAccount' => array(
					'type'       => 'checkbox',
					'label'      => $this->lang->line('I understand, delete my account'),
					'checked'    => false,
					'hideOffset' => true,
				)
			)
		);
		
		$form['rules'] = array(
			array(
				'field' => 'chkRemoveAccount',
				'label' => ' eliminar ', 
				'rules' => 'callback__validate_remove_account',
			),
		);
		
		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message('_validate_remove_account', $this->lang->line('You must confirm the deletion of your account'));
		
		if ($this->input->post() != false) {
			if ($this->form_validation->run() == FALSE) {
				return loadViewAjax(false);
			}
			return $this->_saveRemoveAccount();
		}

		return $this->load->view('includes/crJsonForm', array( 'form' => $form ));
	}
	
	function _saveRemoveAccount() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }

		$this->Users_Model->removeAccount($this->session->userdata('userId'));
		
		$this->session->sess_destroy();
		
		return loadViewAjax(true, array('goToUrl' => base_url(''), 'skipAppLink' => true));
	}

	function downloadOPML() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = array(
			'frmId'			=> 'frmDownloadOPML',
			'buttons'		=> array(),
			'title'			=> $this->lang->line('Download OPML'),
			'fields'		=> array(
				'downloadHtml' => array(
					'type'	=> 'html',
					'value'	=> '<p>'.$this->lang->line('OPML is a format which allows migrate the feeds to another reader').'</p><a href="'.site_url('profile/doDownloadOPML').'" data-skip-app-link="true"> '.$this->lang->line('Download OPML').' <i class="fa fa-download" /> </a>' 
				),
			)
		);

		return $this->load->view('includes/crJsonForm', array( 'form' => $form ));
	}
	
	function doDownloadOPML() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }

		$this->load->model('Feeds_Model');
		$this->load->helper('download');
		
		$userId = $this->session->userdata('userId');
		$data 	= $this->Users_Model->get($userId);
		$query 	= $this->Feeds_Model->selectFeedsOPML($userId);

		$xml  	= new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><opml version="1.0" />');
		$xml->xmlEndoding='UTF-8';
		$nHead  = $xml->addChild('head');
		$nTitle = $nHead->addChild('title', 'cReader feeds of '.element('userFirstName', $data).' '.element('userLastName', $data));
		$nBody 	= $xml->addChild('body');
		$tagId	= null;
		
		foreach ($query as $row) {
			if ($row['tagId'] != null) {
				
				if ($tagId != $row['tagId']) {
					$nTag 	= $nBody->addChild('outline');
					$nTag->addAttribute('text', $row['tagName']);
					$nTag->addAttribute('title', $row['tagName']);
				}
				
				$tagId = $row['tagId'];
				$nParent = $nTag;
			}
			else {
				$nParent = $nBody;
			} 
		
			$nFeed 	= $nParent->addChild('outline');
			$nFeed->addAttribute('type', 'rss');
			$nFeed->addAttribute('text', $row['feedName']);
			$nFeed->addAttribute('title', $row['feedName']);
			$nFeed->addAttribute('xmlUrl', $row['feedUrl']);
			$nFeed->addAttribute('htmlUrl', $row['feedLink']);
		
		
		}

		

		force_download('cReader.opml', $xml->saveXML());
	}
}
