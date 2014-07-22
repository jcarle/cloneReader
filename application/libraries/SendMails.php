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
		
		$this->CI->email->to('jcarle@gmail.com, pilchoide@gmail.com');  // TODO: desharckodear!
	}
	
	function sendEmailToResetPassword($params = array()) {
		if(empty($params) || !is_array($params)){
			return false;
		}
		$this->CI->load->model('Users_Model');
		$user 				= $this->CI->Users_Model->getByUserEmail($params['userEmail']);
		$resetPasswordKey 	= random_string('alnum', 20);
		$url 				= base_url('resetPassword?key='.$resetPasswordKey);
		$message 			= $this->CI->load->view('pageEmail',
			array(
				'view'  => 'email/resetPassword.php',
				'user'  => $user,
				'url'   => $url,
			),
			true);
		
		$this->CI->Users_Model->updateResetPasswordKey($user['userId'], $resetPasswordKey);
		$this->CI->email->from(config_item('emailFrom'), config_item('siteName'));
		$this->_addEmailTo($user['userEmail']); 
		$this->CI->email->subject(config_item('siteName').' - '.$this->CI->lang->line('Reset password'));
		$this->CI->email->message($message);
		if($this->CI->email->send()){
			return true;
		}
		return false;
		// echo $this->CI->email->print_debugger();	die;	
	}
	
	function sendEmailToChangeEmail($params = array()) {
		if(empty($params) || !is_array($params)){
			return false;
		}
		
		$this->CI->load->model('Users_Model');
		$userId 		= $params['userId'];
		$userEmail 		= $params['userEmail'];
		$user 			= $this->CI->Users_Model->get($userId);
		$changeEmailKey = random_string('alnum', 20);
		$url 			= base_url('confirmEmail?key='.$changeEmailKey);
		$message 		= $this->CI->load->view('pageEmail',
			array(
				'view'   => 'email/changeEmail.php',
				'user'   => $user, 
				'url'    => $url
			), true);
		
		$this->CI->Users_Model->updateChangeEmailKey($userId, $userEmail, $changeEmailKey);

		$this->CI->email->from(config_item('emailFrom'), config_item('siteName'));
		$this->_addEmailTo($userEmail); 
		$this->CI->email->subject(config_item('siteName').' - '.$this->CI->lang->line('Change email'));
		$this->CI->email->message($message);
		if($this->CI->email->send()){
			return true;
		}
		return false;
		//echo $this->CI->email->print_debugger();	die;	
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
			),
			true);
		
		$this->CI->email->from(element('feedbackUserEmail', $params), element('feedbackUserName', $params));
		$this->_addEmailTo('jcarle@gmail.com, pilchoide@gmail.com');  // TODO: desharckodear!
		$this->CI->email->subject(config_item('siteName').' - Comentario de '.element('feedbackUserName', $params));
		$this->CI->email->message($message);
		if($this->CI->email->send()){
			return true;
		}
		return false;
		//echo $this->CI->email->print_debugger();	die;
	}

}
