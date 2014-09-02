<?php
class SendMails {
	/*
	 * 
	 * Libreria de emails, cada metodo debe corresponder a un email determinado.
	 * El metodo solo recibe un array con los datos necesarios  para el email especifico.
	 * 
	 */
	
	//Para definir la instancia de la app
	private $CI	= null;
	
	function __construct() {
		$this->CI = &get_instance();
		$this->CI->load->library('email');
		$this->CI->load->helper('email');		
	}
	
	/**
	 * Setea el mail por default si env != prod
	 */
	function _addEmailTo($email) {
		if (ENVIRONMENT == 'production') {
			$this->CI->email->to($email);
			return;
		}
		
		$this->CI->email->to(config_item('emailDebug'));
	}
	
	function _sendEmail($emailTo, $subject, $message, $emailCc = null, $emailFrom = null) {
		if ($emailFrom != null) {
			$this->CI->email->from($emailFrom['email'], $emailFrom['name']);
		}
		else {		
			$this->CI->email->from(config_item('emailFrom'), config_item('siteName'));
		}
		$this->_addEmailTo($emailTo);

		if ($emailCc != null) {
			$this->CI->email->cc($emailCc);
		}
		
		$this->CI->email->subject($subject);
		$this->CI->email->message($message);
		if ($this->CI->email->send()) {
			return true;
		}
		//echo $this->CI->email->print_debugger();	die;
		return false;	
	}
	
	function sendEmailWelcome($params = array()) {
		if(empty($params) || !is_array($params)){
			return false;
		}
		$this->CI->load->model('Users_Model');
		$user  = $this->CI->Users_Model->get($params['userId'], false);
		$url   = ($user['confirmEmailKey'] != null ? base_url('confirmEmail?key='.$user['confirmEmailKey']) : null);
		$message         = $this->CI->load->view('pageEmail',
			array(
				'view'   => 'email/welcome.php',
				'user'   => $user, 
				'url'    => $url
			), true);
		

		return $this->_sendEmail($user['userEmail'], sprintf($this->CI->lang->line('Welcome to %s'), config_item('siteName')), $message);
	}
	
	function sendEmailToResetPassword($params = array()) {
		if(empty($params) || !is_array($params)){
			return false;
		}
		$this->CI->load->model('Users_Model');
		
		$user = $this->CI->Users_Model->get($params['userId'], false);

		$userEmail          = $user['userEmail'];		
		$resetPasswordKey   = $user['resetPasswordKey'];
		$url                = base_url('resetPassword?key='.$resetPasswordKey);
		$message            = $this->CI->load->view('pageEmail',
			array(
				'view'  => 'email/resetPassword.php',
				'user'  => $user,
				'url'   => $url,
			),
			true);
		
		return $this->_sendEmail($userEmail, sprintf($this->CI->lang->line('Reset password in %s'), config_item('siteName')), $message);
	}
	
	function sendEmailToChangeEmail($params = array()) {
		if(empty($params) || !is_array($params)){
			return false;
		}
		
		$this->CI->load->model('Users_Model');
		$userId          = $params['userId'];
		$user            = $this->CI->Users_Model->get($userId, false);
		$userEmail       = $user['confirmEmailValue'];
		$confirmEmailKey = $user['confirmEmailKey'];
		$url             = base_url('confirmEmail?key='.$confirmEmailKey);
		$message         = $this->CI->load->view('pageEmail',
			array(
				'view'   => 'email/changeEmail.php',
				'user'   => $user, 
				'url'    => $url
			), true);

		return $this->_sendEmail($userEmail, sprintf($this->CI->lang->line('Change email in %s'), config_item('siteName')), $message);
	}
	
	function sendFeedback($params = array()) {
		if(empty($params) || !is_array($params)){
			return false;
		}
		
		$message = $this->CI->load->view('pageEmail',
			array(
				'view'                  => 'email/feedback.php',
				'feedbackUserName'      => element('feedbackUserName', $params),
				'feedbackUserEmail'     => element('feedbackUserEmail', $params),
				'feedbackDesc'          => element('feedbackDesc', $params),
				'feedbackDate'          => element('feedbackDate', $params),
				'url'                   => null,
			),
			true);
		
		return $this->_sendEmail(config_item('emailDebug'), 'Comentario de '.element('feedbackUserName', $params), $message, null, array('email' => element('feedbackUserEmail', $params), 'name' => element('feedbackUserName', $params)));
	}

	function shareByEmail($params = array()) {
		if(empty($params) || !is_array($params)){
			return false;
		}

		$this->CI->load->model(array('Users_Model', 'Entries_Model'));
		
		$userId                = $params['userId'];
		$entryId               = $params['entryId'];
		$userFriendEmail       = $params['userFriendEmail'];
		$sendMeCopy            = $params['sendMeCopy'];
		$shareByEmailComment   = $params['shareByEmailComment'];
		$entry                 = $this->CI->Entries_Model->get($entryId, false);
		$user                  = $this->CI->Users_Model->get($userId);
		$userFullName          = $user['userFirstName'].' '.$user['userLastName'];

		if ($entry['entryAuthor'] == '') {
			$entryOrigin = sprintf($this->CI->lang->line('From %s'), '<a href="'.$entry['entryUrl'].'" >' . $entry['feedName'] . '</a>');
		}
		else {
			$entryOrigin = sprintf($this->CI->lang->line('From %s by %s'), '<a href="'.$entry['entryUrl'].'" >' . $entry['feedName'] . '</a>', $entry['entryAuthor']);
		}

		$message = $this->CI->load->view('pageEmail',
			array(
				'view'                  => 'email/shareEntry.php',
				'shareByEmailComment'   => $shareByEmailComment,
				'userFullName'          => $userFullName,
				'entry'                 => $entry,
				'entryOrigin'           => $entryOrigin,
				'url'                   => null,
			),
			true);
		//echo $message; die;	

		$this->CI->email->from(config_item('emailFrom'), config_item('siteName'));
		$this->_addEmailTo($userFriendEmail); 
		$this->CI->email->reply_To($user['userEmail'], $userFullName);
		if ($sendMeCopy == true) {
			$this->CI->email->cc($user['userEmail']); 
		}
		$this->CI->email->subject($entry['entryTitle']);
		$this->CI->email->message($message);
		if($this->CI->email->send()){
			return true;
		}
		return false;
		//echo $this->CI->email->print_debugger();	die;
	}
}
