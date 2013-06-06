<?php 
class Login extends CI_Controller {

	function __construct() {
		parent::__construct();	
		
		$this->load->model('Users_Model');
	}
	
	function index() {
		if (! $this->safety->allowByControllerName(__METHOD__) ) { redirect('error/notAuthorized'); }
		
		$form = array(
			'frmId'				=> 'frmLogin',
			'messages' 			=> getRulesMessages(),
			'showBtnBack'		=> false,
			'btnSubmitValue'	=>	'Ingresar',	
			'fields'			=> array(
				'email' => array(
					'type'	=> 'text',
					'label'	=> 'Email', 
					'value'	=> set_value('email')
				),
				'password' => array(
					'type'	=> 'password',
					'label'	=> 'Contraseña', 
					'value'	=> set_value('password')
				),
				'link'	=> array(
					'type'	=> 'link',
					'label'	=> 'reset password', 
					'value'	=> 'users/forgotPassword'				
				)
			)
		);
		
		$form['rules'] = array( 
			array(
				'field' => 'email',
				'label' => $form['fields']['email']['label'],
				'rules' => 'required|valid_email|callback__login'
			),
			array(				 
				'field' => 'password',
				'label' => $form['fields']['password']['label'],
				'rules' => 'required'
			)
		);		
		
		$this->form_validation->set_rules($form['rules']);
		$this->form_validation->set_message($form['messages']);


		if ($this->input->is_ajax_request()) {
			$code = $this->form_validation->run(); 
			return $this->load->view('ajax', array(
				'code'		=> $code, 
				'result' 	=> ($code == false ? validation_errors() : array('goToUrl' => base_url('home'))) 
			));
		}			
					
		if ($this->form_validation->run() == FALSE) {
			return $this->load->view('includes/template', array(
				'view'		=> 'login', 
				'title'		=> 'Ingresar',
				'form'		=> $form,
			));
		}
		
		redirect('home');
	}
	

	function _login() {
		$query = $this->Users_Model->login($this->input->post('email'), $this->input->post('password'));

		if ($query->num_rows() == null) {
			return false;
		}

		$row = $query->row();
		
		$this->session->set_userdata(array(
			'userId'  		=> $row->userId,
			'userEmail'		=> $row->userEmail,
			'userFullName' 	=> $row->userFirstsName.' '.$row->userLastName
		));		
		
		return true;
	}
	
	function loginFB() {
		$user = $this->Users_Model->loginFB($this->input->post('userEmail'), $this->input->post('userLastName'), $this->input->post('userFirstsName'), $this->input->post('oauth_uid'), 'facebook');

		if ($user == null) {
			return $this->load->view('ajax', array(
				'code'		=> false, 
				'result' 	=> 'error!' 
			));
		}

		$this->session->set_userdata(array(
			'userId'  		=> $user->userId,
			'userEmail'		=> $user->userEmail,
			'userFullName' 	=> $user->userFirstsName.' '.$user->userLastName
		));		
		
		return $this->load->view('ajax', array(
			'code'		=> true, 
			'result' 	=> '' 
		));
	}
}
