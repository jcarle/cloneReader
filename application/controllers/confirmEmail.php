<?php
class ConfirmEmail extends CI_Controller {

	function __construct() {
		parent::__construct();	
	}
	
	function index() {
		if (! $this->safety->allowByControllerName('profile/edit') ) { return errorForbidden(); }
		
		$this->load->model(array('Users_Model', 'Countries_Model'));
		
		$changeEmailKey = $this->input->get('key');
		$userId 		= $this->session->userdata('userId');
		$user 			= $this->Users_Model->getUserByUserIdAndChangeEmailKey($userId, $changeEmailKey);
		if (empty($user)) {
			return error404();
		}
		
		$this->Users_Model->confirmEmail($userId);

		$this->load->view('pageHtml', array(
			'view'		=> 'message', 
			'title'		=> $this->lang->line('Change email'),
			'message'	=> $this->lang->line('Your email has been updated')
		));	
	}
}
