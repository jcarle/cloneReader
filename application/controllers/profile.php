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
		$data 	= $this->Users_Model->get($userId);
		
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
			$data = array(
				'userId'	=> $this->session->userdata('userId'),
				'userEmail' => $this->input->post('userEmail')
			);
			$this->Tasks_Model->addTask('sendEmailToChangeEmail', $data);
			return loadViewAjax(true, array( 'notification' => $this->lang->line('We have sent you an email with instructions to change your email')));	
		}		
		
		return $this->load->view('includes/crJsonForm', array( 'form' => $form ));
	}

	function changePassword() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$form = array(
			'frmId'			=> 'frmChangePassword',
			'title'			=> $this->lang->line('Change password'),
			'buttons'		=> array('<button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> '.$this->lang->line('Change password').' </button>'),
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
		
		if ($this->input->post() != false) {
			return $this->_saveChangePassword();
		}

		$this->load->view('includes/crJsonForm', array( 'form' => $form ));
	}
	
	function _saveChangePassword() {
		if ($this->form_validation->run() == FALSE) {
			return loadViewAjax(false);
		}

		$this->Users_Model->updatePassword($this->session->userdata('userId'), $this->input->post('passwordNew'));		
		
		return loadViewAjax(true, array('notification' => $this->lang->line('Data updated successfully')));
	}
	
	function _validate_password() {
		return $this->Users_Model->checkPassword($this->session->userdata('userId'), $this->input->post('passwordOld'));
	}
	
	function _validate_exitsEmail() {
		return ($this->Users_Model->exitsEmail($this->input->post('userEmail'), 0) != true);
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
					'value'	=> '<p>'.$this->lang->line('OPML is a format which allows migrate the feeds to another reader').'</p><a href="'.site_url('profile/doDownloadOPML').'" data-skip-app-link="true">'.$this->lang->line('Download OPML').'</a>' 
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
	
	function removeAccount() {
		return loadViewAjax(false, 'coming soon');
	}	
}
